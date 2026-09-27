<?php

namespace App\Http\Controllers;

use App\Support\DeliverableEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;


class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('Profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => [
                'required',
                'string',
                'email',
                'max:255',
                Rule::unique('users', 'email')->ignore($user->id),
                // a CHANGED email must be a real one (no .local / .test / example.com)
                function (string $attribute, mixed $value, \Closure $fail) use ($user) {
                    if (strcasecmp($value, $user->email) !== 0 && ! DeliverableEmail::check($value)) {
                        $fail('Please enter a real email address.');
                    }
                },
            ],
            // current password is required before ANY profile change
            'current_password' => ['required', 'current_password'],
        ]);

        $user->fill([
            'name'  => $validated['name'],
            'email' => $validated['email'],
        ]);

        $emailChanged = $user->isDirty('email');
        $oldEmail     = $user->getOriginal('email');

        // UserObserver un-verifies the new email and mails the notice + verify link
        $user->save();

        if ($emailChanged) {
            DB::table('password_reset_codes')->where('email', $oldEmail)->delete();
        }

        return redirect()->route('Profile.edit')
            ->with('status', $emailChanged ? 'email-changed' : 'profile-updated');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
