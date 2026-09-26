<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\User;
use App\Support\DeliverableEmail;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

/**
 * Forgot password using a 6-digit code sent by email.
 *   Step 1: user enters email      -> code is emailed
 *   Step 2: user enters code + new -> password is changed
 */
class PasswordCodeController extends Controller
{
    private const CODE_TTL_MINUTES        = 10;
    private const RESEND_COOLDOWN_SECONDS = 60;
    private const MAX_ATTEMPTS            = 5;

    /**
     * true  = codes are sent only to VERIFIED, real emails (recommended: a typo'd
     *         address never receives a code, and users must confirm the mailbox
     *         works before they rely on it).
     * false = codes go to any real (non-placeholder) email on the account.
     */
    private const REQUIRE_VERIFIED_EMAIL = true;

    /** Step 1 form */
    public function create(): View
    {
        return view('Auth.forgot-password');
    }

    /** Step 1 submit: generate + email the code */
    public function store(Request $request): RedirectResponse
    {
        $data  = $request->validate(['email' => ['required', 'email']]);
        $email = trim($data['email']);

        $user = User::where('email', $email)->first();

        // Placeholder emails (real address not set yet) never get a code
        if ($user
            && DeliverableEmail::check($user->email)
            && (! self::REQUIRE_VERIFIED_EMAIL || $user->hasVerifiedEmail())) {
            $tooSoon = DB::table('password_reset_codes')
                ->where('email', $user->email)
                ->where('created_at', '>', now()->subSeconds(self::RESEND_COOLDOWN_SECONDS))
                ->exists();

            // Inside the cooldown we silently skip re-sending (stops email spamming)
            if (! $tooSoon) {
                $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

                DB::table('password_reset_codes')->where('email', $user->email)->delete();
                DB::table('password_reset_codes')->insert([
                    'email'      => $user->email,
                    'code_hash'  => Hash::make($code),
                    'attempts'   => 0,
                    'expires_at' => now()->addMinutes(self::CODE_TTL_MINUTES),
                    'created_at' => now(),
                ]);

                try {
                    Mail::to($user->email)->send(new PasswordResetCodeMail($user, $code, self::CODE_TTL_MINUTES));
                } catch (\Throwable $e) {
                    Log::error('Reset code email failed for ' . $user->email . ': ' . $e->getMessage());

                    if (config('app.debug')) {
                        return back()->withInput()->withErrors(['email' => 'Email could not be sent: ' . $e->getMessage()]);
                    }
                }
            }
        }

        // Only for debugging (never shown to the user): why no code was sent
        if (! $user) {
            Log::info('Reset code not sent: no account for ' . $email);
        } elseif (! DeliverableEmail::check($user->email)) {
            Log::info('Reset code not sent: placeholder email ' . $user->email);
        } elseif (self::REQUIRE_VERIFIED_EMAIL && ! $user->hasVerifiedEmail()) {
            Log::info('Reset code not sent: email not verified ' . $user->email);
        }

        // Same response whether or not the email exists (no account enumeration)
        $request->session()->put('pw_reset_email', $email);

        return redirect()->route('password.code')
            ->with('status', 'If that email is registered, a 6-digit code has been sent to it.');
    }

    /** Step 2 form */
    public function showCode(Request $request): View|RedirectResponse
    {
        $email = $request->session()->get('pw_reset_email');

        if (! $email) {
            return redirect()->route('password.request');
        }

        $at = strpos($email, '@') ?: 3;

        return view('Auth.reset-password-code', [
            'email'       => $email,
            'maskedEmail' => Str::mask($email, '*', 1, max($at - 2, 1)),
            'minutes'     => self::CODE_TTL_MINUTES,
        ]);
    }

    /** Step 2 submit: check code, set new password */
    public function update(Request $request): RedirectResponse
    {
        $email = $request->session()->get('pw_reset_email');

        if (! $email) {
            return redirect()->route('password.request')
                ->withErrors(['email' => 'Your session expired. Please request a new code.']);
        }

        $request->validate([
            'code'     => ['required', 'digits:6'],
            'password' => ['required', 'confirmed', Password::min(8)->mixedCase()->numbers()->symbols()],
        ]);

        $record = DB::table('password_reset_codes')->where('email', $email)->first();

        if (! $record || Carbon::parse($record->expires_at)->isPast()) {
            DB::table('password_reset_codes')->where('email', $email)->delete();

            return back()->withErrors(['code' => 'This code is invalid or has expired. Please request a new one.']);
        }

        if ($record->attempts >= self::MAX_ATTEMPTS) {
            DB::table('password_reset_codes')->where('email', $email)->delete();

            return back()->withErrors(['code' => 'Too many wrong attempts. Please request a new code.']);
        }

        if (! Hash::check($request->code, $record->code_hash)) {
            DB::table('password_reset_codes')->where('id', $record->id)->increment('attempts');

            return back()->withErrors(['code' => 'That code is incorrect.']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            return back()->withErrors(['code' => 'This code is invalid or has expired. Please request a new one.']);
        }

        // Plain query so it works whether or not the User model has a 'hashed' cast.
        // Getting the code by email also proves the mailbox is theirs -> mark verified.
        DB::table('users')->where('id', $user->id)->update([
            'password'          => Hash::make($request->password),
            'remember_token'    => Str::random(60),
            'email_verified_at' => $user->email_verified_at ?? now(),
            'updated_at'        => now(),
        ]);

        DB::table('password_reset_codes')->where('email', $email)->delete();
        $request->session()->forget('pw_reset_email');

        event(new PasswordReset($user));

        return redirect('/')->with('status', 'Your password has been reset. You can now log in.');
    }
}