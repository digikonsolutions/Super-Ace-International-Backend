<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in · Super Ace Admin</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
    <link rel="preload" href="{{ asset('admin-assets/fonts/montserrat-variable.woff2') }}" as="font" type="font/woff2" crossorigin>
    <link rel="stylesheet" href="{{ asset('admin-assets/css/admin.css') }}?v=1.0">
</head>
<body>
<div class="auth">
    <div class="auth__box">
        <img class="auth__logo" src="{{ asset('admin-assets/img/logo-light.png') }}" alt="Super Ace International">

        <div class="auth__card">
            <div class="eyebrow">Control Panel</div>
            <h1>Welcome back</h1>
            <p>Sign in to manage products, blogs and incoming requests.</p>

            @if (session('success'))
                <div class="alert alert--success">{{ session('success') }}</div>
            @endif
            @if ($errors->any())
                <div class="alert alert--error">{{ $errors->first() }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.attempt') }}" class="stack">
                @csrf
                <div class="field">
                    <label class="label" for="email">Email <span class="req">*</span></label>
                    <input class="input" type="email" id="email" name="email" value="{{ old('email') }}" placeholder="name@example.com" required autofocus autocomplete="username">
                </div>
                <div class="field">
                    <label class="label" for="password">Password <span class="req">*</span></label>
                    <input class="input" type="password" id="password" name="password" placeholder="Your password" required autocomplete="current-password">
                </div>
                <label class="auth__remember">
                    <input type="checkbox" class="checkbox" name="remember" value="1"> Keep me signed in
                </label>
                <button class="btn btn-primary btn-block" type="submit">
                    Sign in <svg class="i i-sm" viewBox="0 0 24 24"><path d="M5 12h14M13 6l6 6-6 6"/></svg>
                </button>
            </form>
        </div>

        <div class="auth__foot">Engineer performance. Export excellence.</div>
    </div>
</div>
</body>
</html>
