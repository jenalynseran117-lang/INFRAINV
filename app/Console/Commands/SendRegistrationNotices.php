<?php

namespace App\Console\Commands;

use App\Mail\AccountRegisteredMail;
use App\Models\User;
use App\Support\DeliverableEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * For accounts that ALREADY exist in the database (seeded before the observer
 * was installed): sends them the "your email was registered" notice + verify link.
 *
 *   php artisan users:send-registration-notice
 *   php artisan users:send-registration-notice --email=someone@gmail.com
 *   php artisan users:send-registration-notice --unverified
 */
class SendRegistrationNotices extends Command
{
    protected $signature = 'users:send-registration-notice
                            {--email= : Send only to this email address}
                            {--unverified : Send only to users who have not verified their email yet}';

    protected $description = 'Email the registration notice (with verify link) to existing users';

    public function handle(): int
    {
        $users = User::query()
            ->when($this->option('email'), fn ($q, $email) => $q->where('email', $email))
            ->when($this->option('unverified'), fn ($q) => $q->whereNull('email_verified_at'))
            ->get();

        if ($users->isEmpty()) {
            $this->warn('No matching users found.');
            return self::SUCCESS;
        }

        foreach ($users as $user) {
            if (! DeliverableEmail::check($user->email)) {
                $this->line("Skipped {$user->email} (placeholder email)");
                continue;
            }

            try {
                Mail::to($user->email)->send(new AccountRegisteredMail($user));
                $this->info("Sent to {$user->email}");
            } catch (\Throwable $e) {
                $this->error("FAILED {$user->email}: " . $e->getMessage());
            }
        }

        return self::SUCCESS;
    }
}