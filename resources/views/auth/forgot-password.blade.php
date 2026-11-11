@extends('layouts.auth')

@section('title', 'Reset password')

@section('content')
    <a class="back-link" href="{{ route('login') }}"><span aria-hidden="true">←</span> Back to sign in</a>
    <span class="form-kicker">ACCOUNT RECOVERY</span>
    <h2>Forgot your password?</h2>
    <p class="form-intro">No worries. Enter the email linked to your account and we’ll send you a reset link.</p>

    @if (session('status'))
        <div class="notice notice-success" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.email') }}" class="auth-form">
        @csrf
        <div class="field">
            <label for="email">Work email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required autofocus class="@error('email') is-invalid @enderror">
            @error('email') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <button class="primary-button" type="submit">Send reset link <span aria-hidden="true">→</span></button>
    </form>
@endsection
