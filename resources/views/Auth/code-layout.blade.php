<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Reset password') — INFRA-INV</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@600;800&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        :root { --ink:#0B1229; --navy:#16233F; --gold:#C9A24B; --gold-light:#E8D5A3; --paper:#F7F7F5; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            padding: 24px; font-family: 'Inter', sans-serif; color: var(--ink);
            background-color: var(--paper);
            background-image: linear-gradient(180deg, rgba(247,247,245,.35), rgba(247,247,245,.75)), url('/PICTURE/morning-urban-landscape.jpg');
            background-size: cover; background-position: center top; background-attachment: fixed;
        }
        .card {
            width: 100%; max-width: 440px; padding: 36px 32px; border-radius: 24px;
            background: rgba(255,255,255,.93); backdrop-filter: blur(14px);
            border: 1px solid rgba(201,162,75,.2);
            box-shadow: 0 20px 60px -15px rgba(11,18,41,.2), 0 2px 8px -2px rgba(11,18,41,.08);
        }
        .brand { display: flex; align-items: center; gap: 10px; text-decoration: none; margin-bottom: 24px; }
        .brand img { width: 36px; height: 36px; object-fit: contain; }
        .brand span { font-family: 'Manrope', sans-serif; font-weight: 800; font-size: 1.1rem; color: var(--ink); letter-spacing: -.02em; }
        h1 { font-family: 'Manrope', sans-serif; font-weight: 800; font-size: 1.6rem; letter-spacing: -.02em; margin: 0 0 8px; }
        .sub { color: #6B7280; font-size: .92rem; line-height: 1.55; margin: 0 0 24px; }
        label { display: block; font-size: .72rem; font-weight: 700; letter-spacing: .12em; text-transform: uppercase; color: #6B7280; margin: 0 0 8px 2px; }
        .field { margin-bottom: 18px; }
        .input {
            width: 100%; padding: 14px 16px; border-radius: 12px; font: inherit; color: var(--ink);
            background: #FBFBFA; border: 1px solid #E5E3DD; outline: none; transition: all .2s ease;
        }
        .input:focus { background: #fff; border-color: var(--gold); box-shadow: 0 0 0 3px rgba(201,162,75,.15); }
        .input.code { text-align: center; font-family: ui-monospace, Menlo, Consolas, monospace; font-size: 1.6rem; letter-spacing: .5em; padding-left: 1.1em; }
        .input.ok  { border-color: #34d399; }
        .input.bad { border-color: #fca5a5; }
        .pw-wrap { position: relative; }
        .pw-wrap .input { padding-right: 56px; }
        .toggle { position: absolute; right: 6px; top: 50%; transform: translateY(-50%); border: 0; background: transparent;
                  color: #6B7280; font: 600 .75rem 'Inter', sans-serif; padding: 8px 10px; cursor: pointer; border-radius: 8px; }
        .toggle:hover { color: var(--navy); }
        .btn {
            width: 100%; padding: 14px; border: 0; border-radius: 12px; cursor: pointer;
            background: var(--navy); color: #fff; font: 600 1rem 'Inter', sans-serif; transition: all .25s ease;
        }
        .btn:hover:not(:disabled) { background: var(--ink); box-shadow: 0 8px 24px -6px rgba(11,18,41,.45); transform: translateY(-1px); }
        .btn:disabled { opacity: .5; cursor: not-allowed; }
        .link { color: var(--navy); font-weight: 600; font-size: .88rem; text-decoration: underline; text-underline-offset: 3px; background: none; border: 0; padding: 0; cursor: pointer; font-family: inherit; }
        .row { display: flex; justify-content: space-between; align-items: center; margin-top: 18px; }
        .alert { font-size: .875rem; font-weight: 500; padding: 12px; border-radius: 8px; margin-bottom: 18px; }
        .alert.ok  { color: #067647; background: #ecfdf3; border: 1px solid #abefc6; }
        .alert.err { color: #b42318; background: #fef3f2; border: 1px solid #fecdca; }
        .err-text { color: #dc2626; font-size: .8rem; margin: 6px 0 0 2px; }
        .hint { font-size: .8rem; margin: 6px 0 0 2px; }
        .meter { height: 8px; border-radius: 999px; background: #E5E7EB; overflow: hidden; margin-top: 12px; }
        .meter > div { height: 100%; width: 0; border-radius: 999px; transition: width .3s ease, background-color .3s ease; }
        .meter-label { text-align: right; font-size: .85rem; font-weight: 700; margin-top: 6px; color: #475569; }
        .rules { list-style: none; margin: 12px 0 0; padding: 0; }
        .rules li { display: flex; align-items: center; gap: 8px; font-size: .85rem; color: #9CA3AF; margin-bottom: 6px; transition: color .2s; }
        .rules li .dot { width: 16px; text-align: center; font-weight: 800; }
        .rules li.met { color: #059669; }
    </style>
</head>
<body>
    <main class="card">
        <a href="{{ url('/') }}" class="brand">
            <img src="{{ asset('PICTURE/LOGOS.png') }}" alt="">
            <span>INFRA-INV</span>
        </a>

        @if (session('status'))
            <div class="alert ok">
                {{ session('status') === 'verification-link-sent'
                    ? 'A new verification link has been sent to your email address.'
                    : session('status') }}
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>