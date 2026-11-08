<?php

namespace App\Support;

use App\Models\ContentBlock;
use Illuminate\Support\Facades\Log;

/**
 * Окончателните цени на собствениците (08.11.2026) в текстовете, редактирани от админа (`content_blocks`).
 *
 * Админът може да е записал `plans` (и други блокове) преди новите цени; такъв запис има предимство пред config и би
 * продължил да показва старите цени. apply() за всеки такъв запис:
 *   1. първо копира стойността в нов запис `<ключ>:backup-2026-11-08` (същия език, никога не презаписва съществуващ архив);
 *   2. заменя САМО свързаното с цените, а всичко останало остава както са го написали собствениците.
 *
 * Какво се сменя в `plans` (bg и en; други езици се пропускат):
 *   - пакетите (по индекс): name, audience, prices, deposit, year_total, build_title, build_items, monthly_title, monthly_items;
 *     badge, highlighted, button и upgrade_note остават; при различен брой пакети `items` се заменя цял;
 *   - compare и compare_note: целите (сравнителната таблица е нова);
 *   - terms: условията за плащането и месечната такса (по заглавие) се заменят, липсващите нови се добавят, другите остават;
 *   - trust, steps, intro: само редове, които още са точно старите текстове по подразбиране;
 *   - billing (годишно плащане) се добавя, ако липсва; pricing (плъзгачът) се маха.
 * В `questions` се сменя отговорът само ако е точно старият текст по подразбиране; в `home_problem` – чипът „фиксирана цена и демо преди старта“.
 * guarantee, faq_*, call_*, button и всеки друг ключ не се пипат. Всичко останало, което още съдържа стара цена, се връща в доклада.
 */
class FinalPrices2026
{
    public const BACKUP_SUFFIX = ':backup-2026-11-08';

    public const ITEM_KEYS = ['name', 'audience', 'prices', 'deposit', 'year_total', 'build_title', 'build_items', 'monthly_title', 'monthly_items'];

    /** Заглавия на условията, които се заменят с новите (стари и нови имена на плащането, месечната такса). */
    private const OLD_TERM_TITLES = ['Плащане на две части', 'Payment in two parts'];

    private const PRICE_TERM_TITLES = [
        'Какво покрива месечната такса', 'What the monthly fee covers',
        'Плащане на старта на две части', 'The start price in two parts',
        'Абонамент от втория месец', 'Subscription from the second month',
        'Годишно плащане: 2 месеца безплатно', 'Yearly billing: 2 months free',
    ];

    /** Старите текстове по подразбиране (преди 08.11.2026), които се заменят само при точно съвпадение. */
    private const OLD = [
        'bg' => [
            'intro' => 'Всеки пакет включва цялата система: марка, сайт, видимост, маркетинг, връщащи се клиенти и грижа всеки месец. Разликата е в мащаба.',
            'trust' => [
                'Демо преди пускане: виждате сайта и го одобрявате, преди да тръгне',
                'Плащате на две части: 50% при договор, 50% след одобрено демо',
            ],
            'steps' => [
                ['Оферта и демо', 'Получавате оферта с фиксирана цена. Преди пускането виждате демо на сайта.'],
                ['Старт след одобрение', 'Стандартният срок е 1–2 седмици. Сайтът тръгва едва след вашето одобрение.'],
            ],
            'questions' => [
                'Как се определя цената на проекта?' => 'Цената е фиксирана и я знаете, преди да започнем: от €600 старт за Стартов двигател и от €900 старт за Активен маркетинг и растеж, и после месечна такса от €150 за Стартов двигател и от €400 за Активен маркетинг и растеж, според колко публикации и статии на месец ви трябват (плъзгачът на страницата с пакетите). И в двата пакета получавате цялата система: марка, сайт, видимост, маркетинг, връщащи се клиенти и грижа всеки месец. Какво точно получавате и в какъв срок, пишем в офертата.',
                'Как протича процесът на плащане?' => 'Плащате на две части: половината при подписване на договора, другата половина след като одобрите демото и преди сайтът да тръгне.',
            ],
            'system_trust' => 'Фиксирана цена и демо преди старта',
        ],
        'en' => [
            'intro' => 'Every package includes the whole system: brand, website, visibility, marketing, returning customers and care every month. The difference is the scale.',
            'trust' => [
                'A demo before launch: you see the website and approve it before it goes live',
                'You pay in two parts: 50% at the contract, 50% after the approved demo',
            ],
            'steps' => [
                ['Quote and demo', 'You get a quote with a fixed price. Before launch you see a demo of the website.'],
                ['Start after approval', 'The standard time is 1–2 weeks. The website goes live only after you approve it.'],
            ],
            'questions' => [
                'How is the project budget set?' => 'The price is fixed and you know it before we start: from €600 to start with Starter Engine and from €900 to start with Active Marketing and Growth, then a monthly fee from €150 for Starter Engine and from €400 for Active Marketing and Growth, depending on how many posts and articles a month you need (the slider on the packages page). Both include the whole system: brand, website, visibility, marketing, returning customers and care every month. What exactly you get and by when is written in the quote.',
                'How does payment work?' => 'You pay in two parts: half when the contract is signed, the other half after you approve the demo and before the site goes live.',
            ],
            'system_trust' => 'A fixed price and a demo before we start',
        ],
    ];

