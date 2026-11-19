{{--
    Error page in design G that needs nothing from the database or the site layout (500, 503 maintenance, 429, 403 can happen while the app is unhealthy).
    Expects: $code, $heading, $text (plain strings), $en (bool). Inline CSS only, the fonts are the built G files when they exist and system fonts otherwise.
    The same tokens as resources/css/g.css (light #ECEEF0 / ink #0C0E12, dark #0B0C0F, one accent), square corners, hairlines.
--}}
@php
    // A path without the host, so the font loads on whichever address the error page is served from (www or not).
    $font = fn (string $file) => rescue(fn () => (string) parse_url(\Illuminate\Support\Facades\Vite::asset('resources/fonts/g/' . $file . '.woff2'), PHP_URL_PATH), '', false);
    $email = (string) config('creatium.contact.email');
    $home = $en ? '/en' : '/';
@endphp
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'bg' }}" class="design-g">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <meta name="theme-color" content="#ECEEF0" media="(prefers-color-scheme: light)">
    <meta name="theme-color" content="#0B0C0F" media="(prefers-color-scheme: dark)">
    <title>{{ $heading }} | Creatium Lab</title>
    <link rel="icon" href="/favicon.ico?v=2" sizes="any">
    <link rel="icon" type="image/svg+xml" href="/favicon.svg?v=2">
    <style>
        @if($font('geologica-cyrillic-300') !== '')
        @font-face { font-family: "Geologica"; font-weight: 300; src: url("{{ $font('geologica-cyrillic-300') }}") format("woff2"); unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116; }
        @font-face { font-family: "Geologica"; font-weight: 300; src: url("{{ $font('geologica-latin-300') }}") format("woff2"); unicode-range: U+0000-00FF, U+2013-2014, U+2018-201E, U+2022, U+2026, U+20AC, U+2212; }
        @font-face { font-family: "Onest"; font-weight: 400; src: url("{{ $font('onest-cyrillic-400') }}") format("woff2"); unicode-range: U+0301, U+0400-045F, U+0490-0491, U+04B0-04B1, U+2116; }
        @font-face { font-family: "Onest"; font-weight: 400; src: url("{{ $font('onest-latin-400') }}") format("woff2"); unicode-range: U+0000-00FF, U+2013-2014, U+2018-201E, U+2022, U+2026, U+20AC, U+2212; }
        @endif
        :root { --bg: #ECEEF0; --ink: #0C0E12; --mute: #4A5058; --line: rgba(12, 14, 18, .5); --accent: #E8431B; --btn: #0C0E12; --btn-fg: #ECEEF0; --hover: #C4300F; }
        @media (prefers-color-scheme: dark) { :root { --bg: #0B0C0F; --ink: #ECEEF0; --mute: #9096A0; --line: rgba(236, 238, 240, .55); --accent: #FF5A33; --btn: #ECEEF0; --btn-fg: #0B0C0F; --hover: #FF5A33; } }
        * { box-sizing: border-box; }
        body { margin: 0; min-height: 100vh; display: flex; align-items: center; background: var(--bg); color: var(--ink); font: 400 17px/1.6 "Onest", system-ui, -apple-system, "Segoe UI", Roboto, sans-serif; }
        main { width: 100%; max-width: 1180px; margin: 0 auto; padding: 48px 24px; }
        .logo { display: block; height: 30px; width: auto; margin-bottom: 56px; }
        .logo.dark { display: none; }
        @media (prefers-color-scheme: dark) { .logo.light { display: none; } .logo.dark { display: block; } }
        .code { margin: 0 0 16px; font: 500 14px/1 ui-monospace, "JetBrains Mono", Menlo, monospace; letter-spacing: .08em; text-transform: uppercase; color: var(--mute); }
        h1 { margin: 0; max-width: 17em; font: 300 clamp(2.2rem, 5vw, 4rem)/1.08 "Geologica", "Onest", system-ui, sans-serif; letter-spacing: -.025em; }
        p.text { margin: 20px 0 0; max-width: 36em; font-size: clamp(1.05rem, 1.3vw, 1.2rem); }
        p.text a { color: var(--ink); text-underline-offset: 3px; }
        .btn { display: inline-block; margin-top: 32px; padding: 14px 24px; background: var(--btn); color: var(--btn-fg); text-decoration: none; font-size: 15px; border: 1px solid var(--btn); }
        .btn:hover { background: var(--hover); border-color: var(--hover); color: #fff; }
        .btn:focus-visible { outline: 2px solid var(--accent); outline-offset: 2px; }
        hr { border: 0; border-top: 1px solid var(--line); margin: 56px 0 0; opacity: .35; }
    </style>
</head>
<body>
    <main>
        <img class="logo light" src="/images/logo-g.webp" alt="Creatium Lab" width="209" height="30">
        <img class="logo dark" src="/images/logo-g-dark.webp" alt="" aria-hidden="true" width="209" height="30">
        @if(filled($code))<p class="code">{{ $code }}</p>@endif
        <h1>{{ $heading }}</h1>
        @php($links = $links ?? $code !== '503')
        <p class="text">{{ $text }} @if($links && filled($code))<a href="mailto:{{ $email }}">{{ $email }}</a>@endif</p>
        @if($links)<a class="btn" href="{{ $home }}">{{ $en ? 'Back to the home page' : 'Към началото' }}</a>@endif
        <hr>
    </main>
</body>
</html>
