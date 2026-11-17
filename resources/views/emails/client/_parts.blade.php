{{-- Помощни части за шаблоните в стила на дизайн Г: @include('emails.client._parts', ['part' => 'label', ...]) --}}
@php
    $f = 'font-family:Onest,Segoe UI,Helvetica,Arial,sans-serif;';
    $fh = 'font-family:Geologica,Segoe UI,Arial,sans-serif;';
    $fm = 'font-family:JetBrains Mono,Consolas,Menlo,monospace;';
@endphp
@switch($part)
    @case('label')
        <div style="{{ $fm }}font-size:11px;line-height:1.4;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:{{ $color ?? '#C4300F' }};">{{ $text }}</div>
        @break
    @case('checklist')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        @foreach($items as $item)
            <tr><td width="26" valign="top" style="padding:7px 0;{{ $f }}font-size:15px;font-weight:bold;color:{{ $color ?? '#C4300F' }};">&#10003;</td><td style="padding:7px 0;{{ $f }}font-size:14px;line-height:1.55;color:#0C0E12;">{{ $item }}</td></tr>
        @endforeach
        </table>
        @break
    @case('steps')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        @foreach($items as [$bold, $rest])
            <tr><td width="38" valign="top" style="padding:11px 0 9px;border-top:1px solid #D9DBDE;{{ $fm }}font-size:12px;font-weight:bold;color:#C4300F;">{{ sprintf('%02d', $loop->iteration) }}</td><td style="padding:10px 0 8px;border-top:1px solid #D9DBDE;{{ $f }}font-size:14px;line-height:1.55;color:#4A5058;"><strong style="color:#0C0E12;">{{ $bold }}</strong> {{ $rest }}</td></tr>
        @endforeach
        </table>
        @break
    @case('quote')
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="background:#FFFFFF;border:1px solid #D9DBDE;border-left:3px solid #E8431B;">
        <tr><td style="padding:18px 22px;">
            <div style="{{ $fm }}font-size:11px;line-height:1.4;font-weight:bold;letter-spacing:1.5px;text-transform:uppercase;color:#C4300F;">{{ $label }}</div>
            <div style="{{ $f }}font-size:15px;line-height:1.65;color:#0C0E12;padding-top:8px;white-space:pre-line;">{{ $text }}</div>
        </td></tr>
        </table>
        @break
    @case('why')
        {{-- Защо с нас: кратки обещания, които са верни за двама души без посредници. --}}
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
        @foreach($items as [$title, $text])
            <tr><td width="38" valign="top" style="padding:12px 0;border-top:1px solid #D9DBDE;{{ $fm }}font-size:12px;font-weight:bold;color:#C4300F;">{{ sprintf('%02d', $loop->iteration) }}</td>
            <td style="padding:11px 0;border-top:1px solid #D9DBDE;{{ $f }}font-size:14px;line-height:1.55;color:#4A5058;"><strong style="display:block;color:#0C0E12;font-size:15px;">{{ $title }}</strong>{{ $text }}</td></tr>
        @endforeach
        </table>
        @break
@endswitch
