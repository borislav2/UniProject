@extends('emails.client.layout')
@php
    $f = 'font-family:Arial,Helvetica,sans-serif;';
    $contact = config('creatium.contact');
    $questions = [
        'Какви са основните услуги или продукти, които предлагате, и кой е вашият идеален клиент?',
        'Разполагате ли със съществуващ уебсайт или регистриран домейн? (Ако да, посочете адреса)',
        'Какво е основното действие, което искате да предприемат посетителите: да ви се обадят, да изпратят запитване или да направят онлайн покупка/резервация?',
        'Имате ли налични бранд материали (лого, текстове, снимки) или предпочитате да поемем тяхното създаване?',
        'Има ли сайтове във вашия бранш (или на конкуренти), чийто стил ви допада?',
        'В какъв времеви рамков период планирате старта на проекта?',
    ];
    $frameworks = [
        ['Стартов двигател', false, 'За бизнеси, търсещи модерна визия, мобилна съвместимост, техническа сигурност и базов маркетинг контрол.'],
        ['Активен Маркетинг & Растеж', true, 'За бизнеси, готови за онлайн продажби, резервации, автоматизиран имейл маркетинг, SEO и позициониране в AI (ChatGPT / Gemini).'],
        ['Индивидуално Партньорство', false, 'За компании със специфични бизнес процеси, софтуерни интеграции (CRM, складови системи) и голям обем от маркетингови ресурси.'],
    ];
@endphp

@section('eyebrow', 'Получихме вашето запитване')
@section('headline'){{ $data['client_name'] }}, благодарим ви за запитването@endsection
@section('sub', 'Преглеждаме го лично. Ето как продължаваме.')

@section('body')
<tr><td style="padding:32px 36px 0;{{ $f }}font-size:15px;line-height:1.65;color:#1f2a3a;">
<p style="margin:0 0 14px;">Здравейте, {{ $data['client_name'] }},</p>
<p style="margin:0 0 14px;">Благодарим ви, че се свързахте с нас относно развитието на {{ $data['business'] }}. Получихме вашето запитване и го преглеждаме лично.</p>
<p style="margin:0;">В Creatium Lab вярваме, че успешният уебсайт и правилният маркетинг вървят ръка за ръка. Затова не изпращаме шаблонни оферти, а проектираме цялостна дигитална система, съобразена с вашите специфични търговски цели.</p>
</td></tr>

@if(! empty($data['personal_note']))
<tr><td style="padding:22px 36px 0;">@include('emails.client._parts', ['part' => 'quote', 'label' => 'Какво забелязахме', 'text' => $data['personal_note']])</td></tr>
@endif

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'С какво се отличава нашият подход'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'why', 'items' => [
    ['Интегрирана система', 'Изграждаме уебсайта и маркетинга заедно – от бързината на платформата до Google Analytics, SEO и имейл автоматизациите.'],
    ['Пълна прозрачност', 'Получавате проектно предложение с точно дефиниран обхват, график и фиксиран бюджет преди официалния старт.'],
    ['Предварителен преглед', 'Виждате и преглеждате интерактивна концепция на проекта, за да сте напълно спокойни за крайния резултат.'],
    ['Директна комуникация', 'Работите директно с екипа, който изгражда и управлява вашия проект – без посредници и забавяния.'],
]])
</td></tr>

<tr><td style="padding:24px 36px 8px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-radius:12px;">
<tr><td style="padding:22px 24px 4px;">@include('emails.client._parts', ['part' => 'label', 'text' => 'За да подготвим точен план за вас'])</td></tr>
<tr><td style="padding:4px 24px 6px;{{ $f }}font-size:14px;line-height:1.6;color:#475569;">Можете да отговорите накратко на този имейл с няколко детайла (или да ги обсъдим в кратък разговор):</td></tr>
<tr><td style="padding:0 24px 18px;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $f }}font-size:14px;line-height:1.55;color:#1f2a3a;">
@foreach($questions as $question)
<tr><td width="30" valign="top" style="padding:6px 0;"><span style="display:inline-block;width:22px;height:22px;line-height:22px;border-radius:11px;background:#0f1a2b;color:#ffffff;font-size:12px;font-weight:bold;text-align:center;">{{ $loop->iteration }}</span></td><td style="padding:6px 0;">{{ $question }}</td></tr>
@endforeach
</table>
</td></tr>
</table>
</td></tr>

