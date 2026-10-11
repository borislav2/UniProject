@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $contact = config('creatium.contact');
    $phone = $contact['phones'][0] ?? null;
    $d = $data;
    $money = fn ($v) => trim($v) . ' €';
    $name = \App\Support\ClientEmails::PACKAGES[$package];
    $validUntil = \Illuminate\Support\Carbon::parse($d['valid_until'])->format('d.m.Y');

    $pkg = [
        'start' => [
            'headline' => 'Сайтът за ' . $d['business'] . ' може да е готов до ' . $d['deadline'],
            'sub' => 'Една страница, която казва всичко важно за вас, изглежда добре на телефон и ви носи запитвания.',
            'why' => 'най-бързият начин да имате професионално присъствие онлайн и да започнете да получавате запитвания.',
            'includes' => [
                'Една страница, която за секунди казва кои сте, какво предлагате и как да се свържат с вас',
                'Изглежда добре и работи бързо на телефон, таблет и компютър',
                'Форма за запитвания, която идва директно на вашия имейл',
                'Основна настройка за Google, за да могат да ви намерят',
            ],
            'upsell' => 'Ако по-късно ви трябват повече страници, минаваме лесно на пакет Бизнес, без да започваме отначало.',
            'needs' => [],
            'steps' => [
                ['Започваме', 'и до няколко дни ви показваме първа версия.'],
                ['Пускаме сайта,', 'след като го одобрите.'],
            ],
        ],
        'biznes' => [
            'headline' => 'Сайт, който показва всичко, което предлагате',
            'sub' => 'До 5 страници за услугите, цените и снимките ви, със SEO настройка и профил в Google Карти.',
            'why' => 'място, където клиентите виждат услугите, цените и снимките ви и ви пишат директно, плюс профил в Google Карти, за да ви намират и когато търсят наблизо.',
            'includes' => [
                'Всичко от пакет Старт: работи на телефон, форма за запитвания, настройка за Google',
                'До 5 страници: ' . ($d['pages'] ?? ''),
                'SEO настройка: заглавия, описания и структура, които Google разбира',
                'Профил в Google Business, за да ви намират в Google Карти',
            ],
            'upsell' => 'Ако искате да се качвате в Google месец след месец, пакет Растеж добавя редовна SEO работа и отчет.',
            'needs' => ['Работно време, адрес и услуги за профила в Google Business'],
            'steps' => [
                ['Започваме', 'и ви показваме как върви работата, преди сайтът да е готов.'],
                ['Пускаме сайта', 'и профила в Google Business, след като ги одобрите.'],
            ],
        ],
        'rastezh' => [
            'headline' => 'Сайт и работа всеки месец, за да ви намират в Google',
            'sub' => 'Сайт плюс постоянна грижа: нови текстове и ясен отчет всеки месец какво се е променило.',
            'why' => 'сайт плюс постоянна работа всеки месец, за да расте видимостта ви в Google, а вие да знаете какво получавате за парите си.',
            'includes' => [
                'Всичко от пакет Бизнес: до 5 страници, SEO настройка, профил в Google Business',
                'SEO работа всеки месец',
                'Нови текстове за сайта: ' . ($d['texts_per_month'] ?? '') . ' на месец',
                'Следим къде излизате в Google по думите, които са важни за вас',
                'Кратък отчет всеки месец на разбираем език: какво направихме и какво се промени',
            ],
            'upsell' => 'Не обещаваме конкретна позиция в Google. Обещаваме редовна работа и ясен отчет всеки месец.',
            'needs' => array_values(array_filter([
                'Работно време, адрес и услуги за профила в Google Business',
                'Основните услуги и градове, по които искате да ви намират' . (! empty($d['keywords']) ? ', напр. ' . $d['keywords'] : ''),
            ])),
            'steps' => [
                ['Правим и пускаме сайта,', 'след като го одобрите.'],
                ['Започва месечната работа', 'и първият отчет идва в края на първия месец.'],
            ],
        ],
    ][$package];

    if (! empty($d['extra_included'])) {
        $pkg['includes'][] = $d['extra_included'];
    }

    $price = [['Изработка (еднократно)', $money($d['price'])]];
    if ($package === 'rastezh') {
        $price[] = ['Месечна поддръжка и SEO', $money($d['monthly_price']) . '/месец'];
        $price[] = ['Минимален срок', $d['min_term']];
    } else {
        $price[] = ['Срок', $d['deadline']];
    }
    $price[] = ['Плащане', $d['payment']];

    $needs = array_merge([
        'Лого и снимки (ако нямате, ще предложим решение)',
        'Текстовете или основната информация, от която да ги напишем',
        'Достъп до домейна или желан нов адрес',
    ], $pkg['needs']);
@endphp

@section('eyebrow', 'Оферта · пакет ' . $name)
@section('headline', $pkg['headline'])
@section('sub', $pkg['sub'])

@section('body')
<tr><td style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте, {{ $d['client_name'] }},</p>
<p style="margin:0;">Благодарим ви за времето и за разговора. Подготвихме тази оферта специално за {{ $d['business'] }}, според това, което ни разказахте.</p>
</td></tr>

