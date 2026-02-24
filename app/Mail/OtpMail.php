<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public string $otpCode;
    public string $actionType;

    public function __construct(string $otpCode, string $actionType)
    {
        $this->otpCode    = $otpCode;
        $this->actionType = $actionType;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Verification Code – ' . config('app.name'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
}
