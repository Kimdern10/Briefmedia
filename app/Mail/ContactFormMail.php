<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ContactFormMail extends Mailable
{
    use Queueable, SerializesModels;

    public $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function build()
    {
        $mail = $this->subject('New message: '.($this->data['subject'] ?? 'Contact form'))
            ->view('emails.contact');

        // Hitting "Reply" in the inbox goes straight to the visitor
        if (! empty($this->data['email']) && filter_var($this->data['email'], FILTER_VALIDATE_EMAIL)) {
            $mail->replyTo($this->data['email'], $this->data['name'] ?? null);
        }

        return $mail;
    }
}
