@extends('Auth.code-layout')

@section('title', 'Enter code')

@section('content')
    <h1>Enter your code</h1>
    <p class="sub">
        We sent a 6-digit code to <strong>{{ $maskedEmail }}</strong>. It expires in {{ $minutes }} minutes.
        Check your spam folder if you don't see it.
    </p>

    <form method="POST" action="{{ route('password.code.update') }}" id="resetForm" autocomplete="off">
        @csrf

        <div class="field">
            <label for="code">6-digit code</label>
            <input id="code" name="code" type="text" inputmode="numeric" pattern="[0-9]*" maxlength="6"
                   autocomplete="one-time-code" class="input code @error('code') bad @enderror" placeholder="••••••" required autofocus>
            @error('code')
                <p class="err-text">{{ $message }}</p>
            @enderror
        </div>

        <div class="field">
            <label for="password">New password</label>
            <div class="pw-wrap">
                <input id="password" name="password" type="password" class="input @error('password') bad @enderror"
                       autocomplete="new-password" required>
                <button type="button" class="toggle" data-target="password">Show</button>
            </div>
            @error('password')
                <p class="err-text">{{ $message }}</p>
            @enderror

            <div id="meterBox" hidden>
                <div class="meter"><div id="meterBar"></div></div>
                <p class="meter-label">Strength: <span id="meterText"></span></p>
            </div>

            <ul class="rules" id="rules">
                <li data-rule="length"><span class="dot">○</span> At least 8 characters long</li>
                <li data-rule="upper"><span class="dot">○</span> Contains uppercase letter</li>
                <li data-rule="lower"><span class="dot">○</span> Contains lowercase letter</li>
                <li data-rule="number"><span class="dot">○</span> Contains number</li>
                <li data-rule="special"><span class="dot">○</span> Contains special character</li>
            </ul>
        </div>

        <div class="field">
            <label for="password_confirmation">Confirm new password</label>
            <div class="pw-wrap">
                <input id="password_confirmation" name="password_confirmation" type="password" class="input"
                       autocomplete="new-password" required>
                <button type="button" class="toggle" data-target="password_confirmation">Show</button>
            </div>
            <p class="hint" id="matchHint" hidden></p>
        </div>

        <button type="submit" class="btn" id="submitBtn" disabled>Reset password</button>
    </form>

    <div class="row">
        <a href="{{ route('password.request') }}" class="link">Use a different email</a>

        <form method="POST" action="{{ route('password.email') }}">
            @csrf
            <input type="hidden" name="email" value="{{ $email }}">
            <button type="submit" class="link">Resend code</button>
        </form>
    </div>

    <script>
        (function () {
            var code    = document.getElementById('code');
            var pw      = document.getElementById('password');
            var cf      = document.getElementById('password_confirmation');
            var btn     = document.getElementById('submitBtn');
            var box     = document.getElementById('meterBox');
            var bar     = document.getElementById('meterBar');
            var txt     = document.getElementById('meterText');
            var hint    = document.getElementById('matchHint');
            var items   = document.querySelectorAll('#rules li');

            var tests = {
                length:  function (v) { return v.length >= 8; },
                upper:   function (v) { return /[A-Z]/.test(v); },
                lower:   function (v) { return /[a-z]/.test(v); },
                number:  function (v) { return /[0-9]/.test(v); },
                special: function (v) { return /[^A-Za-z0-9]/.test(v); }
            };
            var levels = [
                { t: 'Very Weak',   c: '#ef4444' }, { t: 'Very Weak',   c: '#ef4444' },
                { t: 'Weak',        c: '#f97316' }, { t: 'Fair',        c: '#fbbf24' },
                { t: 'Strong',      c: '#84cc16' }, { t: 'Very Strong', c: '#10b981' }
            ];

            // digits only in the code box
            code.addEventListener('input', function () {
                code.value = code.value.replace(/\D/g, '').slice(0, 6);
                update();
            });

            function update() {
                var v = pw.value, score = 0;

                items.forEach(function (li) {
                    var ok = tests[li.dataset.rule](v);
                    li.classList.toggle('met', ok);
                    li.querySelector('.dot').textContent = ok ? '✓' : '○';
                    if (ok) score++;
                });

                box.hidden = v.length === 0;
                bar.style.width = (score / 5 * 100) + '%';
                bar.style.backgroundColor = levels[score].c;
                txt.textContent = levels[score].t;
                txt.style.color = levels[score].c;

                var match = cf.value.length > 0 && v === cf.value;
                hint.hidden = cf.value.length === 0;
                hint.textContent = match ? 'Passwords match' : 'Passwords do not match';
                hint.style.color = match ? '#059669' : '#dc2626';
                cf.classList.toggle('ok', match);
                cf.classList.toggle('bad', cf.value.length > 0 && !match);

                btn.disabled = !(code.value.length === 6 && score === 5 && match);
            }

            pw.addEventListener('input', update);
            cf.addEventListener('input', update);

            document.querySelectorAll('.toggle').forEach(function (b) {
                b.addEventListener('click', function () {
                    var input = document.getElementById(b.dataset.target);
                    var show = input.type === 'password';
                    input.type = show ? 'text' : 'password';
                    b.textContent = show ? 'Hide' : 'Show';
                });
            });

            update();
        })();
    </script>
@endsection