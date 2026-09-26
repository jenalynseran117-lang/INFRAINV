<?php

namespace App\Observers;

use App\Mail\AccountRegisteredMail;
use App\Models\User;
use App\Support\DeliverableEmail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class UserObserver
{
    /**
     * A NEW user row was created through Eloquent (User::create, firstOrCreate,
     * updateOrCreate, factories...). Seeders must use Eloquent, not DB::table()->insert().
     */
    public function created(User $user): void
    {
        $this->sendNotice($user);
    }

    /**
     * The email is being changed (Profile Settings, artisan users:set-email, seeder...).
     * A new address is NOT verified yet.
     */
    public function updating(User $user): void
    {
        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }
    }

    /** The email was changed -> tell the NEW address it was registered (+ verify link). */
    public function updated(User $user): void
    {
        if ($user->wasChanged('email')) {
            $this->sendNotice($user);
        }
    }

    private function sendNotice(User $user): void
    {
        // Placeholder address (real email not known yet) -> don't try to send.
        if (! DeliverableEmail::check($user->email)) {
            Log::info('Registration notice skipped (placeholder email): ' . $user->email);
            return;
        }

        try {
            Mail::to($user->email)->send(new AccountRegisteredMail($user));
        } catch (\Throwable $e) {
            // Never break seeding / profile update just because SMTP failed.
            Log::error('Registration notice failed for ' . $user->email . ': ' . $e->getMessage());
        }
    }
}