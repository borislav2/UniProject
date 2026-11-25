@extends('admin.layout')

@section('title', 'SEO на статиите')

@section('content')
@php use App\Support\Seo\Snippet; $state = fn ($s) => $s === 'long' ? 'bad' : ($s === 'short' ? 'warn' : ($s === 'missing' ? 'missing' : 'ok')); @endphp
<div class="adm-page-head">
    <div>
        <h2>SEO</h2>
        <p class="adm-eyebrow mt-1">Статиите в блога: заглавие, описание, автор и ключова дума</p>
    </div>
    <a href="{{ route('admin.posts.index') }}" class="adm-btn ghost sm"><x-icon name="newspaper" /> Към блога</a>
</div>
@include('admin.seo._tabs', ['active' => 'posts'])

<p class="sx-note">Оценката е колко от 11 проверки са изпълнени (заглавие и описание в мярка, ключовата дума на важните места, поне 300 думи, две връзки в сайта, картинки с alt). Без ключова дума най-много 5 от 11. Червено е, когато заглавието или описанието липсват или са твърде дълги. Всичко се редактира в самата статия, в панела „SEO и споделяне“.</p>

<div class="sx-wrap">
    <table class="sx-table">
        <thead><tr><th>Статия</th><th>Заглавие в Google</th><th>Описание в Google</th><th>Ключова дума</th><th>Автор</th><th class="num">SEO</th><th></th></tr></thead>
        <tbody>
        @forelse($rows as $r)
            @php $post = $r['post']; @endphp
            <tr>
                <td class="sx-page"><b><span class="sx-lang">{{ strtoupper($r['locale']) }}</span>{{ $post->tr('title', $r['locale']) }}</b>
                    <small>{{ $post->status === 'published' ? ($post->published_at?->isFuture() ? 'насрочена' : 'публикувана') : 'чернова' }} · {{ ($post->published_at ?? $post->updated_at)->format('d.m.Y') }}</small></td>
                <td>
                    <div class="sx-text">{{ $r['title'] ?: '—' }}</div>
                    <div class="sx-meta"><span class="sx-len {{ $state(Snippet::titleState($r['title'])) }}">{{ Snippet::length($r['title']) }} / 60 · {{ round(Snippet::width((string) $r['title'])) }} / {{ Snippet::TITLE_PX }} px</span><span class="sx-tag {{ $r['title_own'] ? 'own' : 'quiet' }}">{{ $r['title_own'] ? 'Ваш текст' : 'по подразбиране' }}</span></div>
                </td>
                <td>
                    <div class="sx-text">{{ $r['description'] ?: '—' }}</div>
                    <div class="sx-meta"><span class="sx-len {{ $state(Snippet::descriptionState($r['description'])) }}">{{ Snippet::length($r['description']) }} / 155 · {{ round(Snippet::descriptionWidth($r['description'])) }} / {{ Snippet::DESC_PX }} px</span><span class="sx-tag {{ $r['description_own'] ? 'own' : 'quiet' }}">{{ $r['description_own'] ? 'Ваш текст' : 'по подразбиране' }}</span></div>
                </td>
                <td>{{ $r['keyword'] ?: '—' }}</td>
                <td>{{ $r['author'] }}</td>
                <td class="num"><span class="sx-score {{ $r['overview']['tone'] }}">{{ $r['overview']['ok'] }}/{{ $r['overview']['total'] }}</span></td>
                <td class="num"><a href="{{ route('admin.posts.edit', $post) }}#seo" class="adm-btn ghost sm">Редактирай</a></td>
            </tr>
        @empty
            <tr><td colspan="7" class="sx-note" style="padding:24px">Още няма статии.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
<div class="mt-4">{{ $posts->links() }}</div>
@endsection
