<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Имейл до клиент по шаблон от App\Support\ClientEmails (изпраща се от админ панела). */
class ClientTemplateMail extends Mailable
{
    public function __construct(public array $template, public array $data, public string $mailSubject)
    {
    }

    public function envelope(): Envelope
    {
        $contact = config('creatium.contact.email');

        return new Envelope(
            subject: $this->mailSubject,
            replyTo: $contact ? [new Address($contact, 'Creatium Lab')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: $this->template['view'], with: [
            'data' => $this->data,
            'package' => $this->template['package'] ?? null,
            'subject' => $this->mailSubject,
        ]);
    }
}
