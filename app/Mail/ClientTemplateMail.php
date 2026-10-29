<?php

namespace App\Mail;

use Illuminate\Mail\Attachment;
use App\Mail\Concerns\AddsPlainTextPart;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Имейл до клиент по шаблон от App\Support\ClientEmails (изпраща се от админ панела). */
class ClientTemplateMail extends Mailable
{
    use AddsPlainTextPart;

    /** @param  array{name: string, content: string}|null  $pdf  прикачен PDF (оферта, договор), по желание */
    public function __construct(public array $template, public array $data, public string $mailSubject, public ?array $pdf = null)
    {
    }

    public function attachments(): array
    {
        if (! $this->pdf) {
            return [];
        }
        $content = $this->pdf['content'];

        return [Attachment::fromData(fn () => $content, $this->pdf['name'])->withMime('application/pdf')];
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
