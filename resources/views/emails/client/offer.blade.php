@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $contact = config('creatium.contact');
    $phone = $contact['phones'][0] ?? null;
    $d = $data;
    $money = fn ($v) => '€' . trim($v);
    $name = \App\Support\ClientEmails::PACKAGES[$package];

    $pkg = [
        'start' => [
            'eyebrow' => 'Проектно предложение · Дигитален старт',
            'intro' => 'Благодарим ви за отделеното време и конструктивния разговор. Въз основа на вашите бизнес цели и специфичното позициониране на ' . $d['business'] . ', подготвихме настоящата персонализирана оферта.',
            'why' => 'оптималното решение за бързо, репрезентативно и технически обезпечено стъпване на пазара, с гарантирана възможност за бъдещо надграждане.',
            'solution' => 'Пакет Старт (Интегриран уебсайт + Маркетинг основа)',
            'build_label' => 'Първоначална инвестиция (изработка)',
            'monthly_label' => 'Месечен абонамент (поддръжка и грижа)',
            'groups' => [
                ['Висококонвертираща уеб платформа', [
                    ['Целенасочена архитектура', 'Лендинг структура (Landing Page), проектирана по най-добрите UX/UI стандарти, която води посетителя директно към контакт и запитване.'],
                    ['Мобилна безупречност', 'Светкавично зареждане и перфектен изглед на смарт устройства, таблети и компютри.'],
                    ['Търговски функционалности', 'Динамична форма за запитвания (която изпраща писмата директно в пощата ви), интерактивна Google карта и бутони за незабавно обаждане.'],
                ]],
                ['Маркетинг фундамент & Аналитика', [
                    ['Google Analytics & Search Console', 'Внедряване на аналитични кодове за проследяване на входящия трафик, източниците на клиенти и тяхното поведение.'],
                    ['Базова SEO оптимизация', 'Техническа структура за правилно индексиране в Google търсачката.'],
                    ['Корпоративна идентичност', 'Настройка на служебни имейл адреси с вашия домейн.'],
                ]],
                ['Ежемесечен абонаментен сервиз (' . $money($d['monthly_price']) . '/мес)', [
                    ['Пълна техническа безопасност', '24/7 мониторинг за достъпност, хостинг инфраструктура и защита от сривове.'],
                    ['Сигурност на данните', 'Автоматични ежедневни резервни копия (Backups) и системни обновления.'],
                    ['Оперативна поддръжка', 'Поемаме регулярни промени по цени, текстове, оферти или снимки, за да спестим вашето време.'],
                ]],
            ],
            'guarantees' => [
                ['Интерактивно демо преди старт', 'Преглеждате и тествате сайта в защитена тестова среда. Нанасяме корекции и го пускаме официално едва когато сте напълно доволни.'],
                ['Гъвкавост и мащабируемост', 'Сайтът е изграден с чист код и модулна структура. В бъдеще можете лесно да добавяте нови страници, онлайн плащания или системи без да започвате отначало.'],
                ['Директен контакт с екипа', 'Комуникирате лично със специалистите, които проектират и управляват вашия сайт.'],
            ],
            'next_label' => 'Какво следва?',
            'next_intro' => 'За да потвърдим началния график и да стартираме работата:',
            'next' => [
                ['Отговорете', 'на този имейл с „Одобрявам офертата“.'],
                ['Изпращаме ви', 'кратък договор и фактура за авансово плащане.'],
                ['Организираме', 'встъпителен процес за събиране на наличните материали (лого, снимки, насоки) и започваме разработката.'],
            ],
        ],
        'active' => [
            'eyebrow' => 'Проектно предложение · Дигитален растеж и маркетинг',
            'intro' => 'Благодарим ви за отделеното време и задълбочения разговор. Въз основа на вашите бизнес цели, необходимостта от онлайн продажби/резервации и амбицията на ' . $d['business'] . ' да се наложи на пазара, подготвихме настоящата персонализирана оферта.',
            'why' => 'цялостна система, която не просто изгражда функционален уебсайт, а внедрява пълен маркетингов двигател за постоянни запитвания и клиенти.',
            'solution' => 'Пакет Активен Маркетинг & Растеж (Уеб платформа + Пълен маркетинг сервиз)',
            'build_label' => 'Първоначална инвестиция (изработка & старт)',
            'monthly_label' => 'Месечен абонамент (маркетинг, SEO, AI & поддръжка)',
            'groups' => [
                ['Търговска уеб платформа за висока конверсия', [
                    ['Разширена архитектура', 'Индивидуално структурирани секции и страници (Услуги, Ценообразуване, За нас, Портфолио, Контакти), проектирани по най-добрите UX/UI стандарти за продажба.'],
                    ['Онлайн разплащания & Резервации', 'Внедряване на платформи за онлайн плащания с карти (Stripe, myPOS, Apple Pay, Google Pay) и/или интерактивен календар за запазване на часове.'],
                    ['Бърз достъп (Social Login)', 'Улеснен вход за потребители с един клик през Google профил.'],
                    ['Максимална скорост', 'Светкавично зареждане и перфектна адаптация за мобилни устройства и компютри.'],
                ]],
                ['Маркетинг архитектура, GEO, SEO & AI Позициониране', [
                    ['Локално SEO & Google Maps', 'Пълна оптимизация и верификация на вашия Google Business профил за привличане на клиенти на местно ниво.'],
                    ['Оптимизация за AI Търсачки (AIO)', 'Структуриране на данните в сайта с микроданни, за да бъдете препоръчвани от новите AI асистенти (ChatGPT, Gemini).'],
                    ['Проследяваща аналитика', 'Внедряване на Google Analytics, Tag Manager и проследяващи пиксели за конверсионни фунии.'],
                    ['Бранд съдържание & Копирайтинг', 'Създаване на силни, продаващи текстове и статии, съобразени с аудиторията ви.'],
                ]],
                ['Всекимесечен маркетингов & технически сервиз (' . $money($d['monthly_price']) . '/мес)', [
                    ['Автоматизиран имейл маркетинг', 'Изграждане и управление на автоматични имейл последователности за нови запитвания и задържане на стари клиенти.'],
                    ['SEO & AI Развитие', 'Ежемесечна работа по съдържанието и позиционирането ви на челни места в Google и AI базите данни.'],
                    ['Пълна техническа безопасност', '24/7 мониторинг за достъпност, хостинг инфраструктура, защита от сривове и ежедневни архиви (Backups).'],
                    ['Оперативна поддръжка', 'Приоритетно нанасяне на текущи промени по цени, оферти, снимки или нови секции по всяко време.'],
                ]],
            ],
            'guarantees' => [
                ['Интерактивно демо преди старт', 'Преглеждате и тествате целия сайт и маркетингови системи в защитена среда. Нанасяме финални корекции и пускаме проекта онлайн едва след пълното ви одобрение.'],
                ['Директен експертен контакт', 'Работите лично със специалистите, които кодират платформата и управляват маркетинговата ви стратегия.'],
                ['Ограничен прием на проекти', 'Поемаме ограничен брой клиенти месечно, за да гарантираме максимален фокус върху вашия бизнес.'],
            ],
            'next_label' => 'Карта за изпълнение на проекта',
            'next_intro' => null,
            'next' => [
                ['Потвърждение:', 'Отговорете с „Одобрявам офертата“ на този имейл.'],
                ['Администрация:', 'Изпращаме ви кратък договор и фактура за авансово плащане.'],
                ['Първоначален брифинг:', 'Събираме необходимите материали (лого, достъпи, насоки) и стартираме техническото кодиране и маркетинговата настройка.'],
                ['Демо & Официален старт:', 'Преглеждаме интерактивното демо заедно, финализираме детайлите и пускаме новия ви дигитален двигател онлайн.'],
            ],
        ],
    ][$package];

    $params = [
        ['Избрано решение', $pkg['solution']],
        [$pkg['build_label'], $money($d['price']) . ' (еднократно)'],
        [$pkg['monthly_label'], $money($d['monthly_price']) . ' / месец'],
        ['Срок за изпълнение', $d['deadline']],
        ['Условия на плащане', $d['payment']],
    ];
