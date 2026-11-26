<?php

namespace App\Mail;

use App\Mail\Concerns\AddsPlainTextPart;
use App\Models\Project;
use App\Support\SiteCheck\Offers;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;

/**
 * Едно от трите писма след отчета от безплатната проверка (SiteCheck\FollowUps): 1 един конкретен съвет от отчета, 2 офертата
 * „Оправяме трите най-важни проблема“, 3 покана да проверим пак и следенето. Във всяко има линк за отписване и one-click заглавия (RFC 8058).
 */
class SiteCheckFollowUpMail extends Mailable
{
    use AddsPlainTextPart;

    public function __construct(public Project $project, public int $step)
    {
    }

    public function domain(): string
    {
        return (string) ($this->project->site_check_report['domain'] ?? preg_replace('#^https?://#', '', (string) $this->project->website_url));
    }

    public function envelope(): Envelope
    {
        $contact = config('creatium.contact.email');
        $domain = $this->domain();
        $subject = match ($this->step) {
            1 => __('Едно конкретно нещо за :domain', ['domain' => $domain]),
            2 => __('Оправяме трите най-важни проблема на :domain', ['domain' => $domain]),
            default => __('Да проверим ли пак :domain?', ['domain' => $domain]),
        };

        return new Envelope(subject: $subject, replyTo: $contact ? [new Address($contact, 'Creatium Lab')] : []);
    }

    public function headers(): Headers
    {
        $contact = config('creatium.contact.email');

        return new Headers(text: [
            'List-Unsubscribe' => '<' . Offers::unsubscribeUrl($this->project) . '>' . (filled($contact) ? ', <mailto:' . $contact . '?subject=unsubscribe>' : ''),
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    public function content(): Content
    {
        $top = Offers::topIssues($this->project, 3);
        $domain = $this->domain();

        return new Content(view: 'emails.site-check-followup', with: [
            'project' => $this->project,
            'step' => $this->step,
            'domain' => $domain,
            'top' => $top,
            'first' => $top[0] ?? null,
            'fixUrl' => Offers::url($this->project, 'fix'),
            'monitorUrl' => Offers::url($this->project, 'monitor'),
            'checkUrl' => lroute('site-check') . '?' . http_build_query(['site' => $domain]),
            'unsubscribeUrl' => Offers::unsubscribeUrl($this->project),
            'data' => ['pad' => 28],
            'subject' => $this->envelope()->subject,
        ]);
    }
}
