{{-- Помощни части за шаблоните: @include('emails.client._parts', ['part' => 'label', ...]) --}}
@php($f = 'font-family:Arial,Helvetica,sans-serif;')
@switch($part)
    @case('label')
        <div style="{{ $f }}font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:{{ $color ?? '#2d63a3' }};">{{ $text }}</div>
        @break
    @case('checklist')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        @foreach($items as $item)
            <tr><td width="26" valign="top" style="padding:6px 0;{{ $f }}font-size:15px;font-weight:bold;color:{{ $color ?? '#2d63a3' }};">&#10003;</td><td style="padding:6px 0;{{ $f }}font-size:14px;line-height:1.55;color:#1f2a3a;">{{ $item }}</td></tr>
        @endforeach
        </table>
        @break
    @case('steps')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        @foreach($items as [$bold, $rest])
            <tr><td width="34" valign="top" style="padding:8px 0;"><span style="display:inline-block;width:24px;height:24px;line-height:24px;border-radius:12px;border:2px solid #2d63a3;color:#2d63a3;{{ $f }}font-size:12px;font-weight:bold;text-align:center;">{{ $loop->iteration }}</span></td><td style="padding:10px 0 8px;{{ $f }}font-size:14px;line-height:1.55;color:#1f2a3a;"><strong style="color:#0f1a2b;">{{ $bold }}</strong> {{ $rest }}</td></tr>
        @endforeach
        </table>
        @break
    @case('quote')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#f5f8fc;border-left:4px solid #2d63a3;border-radius:0 12px 12px 0;">
        <tr><td style="padding:18px 22px;">
            <div style="{{ $f }}font-size:12px;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#2d63a3;">{{ $label }}</div>
            <div style="{{ $f }}font-size:15px;line-height:1.65;color:#0f1a2b;font-style:italic;padding-top:8px;white-space:pre-line;">{{ $text }}</div>
        </td></tr>
        </table>
        @break
    @case('why')
        {{-- Защо с нас: кратки обещания, които са верни за двама души без посредници. --}}
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        @foreach($items as [$title, $text])
            <tr><td width="40" valign="top" style="padding:10px 0;"><span style="display:inline-block;width:28px;height:28px;line-height:28px;border-radius:8px;background:#0f1a2b;color:#9cc0eb;{{ $f }}font-size:14px;font-weight:bold;text-align:center;">&#10003;</span></td>
            <td style="padding:10px 0;{{ $f }}font-size:14px;line-height:1.55;color:#475569;"><strong style="display:block;color:#0f1a2b;font-size:15px;">{{ $title }}</strong>{{ $text }}</td></tr>
        @endforeach
        </table>
        @break
@endswitch