@endphp

@section('eyebrow', $pkg['eyebrow'])
@section('headline'){{ $name }} за {{ $d['business'] }}@endsection
@section('sub', 'Персонализирана оферта, подготвена след разговора ни.')

@section('body')
<tr><td style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте, {{ $d['client_name'] }},</p>
<p style="margin:0 0 14px;">{{ $pkg['intro'] }}</p>
<p style="margin:0;">За вашия проект препоръчваме <strong>Пакет {{ $name }}</strong> – {{ $pkg['why'] }}</p>
</td></tr>

<tr><td style="padding:24px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:12px;">
<tr><td style="padding:20px 24px 6px;">@include('emails.client._parts', ['part' => 'label', 'text' => 'Параметри на предложението', 'color' => '#9cc0eb'])</td></tr>
<tr><td style="padding:4px 24px 18px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($params as [$label, $value])
<tr><td valign="top" style="padding:10px 12px 10px 0;border-bottom:1px solid #2a4468;{{ $f }}font-size:13px;line-height:1.5;color:#c5d6ec;width:45%;">{{ $label }}</td><td valign="top" style="padding:10px 0;border-bottom:1px solid #2a4468;{{ $f }}font-size:14px;line-height:1.5;font-weight:bold;color:#ffffff;">{{ $value }}</td></tr>
@endforeach
</table></td></tr>
</table>
</td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво включва вашият Пакет ' . $name])
</td></tr>
@foreach($pkg['groups'] as [$title, $items])
<tr><td style="padding:16px 36px 0;{{ $f }}">
<div style="font-size:16px;font-weight:bold;color:#0f1a2b;">{{ $loop->iteration }}. {{ $title }}</div>
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:4px;">
@foreach($items as [$head, $text])
<tr><td width="26" valign="top" style="padding:6px 0;{{ $f }}font-size:15px;font-weight:bold;color:#2d63a3;">&#10003;</td><td style="padding:6px 0;{{ $f }}font-size:14px;line-height:1.55;color:#475569;"><strong style="color:#0f1a2b;">{{ $head }}:</strong> {{ $text }}</td></tr>
@endforeach
</table>
</td></tr>
@endforeach

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Вашите гаранции за качество'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'why', 'items' => $pkg['guarantees']])
</td></tr>

