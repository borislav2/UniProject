<?php

namespace App\Support;

use App\Models\ContentBlock;

/**
 * Един модел на цените навсякъде (17.11.2026, решение на собствениците: „Навсякъде да е като в дизайн Г, само месечно без капаро“).
 * Само месечен абонамент: Core Изграждане от €500, Pro Ускорение от €800, Enterprise Партньорство от €1 200 на месец; годишно плащане 10 месеца
 * вместо 12 (€5 000, €8 000, €12 000). Няма еднократна стартова цена и няма капаро. Гаранцията за 60 дни остава, махнато е само
 * това, което описваше стартовата цена или капарото (изречението за стартовата сума в стъпките и в условията).
 *
 * Тук се пренасят в админ редакциите само цели низове, които са точно старият текст по подразбиране (стойностите са изписани отдолу, защото
 * config вече е с новите), и списъци (цените и условията на пакетите), които още са точно старият списък; всичко редактирано остава както е.
 * Всеки променен запис първо се копира в „<ключ>:backup-2026-11-17c“ (съществуващо копие не се презаписва). Обхваща редактируемите блокове
 * (Content::BLOCKS) и настройките на SEO по страници („seo:<маршрут>“). Блок, в който след смяната още има стари суми или думи за капаро
 * (защото собствениците са го редактирали), се връща от apply() в `review`, за да го прегледа човек.
 */
class OnePriceModel2026
{
    public const BACKUP = 'backup-2026-11-17c';

    /** Стари суми и думи (стартова цена, капаро), които не бива да са останали в текст след смяната. */
    public const OLD_PATTERN = '/€\s?(?:400|200|650|100|300|600)(?!\d)|€\s?(?:2[\s\x{202F}\x{00A0},]?(?:000|400|600)|6[\s\x{202F}\x{00A0},]?500|7[\s\x{202F}\x{00A0},]?(?:800|950))|капаро|депозит|deposit|еднократно|one-off|non-refundable|не се връща/iu';

