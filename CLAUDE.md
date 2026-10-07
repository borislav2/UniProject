# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Laravel 12 app that is two things at once:

- **The public site of Creatium Lab** (a two-person agency for small Bulgarian businesses: website build and monitoring, GEO/SEO, email campaigns), live at creatiumlab.com. Bulgarian is the main language (root URLs); an English version lives under `/en`.
- **An internal admin panel** (`/admin`) for managing client projects. It started as a university "project management system", which is why the core model is `Project` with `Category` / `Technology` and roles `admin` / `project-manager` / `developer`.

The two halves meet in one place: **a contact-form submission becomes a `Project`** (`source = 'website'`, status `Planning`, category "Ново запитване"), so leads show up in the admin list next to real client work.

Visual and copy rules live in `DESIGN.md`; read it before touching views or text. The site follows a technical specification from the marketing partner (Владимир Цончев); GTM + Consent Mode, BG/EN and the CMS (blog, editable texts, per-page SEO) are all built.

## Commands

```bash
composer install && npm ci          # dependencies
npm run build                       # Vite build -> public/build (Tailwind v4)
php artisan test                    # full suite (SQLite in memory, see phpunit.xml)
php artisan test --filter=test_contact_form_creates_lead_and_notifies_team   # one test
php artisan test tests/Feature/MarketingTest.php                              # one file
php artisan migrate:fresh --seed    # local: also seeds demo users + demo projects
php artisan creatium:make-admin you@example.com --name="Name"   # create/promote an admin
```

Local DB: set `DB_CONNECTION=sqlite` and `DB_DATABASE=<abs path>/database/database.sqlite` in `.env`. Demo logins (local/testing only): `admin@projectmanager.com` / `password`.

`npm run dev` (and `composer dev`, which runs it) writes `public/hot`; while that file exists `@vite` points at the dev server and the built CSS is not used. Never leave it on a server (`deploy/deploy.sh` deletes it).

CI (`.github/workflows/tests.yml`) runs `php artisan test` on PHP 8.2/8.3/8.4 after copying `.env.example`; tests call `withoutVite()` (see `tests/TestCase.php`), so no build is needed for tests.

## Architecture

**Routing** (`routes/web.php`): public pages are registered from one `$publicPages` map for both languages. Bulgarian uses Bulgarian slugs at the root (`/uslugi`, `/paketi`, `/proekti`, `/za-nas`, `/kontakti`, `/poveritelnost`, `/usloviya`, `/biskvitki`); English uses `/en/...` with English slugs and the same route names prefixed `en.`. In views always link with `lroute('services')` (from `app/helpers.php`), never `route()`, so links stay in the current language. Dynamic `/sitemap.xml` (both languages with hreflang alternates) and `/robots.txt` come from `PageController`. `/about` and `/contact` 301-redirect. There is **no public registration**; accounts come from `creatium:make-admin` or the admin Users screen. Admin routes use `auth` + `admin` (`CheckAdminAccess`: any of the three roles); user management additionally needs `role:admin` (`RequireRole`). Login and the contact POST are throttled.

**Languages**: the global `SetLocale` middleware sets the locale to `en` when the first path segment is `en`, otherwise `bg` (this also covers 404 pages). Views wrap UI text as `__('Bulgarian text')`; `lang/en.json` maps it to English, and a test fails if a translation is empty or still Cyrillic. Long legal pages have separate English views in `resources/views/en/` (`PageController::localized()`). The layout outputs hreflang links and the BG/EN switcher for any route that exists in both languages. Leads record `locale`; admin and the notification email stay Bulgarian and always store the Bulgarian service label.

