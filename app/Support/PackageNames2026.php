<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Имената на пакетите навсякъде (17.11.2026, решение на собствениците): „Стартов двигател“ става „Core Изграждане“, „Активен маркетинг и растеж“
 * става „Pro Ускорение“, „Индивидуален маркетинг“ става „Enterprise Партньорство“ (на английски „Core Build“, „Pro Accelerate“, „Enterprise Partnership“).
 * Цените не се променят. Тук се пренасят в админ редакциите само цели низове, които са точно старият текст по подразбиране (стойностите са
 * изписани отдолу, защото config вече е с новите имена); всичко, което е редактирано, остава както е. Старите имена продължават да се разпознават
 * от Plans::find() (Plans::ALIASES). Всеки променен запис първо се копира в „<ключ>:backup-2026-11-17b“ (съществуващо копие не се презаписва).
 * Обхваща редактируемите блокове (Content::BLOCKS) и настройките на SEO по страници („seo:<маршрут>“).
 */
class PackageNames2026
{
    public const BACKUP = 'backup-2026-11-17b';

    /** Стар текст по подразбиране => нов, по език. */
    public const REPLACEMENTS = [
        'bg' => [
        'Цената е фиксирана и я знаете, преди да започнем. Стартов двигател е €400 еднократно (изграждане и първия месец), после €200 на месец. Активен маркетинг и растеж е €800 еднократно, после €650 на месец. Индивидуален маркетинг е от €1 200 на месец, по индивидуална оферта. При годишно плащане получавате 2 месеца безплатно. Какво точно получавате и в какъв срок, пишем в офертата.'
            => 'Цената е фиксирана и я знаете, преди да започнем. Core Изграждане е €400 еднократно (изграждане и първия месец), после €200 на месец. Pro Ускорение е €800 еднократно, после €650 на месец. Enterprise Партньорство е от €1 200 на месец, по индивидуална оферта. При годишно плащане получавате 2 месеца безплатно. Какво точно получавате и в какъв срок, пишем в офертата.',
        'Стартовата цена се плаща на две части. За Стартов двигател плащате €100 при заявка за оферта и демо и останалите €300 при съгласие. За Активен маркетинг и растеж плащате €200 при заявка за оферта и демо и останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите. При Индивидуален маркетинг също има капаро преди офертата и демото. Сумата се уточнява в индивидуалната оферта и също не се връща. Абонаментът започва от втория месец.'
            => 'Стартовата цена се плаща на две части. За Core Изграждане плащате €100 при заявка за оферта и демо и останалите €300 при съгласие. За Pro Ускорение плащате €200 при заявка за оферта и демо и останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите. При Enterprise Партньорство също има капаро преди офертата и демото. Сумата се уточнява в индивидуалната оферта и също не се връща. Абонаментът започва от втория месец.',
        'Стартов двигател'
            => 'Core Изграждане',
        'Активен маркетинг и растеж'
            => 'Pro Ускорение',
        'Годишното плащане е за 12 месеца абонамент, платени като 10. Абонаментът започва от втория месец, а стартовата цена се плаща отделно. Индивидуален маркетинг е по индивидуална оферта.'
            => 'Годишното плащане е за 12 месеца абонамент, платени като 10. Абонаментът започва от втория месец, а стартовата цена се плаща отделно. Enterprise Партньорство е по индивидуална оферта.',
        'Всичко от Стартов двигател'
            => 'Всичко от Core Изграждане',
        'Включва всичко от Стартов двигател, плюс'
            => 'Включва всичко от Core Изграждане, плюс',
        'Индивидуален маркетинг'
            => 'Enterprise Партньорство',
        'Всичко от Активен маркетинг и растеж според нуждите ви'
            => 'Всичко от Pro Ускорение според нуждите ви',
        'Включва според нуждите ви всичко от Активен маркетинг и растеж, плюс'
            => 'Включва според нуждите ви всичко от Pro Ускорение, плюс',
        'Какво точно влиза в Индивидуален маркетинг, уточняваме в индивидуалната оферта според нуждите ви.'
            => 'Какво точно влиза в Enterprise Партньорство, уточняваме в индивидуалната оферта според нуждите ви.',
        'За Стартов двигател плащате €100 при заявка за оферта и демо (не се връщат) и останалите €300 при съгласие. За Активен маркетинг и растеж плащате €200 при заявка за оферта и демо (не се връщат) и останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите.'
            => 'За Core Изграждане плащате €100 при заявка за оферта и демо (не се връщат) и останалите €300 при съгласие. За Pro Ускорение плащате €200 при заявка за оферта и демо (не се връщат) и останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите.',
        'Първият месец е в стартовата цена. След това абонаментът е €200 / месец за Стартов двигател и €650 / месец за Активен маркетинг и растеж. Индивидуален маркетинг е от €1 200 / месец според индивидуалната оферта.'
            => 'Първият месец е в стартовата цена. След това абонаментът е €200 / месец за Core Изграждане и €650 / месец за Pro Ускорение. Enterprise Партньорство е от €1 200 / месец според индивидуалната оферта.',
        'Годишният абонамент е €2 000 вместо €2 400 за Стартов двигател и €6 500 вместо €7 800 за Активен маркетинг и растеж. Плаща се веднъж за 12 месеца, считано от втория месец.'
            => 'Годишният абонамент е €2 000 вместо €2 400 за Core Изграждане и €6 500 вместо €7 800 за Pro Ускорение. Плаща се веднъж за 12 месеца, считано от втория месец.',
        'Цени от €400 старт и €200/месец или €800 старт и €650/месец. Индивидуален маркетинг от €1 200/месец. Фиксирана цена и 2 месеца безплатно при годишен план.'
            => 'Цени от €400 старт и €200/месец или €800 старт и €650/месец. Enterprise Партньорство от €1 200/месец. Фиксирана цена и 2 месеца безплатно при годишен план.',
        ],
        'en' => [
        'The price is fixed and you know it before we start. Starter Engine is €400 one-off (the build and the first month), then €200 a month. Active Marketing and Growth is €800 one-off, then €650 a month. Custom Marketing is from €1,200 a month under a custom quote. With yearly billing you get 2 months free. What exactly you get and by when is written in the quote.'
            => 'The price is fixed and you know it before we start. Core Build is €400 one-off (the build and the first month), then €200 a month. Pro Accelerate is €800 one-off, then €650 a month. Enterprise Partnership is from €1,200 a month under a custom quote. With yearly billing you get 2 months free. What exactly you get and by when is written in the quote.',
        'The start price is paid in two parts. For Starter Engine you pay €100 when you request a quote and demo and the remaining €300 when you agree. For Active Marketing and Growth you pay €200 when you request a quote and demo and the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue. Custom Marketing also has a deposit before the quote and the demo. The amount is set in the custom quote and is not refunded either. The subscription starts in the second month.'
            => 'The start price is paid in two parts. For Core Build you pay €100 when you request a quote and demo and the remaining €300 when you agree. For Pro Accelerate you pay €200 when you request a quote and demo and the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue. Enterprise Partnership also has a deposit before the quote and the demo. The amount is set in the custom quote and is not refunded either. The subscription starts in the second month.',
        'Yearly billing is 12 months of subscription paid as 10. The subscription starts in the second month, and the start price is paid separately. Custom Marketing is a custom quote.'
            => 'Yearly billing is 12 months of subscription paid as 10. The subscription starts in the second month, and the start price is paid separately. Enterprise Partnership is a custom quote.',
        'Starter Engine'
            => 'Core Build',
        'Active Marketing and Growth'
            => 'Pro Accelerate',
        'Everything in Starter Engine'
            => 'Everything in Core Build',
        'Includes everything in Starter Engine, plus'
            => 'Includes everything in Core Build, plus',
        'Custom Marketing'
            => 'Enterprise Partnership',
        'Everything in Active Marketing and Growth, tailored to you'
            => 'Everything in Pro Accelerate, tailored to you',
        'Includes, tailored to your needs, everything in Active Marketing and Growth, plus'
            => 'Includes, tailored to your needs, everything in Pro Accelerate, plus',
        'What exactly goes into Custom Marketing, we agree in the custom quote according to your needs.'
            => 'What exactly goes into Enterprise Partnership, we agree in the custom quote according to your needs.',
        'For Starter Engine you pay €100 when you request a quote and demo (non-refundable) and the remaining €300 when you agree. For Active Marketing and Growth you pay €200 when you request a quote and demo (non-refundable) and the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue.'
            => 'For Core Build you pay €100 when you request a quote and demo (non-refundable) and the remaining €300 when you agree. For Pro Accelerate you pay €200 when you request a quote and demo (non-refundable) and the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue.',
        'The first month is in the start price. After that the subscription is €200 / month for Starter Engine and €650 / month for Active Marketing and Growth. Custom Marketing is from €1,200 / month under a custom quote.'
            => 'The first month is in the start price. After that the subscription is €200 / month for Core Build and €650 / month for Pro Accelerate. Enterprise Partnership is from €1,200 / month under a custom quote.',
        'The yearly subscription is €2,000 instead of €2,400 for Starter Engine and €6,500 instead of €7,800 for Active Marketing and Growth. It is paid once for 12 months, counted from the second month.'
            => 'The yearly subscription is €2,000 instead of €2,400 for Core Build and €6,500 instead of €7,800 for Pro Accelerate. It is paid once for 12 months, counted from the second month.',
        'Prices from €400 to start and €200/month, or €800 and €650/month. Custom Marketing from €1,200/month. Fixed price, 2 months free with yearly billing.'
            => 'Prices from €400 to start and €200/month, or €800 and €650/month. Enterprise Partnership from €1,200/month. Fixed price, 2 months free with yearly billing.',
        ],
    ];

    /** @return list<string> променените записи като „ключ/език“ */
    public static function apply(): array
    {
        $changed = [];

        foreach (ContentBlock::all() as $block) {
            $base = explode(':', $block->key, 2);
            $editable = array_key_exists($block->key, Content::BLOCKS) || ($base[0] === 'seo' && count($base) === 2 && ! str_starts_with($base[1], 'backup'));
            if (! $editable || str_contains($block->key, ':backup-')) {
                continue;
            }

            $value = self::transform((array) $block->value, $block->locale);
            if ($value === $block->value) {
                continue;
            }

            ContentBlock::firstOrCreate(
                ['key' => $block->key . ':' . self::BACKUP, 'locale' => $block->locale],
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

    /** Сменя всеки низ (на всяка дълбочина), който е точно старият текст по подразбиране; всичко останало остава. */
    public static function transform(array $value, string $locale = 'bg'): array
    {
        $map = self::REPLACEMENTS[$locale === 'en' ? 'en' : 'bg'];

        $walk = function (mixed $node) use (&$walk, $map) {
            if (is_string($node)) {
                return $map[$node] ?? $node;
            }

            return is_array($node) ? array_map($walk, $node) : $node;
        };

        return $walk($value);
    }
}
