<?php

namespace App\Mail;

use App\Mail\Concerns\AddsPlainTextPart;
use App\Models\Project;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Отчетът от безплатната проверка на сайт (App\Support\SiteCheck\SiteChecker) до човека, който я е поискал. */
class SiteCheckReportMail extends Mailable
{
    use AddsPlainTextPart;

    /**
     * $lead (запитването) дава подписаните линкове към офертите; без него писмото е без оферти (преглед, стари извиквания).
     * $monthly е месечната проверка на абонат: без продажбени части и с реда за предишния резултат ($previousScore).
     */
    public function __construct(public array $report, public ?Project $lead = null, public bool $monthly = false, public ?int $previousScore = null)
    {
    }

    public function envelope(): Envelope
    {
        $contact = config('creatium.contact.email');

        return new Envelope(
            subject: ($this->monthly ? __('Месечна проверка на :domain, оценка :score от 100', ['domain' => $this->report['domain'], 'score' => $this->report['score']])
                : __('Проверка на :domain, оценка :score от 100', ['domain' => $this->report['domain'], 'score' => $this->report['score']])),
            replyTo: $contact ? [new Address($contact, 'Creatium Lab')] : [],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.site-check', with: [
            'report' => $this->report,
            'lead' => $this->lead,
            'monthly' => $this->monthly,
            'previousScore' => $this->previousScore,
            'data' => ['pad' => 28],
            'subject' => $this->envelope()->subject,
        ]);
    }
}
