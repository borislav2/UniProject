<!DOCTYPE html>
<html lang="bg">
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.5;">
    <h2 style="margin-bottom: 4px;">Ново запитване от сайта</h2>
    <p style="margin-top: 0; color: #6b7280;">{{ $lead->created_at->format('d.m.Y H:i') }}</p>

    <p><strong>Име:</strong> {{ str_replace('Запитване от ', '', $lead->name) }}</p>
    @if($lead->client_email)
        <p><strong>Имейл:</strong> {{ $lead->client_email }}</p>
    @endif
    @if($lead->client_phone)
        <p><strong>Телефон:</strong> <a href="tel:{{ preg_replace('/[^0-9+]/', '', $lead->client_phone) }}">{{ $lead->client_phone }}</a></p>
    @endif
    @if($lead->service)
        <p><strong>Услуга:</strong> {{ $lead->service }}</p>
    @endif
    <p><strong>Съобщение:</strong></p>
    <p style="white-space: pre-line; background: #f3f4f6; padding: 12px; border-radius: 6px;">{{ $lead->description }}</p>

    <p><a href="{{ route('admin.projects.show', $lead) }}">Отвори в административния панел</a></p>
</body>
</html>
