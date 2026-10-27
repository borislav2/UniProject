<?php

namespace App\Mail;

use App\Models\OutreachProspect;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Headers;
use Illuminate\Support\Facades\URL;

/** Първо писмо до намерен бизнес. Кратко писмо без картинки (за да стига до входящата поща), с линк за отписване във всяко писмо. */
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
        // Без настроена поща (OUTREACH_MAIL_FROM) писмото не се изпраща (Outreach::configured()),
        // но прегледът в админа трябва да работи, затова тогава просто няма подател.
        $address = config('creatium.outreach.from_address');
        if (blank($address)) {
            return new Envelope(subject: $this->mailSubject);
        }
        $from = new Address($address, config('creatium.outreach.from_name'));

        return new Envelope(from: $from, replyTo: [$from], subject: $this->mailSubject);
    }

    public function headers(): Headers
    {
        $address = config('creatium.outreach.from_address');

        return new Headers(text: [
            'List-Unsubscribe' => '<' . $this->unsubscribeUrl() . '>' . (filled($address) ? ', <mailto:' . $address . '?subject=unsubscribe>' : ''),
            'List-Unsubscribe-Post' => 'List-Unsubscribe=One-Click',
        ]);
    }

    /**
     * Наблюдението, разделено за HTML писмото: редовете с „– “ или „- “ стават точки от списък
     * („Заглавие. Подробност“), останалите са обикновени абзаци. Ръчно написан текст без тирета остава само абзаци.
     *
     * @return array{intro: list<string>, items: list<array{title: string, detail: string}>, outro: list<string>}
     */
    public static function observationParts(string $observation): array
    {
        $parts = ['intro' => [], 'items' => [], 'outro' => []];
        foreach (preg_split('/\R/u', trim($observation)) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            if (preg_match('/^[–\-•]\s*(.+)$/u', $line, $m)) {
                [$title, $detail] = array_pad(preg_split('/(?<=[.!?])\s+/u', $m[1], 2), 2, '');
                $parts['items'][] = ['title' => rtrim($title, '.'), 'detail' => trim($detail)];
            } else {
                $parts[$parts['items'] ? 'outro' : 'intro'][] = $line;
            }
        }

        return $parts;
    }

    /** Адресите, към които водят линковете в писмото (с utm), без проследяването. */
    public static function targets(): array
    {
        $utm = '?' . http_build_query(['utm_source' => 'outreach', 'utm_medium' => 'email', 'utm_campaign' => 'first-email']);

        return [
            'site' => route('home') . $utm,
            'check' => route('site-check') . $utm,
            'booking' => route('contact') . $utm . '#konsultatsiya',
        ];
    }

    /**
     * Линковете в писмото минават през /o/{prospect}/{link} (подписан адрес), за да видим в админа дали бизнесът е кликнал.
     * Пиксел за отваряне няма: неточен е, вреди на доставката и иска съгласие.
     */
    public function links(): array
    {
        $check = route('site-check');
        $links = [];
        foreach (array_keys(self::targets()) as $key) {
            $links[$key] = URL::signedRoute('outreach.click', ['prospect' => $this->prospect->id, 'link' => $key]);
        }

        return $links + [
            'check_label' => preg_replace('#^https?://(www\.)?#', '', $check),
            'minutes' => config('creatium.booking.minutes'),
        ];
    }

    public function content(): Content
    {
        return new Content(view: 'emails.outreach', text: 'emails.outreach-text', with: [
            'data' => $this->data,
            'parts' => self::observationParts($this->data['observation']),
            'links' => $this->links(),
            'plain' => self::targets(),
            'prospect' => $this->prospect,
            'unsubscribeUrl' => $this->unsubscribeUrl(),
        ]);
    }
}
