{{-- One line of a package list: "Заглавие: текст" shows the part before the colon in bold. Colours follow the card (.plan-card variables). --}}
@php([$head, $rest] = str_contains($line, ': ') ? explode(': ', $line, 2) : [null, $line])
<li class="pc-body flex items-start gap-3 text-sm leading-relaxed">
    <span class="pc-chip mt-0.5 w-5 h-5 shrink-0 rounded-full flex items-center justify-center"><x-icon name="check" class="text-[10px]" /></span>
    <span>@if($head)<strong class="pc-fg">{{ $head }}:</strong> @endif{{ $rest }}</span>
</li>
