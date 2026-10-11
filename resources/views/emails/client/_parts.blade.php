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
@endswitch
