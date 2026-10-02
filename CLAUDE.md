# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## What this is

A Laravel 12 app that is two things at once:

- **The public site of Creatium Lab** (a two-person agency: websites + SEO for small Bulgarian businesses), live at creatiumlab.com. All public copy is Bulgarian.
- **An internal admin panel** (`/admin`) for managing client projects. It started as a university "project management system", which is why the core model is `Project` with `Category` / `Technology` and roles `admin` / `project-manager` / `developer`.

The two halves meet in one place: **a contact-form submission becomes a `Project`** (`source = 'website'`, status `Planning`, category "Ново запитване"), so leads show up in the admin list next to real client work.

Visual and copy rules live in `DESIGN.md`; read it before touching views or text.

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

**Routing** (`routes/web.php`): public pages use Bulgarian slugs (`/uslugi`, `/proekti`, `/za-nas`, `/kontakti`, `/poveritelnost`, `/usloviya`) plus dynamic `/sitemap.xml` and `/robots.txt` (`PageController`). `/about` and `/contact` 301-redirect. There is **no public registration**; accounts come from `creatium:make-admin` or the admin Users screen. Admin routes use `auth` + `admin` (`CheckAdminAccess`: any of the three roles); user management additionally needs `role:admin` (`RequireRole`). Login and the contact POST are throttled.

**Site content is data, not markup.** `config/creatium.php` holds contact details, services, process steps, packages/prices, FAQ, team, industries, legal entity data, `meta_pixel_id`, `inline_css` and `notify_email`. Views loop over it; change copy there first. Contact/legal values can be overridden by `CREATIUM_*` env vars. Because production runs `config:cache`, read env only inside config files.

**Lead flow** (`HomeController@submitContact`):
1. Validates name, contact (email *or* phone, checked by a closure), message, and a required `consent` checkbox. A hidden `website` field is a honeypot: if filled, the request fakes success and stores nothing.
2. Creates the `Project` lead, copying attribution from the session.
3. Sends `App\Mail\NewLeadMail` to `creatium.notify_email`; a mail failure is logged and never loses the lead.
4. Flashes `success` and `lead_created`. The latter drives the Meta Pixel `Lead` event.

**Attribution**: `CaptureLeadAttribution` (appended to the `web` group in `bootstrap/app.php`) stores UTM params, `gclid`/`fbclid` and the external referrer host in the session on GET requests (skipping admin/login). `App\Support\LeadAttribution::channel()` maps that to a channel label saved on the lead; the admin dashboard aggregates leads by channel for the last 30 days.

**Portfolio** (`/proekti`) shows only projects with `is_public = true` and status `Completed`; the admin form has the checkbox.

**Meta Pixel** is opt-in twice: nothing renders unless `META_PIXEL_ID` is set (validated as digits in config), and then `partials/cookie-consent` loads the pixel only after the visitor accepts. The privacy page switches its cookie section on the same config value.

**Frontend**: Tailwind v4 via Vite with brand tokens and custom utilities defined in `resources/css/app.css`. Icons are inline SVG via `<x-icon name="...">` (`resources/views/components/icon.blade.php`), with paths stored in `App\Support\Icons` (Font Awesome Free, CC BY 4.0). The Font Awesome package is not installed: to add an icon, add its solid SVG path to `Icons::ICONS`. In production the public layout inlines the built CSS (`Vite::content`) when `creatium.inline_css` is true; the admin layout always links it. Shared public pieces: `partials/page-header`, `partials/cta`, `partials/contact-form`.

**Seeders** are idempotent (`firstOrCreate`). `DatabaseSeeder` only seeds roles/categories/technologies in production; demo users and projects are limited to `local`/`testing`.

## Deployment

Production is a Webdock VPS (AlmaLinux 9, Nginx, PHP-FPM, MariaDB, SELinux enforcing). Full guide: `DEPLOY-WEBDOCK.md` (general notes in `DEPLOY.md`). Scripts in `deploy/`:

- `almalinux-setup.sh`: one-time setup; safe to rerun.
- `configure-env.sh`: writes the production `.env` from the DB credentials in `/root/creatium-db.txt`.
- `deploy.sh`: pull, composer, npm build, migrate, seed, caches. Run it as the `deploy` user.
- `update-nginx.sh`: static-file caching.
- `backup.sh`: DB + uploads backup.

PHP-FPM runs as the `deploy` user, so app files belong to `deploy`; don't reintroduce `chgrp`/group-based permissions. Run git and artisan on the server as `deploy`.
