<?php

namespace App\Support\SiteCheck;

use App\Models\Project;
use Illuminate\Support\Str;

/**
 * Предварително попълнено писмо „Личен преглед на сайт“ (шаблон site-review) от записания автоматичен отчет:
 * адресът като име на бизнеса, кратко резюме и до 5 най-важни неща (без „информативните“). Всичко се редактира в екрана за писмо.
 * Текстовете се чистят от двоеточия и тирета, за да не нарушат правилото за текстовете на собствениците.
 */
class SiteReviewDraft
{
    public const MAX_ITEMS = 5;

    /** @return array<string, string> стойности за полетата на шаблона; празни там, където отчетът липсва */
    public static function for(Project $project): array
    {
        $report = is_array($project->site_check_report) ? $project->site_check_report : [];
        $domain = preg_replace('#^https?://#', '', (string) ($report['domain'] ?? $project->website_url));
        $out = ['client_name' => '', 'business' => rtrim((string) $domain, '/')];

        if (! isset($report['score'])) {
            // Няма автоматичен отчет (проверката не е успяла): точките се пишат на ръка.
            $out['items'] = '';
            $out['summary'] = 'Прегледахме сайта на {business} ръчно. Ето най-важното.' . self::goalSentence($project);

            return $out;
        }

        $items = Areas::top(SiteAudit::items($report), self::MAX_ITEMS);
        $out['items'] = collect($items)->map(fn ($i) => self::clean($i['title']) . ': ' . self::short($i['detail']))->implode("\n");
        $out['summary'] = 'Автоматичната проверка даде оценка ' . (int) $report['score'] . ' от 100, а ние прегледахме сайта на {business} и ръчно. Ето най-важното, подредено по приоритет.' . self::goalSentence($project);

        return $out;
    }

    private static function goalSentence(Project $project): string
    {
        $label = ReviewDue::goalLabel($project->check_goal);

        return $label ? ' Знаем, че основната ви цел сега е „' . $label . '“, затова започваме с нещата, които са най-близо до нея.' : '';
    }

    /** Първото изречение, най-много 170 знака. */
    private static function short(string $text): string
    {
        $text = self::clean($text);
        $first = preg_split('/(?<=[.!?])\s/u', $text, 2)[0] ?? $text;

        return Str::limit($first, 170, '');
    }

    /** Без двоеточия и тирета в текста (двоеточие + интервал остава само като разделител между заглавие и текст). */
    private static function clean(string $text): string
    {
        $text = str_replace([' – ', ' — ', ' - ', '–', '—'], ', ', $text);

        return trim(str_replace(':', ',', $text));
    }
}
