@extends('Auth.code-layout')

@section('title', 'Verify your email')

@section('content')
    <h1>Verify your email</h1>
    <p class="sub">
        Your account's email address is not verified yet. We sent a verification link to
        <strong>{{ auth()->user()->email }}</strong>. Click the link in that email to continue.
        Check your spam folder if you don't see it.
    </p>

    <form method="POST" action="{{ route('verification.send') }}">
        @csrf
        <button type="submit" class="btn">Resend verification email</button>
    </form>

    <div class="row">
        <a href="{{ route('profile.edit') }}" class="link">Wrong email? Edit profile</a>

        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="link">Log out</button>
        </form>
    </div>
@endsection
