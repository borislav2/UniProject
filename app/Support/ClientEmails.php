<?php

namespace App\Support;

/**
 * Имейл шаблоните към клиенти, които се пращат от админ панела (меню „Имейли до клиенти“).
 *
 * Всеки шаблон има изглед в resources/views/emails/client/ и списък с полета за формата.
 * Полетата с 'default' са предварително попълнени; 'required' => false са по желание
 * (празно поле = редът изчезва от имейла).
 */
class ClientEmails
{
    public const PACKAGES = [
        'start' => 'Старт',
        'biznes' => 'Бизнес',
        'rastezh' => 'Растеж',
    ];

    public static function all(): array
    {
        $common = [
            'client_name' => ['label' => 'Име на клиента', 'help' => 'Както ще се обърнем: „Здравейте, …“', 'required' => true],
            'business' => ['label' => 'За кой бизнес', 'help' => 'Довършва изречението „сайт за …“, напр. „козметичния ви салон в София“', 'required' => true],
            'sender' => ['label' => 'Подпис', 'help' => 'Кой подписва имейла', 'required' => true],
        ];

        // FOMO полетата са по желание и се показват само ако са попълнени: пишете ги само когато са верни.
        $slots = ['slots' => ['label' => 'Свободни места (FOMO)', 'required' => false, 'help' => 'Само ако е вярно, напр. „Имаме място за още 2 нови проекта през ноември.“ Празно = не се показва.']];

        $offer = fn (array $extra) => $common + [
            'heard' => ['label' => 'Какво чухме от клиента', 'type' => 'textarea', 'required' => false, 'help' => '1–3 изречения от разговора, с неговите думи: какво иска и какво го спира. Това показва, че сме слушали. Силно препоръчително.'],
            'price' => ['label' => 'Цена за изработка (€)', 'required' => true, 'help' => 'Само числото, напр. 450'],
        ] + $extra + [
            'deadline' => ['label' => 'Срок за изработка', 'required' => true, 'default' => '2 седмици'],
            'advance' => ['label' => 'Аванс (€)', 'required' => true, 'help' => 'Сумата във фактурата за аванс'],
            'valid_until' => ['label' => 'Офертата е валидна до', 'type' => 'date', 'required' => true],
            'extra_included' => ['label' => 'Допълнително включено', 'required' => false, 'help' => 'Ако сте договорили нещо извън пакета. Празно = не се показва.'],
        ] + $slots + [
            'bonus' => ['label' => 'Бонус при потвърждение до датата', 'required' => false, 'help' => 'По желание, напр. „първият месец поддръжка без допълнително заплащане“. Празно = не се показва.'],
        ];

        return [
            'inquiry-reply' => [
                'label' => 'Отговор на запитване за сайт',
                'description' => 'Първи отговор: лична бележка, въпроси към клиента, трите пакета и как продължаваме.',
                'subject' => 'Получихме запитването ви: ето как продължаваме',
                'view' => 'emails.client.inquiry-reply',
                'fields' => $common + [
                    'personal_note' => ['label' => 'Лична бележка', 'type' => 'textarea', 'required' => false, 'help' => 'По желание, но прави най-силно впечатление: какво забелязахте за бизнеса им, напр. „Разгледахме профила ви в Google и видяхме, че…“'],
                ] + $slots,
            ],
            'offer-start' => [
                'label' => 'Оферта: пакет Старт',
                'description' => 'Една страница, работи на телефон, форма за запитвания, основна настройка за Google.',
                'subject' => 'Вашата оферта е готова: пакет Старт',
                'view' => 'emails.client.offer',
                'package' => 'start',
                'fields' => $offer([
                    'payment' => ['label' => 'Начин на плащане', 'required' => true, 'default' => '50% аванс, 50% при пускане'],
                ]) + [
                    'not_included' => ['label' => 'Не е включено', 'required' => false, 'default' => 'хостинг и домейн, писане на текстове, снимки'],
                ],
            ],
            'offer-biznes' => [
                'label' => 'Оферта: пакет Бизнес',
                'description' => 'До 5 страници, SEO настройка и профил в Google Business.',
                'subject' => 'Вашата оферта е готова: пакет Бизнес',
                'view' => 'emails.client.offer',
                'package' => 'biznes',
                'fields' => $offer([
                    'payment' => ['label' => 'Начин на плащане', 'required' => true, 'default' => '50% аванс, 50% при пускане'],
                    'pages' => ['label' => 'Страници', 'required' => true, 'default' => 'Начало, Услуги, Цени, За нас, Контакти'],
                ]) + [
                    'not_included' => ['label' => 'Не е включено', 'required' => false, 'default' => 'хостинг и домейн, професионални снимки'],
                ],
            ],
            'offer-rastezh' => [
                'label' => 'Оферта: пакет Растеж',
                'description' => 'Сайт плюс SEO работа, нови текстове и отчет всеки месец.',
                'subject' => 'Вашата оферта е готова: пакет Растеж',
                'view' => 'emails.client.offer',
                'package' => 'rastezh',
                'fields' => $offer([
                    'monthly_price' => ['label' => 'Месечна цена (€)', 'required' => true, 'help' => 'Само числото, напр. 150'],
                    'min_term' => ['label' => 'Минимален срок', 'required' => true, 'default' => '6 месеца'],
                    'payment' => ['label' => 'Начин на плащане', 'required' => true, 'default' => '50% аванс, месечно до 5-о число'],
                    'texts_per_month' => ['label' => 'Нови текстове на месец', 'required' => true, 'default' => '2'],
                    'keywords' => ['label' => 'Пример за търсене в Google', 'required' => false, 'help' => 'напр. „зъболекар Варна“'],
                ]) + [
                    'not_included' => ['label' => 'Не е включено', 'required' => false, 'default' => 'платена реклама в Google, хостинг и домейн'],
                ],
            ],
        ];
    }

    public static function find(string $key): ?array
    {
        $template = self::all()[$key] ?? null;

        return $template ? $template + ['key' => $key] : null;
    }

    /** Валидиращи правила: текст до 255 знака, дълъг текст (textarea) до 1000, датите са дати от днес нататък. */
    public static function rules(array $template): array
    {
        $rules = [];
        foreach ($template['fields'] as $name => $field) {
            $type = $field['type'] ?? 'text';
            $rules["data.$name"] = array_filter([
                ($field['required'] ?? true) ? 'required' : 'nullable',
                $type === 'date' ? 'date' : 'string',
                match ($type) { 'date' => 'after_or_equal:today', 'textarea' => 'max:1000', default => 'max:255' },
            ]);
        }

        return $rules;
    }
}
