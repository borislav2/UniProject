<?php

namespace Tests\Feature;

use App\Mail\ClientTemplateMail;
use App\Support\ClientEmails;
use App\Support\PlainText;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\Mime\Email;
use Tests\TestCase;

class MailFormatTest extends TestCase
{
    private function sendAndCapture($mailable): Email
    {
        config(['mail.default' => 'array']);
        Mail::to('klient@example.bg')->send($mailable);

        return app('mailer')->getSymfonyTransport()->messages()->last()->getOriginalMessage();
    }

    public function test_client_emails_have_a_text_part_and_an_embedded_logo(): void
    {
        $data = ['client_name' => 'Мария', 'business' => 'Студио Лавандула', 'sender' => 'Борислав', 'demo_url' => 'https://creatiumlab.com/demo/salon', 'call_note' => '', 'follow_up' => ''];
        $email = $this->sendAndCapture(new ClientTemplateMail(ClientEmails::find('demo-site'), $data, 'Демо'));

        $text = $email->getTextBody();
        $this->assertIsString($text);
        $this->assertStringContainsString('Здравейте, Мария,', $text);
        $this->assertStringContainsString('Вижте демото → (https://creatiumlab.com/demo/salon)', $text);
        $this->assertStringNotContainsString('<', $text);

        $this->assertStringContainsString('src="cid:', $email->getHtmlBody());
        $this->assertCount(1, $email->getAttachments());
        $this->assertSame('inline', $email->getAttachments()[0]->getDisposition());
    }

    public function test_the_admin_preview_still_loads_the_logo_from_the_site(): void
    {
        $html = view('emails.client.demo-site', ['data' => ['client_name' => 'Мария', 'business' => 'Лавандула', 'sender' => 'Екипът', 'demo_url' => '']])->render();

        $this->assertStringContainsString('src="' . asset('images/logo-white.png') . '"', $html);
    }

    public function test_plain_text_conversion(): void
    {
        $text = PlainText::fromHtml('<html><head><style>p{color:red}</style><title>T</title></head><body>'
            . '<p>Здравейте,&nbsp;Иван</p><table><tr><td>Цена</td><td>€450</td></tr></table>'
            . '<ul><li>Едно</li><li>Две</li></ul><a href="https://creatiumlab.com/paketi">Пакети</a> '
            . '<a href="tel:+359877200546">+359 877 200 546</a> <a href="https://creatiumlab.com">https://creatiumlab.com</a></body></html>');

        $this->assertStringNotContainsString('color:red', $text);
        $this->assertStringContainsString('Здравейте, Иван', $text);
        $this->assertStringContainsString('Цена €450', $text);
        $this->assertStringContainsString("– Едно\n", $text);
        $this->assertStringContainsString('Пакети (https://creatiumlab.com/paketi)', $text);
        $this->assertStringContainsString('+359 877 200 546 https://creatiumlab.com', $text);
        $this->assertDoesNotMatchRegularExpression("/\n{3,}/", $text);
    }
}
