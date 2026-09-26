@extends('Auth.code-layout')

@section('title', 'Forgot password')

@section('content')
    <h1>Forgot your password?</h1>
    <p class="sub">Enter the email address registered to your account. We'll send a 6-digit code you can use to set a new password.</p>
    <p class="sub" style="margin-top:-12px;font-size:.82rem;">
        Codes are only sent to <strong>verified</strong> emails. If nothing arrives, check your spam folder or ask the administrator for help.
    </p>

    <form method="POST" action="{{ route('password.email') }}">
        @csrf

        <div class="field">
            <label for="email">Email address</label>
            <input id="email" name="email" type="email" class="input @error('email') bad @enderror"
                   value="{{ old('email') }}" placeholder="you@gmail.com" required autofocus autocomplete="email">
            @error('email')
                <p class="err-text">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn">Send code</button>

        <div class="row">
            <a href="{{ url('/') }}" class="link">Back to log in</a>
        </div>
    </form>
@endsection