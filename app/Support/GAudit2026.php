<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Дизайн Г, пети кръг (13.11.2026, вторият одит на собствениците): няколко `g_` текста по подразбиране са сменени в config.
 * Тук се пренасят в админ редакциите само точните стари текстове (цели низове); всичко друго остава. Всеки променен запис
 * първо се копира в `<ключ>:backup-2026-11-13` (без да се презаписва съществуващо копие). Новите ключове (g_highlight, g_goal_*,
 * g_contact_*, g_facts, contact_budgets) не се пипат, защото Content::withDefaults ги показва и при по-стара редакция.
 */
class GAudit2026
{
    /** ключ в content_blocks => [път => [старо => ново]] */
    public const CHANGES = [
        'home_trust' => [
            'g_parts.0.title' => [
                'Бранд позициониране' => 'Бранд идентичност и позициониране',
                'Brand positioning' => 'Brand identity and positioning',
            ],
        ],
        'plans' => [
            'g_subtitle' => [
                'Ясна структура за изграждане, поддръжка и устойчив растеж.' => 'От стартиращата идея до мащабирането на пазара, изграждаме устойчив дигитален фундамент, поддържаме го стабилен и ви осигуряваме дългосрочен ръст. Разгледайте пакетите ни и изберете решението, което отговаря точно на настоящите ви цели.',
                'A clear structure for building, maintaining and growing steadily.' => 'From a starting idea to scaling in the market, we build a lasting digital foundation, keep it stable and secure long term growth for you. Explore our packages and choose the solution that fits your current goals exactly.',
            ],
            'g_items.0.audience' => [
                'Подходящо за нови или малки бизнеси, които нямат уебсайт или настоящият им сайт стои просто като визитка, която не носи запитвания и поръчки. Фокусът тук е бързото пускане на пазара, изграждането на доверие и привличането на първите локални клиенти.'
                    => 'Нови или малки бизнеси, които нямат изградено онлайн присъствие, не използват активен маркетинг или настоящият им сайт просто не генерира запитвания и продажби. Фокусът тук е бързото излизане на пазара, изграждането на първоначално доверие и привличането на първите клиенти.',
                'For new or small businesses that have no website, or whose current site is just a business card that brings no enquiries or orders. The focus here is a fast launch, building trust and winning your first local customers.'
                    => 'New or small businesses that have no established online presence, do not use active marketing, or whose current website simply does not generate enquiries and sales. The focus here is a fast market launch, building first trust and winning your first customers.',
            ],
        ],
        'about' => [
            'g_founders_title' => [
                'Основатели' => 'Екипът',
                'Founders' => 'The team',
            ],
        ],
    ];

    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::whereIn('key', array_keys(self::CHANGES))->get() as $block) {
            $value = self::transform($block->key, (array) $block->value);
            if ($value === $block->value) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => $block->key . ':backup-2026-11-13', 'locale' => $block->locale],
                ['value' => $block->value]
            );
            $block->update(['value' => $value]);
            $changed[] = $block->key . '/' . $block->locale;
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return $changed;
    }

    /** Сменя само низове, които са точно старият текст на дадения път. */
    public static function transform(string $key, array $value): array
    {
        foreach (self::CHANGES[$key] ?? [] as $path => $map) {
            $current = data_get($value, $path);
            if (is_string($current) && isset($map[$current])) {
                data_set($value, $path, $map[$current]);
            }
        }

        return $value;
    }
}
