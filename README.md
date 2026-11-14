# Creatium Lab

Laravel 12 app with two parts:

- **The public site of Creatium Lab** (creatiumlab.com), an agency for small Bulgarian businesses (websites, SEO and AI visibility, email campaigns). Bulgarian at the root, English under `/en`.
- **The admin panel** (`/admin`): leads, client projects and subscriptions, outreach, blog, editable texts and SEO. One menu per person (profiles Админ / Сайтове и техника / Маркетинг и продажби / Бранд и съдържание).

Stack: PHP 8.2+, Laravel 12, Vite + Tailwind v4, SQLite locally and MariaDB in production.

## Run it locally

```bash
composer install && npm ci
cp .env.example .env && php artisan key:generate
# in .env: DB_CONNECTION=sqlite and DB_DATABASE=<absolute path>/database/database.sqlite
touch database/database.sqlite
php artisan migrate:fresh --seed     # also seeds demo users (local only): admin@projectmanager.com / password
npm run build                        # or npm run dev while working on CSS and JS
php artisan serve
```

Create or promote an admin: `php artisan creatium:make-admin you@example.com --name="Name" --profile=all`.

## Tests

```bash
php artisan test                                  # the full suite (SQLite in memory, no build needed)
php artisan test --filter=DesignGTest             # one class or test
```

## Where the documentation is

- `CLAUDE.md`: how the project is built (architecture, content and admin overrides, designs, admin panel, integrations). Start here.
- `DESIGN.md`: visual and copy rules for the public site.
- `DEPLOY-WEBDOCK.md`: production on a Webdock VPS (`DEPLOY.md` has general notes); scripts are in `deploy/`.
- `tools/*/README.md`: guides for Facebook, LinkedIn, Telegram and Harvey.
