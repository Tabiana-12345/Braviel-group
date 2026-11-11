@extends('layouts.admin')

@section('title', 'My Profile')

@section('content')
    <section class="page-heading">
        <div>
            <span class="form-kicker">ACCOUNT</span>
            <h1>My Profile</h1>
            <p>Update your account name, email address, or password.</p>
        </div>
    </section>

    <form class="admin-form-card" method="POST" action="{{ route('profile.update') }}">
        @csrf
        @method('PUT')
        <div class="form-card-heading">
            <div>
                <h2>Account details</h2>
                <p>Keep your profile information current. Leave the password blank to keep it unchanged.</p>
            </div>
        </div>
        <div class="admin-form-grid">
            <div class="admin-field">
                <label for="name">Name <span aria-hidden="true">*</span></label>
                <input id="name" name="name" type="text" required maxlength="255" value="{{ old('name', $user->name) }}" autocomplete="name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}">
                @error('name')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-field">
                <label for="username">Username <span aria-hidden="true">*</span></label>
                <input id="username" name="username" type="text" required maxlength="100" pattern="[A-Za-z0-9_-]+" value="{{ old('username', $user->username) }}" autocomplete="username" aria-invalid="{{ $errors->has('username') ? 'true' : 'false' }}">
                @error('username')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-field">
                <label for="email">Email <span aria-hidden="true">*</span></label>
                <input id="email" name="email" type="email" required maxlength="255" value="{{ old('email', $user->email) }}" autocomplete="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}">
                @error('email')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-field">
                <label for="password">New password</label>
                <input id="password" name="password" type="password" minlength="8" autocomplete="new-password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}">
                <small class="field-help">Leave blank to keep your current password.</small>
                @error('password')<span class="admin-field-error">{{ $message }}</span>@enderror
            </div>
            <div class="admin-field">
                <label for="password_confirmation">Confirm new password</label>
                <input id="password_confirmation" name="password_confirmation" type="password" minlength="8" autocomplete="new-password" aria-invalid="{{ $errors->has('password_confirmation') ? 'true' : 'false' }}">
            </div>
        </div>
        <div class="admin-form-actions">
            <a class="admin-secondary-button" href="{{ route('dashboard') }}">Cancel</a>
            <button class="admin-primary-button" type="submit">Save profile</button>
        </div>
    </form>
@endsection
