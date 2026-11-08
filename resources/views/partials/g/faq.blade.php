{{-- Hairline accordions. $items: [['q' => ..., 'a' => ...], ...] or [[question, answer], ...] --}}
<div class="g-acc-list">
    @foreach($items as $item)
        @php([$q, $a] = isset($item['q']) ? [$item['q'], $item['a']] : [$item[0], $item[1]])
        <details class="g-acc">
            <summary><span>{{ $q }}</span><i aria-hidden="true"></i></summary>
            <p>{{ $a }}</p>
        </details>
    @endforeach
</div>
