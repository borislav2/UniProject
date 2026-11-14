@extends('layouts.public')

{{--
    The legal pages (privacy, terms, cookies; Bulgarian and English) in design G. The text lives in the old views; PageController::legal() renders
    their sections and this view takes the title, the headline of the old page header and the text after it, and sets them in g typography.
--}}
@php
    $content = (string) ($sections['content'] ?? '');
    // The old view starts with the page header section (partials/page-header); its <h1> is the headline, everything after it is the text.
    preg_match('~<h1[^>]*>(.*?)</h1>~su', $content, $h1);
    $headline = trim(strip_tags($h1[1] ?? ''));
    $cut = strpos($content, '</section>');
    $text = $cut === false ? $content : substr($content, $cut + strlen('</section>'));
@endphp
@section('title', trim($sections['title'] ?? $headline))
@section('meta_description', trim($sections['meta_description'] ?? ''))
@section('g_lean', 'inner')

@section('content')
@include('partials.g.page-head', ['eyebrow' => __('Правна информация'), 'heading' => $headline])
<section class="g-s g-last">
    <div class="g-wrap g-narrow">
        <div class="g-prose g-legal">{!! $text !!}</div>
    </div>
</section>
@endsection
