<?php

namespace App\Models;

use App\Support\Outreach\BusinessSearch;
use App\Support\Outreach\Markets;
use Illuminate\Database\Eloquent\Model;

/** Бизнес, намерен от Harvey, до който може да пишем първо писмо от админа („Потенциални клиенти“). */
class OutreachProspect extends Model
{
    public const STATUSES = [
        'new' => 'Нов',
        'sent' => 'Изпратено',
        'replied' => 'Отговори',
        'skipped' => 'Пропуснат',
        'routed' => 'Обаждане или LinkedIn',
        'unsubscribed' => 'Отписан',
    ];

    /** Линковете в първото писмо, които броим (ключът е в адреса /o/{prospect}/{link}). */
    public const CLICK_LINKS = [
        'site' => 'Сайта',
        'check' => 'Безплатна проверка',
        'booking' => 'Запазване на час',
    ];

    /**
     * Потвърдени (verified), catch-all (risky) или публикувани от самия бизнес на домейн, който приема поща (published).
     * „guess“ (налучкан или домейн без поща) не се праща.
     */
    public const SENDABLE_EMAIL_STATUSES = ['verified', 'risky', 'published'];

    public const EMAIL_STATUSES = [
        'verified' => 'потвърден',
        'risky' => 'приема всичко',
        'published' => 'публикуван от бизнеса',
        'guess' => 'непроверен',
        'invalid' => 'невалиден',
    ];

    protected $fillable = [
        'email', 'first_name', 'last_name', 'company', 'title', 'website', 'industry', 'city',
        'personalization', 'findings', 'site_checked_at', 'check_note', 'email_status', 'score', 'status', 'sent_at', 'unsubscribed_at',
        'clicked_at', 'last_clicked_at', 'click_count', 'last_click_link', 'phone', 'project_id', 'followup_sent_at',
        'country', 'language', 'company_evidence', 'score_reasons', 'draft_subject', 'draft_observation', 'draft_at', 'approved_by', 'outreach_search_id',
    ];

    protected $casts = ['score_reasons' => 'array', 'draft_at' => 'datetime', 'sent_at' => 'datetime', 'unsubscribed_at' => 'datetime', 'score' => 'integer', 'findings' => 'array', 'site_checked_at' => 'datetime',
        'clicked_at' => 'datetime', 'last_clicked_at' => 'datetime', 'click_count' => 'integer', 'followup_sent_at' => 'datetime'];

    /**
     * Бизнеси, на които може да се прати едно напомняне: първото писмо е изпратено преди поне
     * creatium.outreach.followup_after_days дни, не са отговорили, не са се отписали и още нямат напомняне.
     */
    public function scopeDueForFollowUp($query)
    {
        return $query->where('status', 'sent')
            ->whereNull('followup_sent_at')->whereNull('unsubscribed_at')->whereNull('project_id')
            ->where('sent_at', '<=', now()->subDays(max(1, (int) config('creatium.outreach.followup_after_days'))))
            ->whereNotIn('email', OutreachSuppression::select('email'));
    }

