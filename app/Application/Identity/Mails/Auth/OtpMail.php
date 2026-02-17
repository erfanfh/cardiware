<?php

namespace App\Application\Identity\Mails\Auth;

use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use SerializesModels;

    public string $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function build()
    {
        return $this->subject('کد تایید شما')
            ->view('emails.otp');
    }
}
