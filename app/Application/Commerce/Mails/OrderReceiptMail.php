<?php

namespace App\Application\Commerce\Mails;

use App\Application\Commerce\DTOs\OrderReceiptData;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

final class OrderReceiptMail extends Mailable
{
    public function __construct(public readonly OrderReceiptData $receipt) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'فاکتور سفارش '.$this->receipt->orderNumber.' | مودینگ‌وی');
    }

    public function content(): Content
    {
        return new Content(view: 'emails.commerce.order-receipt', text: 'emails.commerce.order-receipt-text');
    }
}
