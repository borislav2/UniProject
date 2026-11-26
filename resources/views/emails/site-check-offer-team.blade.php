Клиент от безплатната проверка поиска оферта „{{ $name }}“.

Сайт {{ preg_replace('#^https?://#', '', (string) $project->website_url) }}
Имейл {{ $project->client_email }}
Цена {{ \App\Support\SiteCheck\Offers::money(\App\Support\SiteCheck\Offers::price($offer)) }}{{ $offer === 'monitor' ? ' на месец' : ', еднократно' }}

Още нищо не е платено. Обещахме да му пишем до един работен ден. Запитването е тук
{{ route('admin.projects.show', $project) }}