@if(! empty($d['heard']))
<tr><td style="padding:22px 36px 0;">@include('emails.client._parts', ['part' => 'quote', 'label' => 'Какво чухме от вас', 'text' => $d['heard']])</td></tr>
@endif

<tr><td style="padding:18px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
{{ ! empty($d['heard']) ? 'Затова ви предлагаме' : 'Предлагаме ви' }} пакет <strong>{{ $name }}</strong>: {{ $pkg['why'] }}
</td></tr>

<tr><td style="padding:24px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:12px;">
<tr><td style="padding:20px 24px 6px;">@include('emails.client._parts', ['part' => 'label', 'text' => 'Вашата оферта', 'color' => '#9cc0eb'])</td></tr>
<tr><td style="padding:4px 24px 6px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($price as [$label, $value])
<tr><td style="padding:10px 12px 10px 0;border-bottom:1px solid #2a4468;{{ $f }}font-size:14px;color:#c5d6ec;">{{ $label }}</td><td align="right" style="padding:10px 0;border-bottom:1px solid #2a4468;{{ $f }}font-size:16px;font-weight:bold;color:#ffffff;">{{ $value }}</td></tr>
@endforeach
</table></td></tr>
<tr><td style="padding:8px 24px 20px;{{ $f }}font-size:13px;color:#9cc0eb;">&#10003; Цената е фиксирана. Без скрити такси и изненади в края.</td></tr>
</table>
</td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво получавате'])
<div style="height:8px;line-height:8px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => $pkg['includes']])
</td></tr>

<tr><td style="padding:14px 36px 0;"><div style="border-left:3px solid #dde3ec;padding:2px 0 2px 14px;{{ $f }}font-size:13px;line-height:1.6;color:#64748b;">@if(! empty($d['not_included']))<strong style="color:#475569;">Не е включено:</strong> {{ $d['not_included'] }}. @endif{{ $pkg['upsell'] }}</div></td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Защо с нас'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'why', 'items' => [
    ['Работите директно с нас двамата', 'Без акаунт мениджъри и посредници. Пишете ни и отговаря човекът, който прави сайта ви.'],
    ['Виждате сайта, преди да е готов', 'Показваме ви работата по пътя и я съобразяваме с вас, не накрая.'],
    ['Не изчезваме след пускането', 'Ако нещо не работи или искате промяна, пишете ни директно.'],
]])
</td></tr>

<tr><td style="padding:24px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-radius:12px;"><tr><td style="padding:20px 24px 14px;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво ни трябва от вас'])
<div style="height:6px;line-height:6px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => $needs, 'color' => '#0f1a2b'])
</td></tr></table>
</td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Как започваме'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'steps', 'items' => array_merge([
    ['Отговаряте с „Да“', 'на този имейл.'],
    ['Изпращаме фактура', 'за аванса (' . $money($d['advance']) . ') и запазваме мястото ви в графика.'],
], $pkg['steps'])])
</td></tr>

<tr><td style="padding:26px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:2px solid #0f1a2b;border-radius:12px;">
<tr><td align="center" style="padding:24px 24px;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Запазете мястото си'])
<div style="{{ $f }}font-size:18px;line-height:1.35;font-weight:bold;color:#0f1a2b;padding-top:8px;">Цената и срокът са запазени до {{ $validUntil }}</div>
<div style="{{ $f }}font-size:14px;line-height:1.6;color:#475569;padding:8px 0 4px;">Работим с малко клиенти наведнъж, за да има време за всеки. След тази дата цената и срокът може да се променят, защото графикът ни се пълни.</div>
@if(! empty($d['slots']))
<div style="{{ $f }}font-size:14px;line-height:1.6;padding:10px 0 4px;"><span style="background:#fde68a;color:#0f1a2b;font-weight:bold;padding:3px 8px;border-radius:6px;">{{ $d['slots'] }}</span></div>
@endif
@if(! empty($d['bonus']))
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-top:14px;background:#fff7e0;border:1px dashed #e0b84a;border-radius:10px;">
<tr><td align="center" style="padding:12px 16px;{{ $f }}font-size:14px;line-height:1.55;color:#5a4500;"><strong>Бонус, ако потвърдите до {{ $validUntil }}:</strong> {{ $d['bonus'] }}</td></tr>
</table>
@endif
<div style="height:18px;line-height:18px;font-size:0;">&nbsp;</div>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:10px;"><a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Да, започваме: пакет ' . $name) }}" style="display:inline-block;padding:14px 26px;{{ $f }}font-size:15px;font-weight:bold;color:#ffffff;text-decoration:none;">Да, искам да започнем</a></td>
</tr></table>
<div style="{{ $f }}font-size:13px;color:#64748b;padding-top:10px;">или просто отговорете с „Да“ на този имейл</div>
</td></tr></table>
</td></tr>
<tr><td style="height:28px;line-height:28px;font-size:0;">&nbsp;</td></tr>
@endsection

@if($phone)
@section('ps')Ако нещо в офертата не е ясно или искате да променим нещо, звъннете ни на <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" style="color:#2d63a3;font-weight:bold;text-decoration:none;">{{ $phone }}</a>. Пет минути разговор са по-добре от едно недоразумение.@if(! empty($d['bonus'])) И не забравяйте: бонусът важи до {{ $validUntil }}.@endif @endsection
@endif
