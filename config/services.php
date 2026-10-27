<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    // Търсене на бизнеси в Google Maps (serper.dev, 2 500 безплатни търсения). Без ключ се търси в OpenStreetMap.
    'serper' => [
        'key' => env('SERPER_API_KEY'),
    ],

    // Проверка на имейлите от търсенето на бизнеси (по желание; reoon.com, 600 безплатни на месец).
    'reoon' => [
        'key' => env('REOON_API_KEY'),
    ],

    // Чернови на отговори на отзиви (админ: „Отговори на отзиви“). Без ключ се ползват шаблони.
    'anthropic' => [
        'key' => env('ANTHROPIC_API_KEY'),
        'model' => env('ANTHROPIC_MODEL', 'claude-opus-5-5'),
        // Моделът на чата на сайта; по подразбиране същият.
        'chat_model' => env('ANTHROPIC_CHAT_MODEL') ?: env('ANTHROPIC_MODEL') ?: 'claude-opus-5-5',
    ],

    // Чатът на сайта с Mistral (безплатен план: изключете използването на данните за обучение в акаунта).
    'mistral' => [
        'key' => env('MISTRAL_API_KEY'),
        'model' => env('MISTRAL_MODEL') ?: 'mistral-medium-latest',
        'base_url' => env('MISTRAL_BASE_URL') ?: 'https://api.mistral.ai/v1',
    ],

    // Публикуване на статиите от блога във Facebook страницата. Ключът се взима с php artisan creatium:facebook-connect.
    'facebook' => [
        'page_id' => env('FACEBOOK_PAGE_ID'),
        'page_token' => env('FACEBOOK_PAGE_TOKEN'),
        'page_name' => env('FACEBOOK_PAGE_NAME'),
        'graph_url' => env('FACEBOOK_GRAPH_URL') ?: 'https://graph.facebook.com',
    ],

    // Известия в Telegram (ново запитване, записан час, клик по първото писмо). Свързва се с php artisan creatium:telegram-connect.
    'telegram' => [
        'bot_token' => env('TELEGRAM_BOT_TOKEN'),
        'chat_id' => env('TELEGRAM_CHAT_ID'),
        'api_url' => env('TELEGRAM_API_URL') ?: 'https://api.telegram.org',
    ],

];
