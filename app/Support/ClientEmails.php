<?php

namespace App\Support;

/**
 * Имейл шаблоните към клиенти, които се пращат от админ панела (меню „Имейли до клиенти“).
 *
 * Всеки шаблон има изглед в resources/views/emails/client/ и списък с полета за формата.
 * Полетата с 'default' са предварително попълнени; 'required' => false са по желание
 * (празно поле = редът изчезва от имейла). В темата {client_name} и {business} се заменят с полетата.
 * Текстовете на шаблоните са дадени от собствениците (октомври 2026).
 */
class ClientEmails
{
    /** Пакетите, за които има оферта (ключ => име като на /paketi). */
    public const PACKAGES = [
        'start' => 'Стартов двигател',
        'active' => 'Активен Маркетинг & Растеж',
    ];

    /** Етикети за историята: шаблони, които вече ги няма, и първите писма от „Потенциални клиенти“. */
    public const RETIRED = [
        'outreach' => 'Първо писмо (потенциален клиент)',
        'offer-biznes' => 'Оферта: пакет Бизнес (стар)',
        'offer-rastezh' => 'Оферта: пакет Растеж (стар)',
    ];

    public static function all(): array
    {
        $common = [
            'client_name' => ['label' => 'Име на клиента', 'help' => 'Както ще се обърнем: „Здравейте, …“', 'required' => true],
            'business' => ['label' => 'Име на бизнеса / компанията', 'help' => 'напр. Студио „Лавандула“', 'required' => true],
            'sender' => ['label' => 'Подпис', 'help' => 'Кой подписва имейла', 'required' => true, 'default' => 'Екипът на Creatium Lab'],
        ];

        $payment = '50% авансово при старт на проекта и 50% след одобрение на готовата демо версия.';

        return [
            'inquiry-reply' => [
                'label' => 'Отговор на запитване',
                'description' => 'Първи отговор: подходът ни, въпроси към клиента, трите пакета и какво следва.',
                'subject' => '{client_name}, благодарим ви за запитването | Creatium Lab',
                'view' => 'emails.client.inquiry-reply',
                'fields' => $common + [
                    'personal_note' => ['label' => 'Лична бележка', 'type' => 'textarea', 'required' => false, 'help' => 'По желание: какво забелязахте за бизнеса им. Празно = не се показва.'],
                ],
            ],
            'offer-start' => [
                'label' => 'Оферта: Стартов двигател',
                'description' => 'Уебсайт + маркетинг основа: €450 изработка и €150 на месец.',
                'subject' => 'Проектно предложение: Дигитален старт за {business} | Creatium Lab',
                'view' => 'emails.client.offer',
                'package' => 'start',
                'fields' => $common + [
                    'price' => ['label' => 'Първоначална инвестиция (€)', 'required' => true, 'default' => '450', 'help' => 'Само числото'],
                    'monthly_price' => ['label' => 'Месечен абонамент (€)', 'required' => true, 'default' => '150', 'help' => 'Само числото'],
                    'deadline' => ['label' => 'Срок за изпълнение', 'required' => true, 'default' => '2 седмици (от момента на подаване на началните материали)'],
                    'payment' => ['label' => 'Условия на плащане', 'required' => true, 'default' => $payment],
                ],
            ],
            'offer-active' => [
                'label' => 'Оферта: Активен Маркетинг & Растеж',
                'description' => 'Уеб платформа + пълен маркетинг сервиз: €850 изработка и старт, €400 на месец.',
                'subject' => 'Проектно предложение: Дигитален растеж и маркетинг за {business} | Creatium Lab',
                'view' => 'emails.client.offer',
                'package' => 'active',
                'fields' => $common + [
                    'price' => ['label' => 'Първоначална инвестиция (€)', 'required' => true, 'default' => '850', 'help' => 'Само числото'],
                    'monthly_price' => ['label' => 'Месечен абонамент (€)', 'required' => true, 'default' => '400', 'help' => 'Само числото'],
                    'deadline' => ['label' => 'Срок за изпълнение', 'required' => true, 'default' => '2 до 3 седмици (от момента на подаване на началните параметри)'],
                    'payment' => ['label' => 'Условия на плащане', 'required' => true, 'default' => $payment],
                    'valid_until' => ['label' => 'Параметрите и срокът са валидни до', 'type' => 'date', 'required' => true],
                ],
            ],
            'monthly-report' => [
                'label' => 'Месечен отчет',
                'description' => 'За клиентите на абонамент: какво направихме през месеца, резултати, състояние на сайта и план за следващия.',
                'subject' => 'Месечен отчет за {business} | Creatium Lab',
                'view' => 'emails.client.monthly-report',
                'fields' => $common + [
                    'month' => ['label' => 'Месец', 'required' => true, 'default' => now('Europe/Sofia')->locale('bg')->isoFormat('MMMM YYYY'), 'help' => 'напр. октомври 2026'],
                    'done' => ['label' => 'Какво направихме', 'type' => 'textarea', 'required' => true, 'help' => 'По една точка на ред.'],
                    'results' => ['label' => 'Резултати', 'type' => 'textarea', 'required' => false, 'help' => 'По желание, по една на ред: запитвания, позиции в Google, посещения. Само истински числа.'],
                    'site_status' => ['label' => 'Състояние на сайта', 'required' => false, 'help' => 'Попълва се от наблюдението; можете да го промените.'],
                    'next' => ['label' => 'План за следващия месец', 'type' => 'textarea', 'required' => false, 'help' => 'По желание, по една точка на ред.'],
                ],
            ],
        ];
    }

    public static function find(string $key): ?array
    {
        $template = self::all()[$key] ?? null;

        return $template ? $template + ['key' => $key] : null;
    }

    public static function label(string $key): string
    {
        return self::all()[$key]['label'] ?? self::RETIRED[$key] ?? $key;
    }

    /** Попълва {client_name} и {business} в темата. */
    public static function subject(string $subject, array $data): string
    {
        return strtr($subject, ['{client_name}' => $data['client_name'] ?? '', '{business}' => $data['business'] ?? '']);
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
                match ($type) { 'date' => 'after_or_equal:today', 'textarea' => 'max:2000', default => 'max:255' },
            ]);
        }

        return $rules;
    }
}
