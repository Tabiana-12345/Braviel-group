<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
// use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:255'],
            'page_size' => ['nullable', 'integer', Rule::in([10, 25, 50, 100])],
        ]);
        $users = $this->filteredUsers($validated['search'] ?? '')
            ->paginate((int) ($validated['page_size'] ?? 10))
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'search' => $validated['search'] ?? '',
            'pageSize' => (int) ($validated['page_size'] ?? 10),
        ]);
    }

    public function show(User $user): View
    {
        return view('admin.users.show', ['managedUser' => $user->load('roleRecord')]);
    }

    public function export(Request $request, string $format): StreamedResponse
    {
        abort_unless(in_array($format, ['csv', 'excel'], true), 404);
        $validated = $request->validate(['search' => ['nullable', 'string', 'max:255']]);
        $users = $this->filteredUsers($validated['search'] ?? '')->get();
        $headers = ['Username', 'Name', 'Role', 'Email'];

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($users, $headers): void {
                $output = fopen('php://output', 'w');
                if ($output === false) {
                    throw new \RuntimeException('Unable to open the export stream.');
                }
                fputcsv($output, $headers);
                foreach ($users as $user) {
                    fputcsv($output, array_map($this->safeCsvValue(...), [$user->username, $user->name, $user->role_name, $user->email]));
                }
                fclose($output);
            }, 'users.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
        }

        return response()->streamDownload(function () use ($users, $headers): void {
            $xml = static fn (string $value): string => htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
            echo '<?xml version="1.0"?>';
            echo '<?mso-application progid="Excel.Sheet"?>';
            echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"><Worksheet ss:Name="Users"><Table>';
            foreach (array_merge([$headers], $users->map(fn ($user): array => [$user->username, $user->name, $user->role_name, $user->email])->all()) as $row) {
                echo '<Row>';
                foreach ($row as $value) {
                    echo '<Cell><Data ss:Type="String">'.$xml((string) $value).'</Data></Cell>';
                }
                echo '</Row>';
            }
            echo '</Table></Worksheet></Workbook>';
        }, 'users.xml', ['Content-Type' => 'application/vnd.ms-excel; charset=UTF-8']);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'managedUser' => null,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash:ascii', 'unique:users,username'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'exists:roles,key'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($request, $validated): void {
            $user = User::create($validated);
            $this->audit($request, 'created', $user, ['role' => $user->role]);
        });

        return redirect()->route('admin.users.index')->with('status', 'User added.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'managedUser' => $user,
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'max:100', 'alpha_dash:ascii', 'unique:users,username,'.$user->id],
            'email' => ['required', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'role' => ['required', 'exists:roles,key'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        if ($user->isAdmin() && $validated['role'] !== 'admin') {
            $this->ensureAnotherAdministratorExists($user);
        }

        DB::transaction(function () use ($request, $user, $validated): void {
            $before = ['name' => $user->name, 'username' => $user->username, 'email' => $user->email, 'role' => $user->role];
            $attributes = collect($validated)->except('password')->all();
            if (filled($validated['password'] ?? null)) {
                $attributes['password'] = $validated['password'];
            }
            $user->update($attributes);
            $this->audit($request, 'updated', $user, ['before' => $before, 'after' => ['name' => $user->name, 'username' => $user->username, 'email' => $user->email, 'role' => $user->role]]);
        });

        return redirect()->route('admin.users.index')->with('status', 'User updated.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_if($request->user()->is($user), 422, 'You cannot delete your own account.');
        if ($user->isAdmin()) {
            $this->ensureAnotherAdministratorExists($user);
        }

        DB::transaction(function () use ($request, $user): void {
            $userSnapshot = ['name' => $user->name, 'username' => $user->username, 'email' => $user->email, 'role' => $user->role];
            $this->audit($request, 'deleted', $user, ['before' => $userSnapshot]);
            $user->delete();
        });

        return redirect()->route('admin.users.index')->with('status', 'User deleted.');
    }

    private function ensureAnotherAdministratorExists(User $user): void
    {
        abort_if(
            User::where('role', 'admin')->where('id', '!=', $user->id)->count() === 0,
            422,
            'The last administrator cannot be demoted or deleted.'
        );
    }

    private function filteredUsers(string $search): Builder
    {
        $query = User::query()->with('roleRecord');

        if ($search !== '') {
            $query->where(function (Builder $query) use ($search): void {
                $query->where('username', 'like', '%'.$search.'%')
                    ->orWhere('name', 'like', '%'.$search.'%')
                    ->orWhere('role', 'like', '%'.$search.'%')
                    ->orWhere('email', 'like', '%'.$search.'%')
                    ->orWhereHas('roleRecord', fn (Builder $roleQuery) => $roleQuery->where('name', 'like', '%'.$search.'%'));
            });
        }

        $query->orderBy('name');
        $query->orderBy('id');

        return $query;
    }

    private function safeCsvValue(string $value): string
    {
        return preg_match('/^[=+\-@\t\r]/', $value) === 1 ? "'".$value : $value;
    }

    private function audit(Request $request, string $action, User $user, array $changes): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'module' => 'users',
            'action' => $action,
            'record_id' => $user->id,
            'description' => ucfirst($action)." user {$user->email}.",
            'changes' => $changes,
        ]);
    }
}
