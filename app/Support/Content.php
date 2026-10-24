<?php

namespace App\Support;

use App\Models\ContentBlock;
use Illuminate\Support\Facades\Cache;

/**
 * Site content: defaults come from config/creatium.php (Bulgarian) and config/creatium_en.php (English),
 * and anything edited in the admin panel (content_blocks table) replaces the default for that key and language.
 * Bound as a singleton, so the table is read at most once per request (and cached between requests).
 */
class Content
{
    /** Content keys editable in the admin panel, in menu order. */
    public const BLOCKS = [
        'home_hero' => 'Начална страница: горна част',
        'home_problem' => 'Начална страница: „Проблемът vs. Синергията“',
        'home_engine' => 'Начална страница: „Двигателят“',
        'home_trust' => 'Начална страница: доказателства, „С кого работим“, принципи, браншове, блог',
        'home_process' => 'Начална страница: работен процес',
        'home_contact' => 'Начална страница: контакти',
        'services_page' => 'Услуги',
        'plans' => 'Пакети и цени',
        'questions' => 'Често задавани въпроси (За нас)',
        'about' => 'За нас: текст',
        'team' => 'За нас: екипът',
    ];

    /** Pages with their own SEO settings (route names without the "en." prefix). */
    public const SEO_PAGES = [
        'home' => 'Начало',
        'services' => 'Услуги',
        'site-check' => 'Проверка на сайт',
        'packages' => 'Пакети',
        'portfolio' => 'Проекти',
        'blog.index' => 'Блог',
        'about' => 'За нас',
        'contact' => 'Контакти',
        'privacy' => 'Политика за поверителност',
        'terms' => 'Общи условия',
        'cookies' => 'Политика за бисквитките',
    ];

    public const SEO_FIELDS = ['meta_title', 'meta_description', 'canonical_url', 'og_title', 'og_description', 'og_image'];

    /** Labels for content fields in the admin form. Unknown keys are shown as they are. */
    public const FIELD_LABELS = [
        'title' => 'Заглавие', 'highlight' => 'Заглавие: втора част (в цвят)', 'subtitle' => 'Подзаглавие', 'text' => 'Текст',
        'intro' => 'Въведение', 'cards' => 'Карти', 'icon' => 'Икона', 'slug' => 'Котва в адреса (латиница)', 'tag' => 'Етикет под заглавието',
        'description' => 'Кратко описание', 'details' => 'Подробно (при „Вижте повече“)', 'includes' => 'Какво включва (по едно на ред)',
        'name' => 'Име', 'price_note' => 'Цена', 'features' => 'Какво включва (по едно на ред)', 'highlighted' => 'Препоръчан (тъмна карта)',
        'q' => 'Въпрос', 'a' => 'Отговор', 'role' => 'Роля', 'image' => 'Снимка', 'linkedin' => 'LinkedIn (адрес на профила)', 'link' => 'Връзка към страница от сайта (по желание, напр. /proverka-na-sait)', 'proof_label' => 'Надпис над доказателствата', 'fit_list_label' => 'Надпис над списъка', 'bio' => 'Разказ за човека (по един абзац на ред)', 'skills' => 'Умения (по едно на ред)', 'paragraphs' => 'Абзаци (по един на ред)',
        'button' => 'Текст на бутона', 'scenarios' => 'Сценарии', 'label' => 'Етикет',
        'tech_title' => 'Блок 1: заглавие', 'tech_tag' => 'Блок 1: етикет', 'tech_items' => 'Блок 1: елементи', 'growth_title' => 'Блок 2: заглавие', 'growth_tag' => 'Блок 2: етикет', 'growth_items' => 'Блок 2: елементи', 'steps' => 'Стъпки', 'cta_title' => 'Призив: заглавие', 'cta_button' => 'Призив: бутон',
        'accent' => 'Думи от заглавието в цвят (по една на ред)', 'eyebrow' => 'Надзаглавие', 'proof' => 'Доказателства под горната част', 'value' => 'Число или кратък факт',
        'engine_eyebrow' => 'Двигателят: надзаглавие', 'engine_center' => 'Двигателят: текст в центъра на схемата',
        'fit_eyebrow' => '„С кого работим“: надзаглавие', 'fit_title' => '„С кого работим“: заглавие', 'fit_text' => '„С кого работим“: текст', 'fit_items' => '„С кого работим“: подходящи бизнеси',
        'not_title' => '„Не сме подходящи“: заглавие', 'not_items' => '„Не сме подходящи“: случаи (по един на ред)',
        'principles_eyebrow' => 'Принципи: надзаглавие', 'principles_title' => 'Принципи: заглавие', 'principles_text' => 'Принципи: текст', 'principle_items' => 'Принципи',
        'demos_eyebrow' => 'Браншове: надзаглавие', 'demos_title' => 'Браншове: заглавие', 'demos_text' => 'Браншове: текст',
        'blog_eyebrow' => 'Блог: надзаглавие', 'blog_title' => 'Блог: заглавие', 'blog_text' => 'Блог: текст', 'contact_eyebrow' => 'Контакти: надзаглавие',
        'items' => 'Пакети', 'badge' => 'Етикет (напр. „Най-предпочитан“)', 'audience' => 'За кого е', 'prices' => 'Цени (по една на ред)', 'build_title' => 'Първа част: заглавие', 'build_items' => 'Първа част (по един на ред, „Заглавие: текст“)', 'monthly_title' => 'Месечна част: заглавие', 'monthly_items' => 'Месечна част (по един на ред, „Заглавие: текст“)',
    ];

