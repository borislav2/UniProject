<?php

namespace App\Mail;

use App\Models\Project;
use App\Mail\Concerns\AddsPlainTextPart;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Потвърждение до клиента, когато във формата е избрал ден и час за консултация. */
class ConsultationBookedMail extends Mailable
{
    use AddsPlainTextPart;

    public function __construct(public Project $lead)
    {
    }

    public function envelope(): Envelope
    {
        $contact = config('creatium.contact.email');

        return new Envelope(
            subject: __('Консултация с Creatium Lab: :time', ['time' => \App\Support\Booking::label($this->lead->consultation_at)]),
            replyTo: $contact ? [new Address($contact, 'Creatium Lab')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.consultation', with: ['data' => [], 'subject' => $this->envelope()->subject]);
    }
}
