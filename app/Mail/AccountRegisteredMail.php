<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

/**
 * Sent to the NEW address when a user sets or changes their email in
 * Profile Settings. Tells them the email was registered in INFRA-INV and
 * carries the signed "Verify my email" link.
 */
class AccountRegisteredMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public User $user)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your email was registered in INFRA-INV');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-registered',
            with: [
                'name'      => $this->user->name,
                'email'     => $this->user->email,
                'roleLabel' => $this->roleLabel(),
                'verifyUrl' => $this->verifyUrl(),
                'loginUrl'  => url('/'),
            ],
        );
    }

    private function roleLabel(): string
    {
        $names = [];

        // Roles from the role_user pivot (works from artisan/observer, no login session needed)
        if (method_exists($this->user, 'roles')) {
            try {
                $names = $this->user->roles()->pluck('name')->all();
            } catch (\Throwable $e) {
                $names = [];
            }
        }

        // Fallback: a plain `role` column / accessor
        if (! $names) {
            try {
                $single = $this->user->role ?? null;
                $names  = $single ? [(string) $single] : [];
            } catch (\Throwable $e) {
                $names = [];
            }
        }

        if (! $names) {
            return 'User';
        }

        return collect($names)->map(fn ($r) => match (strtolower((string) $r)) {
            'admin', 'admin aide'     => 'Admin Aide',
            'supply', 'supply office' => 'Supply Office',
            'inspector'               => 'Inspector',
            default                   => ucfirst((string) $r),
        })->unique()->implode(' / ');
    }

    /** Laravel's own signed verification link (needs the verification.verify route). */
    private function verifyUrl(): ?string
    {
        if ($this->user->hasVerifiedEmail() || ! Route::has('verification.verify')) {
            return null;
        }

        return URL::temporarySignedRoute(
            'verification.verify',
            now()->addDays(3),
            [
                'id'   => $this->user->getKey(),
                'hash' => sha1($this->user->getEmailForVerification()),
            ]
        );
    }
}