# Пускане на Creatium Lab в реална среда

Този файл описва как от кода в това хранилище се стига до работещ сайт на `creatiumlab.com`.

## 1. Домейн

1. Проверете дали `creatiumlab.com` е свободен при регистратор (Namecheap, Cloudflare Registrar, Gandi и др.). Обикновено струва около 10-15 USD/година.
2. Регистрирайте го на **вашето име/фирма**, не на посредник. Включете автоматично подновяване, WHOIS защита и двуфакторен вход.
3. Препоръка: заемете и `creatiumlab.bg`, ако е свободен, и го пренасочете към `.com`.

## 2. Хостинг

> Избран вариант: **Webdock VPS с AlmaLinux 9**. Пълните стъпки са в [DEPLOY-WEBDOCK.md](DEPLOY-WEBDOCK.md). Общото описание по-долу важи за всеки хостинг.

Приложението е Laravel (PHP 8.2+, MySQL/MariaDB). Три реалистични пътя:

| Път | Подходящ за | Забележка |
|---|---|---|
| **Споделен хостинг** с PHP 8.2+, MySQL и SSH | най-евтино начало | Document root трябва да сочи към папка `public/`. Билдът на ресурсите (`npm run build`) се прави локално и папката `public/build` се качва. |
| **VPS** (Hetzner, DigitalOcean) + Nginx + PHP-FPM + MySQL + Certbot | пълен контрол | Изисква администриране на сървър. |
| **Управляван Laravel хостинг** (Laravel Cloud, Forge + VPS) | най-малко грижи | Най-скъпо, но автоматизира деплой, SSL и бекъпи. |

Който и да изберете: нужен е **HTTPS** (Let's Encrypt е безплатен) и документен root към `public/`.

## 3. Първо пускане (общи стъпки)

```bash
git clone https://github.com/borislav2/UniProject.git && cd UniProject
composer install --no-dev --optimize-autoloader
npm ci && npm run build            # или качете public/build, ако сървърът няма Node
cp .env.example .env && php artisan key:generate
```

Редактирайте `.env` за production:

```env
APP_NAME="Creatium Lab"
APP_ENV=production
APP_DEBUG=false
APP_URL=https://creatiumlab.com
APP_LOCALE=bg

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=...
DB_USERNAME=...
DB_PASSWORD=...

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=sync

MAIL_MAILER=smtp
MAIL_HOST=...          # от доставчика за имейли, вижте по-долу
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_FROM_ADDRESS=hello@creatiumlab.com
MAIL_FROM_NAME="Creatium Lab"

CREATIUM_EMAIL=hello@creatiumlab.com
CREATIUM_PHONE="+359 ..."
CREATIUM_NOTIFY_EMAIL=...   # къде да пристигат известията за запитвания
CREATIUM_COMPANY="..."
CREATIUM_EIK=...
CREATIUM_ADDRESS="..."

# META_PIXEL_ID=123456789012345   # по желание, вижте „Проследяване на запитванията и Meta Pixel“
```

След това:

```bash
php artisan migrate --force
php artisan db:seed --force            # в production добавя само роли, категории и технологии (без демо данни)
php artisan creatium:make-admin you@creatiumlab.com --name="Вашето име"   # пита за парола (мин. 12 символа)
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

Папките `storage/`, `bootstrap/cache/` и `public/uploads/` трябва да са записваеми от уеб сървъра.

Няма публична регистрация. Допълнителни акаунти за екипа се създават от админ панела (Users) или с командата `creatium:make-admin`.

## 4. Имейл

- **Известия за запитвания:** формата изпраща имейл на `CREATIUM_NOTIFY_EMAIL`. Ползвайте доставчик за транзакционни имейли (Brevo, Mailgun, Postmark, Resend) и добавете **SPF, DKIM и DMARC** записи в DNS, иначе писмата ще попадат в спам.
- Запитването винаги се записва в админ панела, дори имейлът да не успее да се изпрати.
- **Фирмена поща** (`hello@creatiumlab.com`): Google Workspace, Zoho Mail или хостингът ви.

## 5. След пускане: чеклист

- [ ] Изпратете тестово запитване от сайта и проверете, че пристига в `/admin` и на имейла
- [ ] Заменете всички `TODO` в `config/creatium.php` (телефон, град, цени на пакетите) и данните на фирмата за Политиката за поверителност
- [ ] Прегледайте `/poveritelnost` и `/usloviya` с юрист или счетоводител: това са шаблони, не правна консултация
- [ ] Добавете Google Search Console и подайте `https://creatiumlab.com/sitemap.xml`
- [ ] Създайте Google Business профил за фирмата
- [ ] Следене на наличността (напр. UptimeRobot към `https://creatiumlab.com/up`)
- [ ] Автоматичен бекъп на базата данни и на `public/uploads/` (поне веднъж дневно)
- [ ] Ако добавите Google Analytics, Meta Pixel или подобни: нужен е **банер за съгласие с бисквитки** и обновена политика

## Проследяване на запитванията и Meta Pixel

**Откъде идват клиентите.** Всяко запитване от сайта записва канала, от който е дошъл посетителят. Каналът се вижда в `/admin` (етикет в списъка с проекти, подробности в самия проект и обобщение за последните 30 дни на таблото). Разпознават се:

- UTM параметри в линка: `utm_source`, `utm_medium`, `utm_campaign`
- реклами: `gclid` (Google) и `fbclid` (Meta)
- препращащ сайт (напр. google.com)
- всичко останало е „Директно“

Слагайте UTM параметри на всеки линк към сайта, който публикувате, например:

```
https://creatiumlab.com/?utm_source=instagram&utm_medium=bio
https://creatiumlab.com/?utm_source=facebook&utm_medium=paid&utm_campaign=esen-2026
https://creatiumlab.com/?utm_source=vizitka&utm_medium=qr
```

**Meta Pixel (по желание).** Сложете ID-то на пиксела в `.env` (`META_PIXEL_ID=...`) и пуснете `php artisan config:cache`. Тогава сайтът показва банер за бисквитки. Пикселът се зарежда **само** след „Приемам“ и отчита `PageView`, а при изпратено запитване и `Lead`, по който рекламите в Meta могат да се оптимизират. Политиката за поверителност автоматично добавя текст за пиксела, а във footer-а се появява „Настройки за бисквитки“ за оттегляне на съгласието. Без `META_PIXEL_ID` няма нито банер, нито пиксел.

## 6. Деплой на промени

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
```

## 7. Какво още може да се добави

Блог (за SEO), реално портфолио (маркирайте завършените проекти като публични в админ панела), клиентски портал за следене на проекта, онлайн записване на консултация, английска версия, забравена парола за екипа.
