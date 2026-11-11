@extends('layouts.auth')

@section('title', 'Sign in')

@section('content')
    <span class="form-kicker">WELCOME BACK</span>
    <h2>Sign In to Braviel Group</h2>
    <!-- <p class="form-intro">Pick up right where you left off.</p> -->

    @if (session('status'))
        <div class="notice notice-success" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('login.store') }}" class="auth-form">
        @csrf
        <div class="field">
            <label for="email">Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" placeholder="you@company.com" autocomplete="email" required autofocus class="@error('email') is-invalid @enderror">
            @error('email') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <div class="field">
            <div class="field-label-row">
                <label for="password">Password</label>
                <a class="text-link" href="{{ route('password.request') }}">Forgot password?</a>
            </div>
            <input id="password" name="password" type="password" placeholder="Enter your password" autocomplete="current-password" required class="@error('password') is-invalid @enderror">
            @error('password') <span class="field-error">{{ $message }}</span> @enderror
        </div>
        <label class="check-row"><input type="checkbox" name="remember" value="1" {{ old('remember') ? 'checked' : '' }}><span class="custom-check"></span><span>Keep me signed in</span></label>
        <button class="primary-button" type="submit">Sign in <span aria-hidden="true">→</span></button>
    </form>

@endsection