    /** Стар текст по подразбиране => нов, по език (цели низове; „“ значи, че полето се изчиства). */
    public const REPLACEMENTS = [
        'bg' => [
            'Цената е фиксирана и я знаете, преди да започнем. Core Изграждане е €400 еднократно (изграждане и първия месец), после €200 на месец. Pro Ускорение е €800 еднократно, после €650 на месец. Enterprise Партньорство е от €1 200 на месец, по индивидуална оферта. При годишно плащане получавате 2 месеца безплатно. Какво точно получавате и в какъв срок, пишем в офертата.'
                => 'Цената е фиксирана и я знаете, преди да започнем. Core Изграждане е от €500 на месец, Pro Ускорение е от €800 на месец, а Enterprise Партньорство е от €1 200 на месец, по индивидуална оферта. При годишно плащане получавате 2 месеца безплатно, тоест €5 000, €8 000 и €12 000 на година. Какво точно получавате и в какъв срок, пишем в офертата.',
            'Стартовата цена се плаща на две части. За Core Изграждане плащате €100 при заявка за оферта и демо и останалите €300 при съгласие. За Pro Ускорение плащате €200 при заявка за оферта и демо и останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите. При Enterprise Партньорство също има капаро преди офертата и демото. Сумата се уточнява в индивидуалната оферта и също не се връща. Абонаментът започва от втория месец.'
                => 'Плащането е месечно, по абонамент. Core Изграждане е от €500 на месец, Pro Ускорение от €800 на месец, а Enterprise Партньорство от €1 200 на месец според индивидуалната оферта. Можете да платите и годишно, като за 12 месеца плащате 10.',
            'Бърз, модерен сайт с форми за запитване, резервации и онлайн плащане. Фиксирана цена от €400 старт и одобрение преди пускане.'
                => 'Бърз, модерен сайт с форми за запитване, резервации и онлайн плащане. Фиксирана цена от €500 на месец и одобрение преди пускане.',
            'Салонът има нужда от ясни услуги и цени, форма за записване и редовни промени по офертите. Всичко това е в стартовия пакет с месечната поддръжка.'
                => 'Салонът има нужда от ясни услуги и цени, форма за записване и редовни промени по офертите. Всичко това е в пакета с месечната поддръжка.',
            'Годишното плащане е за 12 месеца абонамент, платени като 10. Абонаментът започва от втория месец, а стартовата цена се плаща отделно. Enterprise Партньорство е по индивидуална оферта.'
                => 'Годишното плащане е за 12 месеца абонамент, платени като 10. Enterprise Партньорство е по индивидуална оферта.',
            'Плащане на две части. Депозит преди офертата и демото, остатъкът при съгласие'
                => 'Месечен абонамент. Плащате само месечната такса на избрания пакет',
            '€100 при заявка за оферта и демо (не се връщат), останалите €300 при съгласие.'
                => '',
            '€2 600 за първата година (€400 + 11 × €200)'
                => '',
            '€200 при заявка за оферта и демо (не се връщат), останалите €600 при съгласие.'
                => '',
            '€7 950 за първата година (€800 + 11 × €650)'
                => '',
            'Капаро при заявка за оферта и демо (не се връща), сумата е в индивидуалната оферта; останалото при съгласие.'
                => '',
            'Първата част от стартовата цена се плаща преди да подготвим офертата и демото. Получавате оферта с фиксирана цена и виждате демо на сайта.'
                => 'Получавате оферта с фиксирана цена и виждате демо на сайта.',
            'С вашето съгласие плащате останалата част от старта. Стандартният срок е от 1 до 2 седмици, а сайтът тръгва едва след вашето одобрение.'
                => 'С вашето съгласие започваме работа. Стандартният срок е от 1 до 2 седмици, а сайтът тръгва едва след вашето одобрение.',
            'Цена за изработка на сайт с пакети от €400'
                => 'Цена за изработка на сайт с пакети от €500',
            'Цени от €400 старт и €200/месец или €800 старт и €650/месец. Enterprise Партньорство от €1 200/месец. Фиксирана цена и 2 месеца безплатно при годишен план.'
                => 'Core Изграждане от €500/месец, Pro Ускорение от €800/месец и Enterprise Партньорство от €1 200/месец. Фиксирана цена и 2 месеца безплатно при годишен план.',
            'Изработка на сайтове и дигитален маркетинг от €400'
                => 'Изработка на сайтове и дигитален маркетинг от €500',
        ],
        'en' => [
            'The price is fixed and you know it before we start. Core Build is €400 one-off (the build and the first month), then €200 a month. Pro Accelerate is €800 one-off, then €650 a month. Enterprise Partnership is from €1,200 a month under a custom quote. With yearly billing you get 2 months free. What exactly you get and by when is written in the quote.'
                => 'The price is fixed and you know it before we start. Core Build is from €500 a month, Pro Accelerate is from €800 a month and Enterprise Partnership is from €1,200 a month under a custom quote. With yearly billing you get 2 months free, that is €5,000, €8,000 and €12,000 a year. What exactly you get and by when is written in the quote.',
            'The start price is paid in two parts. For Core Build you pay €100 when you request a quote and demo and the remaining €300 when you agree. For Pro Accelerate you pay €200 when you request a quote and demo and the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue. Enterprise Partnership also has a deposit before the quote and the demo. The amount is set in the custom quote and is not refunded either. The subscription starts in the second month.'
                => 'Payment is monthly, as a subscription. Core Build is from €500 a month, Pro Accelerate from €800 a month and Enterprise Partnership from €1,200 a month under a custom quote. You can also pay yearly and pay for 10 months instead of 12.',
            'A fast, modern website with enquiry forms, bookings and online payments. Fixed price from €400 to start and your approval before launch.'
                => 'A fast, modern website with enquiry forms, bookings and online payments. Fixed price from €500 a month and your approval before launch.',
            'Yearly billing is 12 months of subscription paid as 10. The subscription starts in the second month, and the start price is paid separately. Enterprise Partnership is a custom quote.'
                => 'Yearly billing is 12 months of subscription paid as 10. Enterprise Partnership is a custom quote.',
            'Payment in two parts. A deposit before the quote and the demo, the rest when you agree'
                => 'Monthly subscription. You pay only the monthly fee of the package you choose',
            '€100 when you request a quote and demo (non-refundable), the remaining €300 when you agree.'
                => '',
            '€2,600 for the first year (€400 + 11 × €200)'
                => '',
            '€200 when you request a quote and demo (non-refundable), the remaining €600 when you agree.'
                => '',
            '€7,950 for the first year (€800 + 11 × €650)'
                => '',
            'A deposit when you request a quote and demo (non-refundable), the amount is set in the custom quote; the rest when you agree.'
                => '',
            'The first part of the start price is paid before we prepare the quote and the demo. You get a quote with a fixed price and see a demo of the website.'
                => 'You get a quote with a fixed price and see a demo of the website.',
            'When you agree, you pay the rest of the start price. The standard time is 1 to 2 weeks, and the website goes live only after you approve it.'
                => 'When you agree, we start work. The standard time is 1 to 2 weeks, and the website goes live only after you approve it.',
            'Website design prices with packages from €400'
                => 'Website design prices with packages from €500',
            'Prices from €400 to start and €200/month, or €800 and €650/month. Enterprise Partnership from €1,200/month. Fixed price, 2 months free with yearly billing.'
                => 'Core Build from €500/month, Pro Accelerate from €800/month and Enterprise Partnership from €1,200/month. Fixed price, 2 months free with yearly billing.',
            'Websites and digital marketing from €400'
                => 'Website design and digital marketing from €500',
        ],
    ];

