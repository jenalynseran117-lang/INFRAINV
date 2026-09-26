<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * The login form lives on the landing page, so send visitors there.
     */
    public function create(): RedirectResponse
    {
        return redirect('/');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Step 0: Validate if the g-recaptcha-response token exists
        $request->validate([
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Please complete the security check.',
        ]);

        // Step 0.1: Verify the token directly with Google's siteverify API
        $recaptchaResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret'   => config('services.recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        if (!$recaptchaResponse->json('success')) {
            return back()->withErrors([
                'g-recaptcha-response' => 'Security check failed. Please try again.',
            ])->withInput();
        }

        // Step 1: Authenticate email + password (Breeze way)
        $request->authenticate();

        $user = Auth::user();

        // Step 2: Map form role → DB role
        $roleMap = [
            'Admin Aide'    => 'admin',
            'Supply Office' => 'supply',
            'Inspector'     => 'inspector',
        ];

        $selectedRole = $roleMap[$request->role] ?? null;

        // Step 3: Check role ownership safely
        if (!$selectedRole || !$user->roles->contains('name', $selectedRole)) {
            Auth::logout();

            return back()->withErrors([
                'role' => 'You are not authorized to log in with this role.',
            ]);
        }

        // Step 4: Regenerate session AFTER role check
        $request->session()->regenerate();

        // Step 4.1: Remember which role the user chose to log in as.
        // A single user can have multiple roles (admin/inspector/supply),
        // so we can't rely on "first role in the pivot table" anywhere
        // else in the app — we store the one they actually authenticated
        // with, and every other part of the app (dashboard redirect,
        // profile layout, etc.) reads this instead.
        $request->session()->put('active_role', $selectedRole);

        // Step 5: Redirect to dashboard
        //
        // NOTE: we intentionally do NOT use redirect()->intended() here.
        // intended() sends the user back to whatever protected URL they
        // tried to visit *before* logging in (Laravel saves this in the
        // session). If someone had an old /supply/dashboard tab open from
        // a previous session, that stale intended URL would override the
        // role they just deliberately chose on the login screen. Since
        // this app always routes through the role-aware /dashboard route
        // anyway, a plain redirect is both simpler and correct here.
        $request->session()->forget('url.intended');

        return redirect()->route('dashboard');
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
