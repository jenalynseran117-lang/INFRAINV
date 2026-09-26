<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 *   php artisan mail:test youraddress@gmail.com
 * Sends one plain test email using the current MAIL_* settings from .env.
 */
class MailTest extends Command
{
    protected $signature = 'mail:test {to : Email address that will receive the test message}';

    protected $description = 'Send a test email with the current MAIL_* settings';

    public function handle(): int
    {
        $to = $this->argument('to');

        $this->line(sprintf(
            'Mailer: %s | Host: %s | Port: %s | From: %s',
            config('mail.default'),
            config('mail.mailers.smtp.host'),
            config('mail.mailers.smtp.port'),
            config('mail.from.address')
        ));

        try {
            Mail::raw(
                'If you can read this, INFRA-INV can send email.',
                fn ($message) => $message->to($to)->subject('INFRA-INV SMTP test')
            );
        } catch (\Throwable $e) {
            $this->error('FAILED: ' . $e->getMessage());

            return self::FAILURE;
        }

        $this->info("Sent to {$to}. Check the inbox (and the Spam folder).");

        return self::SUCCESS;
    }
}