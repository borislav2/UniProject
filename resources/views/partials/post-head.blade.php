{{-- Shared head of a blog post (both designs): dates, BlogPosting JSON-LD and the Markdown twin for AI assistants. --}}
<meta property="article:published_time" content="{{ $post->published_at->toAtomString() }}">
<meta property="article:modified_time" content="{{ $post->updated_at->toAtomString() }}">
<link rel="alternate" type="text/markdown" href="{{ \App\Support\Seo\PostSchema::markdownUrl($post) }}">
<script type="application/ld+json">{!! json_encode(\App\Support\Seo\PostSchema::blogPosting($post), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG) !!}</script>
