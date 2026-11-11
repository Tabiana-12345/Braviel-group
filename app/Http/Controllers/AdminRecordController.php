<?php

namespace App\Http\Controllers;

use App\Models\AdminRecord;
use App\Models\AuditLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class AdminRecordController extends Controller
{
    public function index(string $module): View
    {
        $definition = $this->module($module);

        return view('admin.records.index', [
            'module' => $definition,
            'moduleKey' => $module,
            'records' => AdminRecord::where('module', $module)->latest()->paginate(15),
        ]);
    }

    public function create(string $module): View
    {
        $definition = $this->module($module);

        return view('admin.records.form', [
            'module' => $definition,
            'moduleKey' => $module,
            'record' => null,
            'fieldOptions' => $this->fieldOptions($definition),
        ]);
    }

    public function importForm(string $module): View
    {
        abort_unless(in_array($module, ['products', 'inventory', 'contacts'], true), 404);

        $definition = $module === 'contacts'
            ? [
                'name' => 'Contacts',
                'description' => 'Import customer and supplier records',
                'fields' => config('admin_modules.customers.fields'),
            ]
            : $this->module($module);

        return view('admin.records.import', [
            'module' => $definition,
            'moduleKey' => $module,
        ]);
    }

    public function productImportTemplate(): \Symfony\Component\HttpFoundation\Response
    {
        $columns = ['title', ...array_column($this->module('products')['fields'], 'name')];
        $stream = fopen('php://temp', 'r+');
        if ($stream === false) {
            throw new \RuntimeException('Unable to generate the product import template.');
        }

        fputcsv($stream, $columns);
        rewind($stream);
        $content = stream_get_contents($stream);
        fclose($stream);
        if ($content === false) {
            throw new \RuntimeException('Unable to read the generated product import template.');
        }

        return response($content)
            ->header('Content-Type', 'text/csv; charset=UTF-8')
            ->header('Content-Disposition', 'attachment; filename="product-import-template.csv"');
    }

    public function import(Request $request, string $module): RedirectResponse
    {
        abort_unless(in_array($module, ['products', 'inventory', 'contacts'], true), 404);

        $validated = $request->validate([
            'file' => ['required', 'file', 'extensions:csv,txt,xlsx', 'max:10240'],
        ]);
        if ($module === 'contacts') {
            return $this->importContacts($request, $validated['file']);
        }

        $definition = $this->module($module);
        try {
            $handle = $this->openTabularStream($validated['file']);
        } catch (\UnexpectedValueException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        try {
            $headers = fgetcsv($handle);
            if (! is_array($headers) || $headers === []) {
                return back()->withErrors(['file' => 'The file is empty or has no header row.']);
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            $allowedHeaders = ['title', ...array_column($definition['fields'], 'name')];
            $headers = array_map(static fn ($header) => trim((string) $header), $headers);
            if (count($headers) !== count(array_unique($headers)) || ! in_array('title', $headers, true)) {
                return back()->withErrors(['file' => 'The file must have a unique header row that includes title.']);
            }
            if (array_diff($headers, $allowedHeaders) !== []) {
                return back()->withErrors(['file' => 'The file contains a column that is not supported by this module.']);
            }

            $rows = [];
            $line = 1;
            while (($values = fgetcsv($handle)) !== false) {
                $line++;
                if ($values === [null] || $values === []) {
                    continue;
                }
                if (count($values) !== count($headers)) {
                    return back()->withErrors(['file' => "CSV row {$line} has a different number of values than the header."]);
                }

                $row = array_combine($headers, $values);
                $input = [
                    'title' => $row['title'] ?? null,
                    'data' => array_diff_key($row, ['title' => true]),
                ];
                $validator = Validator::make($input, $this->rules($definition));
                if ($validator->fails()) {
                    return back()->withErrors(['file' => "CSV row {$line}: ".$validator->errors()->first()]);
                }
                $rows[] = $validator->validated();
            }
        } finally {
            fclose($handle);
        }

        if ($rows === []) {
            return back()->withErrors(['file' => 'The file contains no records to import.']);
        }

        DB::transaction(function () use ($request, $module, $definition, $rows): void {
            foreach ($rows as $row) {
                $data = $this->recordData($row, $definition);
                $record = AdminRecord::create([
                    'module' => $module,
                    'title' => $row['title'],
                    'data' => $data,
                    'created_by' => $request->user()->id,
                ]);
                $this->audit($request, $module, 'imported', $record->id, "Imported {$record->title}.", ['after' => $record->only(['title', 'data'])]);
            }
        });

        return redirect()->route('admin.modules.index', $module)
            ->with('status', count($rows).' records imported.');
    }

    private function importContacts(Request $request, UploadedFile $file): RedirectResponse
    {
        try {
            $handle = $this->openTabularStream($file);
        } catch (\UnexpectedValueException $exception) {
            return back()->withErrors(['file' => $exception->getMessage()]);
        }

        try {
            $headers = fgetcsv($handle);
            if (! is_array($headers) || $headers === []) {
                return back()->withErrors(['file' => 'The file is empty or has no header row.']);
            }

            $headers[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $headers[0]);
            $headers = array_map(static fn ($header) => trim((string) $header), $headers);
            $allowedHeaders = ['title', 'type', 'contact_name', 'email', 'phone', 'address'];
            if (count($headers) !== count(array_unique($headers))
                || ! in_array('title', $headers, true)
                || ! in_array('type', $headers, true)
                || array_diff($headers, $allowedHeaders) !== []) {
                return back()->withErrors(['file' => 'Contacts file needs unique title and type columns. Supported columns: title, type, contact_name, email, phone, address.']);
            }

            $rows = [];
            $line = 1;
            while (($values = fgetcsv($handle)) !== false) {
                $line++;
                if ($values === [null] || $values === []) {
                    continue;
                }
                if (count($values) !== count($headers)) {
                    return back()->withErrors(['file' => "Row {$line} has a different number of values than the header."]);
                }

                $row = array_combine($headers, $values);
                $type = strtolower(trim((string) ($row['type'] ?? '')));
                if (! in_array($type, ['customer', 'supplier'], true)) {
                    return back()->withErrors(['file' => "Row {$line}: type must be customer or supplier."]);
                }

                $targetModule = $type === 'customer' ? 'customers' : 'suppliers';
                $input = [
                    'title' => $row['title'] ?? null,
                    'data' => array_diff_key($row, ['title' => true, 'type' => true]),
                ];
                $validator = Validator::make($input, $this->rules($this->module($targetModule)));
                if ($validator->fails()) {
                    return back()->withErrors(['file' => "Row {$line}: ".$validator->errors()->first()]);
                }
                $rows[] = ['module' => $targetModule, 'record' => $validator->validated()];
            }
        } finally {
            fclose($handle);
        }

        if ($rows === []) {
            return back()->withErrors(['file' => 'The file contains no contacts to import.']);
        }

        DB::transaction(function () use ($request, $rows): void {
            foreach ($rows as $row) {
                $definition = $this->module($row['module']);
                $recordData = $this->recordData($row['record'], $definition);
                $record = AdminRecord::create([
                    'module' => $row['module'],
                    'title' => $row['record']['title'],
                    'data' => $recordData,
                    'created_by' => $request->user()->id,
                ]);
                $this->audit($request, $row['module'], 'imported', $record->id, "Imported {$record->title}.", ['after' => $record->only(['title', 'data'])]);
            }
        });

        return redirect()->route('admin.modules.index', 'customers')
            ->with('status', count($rows).' contacts imported.');
    }

    private function openTabularStream(UploadedFile $file)
    {
        if (strtolower($file->getClientOriginalExtension()) !== 'xlsx') {
            $handle = fopen($file->getRealPath(), 'r');
            if ($handle === false) {
                throw new \RuntimeException('The uploaded CSV file could not be opened.');
            }

            return $handle;
        }

        $rows = $this->readXlsxRows($file->getRealPath());
        $handle = fopen('php://temp/maxmemory:5242880', 'r+');
        if ($handle === false) {
            throw new \RuntimeException('The temporary spreadsheet stream could not be opened.');
        }
        foreach ($rows as $row) {
            if (fputcsv($handle, $row) === false) {
                fclose($handle);
                throw new \RuntimeException('A spreadsheet row could not be converted for import.');
            }
        }
        rewind($handle);

        return $handle;
    }

    /**
     * Read the first worksheet from an Office Open XML workbook.
     *
     * @return list<list<string>>
     */
    private function readXlsxRows(string $path): array
    {
        $zip = new \ZipArchive;
        if ($zip->open($path) !== true) {
            throw new \UnexpectedValueException('The Excel file is invalid or damaged.');
        }

        try {
            if ($zip->numFiles > 1000) {
                throw new \UnexpectedValueException('The Excel workbook contains too many files.');
            }
            $uncompressedSize = 0;
            for ($index = 0; $index < $zip->numFiles; $index++) {
                $entry = $zip->statIndex($index);
                if ($entry === false) {
                    throw new \UnexpectedValueException('The Excel workbook could not be read.');
                }
                $uncompressedSize += (int) $entry['size'];
                if ($uncompressedSize > 52428800) {
                    throw new \UnexpectedValueException('The uncompressed Excel workbook exceeds the 50 MB limit.');
                }
            }

            $spreadsheetNamespace = 'http://schemas.openxmlformats.org/spreadsheetml/2006/main';
            $relationshipNamespace = 'http://schemas.openxmlformats.org/officeDocument/2006/relationships';
            $packageNamespace = 'http://schemas.openxmlformats.org/package/2006/relationships';
            $workbook = $this->parseSpreadsheetXml($zip, 'xl/workbook.xml');
            $workbookData = $workbook->children($spreadsheetNamespace);
            if (! isset($workbookData->sheets->sheet[0])) {
                throw new \UnexpectedValueException('The Excel workbook does not contain a worksheet.');
            }
            $sheet = $workbookData->sheets->sheet[0];
            $relationshipId = (string) $sheet->attributes($relationshipNamespace)['id'];
            $relationships = $this->parseSpreadsheetXml($zip, 'xl/_rels/workbook.xml.rels');
            $sheetTarget = null;
            foreach ($relationships->children($packageNamespace)->Relationship as $relationship) {
                $relationshipAttributes = $relationship->attributes('');
                if ((string) $relationshipAttributes['Id'] === $relationshipId) {
                    if ((string) $relationshipAttributes['TargetMode'] === 'External') {
                        throw new \UnexpectedValueException('The first worksheet points outside the Excel workbook.');
                    }
                    $sheetTarget = $this->resolveWorkbookTarget((string) $relationshipAttributes['Target']);
                    break;
                }
            }
            if ($sheetTarget === null) {
                throw new \UnexpectedValueException('The first worksheet could not be found in the Excel workbook.');
            }

            $sharedStrings = [];
            if ($zip->locateName('xl/sharedStrings.xml') !== false) {
                $sharedStringXml = $this->parseSpreadsheetXml($zip, 'xl/sharedStrings.xml');
                foreach ($sharedStringXml->children($spreadsheetNamespace)->si as $sharedString) {
                    $sharedStrings[] = $this->spreadsheetText($sharedString, $spreadsheetNamespace);
                }
            }

            $worksheet = $this->parseSpreadsheetXml($zip, $sheetTarget);
            $sheetData = $worksheet->children($spreadsheetNamespace)->sheetData;
            if (! isset($sheetData)) {
                throw new \UnexpectedValueException('The first worksheet contains no tabular data.');
            }

            $rows = [];
            foreach ($sheetData->row as $row) {
                $values = [];
                foreach ($row->c as $cell) {
                    $cellAttributes = $cell->attributes('');
                    $reference = (string) $cellAttributes['r'];
                    if (preg_match('/^([A-Z]+)\d+$/i', $reference, $matches) !== 1) {
                        throw new \UnexpectedValueException('The Excel workbook contains an invalid cell reference.');
                    }
                    $column = 0;
                    foreach (str_split(strtoupper($matches[1])) as $letter) {
                        $column = ($column * 26) + ord($letter) - 64;
                    }
                    if ($column < 1 || $column > 16384) {
                        throw new \UnexpectedValueException('The Excel workbook contains a cell outside the supported columns.');
                    }

                    $cellData = $cell->children($spreadsheetNamespace);
                    $type = (string) $cellAttributes['t'];
                    if ($type === 'inlineStr') {
                        $value = $this->spreadsheetText($cellData->is, $spreadsheetNamespace);
                    } else {
                        $value = isset($cellData->v) ? (string) $cellData->v : '';
                        if ($type === 's') {
                            $sharedStringIndex = filter_var($value, FILTER_VALIDATE_INT);
                            if ($sharedStringIndex === false || ! array_key_exists($sharedStringIndex, $sharedStrings)) {
                                throw new \UnexpectedValueException('The Excel workbook contains an invalid shared string reference.');
                            }
                            $value = $sharedStrings[$sharedStringIndex];
                        } elseif ($type === 'b') {
                            $value = $value === '1' ? 'TRUE' : 'FALSE';
                        }
                    }
                    $values[$column - 1] = $value;
                }

                if ($values === []) {
                    continue;
                }
                ksort($values);
                $lastColumn = max(array_keys($values));
                $normalized = array_fill(0, $lastColumn + 1, '');
                foreach ($values as $column => $value) {
                    $normalized[$column] = $value;
                }
                if (array_filter($normalized, static fn (string $value): bool => $value !== '') !== []) {
                    $rows[] = $normalized;
                }
            }

            if ($rows === []) {
                throw new \UnexpectedValueException('The first worksheet contains no rows to import.');
            }

            return $rows;
        } finally {
            $zip->close();
        }
    }

    private function parseSpreadsheetXml(\ZipArchive $zip, string $entry): \SimpleXMLElement
    {
        $contents = $zip->getFromName($entry);
        if (! is_string($contents)) {
            throw new \UnexpectedValueException("The Excel workbook is missing {$entry}.");
        }

        $previousErrorSetting = libxml_use_internal_errors(true);
        try {
            $xml = simplexml_load_string($contents, \SimpleXMLElement::class, LIBXML_NONET | LIBXML_COMPACT);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorSetting);
        }
        if (! $xml instanceof \SimpleXMLElement) {
            throw new \UnexpectedValueException("The Excel workbook contains invalid XML in {$entry}.");
        }

        return $xml;
    }

    private function spreadsheetText(\SimpleXMLElement $element, string $namespace): string
    {
        $text = '';
        foreach ($element->children($namespace) as $child) {
            $text .= $child->getName() === 't'
                ? (string) $child
                : $this->spreadsheetText($child, $namespace);
        }

        return $text;
    }

    private function resolveWorkbookTarget(string $target): string
    {
        $path = str_starts_with($target, '/') ? ltrim($target, '/') : 'xl/'.$target;
        $segments = [];
        foreach (explode('/', $path) as $segment) {
            if ($segment === '' || $segment === '.') {
                continue;
            }
            if ($segment === '..') {
                array_pop($segments);
                continue;
            }
            $segments[] = $segment;
        }
        $resolved = implode('/', $segments);
        if (! str_starts_with($resolved, 'xl/')) {
            throw new \UnexpectedValueException('The first worksheet path is not valid.');
        }

        return $resolved;
    }

    public function store(Request $request, string $module): RedirectResponse
    {
        $definition = $this->module($module);
        $validated = $request->validate($this->rules($definition));
        $data = $this->recordData($validated, $definition);

        DB::transaction(function () use ($request, $module, $validated, $data): void {
            $record = AdminRecord::create([
                'module' => $module,
                'title' => $validated['title'],
                'data' => $data,
                'created_by' => $request->user()->id,
            ]);

            $this->audit($request, $module, 'created', $record->id, "Created {$record->title}.", ['after' => $record->only(['title', 'data'])]);
        });

        return redirect()->route('admin.modules.index', $module)->with('status', 'Record added.');
    }

    public function edit(string $module, int $record): View
    {
        $definition = $this->module($module);

        return view('admin.records.form', [
            'module' => $definition,
            'moduleKey' => $module,
            'record' => $entry = $this->findRecord($module, $record),
            'fieldOptions' => $this->fieldOptions($definition, $entry->data ?? []),
        ]);
    }

    public function update(Request $request, string $module, int $record): RedirectResponse
    {
        $definition = $this->module($module);
        $entry = $this->findRecord($module, $record);
        $validated = $request->validate($this->rules($definition, $entry->data ?? []));
        $data = $this->recordData($validated, $definition);

        DB::transaction(function () use ($request, $module, $entry, $validated, $data): void {
            $before = $entry->only(['title', 'data']);
            $entry->update(['title' => $validated['title'], 'data' => $data]);

            $this->audit($request, $module, 'updated', $entry->id, "Updated {$entry->title}.", [
                'before' => $before,
                'after' => $entry->only(['title', 'data']),
            ]);
        });

        return redirect()->route('admin.modules.index', $module)->with('status', 'Record updated.');
    }

    public function destroy(Request $request, string $module, int $record): RedirectResponse
    {
        $this->module($module);

        DB::transaction(function () use ($request, $module, $record): void {
            $entry = $this->findRecord($module, $record);
            $snapshot = $entry->only(['title', 'data']);
            $entry->delete();

            $this->audit($request, $module, 'deleted', $record, "Deleted {$snapshot['title']}.", ['before' => $snapshot]);
        });

        return redirect()->route('admin.modules.index', $module)->with('status', 'Record deleted.');
    }

    private function module(string $key): array
    {
        $modules = config('admin_modules');
        abort_unless(isset($modules[$key]), 404);

        return $modules[$key];
    }

    private function rules(array $module, array $existingData = []): array
    {
        $rules = ['title' => ['required', 'string', 'max:255']];

        foreach ($module['fields'] as $field) {
            $requiredRule = ($field['required'] ?? false) ? 'required' : 'nullable';

            if ($field['type'] === 'select') {
                $allowed = AdminRecord::where('module', $field['source'])
                    ->pluck('title')
                    ->all();
                if (filled($existingData[$field['name']] ?? null)) {
                    $allowed[] = $existingData[$field['name']];
                }
                $rules["data.{$field['name']}"] = [$requiredRule, Rule::in(array_unique($allowed))];
                continue;
            }

            $typeRule = match ($field['type']) {
                'email' => 'email',
                'number' => 'numeric',
                'date' => 'date',
                'datetime-local' => 'date',
                default => 'string',
            };

            $rules["data.{$field['name']}"] = $field['type'] === 'number'
                ? [$requiredRule, $typeRule]
                : [$requiredRule, $typeRule, 'max:5000'];
        }

        return $rules;
    }

    private function fieldOptions(array $module, array $existingData = []): array
    {
        $options = [];
        foreach ($module['fields'] as $field) {
            if ($field['type'] !== 'select') {
                continue;
            }

            $records = AdminRecord::where('module', $field['source'])->orderBy('title')->get();
            $options[$field['name']] = $records->mapWithKeys(function (AdminRecord $record) use ($field): array {
                $label = $record->title;
                if ($field['source'] === 'units' && filled($record->data['abbreviation'] ?? null)) {
                    $label .= ' ('.$record->data['abbreviation'].')';
                } elseif ($field['source'] === 'brands' && filled($record->data['short_name'] ?? null)) {
                    $label .= ' ('.$record->data['short_name'].')';
                }

                return [$record->title => $label];
            })->all();

            $existing = $existingData[$field['name']] ?? null;
            if (filled($existing) && ! array_key_exists($existing, $options[$field['name']])) {
                $options[$field['name']][$existing] = $existing.' (current value)';
            }
        }

        return $options;
    }

    private function recordData(array $validated, array $module): array
    {
        $submitted = $validated['data'] ?? [];
        $allowed = array_column($module['fields'], 'name');

        return array_intersect_key($submitted, array_flip($allowed));
    }

    private function findRecord(string $module, int $id): AdminRecord
    {
        return AdminRecord::where('module', $module)->findOrFail($id);
    }

    private function audit(Request $request, string $module, string $action, int $recordId, string $description, array $changes): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'module' => $module,
            'action' => $action,
            'record_id' => $recordId,
            'description' => $description,
            'changes' => $changes,
        ]);
    }
}
