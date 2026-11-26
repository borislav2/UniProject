<?php

namespace App\Mail;

use App\Models\Project;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/** Кратко известие до екипа, когато клиент потвърди платена оферта от писмо след проверката (винаги на български). */
class SiteCheckOfferOrderedMail extends Mailable
{
    public function __construct(public Project $project, public string $offer)
    {
    }

    public function name(): string
    {
        return (string) config("creatium.site_check_offers.{$this->offer}.name");
    }

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Поръчка след проверка, ' . $this->name() . ', ' . preg_replace('#^https?://#', '', (string) $this->project->website_url));
    }

    public function content(): Content
    {
        return new Content(text: 'emails.site-check-offer-team', with: ['project' => $this->project, 'name' => $this->name(), 'offer' => $this->offer]);
    }
}