    /** Fields shown as a multi-line text box. */
    public const LONG_FIELDS = ['text', 'intro', 'description', 'details', 'a', 'subtitle', 'fit_text', 'principles_text', 'demos_text', 'blog_text'];

    /** An empty copy of a config structure, used for "add item" in the admin form. */
    public static function blank(mixed $template): mixed
    {
        if (is_bool($template)) {
            return false;
        }
        if (is_array($template)) {
            return array_is_list($template) ? [] : array_map([self::class, 'blank'], $template);
        }

        return null;
    }

    private const CACHE_KEY = 'content_blocks';

    private ?array $overrides = null;

    /** Content for the current language, e.g. get('services') or get('hero.title'). */
    public function get(string $key, ?string $locale = null): mixed
    {
        $locale ??= app()->getLocale();
        [$top, $path] = array_pad(explode('.', $key, 2), 2, null);

        $value = $this->override($top, $locale) ?? $this->default($top, $locale);

        return $path === null ? $value : data_get($value, $path);
    }

    /** The value from the config files, ignoring admin edits. */
    public function default(string $key, string $locale): mixed
    {
        return ($locale === 'en' ? config("creatium_en.$key") : null) ?? config("creatium.$key");
    }

    public function override(string $key, string $locale): mixed
    {
        $this->overrides ??= $this->load();

        return $this->overrides[$locale][$key] ?? null;
    }

    /** SEO overrides for a page ("home", "blog.index", ...), only the filled-in fields. */
    public function seo(string $page, ?string $locale = null): array
    {
        return array_filter((array) $this->override("seo:$page", $locale ?? app()->getLocale()), 'filled');
    }

    public function save(string $key, string $locale, mixed $value, ?int $userId = null): void
    {
        ContentBlock::updateOrCreate(['key' => $key, 'locale' => $locale], ['value' => $value, 'updated_by' => $userId]);
        $this->flush();
    }

    public function reset(string $key, string $locale): void
    {
        ContentBlock::where(['key' => $key, 'locale' => $locale])->delete();
        $this->flush();
    }

    public function flush(): void
    {
        $this->overrides = null;
        Cache::forget(self::CACHE_KEY);
    }

    private function load(): array
    {
        try {
            return Cache::rememberForever(self::CACHE_KEY, fn () => ContentBlock::all()
                ->groupBy('locale')
                ->map(fn ($rows) => $rows->pluck('value', 'key')->all())
                ->all());
        } catch (\Throwable) {
            // Before the first migration there is no table: just use the config defaults.
            return [];
        }
    }
}
