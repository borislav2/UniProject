<?php

namespace App\Support;

/**
 * Обикновен текст от HTML писмо. Пощите гледат на писмо само с HTML като на по-вероятен спам,
 * затова писмата от сайта винаги вървят и с текстова версия (App\Mail\Concerns\AddsPlainTextPart).
 */
class PlainText
{
    public static function fromHtml(string $html): string
    {
        $html = preg_replace('#<(head|style|script|title)\b[^>]*>.*?</\1>#is', '', $html);

        // Връзка → „текст (адрес)“; ако текстът е самият адрес или е tel:/mailto:, остава само текстът.
        $html = preg_replace_callback('#<a\b[^>]*href=(["\'])(.*?)\1[^>]*>(.*?)</a>#is', function ($m) {
            $href = html_entity_decode($m[2], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $text = trim(preg_replace('/\s+/u', ' ', strip_tags($m[3])));
            if ($text === '' || preg_match('#^(mailto|tel):#i', $href) || str_contains($href, $text) || str_contains($text, $href)) {
                return $text !== '' ? $text : $href;
            }

            return $text . ' (' . $href . ')';
        }, $html);

        // Елемент с display:block (заглавие на ред в таблица) е отделен ред.
        $html = preg_replace('#<(strong|b|span|div)\b[^>]*display:\s*block[^>]*>(.*?)</\1>#is', "\n$2\n", $html);
        $html = preg_replace('#<li\b[^>]*>#i', "\n– ", $html);
        $html = preg_replace('#<br\s*/?>#i', "\n", $html);
        $html = preg_replace('#</(p|div|tr|table|h[1-6]|li|ul|ol)>#i', "\n", $html);
        $html = preg_replace('#</t[dh]>#i', ' ', $html);

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $lines = array_map(fn ($line) => trim(preg_replace('/[ \t\x{00A0}]+/u', ' ', $line)), explode("\n", $text));

        // Отметка или номер в отделна клетка („✓“, „2“) отива в началото на следващия ред.
        $merged = [];
        foreach ($lines as $line) {
            $last = end($merged);
            $isMark = $last !== false && $last !== '' && mb_strlen($last) <= 2 && ! preg_match('/\p{L}/u', $last);
            if ($isMark && $line === '') {
                continue;
            }
            if ($isMark) {
                $merged[key($merged)] = $last . ' ' . $line;
            } else {
                $merged[] = $line;
            }
        }

        return trim(preg_replace("/\n{3,}/", "\n\n", implode("\n", $merged))) . "\n";
    }
}