    /** Стари списъци по подразбиране (цени, условия) => нови, по език. Списък се сменя само ако е точно старият. */
    public const LISTS = [
        'bg' => [
            [
                ['€400 еднократно за изграждането и първия месец', 'след това €200 / месец'],
                ['от €500 / месец'],
            ],
            [
                ['€800 еднократно за нов или обновен сайт и първия месец', 'след това €650 / месец'],
                ['от €800 / месец'],
            ],
            [
                ['Индивидуална оферта', 'от €1 200 / месец'],
                ['от €1 200 / месец'],
            ],
            [
                [
                    ['title' => 'Фиксирана цена', 'text' => 'Цената и това, което получавате, са записани в офертата.'],
                    ['title' => 'Плащане на старта на две части', 'text' => 'За Core Изграждане плащате €100 при заявка за оферта и демо (не се връщат) и останалите €300 при съгласие. За Pro Ускорение плащате €200 при заявка за оферта и демо (не се връщат) и останалите €600 при съгласие. Първата сума се плаща преди да подготвим офертата и демото и не се връща, ако решите да не продължите.'],
                    ['title' => 'Абонамент от втория месец', 'text' => 'Първият месец е в стартовата цена. След това абонаментът е €200 / месец за Core Изграждане и €650 / месец за Pro Ускорение. Enterprise Партньорство е от €1 200 / месец според индивидуалната оферта.'],
                    ['title' => 'Годишно плащане с 2 месеца безплатно', 'text' => 'Годишният абонамент е €2 000 вместо €2 400 за Core Изграждане и €6 500 вместо €7 800 за Pro Ускорение. Плаща се веднъж за 12 месеца, считано от втория месец.'],
                    ['title' => 'Фиксирана абонаментна цена за 6 месеца', 'text' => 'Абонаментната цена е фиксирана за първите 6 месеца. След това може да се промени, като ви уведомяваме предварително. При годишно плащане цената важи за цялата платена година.'],
                    ['title' => 'Реклама във Facebook и Instagram', 'text' => 'Бюджетът за реклама във Facebook и Instagram се плаща от вас директно на Meta. Ние следим рекламите и ви казваме какво носят.'],
                    ['title' => 'Какво покрива месечната такса', 'text' => 'Настройваме и поддържаме хостинга, домейна и фирмените имейл адреси. Плюс техническа поддръжка, редовни архиви и месечен отчет. Съдържателните единици и маркетингът зависят от пакета.'],
                    ['title' => 'Всичко е на ваше име', 'text' => 'Домейнът, профилите и достъпите са ваши от първия ден. Ако решите да си тръгнете, взимате всичко.'],
                    ['title' => 'Срок', 'text' => 'Стандартно от 1 до 2 седмици за изграждане. Уточняваме срока в началото и го спазваме.'],
                    ['title' => 'Честно за Google', 'text' => 'Не обещаваме позиция в Google. Отговаряме за работата си и ви даваме ясен месечен отчет какво се е променило.'],
                ],
                [
                    ['title' => 'Фиксирана цена', 'text' => 'Цената и това, което получавате, са записани в офертата.'],
                    ['title' => 'Месечен абонамент', 'text' => 'Плащате месечен абонамент според избрания пакет. Core Изграждане е от €500 / месец, Pro Ускорение от €800 / месец, а Enterprise Партньорство от €1 200 / месец според индивидуалната оферта.'],
                    ['title' => 'Годишно плащане с 2 месеца безплатно', 'text' => 'Годишният абонамент е €5 000 вместо €6 000 за Core Изграждане, €8 000 вместо €9 600 за Pro Ускорение и €12 000 вместо €14 400 за Enterprise Партньорство. Плаща се веднъж за 12 месеца.'],
                    ['title' => 'Фиксирана абонаментна цена за 6 месеца', 'text' => 'Абонаментната цена е фиксирана за първите 6 месеца. След това може да се промени, като ви уведомяваме предварително. При годишно плащане цената важи за цялата платена година.'],
                    ['title' => 'Реклама във Facebook и Instagram', 'text' => 'Бюджетът за реклама във Facebook и Instagram се плаща от вас директно на Meta. Ние следим рекламите и ви казваме какво носят.'],
                    ['title' => 'Какво покрива месечната такса', 'text' => 'Настройваме и поддържаме хостинга, домейна и фирмените имейл адреси. Плюс техническа поддръжка, редовни архиви и месечен отчет. Съдържателните единици и маркетингът зависят от пакета.'],
                    ['title' => 'Всичко е на ваше име', 'text' => 'Домейнът, профилите и достъпите са ваши от първия ден. Ако решите да си тръгнете, взимате всичко.'],
                    ['title' => 'Срок', 'text' => 'Стандартно от 1 до 2 седмици за изграждане. Уточняваме срока в началото и го спазваме.'],
                    ['title' => 'Честно за Google', 'text' => 'Не обещаваме позиция в Google. Отговаряме за работата си и ви даваме ясен месечен отчет какво се е променило.'],
                ],
            ],
        ],
        'en' => [
            [
                ['€400 one-off for the build and the first month', 'then €200 / month'],
                ['from €500 / month'],
            ],
            [
                ['€800 one-off for a new or refreshed website and the first month', 'then €650 / month'],
                ['from €800 / month'],
            ],
            [
                ['A custom quote', 'from €1,200 / month'],
                ['from €1,200 / month'],
            ],
            [
                [
                    ['title' => 'A fixed price', 'text' => 'The price and what you get are written in the quote.'],
                    ['title' => 'The start price in two parts', 'text' => 'For Core Build you pay €100 when you request a quote and demo (non-refundable) and the remaining €300 when you agree. For Pro Accelerate you pay €200 when you request a quote and demo (non-refundable) and the remaining €600 when you agree. The first amount is paid before we prepare the quote and the demo and is not refunded if you decide not to continue.'],
                    ['title' => 'Subscription from the second month', 'text' => 'The first month is in the start price. After that the subscription is €200 / month for Core Build and €650 / month for Pro Accelerate. Enterprise Partnership is from €1,200 / month under a custom quote.'],
                    ['title' => 'Yearly billing with 2 months free', 'text' => 'The yearly subscription is €2,000 instead of €2,400 for Core Build and €6,500 instead of €7,800 for Pro Accelerate. It is paid once for 12 months, counted from the second month.'],
                    ['title' => 'Fixed subscription price for 6 months', 'text' => 'The subscription price is fixed for the first 6 months. After that it may change, and we tell you in advance. With yearly billing the price holds for the whole paid year.'],
                    ['title' => 'Facebook and Instagram ads', 'text' => 'The ad budget for Facebook and Instagram is paid by you directly to Meta. We monitor the ads and tell you what they bring.'],
                    ['title' => 'What the monthly fee covers', 'text' => 'We set up and look after hosting, the domain and business email addresses. Plus technical support, regular backups and a monthly report. The content items and the marketing depend on the package.'],
                    ['title' => 'Everything is in your name', 'text' => 'The domain, profiles and access are yours from day one. If you decide to leave, you take everything with you.'],
                    ['title' => 'Timing', 'text' => 'Normally 1 to 2 weeks to build. We agree the deadline at the start and keep to it.'],
                    ['title' => 'Honest about Google', 'text' => 'We do not promise a Google ranking. We stand behind our work and give you a clear monthly report of what has changed.'],
                ],
                [
                    ['title' => 'A fixed price', 'text' => 'The price and what you get are written in the quote.'],
                    ['title' => 'Monthly subscription', 'text' => 'You pay a monthly subscription according to the package you choose. Core Build is from €500 / month, Pro Accelerate from €800 / month and Enterprise Partnership from €1,200 / month under a custom quote.'],
                    ['title' => 'Yearly billing with 2 months free', 'text' => 'The yearly subscription is €5,000 instead of €6,000 for Core Build, €8,000 instead of €9,600 for Pro Accelerate and €12,000 instead of €14,400 for Enterprise Partnership. It is paid once for 12 months.'],
                    ['title' => 'Fixed subscription price for 6 months', 'text' => 'The subscription price is fixed for the first 6 months. After that it may change, and we tell you in advance. With yearly billing the price holds for the whole paid year.'],
                    ['title' => 'Facebook and Instagram ads', 'text' => 'The ad budget for Facebook and Instagram is paid by you directly to Meta. We monitor the ads and tell you what they bring.'],
                    ['title' => 'What the monthly fee covers', 'text' => 'We set up and look after hosting, the domain and business email addresses. Plus technical support, regular backups and a monthly report. The content items and the marketing depend on the package.'],
                    ['title' => 'Everything is in your name', 'text' => 'The domain, profiles and access are yours from day one. If you decide to leave, you take everything with you.'],
                    ['title' => 'Timing', 'text' => 'Normally 1 to 2 weeks to build. We agree the deadline at the start and keep to it.'],
                    ['title' => 'Honest about Google', 'text' => 'We do not promise a Google ranking. We stand behind our work and give you a clear monthly report of what has changed.'],
                ],
            ],
        ],
    ];

