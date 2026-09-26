<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PasswordResetCodeMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $user,
        public string $code,
        public int $minutes = 10,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Your INFRA-INV password reset code');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-code',
            with: [
                'name'    => $this->user->name,
                'code'    => $this->code,
                'minutes' => $this->minutes,
            ],
        );
    }
}