@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $contact = config('creatium.contact');
    $phone = $contact['phones'][0] ?? null;
    $d = $data;
    $money = fn ($v) => trim($v) . ' €';
    $name = \App\Support\ClientEmails::PACKAGES[$package];

    $pkg = [
        'start' => [
            'headline' => 'Сайт за ' . $d['business'] . ', готов за ' . $d['deadline'],
            'sub' => 'Една страница с най-важното за бизнеса ви, която работи на телефон и носи запитвания.',
            'includes' => [
                'Една страница с най-важното: кои сте, какво предлагате и как да се свържат с вас',
                'Работи на телефон, таблет и компютър',
                'Форма за запитвания, която пристига на вашия имейл',
                'Основна настройка за Google, за да може сайтът да бъде намерен',
            ],
            'upsell' => 'Ако по-късно ви трябват повече страници, минаваме лесно на пакет Бизнес.',
            'needs' => [],
            'steps' => [
                ['Започваме', 'и до няколко дни ви показваме първа версия.'],
                ['Пускаме сайта,', 'след като го одобрите.'],
            ],
        ],
        'biznes' => [
            'headline' => 'Сайт с до 5 страници за ' . $d['business'],
            'sub' => 'Когато имате повече за показване: услуги, цени, снимки. С SEO настройка и профил в Google Business.',
            'includes' => [
                'Всичко от пакет Старт: работи на телефон, форма за запитвания, основна настройка за Google',
                'До 5 страници: ' . ($d['pages'] ?? ''),
                'SEO настройка: заглавия, описания и структура, които Google разбира',
                'Профил в Google Business, за да излизате в Google Карти',
            ],
            'upsell' => 'Ако искате да се качвате в Google всеки месец, пакет Растеж добавя редовна SEO работа и отчет.',
            'needs' => ['Работно време, адрес и услуги за профила в Google Business'],
            'steps' => [
                ['Започваме', 'и ви показваме как върви работата, преди сайтът да е готов.'],
                ['Пускаме сайта', 'и профила в Google Business, след като ги одобрите.'],
            ],
        ],
        'rastezh' => [
            'headline' => 'Сайт и работа всеки месец, за да ви намират в Google',
            'sub' => 'Всичко от пакет Бизнес плюс SEO работа, нови текстове и кратък отчет всеки месец.',
            'includes' => [
                'Всичко от пакет Бизнес: до 5 страници, SEO настройка, профил в Google Business',
                'SEO работа всеки месец',
                'Нови текстове за сайта: ' . ($d['texts_per_month'] ?? '') . ' на месец',
                'Следим къде излизате в Google по думите, които са важни за вас',
                'Кратък отчет всеки месец: какво направихме и какво се промени',
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

    $validUntil = \Illuminate\Support\Carbon::parse($d['valid_until'])->format('d.m.Y');
@endphp

@section('eyebrow', 'Оферта · пакет ' . $name)
@section('headline', $pkg['headline'])
@section('sub', $pkg['sub'])

@section('body')
<tr><td style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте, {{ $d['client_name'] }},</p>
<p style="margin:0;">Благодарим ви за разговора. Както се разбрахме, предлагаме ви пакет <strong>{{ $name }}</strong> за {{ $d['business'] }}. По-долу е всичко, което включва, цената и следващите стъпки.</p>
</td></tr>

<tr><td style="padding:24px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:12px;">
<tr><td style="padding:20px 24px 6px;">@include('emails.client._parts', ['part' => 'label', 'text' => 'Вашата оферта', 'color' => '#9cc0eb'])</td></tr>
<tr><td style="padding:4px 24px 18px;"><table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
@foreach($price as [$label, $value])
<tr><td style="padding:10px 12px 10px 0;border-bottom:1px solid #2a4468;{{ $f }}font-size:14px;color:#c5d6ec;">{{ $label }}</td><td align="right" style="padding:10px 0;border-bottom:1px solid #2a4468;{{ $f }}font-size:16px;font-weight:bold;color:#ffffff;">{{ $value }}</td></tr>
@endforeach
</table></td></tr>
</table>
</td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво включва'])
<div style="height:8px;line-height:8px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'checklist', 'items' => $pkg['includes']])
</td></tr>

<tr><td style="padding:14px 36px 0;"><div style="border-left:3px solid #dde3ec;padding:2px 0 2px 14px;{{ $f }}font-size:13px;line-height:1.6;color:#64748b;">@if(! empty($d['not_included']))<strong style="color:#475569;">Не е включено:</strong> {{ $d['not_included'] }}. @endif{{ $pkg['upsell'] }}</div></td></tr>

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
    ['Изпращаме фактура', 'за аванса (' . $money($d['advance']) . ').'],
], $pkg['steps'])])
</td></tr>

<tr><td style="padding:26px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:2px solid #0f1a2b;border-radius:12px;">
<tr><td align="center" style="padding:22px 24px;">
<div style="{{ $f }}font-size:17px;font-weight:bold;color:#0f1a2b;">Съгласни ли сте? Отговорете с „Да“ на този имейл.</div>
<div style="{{ $f }}font-size:13px;line-height:1.6;color:#64748b;padding:6px 0 16px;">Офертата е валидна до {{ $validUntil }}. Ако искате да промените нещо, пишете ни и ще я съобразим.</div>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:10px;"><a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Приемам офертата: пакет ' . $name) }}" style="display:inline-block;padding:13px 24px;{{ $f }}font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">Приемам офертата</a></td>
</tr></table>
</td></tr></table>
</td></tr>
@if($phone)
<tr><td align="center" style="padding:14px 36px 28px;{{ $f }}font-size:13px;color:#64748b;">
Въпроси? Обадете се на <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phone) }}" style="color:#2d63a3;text-decoration:none;font-weight:bold;">{{ $phone }}</a>
</td></tr>
@endif
@endsection
