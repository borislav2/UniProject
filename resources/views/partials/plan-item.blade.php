{{-- One line of a package list: "Заглавие: текст" shows the part before the colon in bold. --}}
@php([$head, $rest] = str_contains($line, ': ') ? explode(': ', $line, 2) : [null, $line])
<li class="flex items-start gap-3 text-sm leading-relaxed {{ $dark ? 'text-gray-300' : 'text-gray-600 dark:text-gray-300' }}">
    <span class="mt-0.5 w-5 h-5 shrink-0 rounded-full flex items-center justify-center {{ $dark ? 'bg-brand-500/25' : 'bg-brand-50 dark:bg-white/10' }}"><x-icon name="check" class="text-[10px] {{ $dark ? 'text-brand-200' : 'text-brand-600 dark:text-brand-300' }}" /></span>
    <span>@if($head)<strong class="{{ $dark ? 'text-white' : 'text-brand-950 dark:text-white' }}">{{ $head }}:</strong> @endif{{ $rest }}</span>
</li>
