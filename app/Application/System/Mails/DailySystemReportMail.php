<?php

namespace App\Application\System\Mails;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class DailySystemReportMail extends Mailable
{
    public function __construct(public readonly array $report) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'گزارش روزانه وضعیت سیستم | '.config('app.name').' | '.$this->report['until']);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.system.daily-report', text: 'emails.system.daily-report-text');
    }
}