    public function canFollowUp(): bool
    {
        return self::whereKey($this->id)->dueForFollowUp()->exists();
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function search()
    {
        return $this->belongsTo(OutreachSearch::class, 'outreach_search_id');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /** Чернови, които чакат одобрение от човек („За одобрение“): нови, с подготвена чернова, още не писани. */
    public function scopeAwaitingApproval($query)
    {
        return $query->where('status', 'new')->whereNotNull('draft_at')
            ->whereNotIn('email', OutreachSuppression::select('email'));
    }

    /**
     * Единственото място, което решава дали на бизнеса може да се пише: статусът, имейлът, отписването И правилата
     * на пазара му (Markets::emailRule). Всяко изпращане минава оттук, така че никой екран не го заобикаля.
     */
    /** Агенция, намерена от търсенето: партньор за бял етикет, не клиент (виж Opportunities\PartnerOpportunities). */
    public function isPartner(): bool
    {
        return \App\Support\Opportunities\PartnerOpportunities::isPartner($this);
    }

    public function canBeEmailed(): bool
    {
        return $this->status === 'new'
            && in_array($this->email_status, self::SENDABLE_EMAIL_STATUSES, true)
            && ! OutreachSuppression::where('email', $this->email)->exists()
            && Markets::emailAllowed($this);
    }

    /** Защо не може да се пише по имейл (български текст), или null, ако може. */
    public function emailBlockReason(): ?string
    {
        if ($this->status !== 'new' || ! in_array($this->email_status, self::SENDABLE_EMAIL_STATUSES, true) || OutreachSuppression::where('email', $this->email)->exists()) {
            return 'На този адрес не може да се пише: вече е изпратено, адресът не е потвърден или е отписан.';
        }

        return Markets::emailReason($this);
    }

    /** Езикът на писмото: записаният, иначе този на пазара (за България bg). */
    public function mailLanguage(): string
    {
        return in_array($this->language, ['bg', 'en'], true) ? $this->language : Markets::language($this->country);
    }

    public function marketLabel(): string
    {
        return Markets::label($this->country);
    }

    /** Темата по подразбиране на първото писмо, на езика на писмото. */
    public function defaultSubject(): string
    {
        if ($this->mailLanguage() === 'en') {
            return $this->findings
                ? 'Findings from checking the ' . $this->displayName() . ' website'
                : 'How ' . $this->displayName() . ' appears on Google and Google Maps';
        }

        return $this->findings
            ? 'Резултати от проверка на сайта на ' . $this->displayName()
            : 'Видимост на ' . $this->displayName() . ' в Google и Google Maps';
    }

    public function displayName(): string
    {
        return $this->company ?: $this->email;
    }

    /**
     * Проблемите от проверката, преведени на езика на собственика: какво е и какво прави клиентът заради това.
     * Ключът е началото на заглавието от SiteChecker; непознатите остават с текста от проверката.
     */
    private const FINDING_TEXTS = [
        'Сайтът не се отваря' => ['Сайтът не се отваря', 'При проверката сайтът не отговори. Всеки, който ви търси в момента, вижда грешка вместо вашата страница.'],
        'Сайтът не работи през HTTPS' => ['Браузърът отбелязва сайта като „незащитен“', 'Част от посетителите затварят страницата още при това предупреждение.'],
        'Адресът с http не води' => ['Старите връзки водят към незащитена версия на сайта', 'Посетителите, които идват по тях, виждат предупреждение в браузъра.'],
        'Адресът с http:// не води' => ['Старите връзки водят към незащитена версия на сайта', 'Посетителите, които идват по тях, виждат предупреждение в браузъра.'], // по-стари проверки
        'Сървърът отговаря' => ['Сайтът започва да се зарежда след :s секунди', 'Голяма част от посетителите не изчакват и се връщат към резултатите в Google.'],
        'Няма заглавие за Google' => ['Сайтът няма заглавие за Google', 'Google сам решава какво да покаже за вас в резултатите и рядко това е най-доброто представяне.'],
        'Заглавието за Google' => ['Заглавието в Google не е оптимизирано', 'Част от него се отрязва или не казва ясно какво предлагате и къде.'],
        'Няма описание за Google' => ['Липсва описание в резултатите на Google', 'Google показва случаен откъс от страницата, който не дава на клиента причина да избере вас.'],
        'Описанието за Google' => ['Описанието в Google не е оптимизирано', 'Текстът под името ви в резултатите е отрязан или твърде кратък, за да убеди.'],
        'Няма основно заглавие' => ['Структурата на страницата не е ясна за Google', 'Това затруднява класирането по търсенията, по които трябва да ви намират.'],
        'Няколко основни заглавия' => ['Структурата на страницата не е ясна за Google', 'Това затруднява класирането по търсенията, по които трябва да ви намират.'],
        'Не е настроен за телефон' => ['Сайтът не е пригоден за телефон', 'Повечето клиенти ви търсят от телефона, а там текстът се показва смален и трудно се чете.'],
        'Сайтът е скрит от Google' => ['Сайтът е изключен от резултатите в Google', 'Дори хората, които търсят бизнеса ви по име, не го намират. Обикновено това е забравена настройка.'],
        'AI асистентите са спрени' => ['ChatGPT и другите AI асистенти нямат достъп до сайта', 'Когато клиент ги помоли за препоръка, те не могат да предложат вас.'],
        'Няма карта на сайта' => ['Липсва карта на сайта (sitemap)', 'Google открива страниците ви по-бавно и може да пропусне част от тях.'],
        'Няма данни за бизнеса' => ['Google няма структурирани данни за бизнеса', 'Адресът, работното време и услугите ви не се разпознават автоматично, което намалява видимостта в местните резултати и в Google Maps.'],
        'Не е настроено как изглежда при споделяне' => ['Сайтът не се показва добре при споделяне', 'Когато някой изпрати връзката във Facebook или Viber, тя излиза без снимка и описание.'],
        'Няма телефон за обаждане' => ['Телефонът на сайта не се набира с едно докосване', 'Посетителите от телефон трябва да преписват номера, а мнозина не го правят.'],
        'Снимки без описание' => ['Снимките нямат описания', 'Google не разбира какво показват и те не помагат в търсенето.'],
    ];

    /**
     * Готово наблюдение за първото писмо от проверката на сайта им. Само истински находки, без измислени.
     * Редовете с „– “ стават номериран списък в писмото (OutreachMail::observationParts).
     */
    public function suggestedObservation(?string $language = null): string
    {
        return ($language ?? $this->mailLanguage()) === 'en' ? $this->observationEn() : $this->observationBg();
    }

    private function observationBg(): string
    {
        $host = $this->website ? preg_replace('/^www\./', '', (string) parse_url($this->website, PHP_URL_HOST)) : null;
        if (! $host) {
            return '';
        }
        $name = $this->company ?: $host;

        if (empty($this->findings)) {
            $query = BusinessSearch::CATEGORIES[$this->industry]['query'] ?? null;
            $search = $query && $this->city
                ? 'потърсете в Google „' . $query . ' ' . $this->city . '“'
                : 'потърсете в Google услугата, която предлагате, заедно с града си';

            return "Проверихме сайта на {$name} ({$host}) по 14 технически показателя и той е в добро състояние. Това не е често срещано и е добра основа.\n"
                . "Следващият въпрос е дали клиентите стигат до него. Препоръчвам ви проста проверка: {$search}. Позициите в първите три резултата и в картата получават по-голямата част от обажданията.";
        }

        $lines = array_map(function ($finding) {
            foreach (self::FINDING_TEXTS as $prefix => [$title, $impact]) {
                if (str_starts_with($finding['title'], $prefix)) {
                    $seconds = preg_match('/\(([\d.,]+) s\)/u', $finding['title'], $m) ? str_replace('.', ',', $m[1]) : '';
                    $title = $seconds === '' ? str_replace('след :s секунди', 'бавно', $title) : str_replace(':s', $seconds, $title);

                    return '– ' . $title . '. ' . $impact;
                }
            }
            $detail = preg_split('/(?<=[.!?])\s/u', trim($finding['detail']))[0] ?? '';

            return '– ' . rtrim($finding['title'], '.') . '. ' . $detail;
        }, $this->findings);

        $where = $this->city ? "При преглед на бизнесите в {$this->city} проверихме и сайта на {$name} ({$host})" : "Проверихме сайта на {$name} ({$host})";

        return $where . ' по 14 технически показателя, които влияят на това дали клиентите ви намират и дали остават на сайта. Ето най-важното, което установихме.' . "\n" . implode("\n", $lines);
    }
    /** Същите проблеми на английски за писмата до Великобритания, Ирландия и Нидерландия. Ключът е същият като в FINDING_TEXTS. */
    private const FINDING_TEXTS_EN = [
        'Сайтът не се отваря' => ['The website did not open when we checked', 'Anyone looking for you at that moment sees an error instead of your page.'],
        'Сайтът не работи през HTTPS' => ['Browsers flag the website as not secure', 'Some visitors leave as soon as they see that warning.'],
        'Адресът с http не води' => ['Old links lead to an unsecured version of the website', 'Visitors who arrive through them see a warning in their browser.'],
        'Адресът с http:// не води' => ['Old links lead to an unsecured version of the website', 'Visitors who arrive through them see a warning in their browser.'],
        'Сървърът отговаря' => ['The website starts loading after :s seconds', 'Many visitors do not wait and go back to the Google results.'],
        'Няма заглавие за Google' => ['The website has no page title for Google', 'Google decides for itself what to show for you in the results, and that is rarely the best way to present you.'],
        'Заглавието за Google' => ['The page title in Google is not optimised', 'Part of it is cut off, or it does not say clearly what you offer and where.'],
        'Няма описание за Google' => ['There is no description in the Google results', 'Google shows a random snippet from the page, which gives a customer no reason to pick you.'],
        'Описанието за Google' => ['The description in Google is not optimised', 'The text under your name in the results is cut off or too short to persuade.'],
        'Няма основно заглавие' => ['The page structure is not clear to Google', 'That makes it harder to rank for the searches where people should find you.'],
        'Няколко основни заглавия' => ['The page structure is not clear to Google', 'That makes it harder to rank for the searches where people should find you.'],
        'Не е настроен за телефон' => ['The website is not set up for mobile phones', 'Most customers look for you on a phone, where the text shows tiny and is hard to read.'],
        'Сайтът е скрит от Google' => ['The website is excluded from the Google results', 'Even people who search for your business by name cannot find it. This is usually a forgotten setting.'],
        'AI асистентите са спрени' => ['ChatGPT and other AI assistants are blocked from the website', 'When a customer asks them for a recommendation, they cannot suggest you.'],
        'Няма карта на сайта' => ['There is no sitemap', 'Google finds your pages more slowly and may miss some of them.'],
        'Няма данни за бизнеса' => ['Google has no structured data about the business', 'Your address, opening hours and services are not recognised automatically, which lowers your visibility in local results and on Google Maps.'],
        'Не е настроено как изглежда при споделяне' => ['The website does not look good when shared', 'When someone sends the link on Facebook or WhatsApp, it appears without a picture or description.'],
        'Няма телефон за обаждане' => ['The phone number on the website cannot be tapped to call', 'Visitors on a phone have to copy the number, and many do not bother.'],
        'Снимки без описание' => ['Images have no descriptions', 'Google cannot tell what they show, so they do not help in search.'],
    ];

    /**
     * Находка на английски като [заглавие, въздействие], или null за непозната (неизвестна находка не се показва в английско писмо,
     * защото заглавието ѝ е на български).
     *
     * @return array{0: string, 1: string}|null
     */
    public static function findingTextEn(array $finding): ?array
    {
        foreach (self::FINDING_TEXTS_EN as $prefix => [$title, $impact]) {
            if (str_starts_with((string) ($finding['title'] ?? ''), $prefix)) {
                $seconds = preg_match('/\(([\d.,]+) s\)/u', $finding['title'], $m) ? str_replace(',', '.', $m[1]) : '';
                $title = $seconds === '' ? str_replace('starts loading after :s seconds', 'starts loading slowly', $title) : str_replace(':s', $seconds, $title);

                return [$title, $impact];
            }
        }

        return null;
    }

    /** Същото готово наблюдение за първото писмо, на английски. Без колони и тирета в текста; редовете със „– “ стават списък в писмото. */
    private function observationEn(): string
    {
        $host = $this->website ? preg_replace('/^www\./', '', (string) parse_url($this->website, PHP_URL_HOST)) : null;
        if (! $host) {
            return '';
        }
        $name = $this->company ?: $host;

        if (empty($this->findings)) {
            if ($this->findings === null) {
                return '';
            }
            $query = Markets::query('gb', (string) $this->industry);
            $search = $query && $this->city ? 'search Google for "' . $query . ' ' . $this->city . '"' : 'search Google for the service you offer together with your town';

            return "We ran {$name} ({$host}) through 14 technical checks that affect whether customers find a business and stay on its website, and it is in good shape. That is not common, and it is a solid base.\n"
                . "The next question is whether customers actually reach it. A simple test is to {$search} and see where you appear. The top three results and the map listings get most of the calls.";
        }

        $lines = [];
        foreach ($this->findings as $finding) {
            if ($text = self::findingTextEn($finding)) {
                $lines[] = '– ' . $text[0] . '. ' . $text[1];
            }
        }
        if ($lines === []) {
            return '';
        }

        $where = $this->city ? "While looking at local businesses in {$this->city} we checked the website of {$name} ({$host})" : "We checked the website of {$name} ({$host})";

        return $where . ' against 14 technical points that affect whether customers find you and whether they stay on the site. Here is what matters most.' . "\n" . implode("\n", $lines);
    }
}
