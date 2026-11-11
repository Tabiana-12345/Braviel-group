@extends('layouts.auth')

@section('title', 'Choose a new password')

@section('content')
    <a class="back-link" href="{{ route('login') }}"><span aria-hidden="true">←</span> Back to sign in</a>
    <span class="form-kicker">ALMOST THERE</span>
    <h2>Choose a new password</h2>
    <p class="form-intro">Make it strong and something you haven’t used before.</p>

    <form method="POST" action="{{ route('password.update') }}" class="auth-form">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="field">
            <label for="email">Work email</label>
            <input id="email" name="email" type="email" value="{{ old('email', $email) }}" placeholder="you@company.com" autocomplete="email" required autofocus class="@error('email') is-invalid @enderror">
            @error('email') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="field">
            <label for="password">New password</label>
            <input id="password" name="password" type="password" placeholder="At least 8 characters" autocomplete="new-password" required class="@error('password') is-invalid @enderror">
            @error('password') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="field">
            <label for="password_confirmation">Confirm new password</label>
            <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Enter your password again" autocomplete="new-password" required>
        </div>
        <button class="primary-button" type="submit">Update password <span aria-hidden="true">→</span></button>
    </form>
@endsection
