{{-- The two drawings of the website check page in design G (/proverka-na-sait), in the style of the service illustrations (.g-sv, g-pages.css).
     $kind = 'report' (mock-up of the audit report with four sections and a recommendation each) or 'points' (the four points of the check).
     Every label comes from site('site_check_page') (BG and EN). No numbers, no scores, no results: it is an example of a document, not a real one. --}}
@php
    $page = (array) site('site_check_page');
@endphp
@if($kind === 'report')
@php
    $v = (array) ($page['report_visual'] ?? []);
    $sections = array_values((array) ($v['sections'] ?? []));
@endphp
<svg class="g-sv g-sv-report" viewBox="0 0 520 400" role="img" aria-label="{{ $v['label'] ?? '' }}" focusable="false"><rect class="sv-b" x=".5" y=".5" width="519" height="399"/>
<rect class="sv-sh" x="52" y="30" width="440" height="360"/>
<rect class="sv-c" x="40" y="18" width="440" height="360"/>
<rect class="sv-k" x="58" y="36" width="22" height="22"/><path class="sv-w" d="M69 40L76 47L69 54L62 47Z"/><text class="sv-txt" x="90" y="52">Creatium Lab</text>
<text class="sv-txt sv-faint" x="462" y="52" text-anchor="end">{{ $v['doc_title'] ?? '' }}</text>
<path class="sv-e" d="M40 72H480"/>
<text class="sv-txt sv-faint" x="58" y="94">{{ $v['doc_site'] ?? '' }}</text>
@foreach($sections as $i => $section)
@php $y = 108 + $i * 66; @endphp
<circle class="sv-a" cx="68" cy="{{ $y + 12 }}" r="9"/><path class="sv-aws" d="M68 {{ $y + 8 }}V{{ $y + 13 }}M68 {{ $y + 16 }}V{{ $y + 16.5 }}"/>
<text class="sv-txt" x="86" y="{{ $y + 16 }}">{{ $section['title'] ?? '' }}</text>
<text class="sv-txt sv-faint" x="462" y="{{ $y + 16 }}" text-anchor="end">{{ $v['recommendation'] ?? '' }}</text>
<rect class="sv-cite" x="58" y="{{ $y + 28 }}" width="404" height="26"/><rect class="sv-a" x="68" y="{{ $y + 37 }}" width="8" height="8"/><text class="sv-txt" x="86" y="{{ $y + 45 }}">{{ $section['tip'] ?? '' }}</text>
@if(! $loop->last)<path class="sv-e" d="M58 {{ $y + 62 }}H462"/>@endif
@endforeach
</svg>
@else
@php
    $v = (array) ($page['points_visual'] ?? []);
    $points = array_values((array) ($v['points'] ?? []));
    $glyphs = [
        '<rect class="sv-ws" x="30" y="25" width="16" height="26"/><path class="sv-ws" d="M35 46H41"/><circle class="sv-a" cx="50" cy="42" r="5"/>',
        '<path class="sv-ws" d="M32 31L25 38L32 45M44 31L51 38L44 45"/><path class="sv-as" d="M40 29L36 47"/>',
        '<circle class="sv-ws" cx="35" cy="35" r="9"/><path class="sv-ws" d="M42 42L51 51"/>',
        '<path class="sv-w" d="M37 24L40 34L50 37L40 40L37 50L34 40L24 37L34 34Z"/><circle class="sv-a" cx="51" cy="27" r="3.5"/>',
    ];
    $places = [[28, 26], [268, 26], [28, 166], [268, 166]];
@endphp
<svg class="g-sv g-sv-points" viewBox="0 0 520 325" role="img" aria-label="{{ ($v['label'] ?? '') . ', ' . collect($points)->map(fn ($p) => ($p['title'] ?? '') . ' (' . ($p['sub'] ?? '') . ')')->implode(', ') }}" focusable="false"><rect class="sv-b" x=".5" y=".5" width="519" height="324"/>
@foreach($points as $i => $point)
<g transform="translate({{ $places[$i][0] ?? 0 }} {{ $places[$i][1] ?? 0 }})"><rect class="sv-c" x="0" y="0" width="224" height="132"/>
<rect class="sv-k" x="16" y="16" width="44" height="44"/><g>{!! $glyphs[$i] ?? '' !!}</g>
<rect class="sv-t" x="84" y="22" width="92" height="5"/><rect class="sv-t" x="84" y="34" width="64" height="5"/><rect class="sv-t" x="84" y="46" width="78" height="5"/>
<circle class="sv-a" cx="198" cy="26" r="9"/><path class="sv-aws" d="M194 26L197 29L202.500 23"/>
<path class="sv-e" d="M16 76H208"/>
<text class="sv-txt" x="16" y="98">{{ $point['title'] ?? '' }}</text><text class="sv-txt sv-faint" x="16" y="116">{{ $point['sub'] ?? '' }}</text></g>
@endforeach
</svg>
@endif