    /**
     * @return array{changed: list<string>, review: list<string>} променените записи и блоковете, в които още има стари суми („ключ/език“)
     */
    public static function apply(): array
    {
        $changed = [];
        $review = [];

        foreach (ContentBlock::all() as $block) {
            $base = explode(':', $block->key, 2);
            $editable = array_key_exists($block->key, Content::BLOCKS) || ($base[0] === 'seo' && count($base) === 2 && ! str_starts_with($base[1], 'backup'));
            if (! $editable || str_contains($block->key, ':backup-')) {
                continue;
            }

            $value = self::transform((array) $block->value, $block->locale);
            if ($value !== $block->value) {
                ContentBlock::firstOrCreate(
                    ['key' => $block->key . ':' . self::BACKUP, 'locale' => $block->locale],
                    ['value' => $block->value]
                );
                $block->update(['value' => $value]);
                $changed[] = $block->key . '/' . $block->locale;
            }

            if (self::leftovers($value)) {
                $review[] = $block->key . '/' . $block->locale;
            }
        }

        if ($changed) {
            app(Content::class)->flush();
        }

        return ['changed' => $changed, 'review' => $review];
    }

    /** Сменя всеки списък, който е точно старият по подразбиране, и всеки низ (на всяка дълбочина), който е точно старият текст; всичко останало остава. */
    public static function transform(array $value, string $locale = 'bg'): array
    {
        $lang = $locale === 'en' ? 'en' : 'bg';
        $strings = self::REPLACEMENTS[$lang];
        $lists = self::LISTS[$lang];

        $walk = function (mixed $node) use (&$walk, $strings, $lists) {
            if (is_string($node)) {
                return $strings[$node] ?? $node;
            }
            if (! is_array($node)) {
                return $node;
            }
            if (array_is_list($node)) {
                foreach ($lists as [$old, $new]) {
                    if ($node === $old) {
                        return $new;
                    }
                }
            }

            return array_map($walk, $node);
        };

        return $walk($value);
    }

    /** Стойностите (на всяка дълбочина), в които още има стара сума или дума за капаро. */
    public static function leftovers(array $value): array
    {
        $found = [];
        array_walk_recursive($value, function ($item) use (&$found) {
            if (is_string($item) && preg_match(self::OLD_PATTERN, $item)) {
                $found[] = $item;
            }
        });

        return $found;
    }
}
