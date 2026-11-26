<?php

namespace App\Application\Identity\Mails\Auth;

use App\Application\Identity\DTOs\LoginAlertData;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class LoginAlertMail extends Mailable
{
    public function __construct(public readonly LoginAlertData $login) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'اعلان ورود به حساب Cardiware');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.auth.login-alert', text: 'emails.auth.login-alert-text');
    }
}
