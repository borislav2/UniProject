<?php

namespace App\Mail;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

class MailTestMail extends Mailable
{
    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Тестово писмо от Creatium Lab');
    }

    public function content(): Content
    {
        return new Content(htmlString: '<p>Ако четете това, имейлите от сайта се изпращат правилно.</p><p>Запитванията от контактната форма ще пристигат на този адрес.</p>');
    }
}