<tr><td style="padding:28px 36px 6px;{{ $f }}">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Нашите концептуални рамки за ориентир'])
</td></tr>
<tr><td style="padding:12px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="{{ $f }}">
@foreach($frameworks as [$name, $dark, $text])
    <tr><td @if($dark) bgcolor="#0f1a2b" @endif style="{{ $dark ? 'background:#0f1a2b;' : 'border:1px solid #dde3ec;' }}border-radius:12px;padding:18px 20px;">
    <div style="font-size:16px;font-weight:bold;color:{{ $dark ? '#ffffff' : '#0f1a2b' }};">{{ $name }}@if($dark) <span style="display:inline-block;margin-left:6px;padding:2px 8px;border-radius:10px;background:#2d63a3;color:#ffffff;font-size:11px;font-weight:bold;letter-spacing:0.5px;vertical-align:middle;">ПРЕПОРЪЧАН</span>@endif</div>
    <div style="font-size:14px;line-height:1.55;color:{{ $dark ? '#d6e3f5' : '#475569' }};padding-top:4px;">{{ $text }}</div>
    </td></tr>
    @unless($loop->last)<tr><td style="height:10px;line-height:10px;font-size:0;">&nbsp;</td></tr>@endunless
@endforeach
</table>
</td></tr>
<tr><td style="padding:14px 36px 0;{{ $f }}font-size:14px;line-height:1.6;color:#475569;">
Не е необходимо да избирате пакет предварително. След като се чуем, ще ви препоръчаме най-рационалното решение за вашите цели.
</td></tr>

<tr><td style="padding:28px 36px 0;">
@include('emails.client._parts', ['part' => 'label', 'text' => 'Какво следва оттук нататък?'])
<div style="height:4px;line-height:4px;font-size:0;">&nbsp;</div>
@include('emails.client._parts', ['part' => 'steps', 'items' => [
    ['Отговор или разговор:', 'Можете да отговорите на въпросите по-горе или да посочите удобен ден и час за 10-минутен телефонен разговор.'],
    ['Подготовка на концепция:', 'На база вашите нужди изготвяме проектно предложение с ясни параметри и визия.'],
    ['Официален старт:', 'Започваме работа едва след като прегледате предложението и дадете вашето одобрение.'],
]])
</td></tr>

<tr><td style="padding:26px 36px 0;">
<table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="border:2px solid #0f1a2b;border-radius:12px;">
<tr><td align="center" style="padding:22px 24px;">
<div style="{{ $f }}font-size:15px;line-height:1.6;color:#0f1a2b;font-weight:bold;">Всеки ден с неработещо онлайн присъствие е ден, в който потенциалните ви клиенти избират други алтернативи на пазара. Нека променим това.</div>
<div style="height:14px;line-height:14px;font-size:0;">&nbsp;</div>
<table role="presentation" cellpadding="0" cellspacing="0" border="0"><tr>
<td bgcolor="#0f1a2b" style="background:#0f1a2b;border-radius:10px;"><a href="mailto:{{ $contact['email'] }}?subject={{ rawurlencode('Отговор на въпросите') }}" style="display:inline-block;padding:13px 22px;{{ $f }}font-size:14px;font-weight:bold;color:#ffffff;text-decoration:none;">Отговарям на въпросите</a></td>
<td width="10" style="font-size:0;">&nbsp;</td>
<td style="border:2px solid #0f1a2b;border-radius:10px;"><a href="{{ route('home') }}#konsultatsiya" style="display:inline-block;padding:11px 20px;{{ $f }}font-size:14px;font-weight:bold;color:#0f1a2b;text-decoration:none;">Изберете час за разговор</a></td>
</tr></table>
</td></tr></table>
</td></tr>
<tr><td style="height:28px;line-height:28px;font-size:0;">&nbsp;</td></tr>
@endsection