<tr><td style="padding:24px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-radius:12px;"><tr><td style="padding:20px 24px 14px;">
@include('emails.client._parts', ['part' => 'label', 'text' => $pkg['next_label']])
@if($pkg['next_intro'])<div style="{{ $f }}font-size:14px;line-height:1.6;color:#475569;padding-top:6px;">{{ $pkg['next_intro'] }}</div>@endif
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'steps', 'items' => $pkg['next']])
</td></tr></table>
</td></tr>

@if(! empty($d['valid_until']))
<tr><td style="padding:20px 36px 0;{{ $f }}font-size:14px;line-height:1.6;"><span style="background:#fde68a;color:#0f1a2b;font-weight:bold;padding:3px 8px;border-radius:6px;">Важно: Посочените параметри и запазеният срок в нашия график са валидни до {{ \Illuminate\Support\Carbon::parse($d['valid_until'])->format('d.m.Y') }} г.</span></td></tr>
@endif

<tr><td style="padding:24px 36px 0;" align="center">
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:10px;"><a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Одобрявам офертата: ' . $name) }}" style="display:inline-block;padding:13px 24px;{{ $f }}font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">Одобрявам офертата</a></td>
</tr></table>
</td></tr>

<tr><td style="padding:22px 36px 28px;{{ $f }}font-size:14px;line-height:1.6;color:#475569;">
Оставаме на разположение за въпроси{{ $package === 'active' ? ' или кратък уточняващ разговор' : ' или допълнителни уточнения' }}.
</td></tr>
@endsection

@if($package === 'active' && $phone)
@section('ps')Ако искате да уточним конкретен детайл по функционалностите, се свържете с нас на <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" style="color:#2d63a3;font-weight:bold;text-decoration:none;">{{ $phone }}</a>.@endsection
@endif
