<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Отчетът от безплатната проверка на сайт (App\Support\SiteCheck\SiteChecker) до човека, който я е поискал. */
class SiteCheckReportMail extends Mailable
{
    public function __construct(public array $report)
    {
    }

    public function envelope(): Envelope
    {
        $contact = config('creatium.contact.email');

        return new Envelope(
            subject: __('Проверка на :domain: оценка :score от 100', ['domain' => $this->report['domain'], 'score' => $this->report['score']]),
            replyTo: $contact ? [new Address($contact, 'Creatium Lab')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.site-check', with: [
            'report' => $this->report,
            'data' => [],
            'subject' => $this->envelope()->subject,
        ]);
    }
}
