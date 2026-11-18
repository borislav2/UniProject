{{-- A line with numbered nodes (website timeline, email sequence). $items = [['day' => ..., 'text' => ..., 'result' => bool], ...], $label = the caption. Real text, the line is CSS. --}}
<figure class="g-bv g-bv-tl-wrap">
    <figcaption class="g-bv-cap g-mono">{{ $label }}</figcaption>
    <ol class="g-bv-tl">
        @foreach($items as $item)
            <li{!! ! empty($item['result']) ? ' class="is-result"' : '' !!}>
                <i aria-hidden="true"></i>
                <b class="g-mono">{{ $item['day'] }}</b>
                <span>{{ $item['text'] }}</span>
            </li>
        @endforeach
    </ol>
</figure>
