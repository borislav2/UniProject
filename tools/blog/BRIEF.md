# Brief for the daily blog articles of creatiumlab.com (Creatium Lab)

Creatium Lab is a team of three in Bulgaria (Борислав, Владимир, Мария). It builds websites for small Bulgarian businesses and looks after visibility in Google, Google Maps and AI assistants (ChatGPT, Gemini), ads, email campaigns, support and hosting. The blog readers are owners of small Bulgarian businesses: a dental clinic, an accountant, a car service, a guest house, a small online shop. They are not technical people.

## Output
Write ONE PHP data file at the exact path you are given. Format:
```php
<?php

// Daily blog articles (owners, 22.11.2026: three a day). Imported once by migration 2026_11_22_000001 with their own publish_at (Sofia time).

return [
    'categories' => [
        'sajtove' => ['bg' => ['Сайтове', 'sajtove'], 'en' => ['Websites', 'websites']],
        'seo' => ['bg' => ['Google и AI търсене', 'google-i-ai-tarsene'], 'en' => ['Google and AI search', 'google-and-ai-search']],
        'imeyl' => ['bg' => ['Имейл кампании', 'imeyl-kampanii'], 'en' => ['Email campaigns', 'email-campaigns']],
        'poddrazhka' => ['bg' => ['Поддръжка и сигурност', 'poddrazhka-i-sigurnost'], 'en' => ['Maintenance and security', 'maintenance-and-security']],
        'ecommerce' => ['bg' => ['Онлайн търговия', 'onlayn-targoviya'], 'en' => ['E-commerce', 'e-commerce']],
    ],
    'posts' => [
        [
            'category' => 'seo',
            'title' => '...',
            'slug' => '...',          // Latin transliteration, lowercase, hyphens, max ~60 chars
            'publish_at' => '2026-11-23 09:00',
            'excerpt' => '...',       // 120 to 155 characters, two full sentences at most, used as the meta description
            'body' => <<<'MD'
...markdown...
MD,
        ],
    ],
];
```
Before writing, open database/data/blog-posts.php in /home/user/UniProject and check the real category names for `imeyl` and `poddrazhka`, then copy those exactly. Check that the file loads with `php -r 'var_dump(count((require "PATH")["posts"]));'` and with `php -l`. Do NOT touch any other file and do NOT run git.

## The owners' writing rules. These are the most important part.
1. **It must not read like AI.** Write like an experienced Bulgarian practitioner talking to a business owner over coffee. Use natural, varied sentence length and concrete everyday examples, such as a hair salon in Пловдив, an accountant in Варна, a car service in Бургас or a guest house in Банско. Present them as examples ("например"), never as our clients.
2. **Only full, complete sentences.** No fragmented text. No telegraphic lines. No bullet lists of fragments. Prefer flowing paragraphs. A short numbered list is allowed at most once per article, and only when every item is a complete sentence.
3. **Never use a dash or hyphen as punctuation** (– — or " - "), **never a colon**, never "=", never an arrow, never an emoji. This applies to the title, the excerpt, the headings and the body. Hyphens inside words or slugs are fine. Quotes „…“ are fine.
4. **Banned AI clichés.** Do not use: "В днешния дигитален свят", "Нека разгледаме", "В заключение", "Не е тайна, че", "ключът към успеха", "революционен", "незаменим", "гмурнем", "пътешествие", "в крайна сметка" at the start of a paragraph, rhetorical openings with a question followed by "Отговорът е...", or groups of exactly three adjectives.
5. **Invent nothing.** No statistics, studies, percentages, client stories, reviews or results. If you mention a fact about Google or another tool, it must be stable public knowledge (for example, that Google Search Console is free). Never promise rankings or results. Do not mention prices of our packages.
6. **Headings.** 3 to 6 `##` headings per article. Each heading is a natural phrase or a full sentence without a colon or dash. You may use `###` rarely.
7. **Length.** 700 to 1100 words of body text. The body starts directly with a strong first paragraph; do not repeat the title.
8. **Internal links.**
   - Exactly 2 or 3 per article, in markdown with relative URLs, woven naturally into sentences.
   - Allowed targets: /proverka-na-sait (a free website check), /uslugi/izrabotka-na-sait, /uslugi/seo-i-ai-vidimost, /uslugi/google-profil-i-otzivi, /uslugi/imeyl-marketing, /uslugi/poddrazhka-i-hosting, /uslugi/podgotovka-za-google-shopping, /uslugi/ai-agenti, /paketi, /kontakti.
   - Do NOT link to other blog posts.
9. **Ending.** End with one short, calm paragraph that suggests a next step, often the free website check at /proverka-na-sait. No hard sell. Never call a consultation free; only the website check is free.
10. **Language.** Bulgarian, standard and correct, polite plural "вие". Bold is allowed sparingly, a few words at most per article.
11. **Titles.** 45 to 65 characters. The title starts with the phrase people would type into Google. No colon, no dash, no question mark spam.

After writing, re-read every article and fix any violation: search your file for "–", "—", " - ", ":" (outside the PHP syntax and URLs) and the banned phrases.
