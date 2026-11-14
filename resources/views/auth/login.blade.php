<!DOCTYPE html>
<html lang="bg">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Creatium Lab</title>
    <meta name="robots" content="noindex, nofollow">
    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <meta name="theme-color" content="#0C0E12">
    @vite('resources/css/admin.css')
</head>
<body>
<div class="adm-login">
    <aside class="adm-login-side" aria-hidden="true">
        <img src="{{ asset('images/logo-g-dark-352.webp') }}" alt="">
        <div>
            <p class="adm-eyebrow mb-4">Екипен панел</p>
            <h1>Една система. <em>Целият</em> ден на едно място.</h1>
            <p>Запитвания, клиенти, обаждания и съдържание. След входа ви чака „Днес“.</p>
        </div>
        <p class="adm-eyebrow">creatiumlab.com</p>
    </aside>
    <main class="adm-login-form">
        <div class="adm-login-box">
            <img class="logo-mobile" src="{{ asset('images/logo-g-352.webp') }}" alt="Creatium Lab">
            <h2>Вход</h2>
            <p class="lead">Въведете вашите данни за достъп до административния панел.</p>
            <form action="{{ route('login.post') }}" method="POST">
                @csrf
                <div class="adm-field">
                    <label for="email">Имейл адрес</label>
                    <input type="email" id="email" name="email" required autocomplete="username" autofocus placeholder="name@creatiumlab.com" value="{{ old('email') }}">
                    @error('email')<p class="adm-err">{{ $message }}</p>@enderror
                </div>
                <div class="adm-field">
                    <label for="password">Парола</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                </div>
                <label class="mb-6 flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" id="remember" name="remember"> Запомни ме</label>
                <button type="submit" class="adm-btn w-full justify-center" style="height:48px">Вход в системата <x-icon name="arrow-right" /></button>
            </form>
            <p class="mt-8 text-sm"><a href="{{ route('home') }}" class="text-gray-600 hover:underline">← Към сайта</a></p>
        </div>
    </main>
</div>
</body>
</html>
