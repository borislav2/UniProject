<!DOCTYPE html>
<html lang="bg">
<head><meta charset="utf-8">@include('emails._font')</head>
<body style="font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif; color: #0C0E12; line-height: 1.5;">
    <h2 style="margin-bottom: 4px;">Ново запитване от сайта</h2>
    <p style="margin-top: 0; color: #5A6068;">{{ $lead->created_at->format('d.m.Y H:i') }}</p>

    {{-- Етикетите са в отделна клетка, без двоеточие (собствениците, ноември 2026: без тирета и двоеточия). --}}
    @php($row = 'padding: 4px 16px 4px 0; color: #5A6068; vertical-align: top; white-space: nowrap;')
    <table role="presentation" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 12px;">
        <tr><td style="{{ $row }}">Име</td><td><strong>{{ str_replace('Запитване от ', '', $lead->name) }}</strong></td></tr>
        @if($lead->client_email)
            <tr><td style="{{ $row }}">Имейл</td><td>{{ $lead->client_email }}</td></tr>
        @endif
        @if($lead->client_phone)
            <tr><td style="{{ $row }}">Телефон</td><td><a href="tel:{{ preg_replace('/[^0-9+]/', '', $lead->client_phone) }}">{{ $lead->client_phone }}</a></td></tr>
        @endif
        @if($lead->website_url)
            <tr><td style="{{ $row }}">Сайт</td><td><a href="{{ $lead->website_url }}">{{ $lead->website_url }}</a></td></tr>
        @endif
        @if($lead->service)
            <tr><td style="{{ $row }}">Услуга</td><td>{{ $lead->service }}</td></tr>
        @endif
        @if($lead->consultation_at)
            <tr><td style="{{ $row }}">Разговор</td><td>{{ \App\Support\Booking::label($lead->consultation_at) }}
                (<a href="{{ \App\Support\Booking::googleCalendarUrl($lead->consultation_at, 'Разговор с ' . str_replace('Запитване от ', '', $lead->name), 'Телефон ' . $lead->client_phone . "\n" . route('admin.projects.show', $lead)) }}">добави в Google Календар</a>)</td></tr>
        @endif
        @if($lead->review_due_at)
            <tr><td style="{{ $row }}">Личен преглед до</td><td><strong>{{ \App\Support\SiteCheck\ReviewDue::label($lead->review_due_at) }}</strong></td></tr>
        @endif
        @if($lead->check_goal && \App\Support\SiteCheck\ReviewDue::goalLabel($lead->check_goal))
            <tr><td style="{{ $row }}">Основна цел</td><td>{{ \App\Support\SiteCheck\ReviewDue::goalLabel($lead->check_goal) }}</td></tr>
        @endif
        @if($lead->business_size)
            <tr><td style="{{ $row }}">Размер на бизнеса</td><td>{{ $lead->business_size }}</td></tr>
        @endif
        @if($lead->locale === 'en')
            <tr><td style="{{ $row }}">Език</td><td>английски (изпратено от английската версия на сайта)</td></tr>
        @endif
    </table>
    <p style="margin-bottom: 4px;"><strong>Съобщение</strong></p>
    <p style="white-space: pre-line; background: #FFFFFF; padding: 12px; ">{{ $lead->description }}</p>

    <p><a href="{{ route('admin.projects.show', $lead) }}">Отвори в административния панел</a></p>
</body>
</html>
