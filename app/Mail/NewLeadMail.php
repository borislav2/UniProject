<?php

namespace App\Mail;

use App\Models\Project;
use App\Mail\Concerns\AddsPlainTextPart;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class NewLeadMail extends Mailable
{
    use AddsPlainTextPart;

    public function __construct(public Project $lead)
    {
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Ново запитване от сайта: ' . $this->lead->name);
    }

    public function content(): Content
    {
        return new Content(view: 'emails.new-lead');
    }
}
