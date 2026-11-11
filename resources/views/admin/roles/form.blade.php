@extends('layouts.admin')

@section('title', $role ? 'Edit Role' : 'Add Role')

@section('content')
    <section class="page-heading">
        <div>
            <!-- <span class="form-kicker">ACCESS CONTROL</span> -->
            <h1>{{ $role ? 'Edit Role' : 'Add Role' }}</h1>
            <p>Set a role name and choose permissions for each menu.</p>
        </div>
        <a class="admin-secondary-button" href="{{ route('admin.roles.index') }}">Back to roles</a>
    </section>

    <form class="role-permissions-form" method="POST" action="{{ $role ? route('admin.roles.update', $role) : route('admin.roles.store') }}">
        @csrf
        @if ($role)
            @method('PUT')
        @endif

        <section class="role-name-card">
            <div class="form-card-heading">
                <div>
                    <h2>Role details</h2>
                    <p>Name this role, then choose the permissions it should have.</p>
                </div>
                <span class="form-required-note"><span aria-hidden="true">*</span> Required</span>
            </div>
            <div class="admin-field">
                <label for="name">Role Name <span aria-hidden="true">*</span></label>
                <input id="name" name="name" type="text" maxlength="100" required value="{{ old('name', $role?->name) }}" autocomplete="off" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                @error('name')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
        </section>

        <section class="permission-card">
            <div class="permission-card-heading">
                <div>
                    <h2>Permissions</h2>
                    <p>Select the actions this role is allowed to perform.</p>
                </div>
                <label class="permission-select-all permission-select-all-global">
                    <input type="checkbox" data-select-all="all">
                    <span>Select all permissions</span>
                </label>
            </div>

            @foreach ($permissionGroups as $groupName => $modules)
                <section class="permission-group">
                    <h3 class="permission-group-title">{{ $groupName }}</h3>
                    @foreach ($modules as $module => $actions)
                        @php
                            $moduleLabel = \Illuminate\Support\Str::of($module)->replace('_', ' ')->title();
                            $permissionRowId = 'permission-row-'.$module;
                        @endphp
                        <div class="permission-row" data-permission-row="{{ $module }}">
                            <div class="permission-module-name">{{ $moduleLabel }}</div>
                            <label class="permission-select-all">
                                <input type="checkbox" data-select-all="{{ $module }}">
                                <span>Select all</span>
                            </label>
                            <div class="permission-options">
                                @foreach ($actions as $action)
                                    @php
                                        $permissionKey = \Illuminate\Support\Str::slug($action, '_');
                                        $permissionName = "permissions[{$module}][{$permissionKey}]";
                                        $permissionId = $permissionRowId.'-'.$permissionKey;
                                        $isChecked = old("permissions.{$module}.{$permissionKey}", $role?->permissions[$module][$permissionKey] ?? false);
                                    @endphp
                                    <label class="permission-option" for="{{ $permissionId }}">
                                        <input id="{{ $permissionId }}" name="{{ $permissionName }}" type="checkbox" value="1" data-permission @checked($isChecked)>
                                        <span>{{ $action }}</span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </section>
            @endforeach
            @error('permissions')<div class="admin-field-error permission-error">{{ $message }}</div>@enderror

            <div class="admin-form-actions permission-form-actions">
                <a class="admin-secondary-button" href="{{ route('admin.roles.index') }}">Cancel</a>
                <button class="admin-primary-button" type="submit">{{ $role ? 'Save role' : 'Create role' }}</button>
            </div>
        </section>
    </form>
@endsection
