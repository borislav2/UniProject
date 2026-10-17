<?php

namespace App\Mail;

use App\Models\OutreachProspect;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\URL;

/** Първо писмо до бизнес, намерен от Harvey. Кратко, почти обикновен текст, с линк за отписване във всяко писмо. */
class OutreachMail extends Mailable
{
    public function __construct(public OutreachProspect $prospect, public array $data, public string $mailSubject)
    {
    }

    public function unsubscribeUrl(): string
    {
        return URL::signedRoute('outreach.unsubscribe', ['prospect' => $this->prospect->id]);
    }

    public function envelope(): Envelope
    {
        $from = new Address(config('creatium.outreach.from_address'), config('creatium.outreach.from_name'));

        return new Envelope(from: $from, replyTo: [$from], subject: $this->mailSubject);
    }

    public function headers(): Headers
    {
        return new Headers(text: [
            'List-Unsubscribe' => '<' . $this->unsubscribeUrl() . '>, <mailto:' . config('creatium.outreach.from_address') . '?subject=unsubscribe>',
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.outreach', text: 'emails.outreach-text', with: [
            'data' => $this->data,
            'prospect' => $this->prospect,
            'unsubscribeUrl' => $this->unsubscribeUrl(),
        ]);
    }
}
