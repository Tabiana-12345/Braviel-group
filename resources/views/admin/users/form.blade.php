@extends('layouts.admin')

@section('title', $managedUser ? 'Edit user' : 'Add user')

@section('content')
    <section class="page-heading">
        <div><span class="form-kicker">ACCESS CONTROL</span><h1>{{ $managedUser ? 'Edit user' : 'Add user' }}</h1><p>Set account details and access level.</p></div>
        <a class="admin-secondary-button" href="{{ route('admin.users.index') }}">Back to users</a>
    </section>

    <form class="admin-form-card" method="POST" action="{{ $managedUser ? route('admin.users.update', $managedUser) : route('admin.users.store') }}">
        @csrf
        @if ($managedUser)
            @method('PUT')
        @endif
        <div class="form-card-heading">
            <div>
                <h2>Account details</h2>
                <p>Enter the user’s contact details, access role, and {{ $managedUser ? 'optional password change' : 'password' }}.</p>
            </div>
            <span class="form-required-note"><span aria-hidden="true">*</span> Required</span>
        </div>
        <div class="admin-form-grid">
            <div class="admin-field">
                <label for="name">Name <span aria-hidden="true">*</span></label>
                <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name', $managedUser?->name) }}" autocomplete="name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                @error('name')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-field">
                <label for="username">Username <span aria-hidden="true">*</span></label>
                <input id="username" name="username" type="text" required maxlength="100" pattern="[A-Za-z0-9_-]+" value="{{ old('username', $managedUser?->username) }}" autocomplete="username" aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}">
                @error('username')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-field">
                <label for="email">Email <span aria-hidden="true">*</span></label>
                <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $managedUser?->email) }}" autocomplete="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                @error('email')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-field">
                <label for="role">Role <span aria-hidden="true">*</span></label>
                <select id="role" name="role" required aria-invalid="{{ $errors->has('role') ? 'true' : 'false' }}">
                    @foreach ($roles as $role)
                        <option value="{{ $role->key }}" @selected(old('role', $managedUser?->role ?? 'staff') === $role->key)>{{ $role->name }}</option>
                    @endforeach
                </select>
                @error('role')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-field">
                <label for="password">{{ $managedUser ? 'New password' : 'Password' }} @unless ($managedUser)<span aria-hidden="true">*</span>@endunless</label>
                <input id="password" name="password" type="password" {{ $managedUser ? '' : 'required' }} minlength="8" autocomplete="new-password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                @if ($managedUser)
                    <small class="field-help">Leave blank to keep the current password.</small>
                @endif
                @error('password')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-field">
                <label for="password_confirmation">Confirm password @unless ($managedUser)<span aria-hidden="true">*</span>@endunless</label>
                <input id="password_confirmation" name="password_confirmation" type="password" {{ $managedUser ? '' : 'required' }} minlength="8" autocomplete="new-password" aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}">
                @error('password_confirmation')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
        </div>
        <div class="admin-form-actions">
            <a class="admin-secondary-button" href="{{ route('admin.users.index') }}">Cancel</a>
            <button class="admin-primary-button" type="submit">{{ $managedUser ? 'Save changes' : 'Create user' }}</button>
        </div>
    </form>
@endsection
