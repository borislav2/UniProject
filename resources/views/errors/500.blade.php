@php($en = request()->segment(1) === 'en')
<!DOCTYPE html>
<html lang="{{ $en ? 'en' : 'bg' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $en ? 'Something went wrong' : 'Нещо се обърка' }} | Creatium Lab</title>
    <style>
        body { font-family: system-ui, sans-serif; background: #f9fafb; color: #111827; display: flex; min-height: 100vh; align-items: center; justify-content: center; text-align: center; margin: 0; padding: 1rem; }
        a { color: #274f84; }
        @media (prefers-color-scheme: dark) {
            body { background: #070d18; color: #f3f4f6; }
            a { color: #8fb5e0; }
        }
    </style>
</head>
<body>
    <div>
        @if($en)
            <h1>Something went wrong</h1>
            <p>A technical error occurred. Please try again in a moment or email us at <a href="mailto:{{ config('creatium.contact.email') }}">{{ config('creatium.contact.email') }}</a>.</p>
            <p><a href="/en">Back to the home page</a></p>
        @else
            <h1>Нещо се обърка</h1>
            <p>Възникна техническа грешка. Опитайте отново след малко или ни пишете на <a href="mailto:{{ config('creatium.contact.email') }}">{{ config('creatium.contact.email') }}</a>.</p>
            <p><a href="/">Към началото</a></p>
        @endif
    </div>
</body>
</html>