    /** Остатъци от старите цени и имена, които не се променят автоматично и се връщат в доклада. */
    private const STALE_PATTERN = '/€\s?(?:600|900|150|450)\b|(?:600|900|150|450)\s?€|мащабиране|Scaling|плъзгач|slider/u';

    /** Прилага промяната върху всички записи; връща доклада. */
    public static function apply(): array
    {
        $report = ['backed_up' => [], 'changed' => [], 'untouched_with_old_prices' => []];

        foreach (ContentBlock::query()->whereIn('locale', ['bg', 'en'])->get() as $row) {
            if (str_contains($row->key, ':backup-')) {
                continue;
            }
            $value = $row->value;
            if (! is_array($value)) {
                continue;
            }

            $new = self::transform($row->key, $row->locale, $value);

            if ($new !== null && $new !== $value) {
                if (! ContentBlock::where(['key' => $row->key . self::BACKUP_SUFFIX, 'locale' => $row->locale])->exists()) {
                    ContentBlock::create(['key' => $row->key . self::BACKUP_SUFFIX, 'locale' => $row->locale, 'value' => $value, 'updated_by' => $row->updated_by]);
                    $report['backed_up'][] = $row->key . ':' . $row->locale;
                }
                $row->update(['value' => $new]);
                $report['changed'][] = $row->key . ':' . $row->locale;
                $value = $new;
            }

            if (in_array($row->key, ['plans', 'questions', 'home_problem', 'home_trust', 'home_hero', 'home_engine', 'home_process', 'home_contact', 'services_page', 'about'], true)
                && preg_match(self::STALE_PATTERN, json_encode($value, JSON_UNESCAPED_UNICODE))) {
                $report['untouched_with_old_prices'][] = $row->key . ':' . $row->locale;
            }
        }

        app(Content::class)->flush();
        if ($report['untouched_with_old_prices']) {
            Log::warning('Final prices 2026: these admin blocks still contain an old price or name and need a manual look: ' . implode(', ', $report['untouched_with_old_prices']));
        }

        return $report;
    }

    /** The new value of one block, or null when the block is not affected. */
    public static function transform(string $key, string $locale, array $value): ?array
    {
        $new = $locale === 'en' ? config('creatium_en.' . $key) : config('creatium.' . $key);

        return match ($key) {
            'plans' => is_array($new) ? self::plans($value, $new, $locale) : null,
            'questions' => self::questions($value, $locale),
            'home_problem' => self::systemTrust($value, $locale, $locale === 'en' ? config('creatium_en.home_problem.system_trust') : config('creatium.home_problem.system_trust')),
            default => null,
        };
    }

