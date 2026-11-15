<?php

namespace App\Mail;

use Illuminate\Mail\Mailables\Content;

/**
 * Второ и последно писмо до бизнес, който не е отговорил на първото: кратко напомняне със същия подател,
 * линк за отписване и проследяване на кликовете. Темата е „Re: “ + темата на първото писмо.
 */
class OutreachFollowUpMail extends OutreachMail
{
    public function content(): Content
    {
        $english = $this->language() === 'en';

        return new Content(view: $english ? 'emails.outreach-en-followup' : 'emails.outreach-followup', text: $english ? 'emails.outreach-en-followup-text' : 'emails.outreach-followup-text', with: [
            'data' => $this->data,
            'links' => $this->links(),
            'plain' => self::targets($this->language()),
            'prospect' => $this->prospect,
            'unsubscribeUrl' => $this->unsubscribeUrl(),
        ]);
    }
}
