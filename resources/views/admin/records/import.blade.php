@extends('layouts.admin')

@section('title', 'Import '.$module['name'])

@section('content')
    @php
        $columns = $moduleKey === 'contacts'
            ? ['title', 'type', ...array_column($module['fields'], 'name')]
            : ['title', ...array_column($module['fields'], 'name')];
        $backModule = $moduleKey === 'contacts' ? 'customers' : $moduleKey;
    @endphp
    @if ($moduleKey === 'products')
        <section class="page-heading product-import-heading">
            <h1>Import Products</h1>
        </section>

        <form class="product-import-card" method="POST" action="{{ route('admin.modules.import', $moduleKey) }}" enctype="multipart/form-data">
            @csrf
            <div class="product-import-row">
                <div class="admin-field">
                    <label for="file">File To Import:</label>
                    <input id="file" name="file" type="file" accept=".csv,.txt,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required aria-invalid="{{ $errors->has('file') ? 'true' : 'false' }}">
                    @error('file')<span class="admin-field-error">{{ $message }}</span>@enderror
                    <small class="field-help">CSV or Excel (.xlsx), up to 10 MB. Use the template file headers.</small>
                </div>
                <button class="product-import-submit" type="submit">Submit</button>
            </div>
            <a class="product-import-template" href="{{ route('admin.modules.products.import-template') }}">
                <span aria-hidden="true">↓</span> Download template file
            </a>
        </form>
    @else
    <section class="page-heading">
        <div>
            <!-- <span class="form-kicker">FILE IMPORT</span> -->
            <h1>{{ match ($moduleKey) { 'products' => 'Import Product', 'contacts' => 'Import Contacts', default => 'Import Opening Stock' } }}</h1>
            <p>
                @if ($moduleKey === 'contacts')
                    Upload a CSV or Excel workbook with a type column set to customer or supplier. Each contact will be added to its matching list.
                @else
                    Upload a CSV file or Excel workbook. Imported rows will be added to your {{ strtolower($module['name']) }} records.
                @endif
            </p>
        </div>
        <a class="admin-secondary-button" href="{{ route('admin.modules.index', $backModule) }}">Back to records</a>
    </section>

    <form class="admin-form-card" method="POST" action="{{ route('admin.modules.import', $moduleKey) }}" enctype="multipart/form-data">
        @csrf
        <div class="form-card-heading">
            <div>
                <h2>Select a file</h2>
                <p>Choose a CSV file or Excel workbook using the column layout below.</p>
            </div>
        </div>
        <div class="admin-field admin-field-full">
            <label for="file">CSV or Excel file <span aria-hidden="true">*</span></label>
            <input id="file" name="file" type="file" accept=".csv,.txt,.xlsx,text/csv,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
            <small class="field-help">Maximum file size: 10 MB. The first row of the first worksheet must contain column names.</small>
        </div>
        <div class="csv-format-help">
            <strong>File format</strong>
            <p>{{ $moduleKey === 'contacts' ? 'Include required title and type columns. Other columns are optional.' : 'Include a title column. Other columns are optional; use the field names listed below.' }}</p>
            <code>{{ implode(',', $columns) }}</code>
        </div>
        <div class="admin-form-actions">
            <a class="admin-secondary-button" href="{{ route('admin.modules.index', $backModule) }}">Cancel</a>
            <button class="admin-primary-button" type="submit">Import records</button>
        </div>
    </form>
    @endif
@endsection