**Site content is data, not markup.** `config/creatium.php` holds contact details, hero and audience copy, services, `contact_topics` (the form's service dropdown), process steps, packages/prices, FAQ, team, industries, legal entity data, `gtm_id`, `google_site_verification`, the `cookies` list shown on `/biskvitki`, `inline_css` and `notify_email`. English versions of the content keys are in `config/creatium_en.php` (same keys; missing keys fall back to Bulgarian). Views read content through `site('services')`, never `config()` directly. `contact_topics` is keyed (`website`, `monitoring`, `geo-seo`): the key is the form value, the label is per language. Change copy there first. Contact/legal values can be overridden by `CREATIUM_*` env vars (phones: `CREATIUM_PHONES`, comma-separated). Because production runs `config:cache`, read env only inside config files.

**Lead flow** (`HomeController@submitContact`):
1. Validates name, phone, `service` (must be one of `creatium.contact_topics`), an optional message, and a required `consent` checkbox. A hidden `website` field is a honeypot: if filled, the request fakes success and stores nothing.
2. Creates the `Project` lead (phone in `client_phone`, topic in `service`), copying attribution from the session.
3. Sends `App\Mail\NewLeadMail` to `creatium.notify_email` (default `hello@creatiumlab.com`) and stamps `email_sent_at`; a mail failure is logged, leaves `email_sent_at` empty and never loses the lead. Production `.env` starts with `MAIL_MAILER=log`: run `bash deploy/configure-mail.sh` once to switch to SMTP and `php artisan creatium:test-mail` to verify. The admin menu **Запитвания** (`Admin\InquiryController`) lists website leads with full messages, unread state (`read_at`) and a warning while the mailer is `log`/`array`.
4. Flashes `success`, `lead_created` and `lead_service`; the next page pushes `{event: 'generate_lead', lead_service}` to the GTM dataLayer.

**Attribution**: `CaptureLeadAttribution` (appended to the `web` group in `bootstrap/app.php`) stores UTM params, `gclid`/`fbclid` and the external referrer host in the session on GET requests (skipping admin/login). `App\Support\LeadAttribution::channel()` maps that to a channel label saved on the lead; the admin dashboard aggregates leads by channel for the last 30 days.

**Portfolio** (`/proekti`) shows only projects with `is_public = true` and status `Completed`; the admin form has the checkbox.

**Tracking (GTM + Consent Mode v2)**: `creatium.gtm_id` comes from `GTM_ID` (validated; defaults to `GTM-5QRGW6DP` only when `APP_ENV=production`, empty disables it). When set, `partials/gtm-head` (top of `<head>`) first sets Consent Mode defaults to denied, re-applies a stored choice from localStorage (`creatium-consent`, valid 12 months), then loads GTM; the noscript iframe follows `<body>`. `partials/cookie-consent` is the category banner (necessary / analytics / marketing): it sends `gtag('consent','update')` plus a `consent_update` dataLayer event, and on withdrawal clears tracking cookies and reloads. GA4, Clarity and ad pixels are configured inside GTM by the marketing partner, never hard-coded in the site. The privacy and cookie pages switch their text on `gtm_id`. `GOOGLE_SITE_VERIFICATION` adds the Search Console meta tag (not needed with DNS verification). The Bing Webmaster Tools tag (`msvalidate.01`, `creatium.bing_site_verification`, default baked in, `BING_SITE_VERIFICATION=` empty disables it) is output on every public page and must not be removed, or Bing drops the verification.

**Theme**: the public layout supports light and dark. A tiny inline script at the top of `<head>` adds `.dark` to `<html>` from localStorage (`creatium-theme`) or the OS setting before paint; the header button toggles and saves it. Tailwind's `dark:` variant is bound to that class in `app.css`, which also defines `ink-*` background colors and dark versions of the custom utilities. Every new public view needs `dark:` classes (see DESIGN.md); the admin panel is light only.

**Frontend**: Tailwind v4 via Vite with brand tokens and custom utilities defined in `resources/css/app.css`. Icons are inline SVG via `<x-icon name="...">` (`resources/views/components/icon.blade.php`), with paths stored in `App\Support\Icons` (Font Awesome Free, CC BY 4.0). The Font Awesome package is not installed: to add an icon, add its solid SVG path to `Icons::ICONS`. In production the public layout inlines the built CSS (`Vite::content`) when `creatium.inline_css` is true; the admin layout always links it. Shared public pieces: `partials/page-header`, `partials/cta`, `partials/contact-form`.

**CMS** (admin "Сайт" menu, open to every admin-panel role):
- **Blog**: `Post` and `PostCategory` store translatable fields as JSON objects keyed by locale (`HasTranslations` trait: `$post->tr('title')`, `trOrBg()`). A language exists for a post only when its title is filled; `Post::visible($locale)` = published, `published_at` in the past, and title/slug present in that language. Slugs are generated in Latin from the title (`Str::slug(..., 'bg')`). Bodies are Markdown rendered with `Str::markdown` and `html_input => strip`. Public routes `/blog`, `/blog/kategoriya/{slug}`, `/blog/{slug}` (English: `/en/blog/category/{slug}`). The admin editor is EasyMDE (`resources/js/admin-editor.js`, own SVG toolbar icons, no CDN) with image upload to `admin.uploads.image`.
- **Editable texts**: `App\Support\Content` (singleton behind `site()`) returns the admin override from `content_blocks` (key + locale, cached forever, flushed on save), else `config/creatium_en.php` for English, else `config/creatium.php`. Only keys in `Content::BLOCKS` are editable; the admin form is generated from the Bulgarian config structure (`admin/content/_field`), so adding a field to the config adds it to the form. Strings are inputs/textareas, lists of strings are "one per line", lists of arrays get add/remove, fields ending in `image` are uploads.
- **SEO**: per-page overrides are stored as `content_blocks` with key `seo:<route name>` (pages in `Content::SEO_PAGES`); posts have their own fields. The public layout prefers controller-provided `$seo` (posts), then the page override, then the view's `@section('title'/'meta_description')`. Overridden Meta Title is used as the full `<title>` (no suffix). Controllers can pass `$pageAlternates` / `$hreflangAlternates` for routes with parameters.
- **Uploads**: `App\Support\Uploads` stores images in `public/uploads/{blog,content}` (jpg/png/webp/gif, 5 MB, no SVG) and only accepts back paths it created.
- **Profile**: `/admin/profile` lets each user change name, email and password (current password required).

**Seeders** are idempotent (`firstOrCreate`). `DatabaseSeeder` only seeds roles/categories/technologies in production; demo users and projects are limited to `local`/`testing`.

## Deployment

Production is a Webdock VPS (AlmaLinux 9, Nginx, PHP-FPM, MariaDB, SELinux enforcing). Full guide: `DEPLOY-WEBDOCK.md` (general notes in `DEPLOY.md`). Scripts in `deploy/`:

- `almalinux-setup.sh`: one-time setup; safe to rerun.
- `configure-env.sh`: writes the production `.env` from the DB credentials in `/root/creatium-db.txt`.
- `deploy.sh`: pull, composer, npm build, migrate, seed, caches. Run it as the `deploy` user.
- `update-nginx.sh`: static-file caching.
- `backup.sh`: DB + uploads backup.

PHP-FPM runs as the `deploy` user, so app files belong to `deploy`; don't reintroduce `chgrp`/group-based permissions. Run git and artisan on the server as `deploy`.
