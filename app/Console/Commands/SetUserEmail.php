<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Support\DeliverableEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;

/**
 * Put the REAL email on a seeded account once it is known.
 * The user becomes "unverified" and the registration notice + verify link
 * is emailed to the new address automatically (via UserObserver).
 *
 *   php artisan users:set-email admin  juan@gmail.com      (by role)
 *   php artisan users:set-email admin@infra-inv.local  juan@gmail.com   (by current email)
 */
class SetUserEmail extends Command
{
    protected $signature = 'users:set-email
                            {who : Current email OR role (admin, supply, inspector)}
                            {email : The new, real email address}';

    protected $description = 'Change the email of a user (e.g. replace a seeded placeholder) and send the registration notice';

    public function handle(): int
    {
        $who   = $this->argument('who');
        $new   = trim($this->argument('email'));

        if (! DeliverableEmail::check($new)) {
            $this->error('The new email looks invalid or is a placeholder (.local / .test / example.com).');
            return self::FAILURE;
        }

        $user = User::where('email', $who)->first();

        if (! $user) {
            // 'role' may be a real column OR come from the role_user pivot table.
            try {
                $byRole = Schema::hasColumn('users', 'role')
                    ? User::where('role', $who)->get()
                    : User::whereHas('roles', fn ($q) => $q->where('name', $who))->get();
            } catch (\Throwable $e) {
                $this->error('Could not look up by role (' . $e->getMessage() . '). Use the current email instead.');
                return self::FAILURE;
            }

            if ($byRole->count() > 1) {
                $this->error("More than one user has the role '{$who}'. Use their current email instead.");
                return self::FAILURE;
            }

            $user = $byRole->first();
        }

        if (! $user) {
            $this->error("No user found for '{$who}'.");
            return self::FAILURE;
        }

        if (User::where('email', $new)->where('id', '!=', $user->id)->exists()) {
            $this->error("{$new} is already used by another account.");
            return self::FAILURE;
        }

        $old = $user->email;
        $user->email = $new;
        $user->save(); // UserObserver: un-verifies + emails the notice

        $this->info("Updated {$user->name}: {$old} -> {$new}");
        $this->line('Registration notice + verify link sent. Check the inbox (and Spam). Errors, if any: storage/logs/laravel.log');

        return self::SUCCESS;
    }
}