<?php

namespace App\Mail\Concerns;

use App\Support\PlainText;

/** Добавя текстова версия, направена от HTML-а, когато писмото няма собствена (виж App\Support\PlainText). */
trait AddsPlainTextPart
{
    protected function runCallbacks($message)
    {
        parent::runCallbacks($message);

        $email = $message->getSymfonyMessage();
        if (! $email->getTextBody() && is_string($html = $email->getHtmlBody()) && $html !== '') {
            $email->text(PlainText::fromHtml($html), 'utf-8');
        }

        return $this;
    }
}