    /** The `plans` override with the price keys of the final structure and everything else as the owners wrote it. */
    public static function plans(array $old, array $new, string $locale): array
    {
        $out = $old;
        $oldTexts = self::OLD[$locale] ?? self::OLD['bg'];

        // Пакетите: същия брой = само ценовите ключове по индекс; иначе целият списък.
        $oldItems = array_values((array) ($old['items'] ?? []));
        $newItems = array_values((array) ($new['items'] ?? []));
        if (count($oldItems) === count($newItems)) {
            foreach ($oldItems as $i => $item) {
                foreach (self::ITEM_KEYS as $k) {
                    if (array_key_exists($k, $newItems[$i])) {
                        $item[$k] = $newItems[$i][$k];
                    }
                }
                $oldItems[$i] = $item;
            }
            $out['items'] = $oldItems;
        } else {
            $out['items'] = $newItems;
        }

        foreach (['compare', 'compare_note'] as $k) {
            if (array_key_exists($k, $new)) {
                $out[$k] = $new[$k];
            }
        }

        if (isset($old['terms']) && is_array($old['terms']) && isset($new['terms'])) {
            $out['terms'] = self::terms($old['terms'], $new['terms']);
        }

        if (($old['intro'] ?? null) === $oldTexts['intro']) {
            $out['intro'] = $new['intro'];
        }
        if (isset($old['trust']) && is_array($old['trust'])) {
            foreach ($old['trust'] as $i => $row) {
                $at = array_search($row['text'] ?? null, $oldTexts['trust'], true);
                if ($at !== false && isset($new['trust'][$at])) {
                    $out['trust'][$i] = ['icon' => $row['icon'] ?? $new['trust'][$at]['icon']] + $new['trust'][$at];
                }
            }
        }
        if (isset($old['steps']) && is_array($old['steps'])) {
            foreach ($old['steps'] as $i => $row) {
                foreach ($oldTexts['steps'] as $at => [$title, $text]) {
                    if (($row['title'] ?? null) === $title && ($row['text'] ?? null) === $text) {
                        // Старите стъпки 3 и 4 са новите на същите места (индекси 2 и 3).
                        $out['steps'][$i] = $new['steps'][$at + 2] ?? $row;
                    }
                }
            }
        }

        if (! isset($old['billing']) && isset($new['billing'])) {
            $out['billing'] = $new['billing'];
        }
        unset($out['pricing']);

        return $out;
    }

    /** Условията: плащането и месечната такса са новите, останалите – на собствениците; добавените от тях остават накрая. */
    private static function terms(array $old, array $new): array
    {
        $byTitle = [];
        foreach ($old as $term) {
            $byTitle[$term['title'] ?? ''] = $term;
        }

        $out = [];
        $used = [];
        foreach ($new as $term) {
            $title = $term['title'];
            $out[] = in_array($title, self::PRICE_TERM_TITLES, true) ? $term : ($byTitle[$title] ?? $term);
            $used[$title] = true;
        }
        foreach ($old as $term) {
            $title = $term['title'] ?? '';
            if (! isset($used[$title]) && ! in_array($title, self::OLD_TERM_TITLES, true) && ! in_array($title, self::PRICE_TERM_TITLES, true)) {
                $out[] = $term;
            }
        }

        return $out;
    }

    private static function questions(array $old, string $locale): ?array
    {
        $texts = (self::OLD[$locale] ?? self::OLD['bg'])['questions'];
        $new = [];
        foreach ((array) config($locale === 'en' ? 'creatium_en.questions' : 'creatium.questions') as $item) {
            $new[$item['q']] = $item['a'];
        }

        $out = $old;
        foreach ($old as $i => $item) {
            $q = $item['q'] ?? null;
            if ($q !== null && isset($texts[$q]) && ($item['a'] ?? null) === $texts[$q] && isset($new[$q])) {
                $out[$i]['a'] = $new[$q];
            }
        }

        return $out;
    }

    private static function systemTrust(array $old, string $locale, mixed $newChips): ?array
    {
        $oldText = (self::OLD[$locale] ?? self::OLD['bg'])['system_trust'];
        $replacement = null;
        foreach ((array) $newChips as $chip) {
            if (($chip['icon'] ?? null) === 'tag') {
                $replacement = $chip['text'] ?? null;
            }
        }
        if ($replacement === null) {
            return null;
        }

        $out = $old;
        foreach ((array) ($old['system_trust'] ?? []) as $i => $chip) {
            if (($chip['text'] ?? null) === $oldText) {
                $out['system_trust'][$i]['text'] = $replacement;
            }
        }

        return $out;
    }
}
