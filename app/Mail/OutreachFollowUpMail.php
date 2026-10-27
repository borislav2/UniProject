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
        return new Content(view: 'emails.outreach-followup', text: 'emails.outreach-followup-text', with: [
            'data' => $this->data,
            'links' => $this->links(),
            'plain' => self::targets(),
            'prospect' => $this->prospect,
            'unsubscribeUrl' => $this->unsubscribeUrl(),
        ]);
    }
}
