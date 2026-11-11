<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminRoleController extends Controller
{
    public function index(): View
    {
        return view('admin.roles.index', [
            'roles' => Role::orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.roles.form', [
            'role' => null,
            'permissionGroups' => config('admin_permissions'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name'],
            'permissions' => ['sometimes', 'array'],
        ]);
        $key = Str::slug($validated['name'], '_');
        if ($key === '') {
            return back()->withInput()->withErrors(['name' => 'Enter a role name containing letters or numbers.']);
        }
        $baseKey = $key;
        $suffix = 2;
        while (Role::where('key', $key)->exists()) {
            $key = $baseKey.'_'.$suffix++;
        }

        DB::transaction(function () use ($request, $validated, $key): void {
            $role = Role::create([
                'name' => $validated['name'],
                'key' => $key,
                'permissions' => $this->cleanPermissions($validated['permissions'] ?? []),
            ]);
            $this->writeAudit($request, 'created', $role, ['after' => ['name' => $role->name, 'permissions' => $role->permissions]]);
        });

        return redirect()->route('admin.roles.index')->with('status', 'Role created.');
    }

    public function edit(Role $role): View
    {
        return view('admin.roles.form', [
            'role' => $role,
            'permissionGroups' => config('admin_permissions'),
        ]);
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100', 'unique:roles,name,'.$role->id],
            'permissions' => ['sometimes', 'array'],
        ]);

        DB::transaction(function () use ($request, $role, $validated): void {
            $before = ['name' => $role->name, 'permissions' => $role->permissions];
            $role->update([
                'name' => $validated['name'],
                'permissions' => $this->cleanPermissions($validated['permissions'] ?? []),
            ]);
            $this->writeAudit($request, 'updated', $role, [
                'before' => $before,
                'after' => ['name' => $role->name, 'permissions' => $role->permissions],
            ]);
        });

        return redirect()->route('admin.roles.index')->with('status', 'Role updated.');
    }

    public function destroy(Request $request, Role $role): RedirectResponse
    {
        if ($role->is_system) {
            return back()->withErrors(['role' => 'Built-in roles cannot be deleted.']);
        }
        if ($role->userCount() > 0) {
            return back()->withErrors(['role' => 'Reassign this role’s users before deleting it.']);
        }

        DB::transaction(function () use ($request, $role): void {
            $snapshot = ['name' => $role->name, 'key' => $role->key];
            $this->writeAudit($request, 'deleted', $role, ['before' => $snapshot]);
            $role->delete();
        });

        return redirect()->route('admin.roles.index')->with('status', 'Role deleted.');
    }

    private function cleanPermissions(array $submitted): array
    {
        $permissions = [];

        foreach (config('admin_permissions') as $modules) {
            foreach ($modules as $module => $actions) {
                foreach ($actions as $action) {
                    $key = Str::slug($action, '_');
                    if (in_array($submitted[$module][$key] ?? null, [true, 1, '1', 'on'], true)) {
                        $permissions[$module][$key] = true;
                    }
                }
            }
        }

        return $permissions;
    }

    private function writeAudit(Request $request, string $action, Role $role, array $changes): void
    {
        AuditLog::create([
            'user_id' => $request->user()->id,
            'module' => 'roles',
            'action' => $action,
            'record_id' => $role->id,
            'description' => ucfirst($action)." role {$role->name}.",
            'changes' => $changes,
        ]);
    }
}
