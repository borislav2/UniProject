{{-- The visible author of a post (a team member, from his own account or by category), linking to the team page. The company is not shown as a person. --}}
@php($byline = \App\Support\Schema::postAuthor($post))
@if(($byline['@type'] ?? '') === 'Person')
    <span class="{{ $class ?? '' }}" data-byline>
        <a href="{{ $byline['url'] }}" rel="author">{{ $byline['name'] }}</a>@if(! empty($byline['jobTitle'])), {{ $byline['jobTitle'] }}@endif
    </span>
@endif
