# Brief for the daily blog articles of creatiumlab.com (Creatium Lab)

Creatium Lab is a team of three in Bulgaria (Борислав, Владимир, Мария). It builds websites for small Bulgarian businesses and looks after visibility in Google, Google Maps and AI assistants (ChatGPT, Gemini), ads, email campaigns, support and hosting. The blog readers are owners of small Bulgarian businesses: a dental clinic, an accountant, a car service, a guest house, a small online shop. They are not technical people.

## Output
Write ONE PHP data file at the exact path you are given, and also one English text file per article (see "Every article exists in Bulgarian AND English" below). Format of the data file:
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
        'reklama' => ['bg' => ['Платена реклама', 'platena-reklama'], 'en' => ['Paid ads', 'paid-ads']],
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
5. **Invent nothing.** No statistics, studies, percentages, client stories, reviews or results. If you mention a fact about Google or another tool, it must be stable public knowledge (for example, that Google Search Console is free). Never promise rankings or results. Do not mention prices of our packages. The one exception is the article „Колко струва сайт за малък бизнес през 2026 година“, which carries our real prices (owners, 25.11.2026).
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

## Every article exists in Bulgarian AND English (owners, 25.11.2026)

Every new article is written in BOTH languages, and the English version is part of the same delivery, never a later task. The English text is written for small business owners the way an experienced practitioner would explain it, not as a literal translation. Bulgarian examples (Plovdiv, Varna, Burgas, Bansko) stay, because the readers are still Bulgarian businesses.

Each post needs four English fields next to the Bulgarian ones, so the data file posts get `title_en`, `slug_en`, `excerpt_en` and `body_en`. The English text of a post sits in `database/data/en/{Bulgarian slug}.md` and `StarterBlogPosts::data()` merges it into the post as those four fields (a data file may also carry the four keys itself). The file has this shape.

```
English title
english-slug
English excerpt
---
English body in Markdown
```

The first three lines are the title, the slug and the excerpt, then a line with three dashes, then the body.

- **Title** 45 to 65 characters, starting with the phrase people would type into Google. **Slug** Latin, lowercase, natural English words, unique across all posts. **Excerpt** 120 to 155 characters.
- **Body** at least about 500 words, 3 to 6 `##` headings, full sentences. The same rules as the Bulgarian text apply: no dash or hyphen as punctuation, no colon (except clock times and URLs), no "=", no arrows or emoji, no bullet lists of fragments (a short numbered list of full sentences is allowed once), no invented statistics, clients or results, and no free consultation (only the website check is free). Write ranges as "10 to 15", never "10-15". An example email subject goes into a sentence ("The subject line reads ..."), not after a colon.
- **Sources.** Numbers that the Bulgarian article attributes to a named source keep the same source and the same figure. External https links stay as in the Bulgarian article.
- **Internal links** point to the English pages only and are the same ones as in the Bulgarian text. Allowed targets are /en/website-check, /en/services/website-design, /en/services/seo-and-ai-visibility, /en/services/google-business-profile, /en/services/email-marketing, /en/services/maintenance-and-hosting, /en/services/google-shopping-readiness, /en/services/ai-agents, /en/packages, /en/contact and /en/projects. A link to an earlier article is `/en/blog/{its slug_en}`. The industry page /izrabotka-na-sait-za-malak-biznes has no English version, so the English text links to /en/services/website-design instead.
- The cover is shared: `cover_image` is one field for both languages (see Covers below).

`EnglishBlogPostsTest` fails when a post has no English, when the shape or the copy rules are broken, or when a link is not allowed. For posts that are already live, ship a migration that calls `StarterBlogPosts::fillEnglish('<file>')` (see `2026_11_25_000002_add_english_to_blog_posts.php`, skipped in tests). It fills English only where the English title is empty and never changes what the owners wrote.

## Covers

Every article, in both languages, needs a cover. Every new daily article needs a cover, or the blog shows it without a picture. For each new batch of articles (details in `tools/blog-covers/README.md`):
1. **Scene.** In `tools/blog-covers/cover-g.html` add one hand drawn scene per new slug (the key is the slug). Design G: light surface, ink, thin frames, one orange accent, simple UI mock-ups and icons that show the article's own idea (a price breakdown, an inbox with a subject line, a Search Console graph, a map with pins). Every scene is different. Visible text follows the owners' rule: no dashes, no colons except clock times and URLs, no invented statistics, short labels only. Do not touch existing scenes. Use only icons that exist in `icons.json`.
2. **Render.** `PLAYWRIGHT_PATH=/opt/node22/lib/node_modules/playwright/index.mjs node tools/blog-covers/render-g.mjs -o /tmp/covers <slugs>`, look at every PNG and fix clipping and overlaps, then `php tools/blog-covers/render-g.php /tmp/covers 88` (writes `public/images/blog/g/{slug}.webp` and `{slug}-640.webp`). Also copy the 1200 version to `public/images/blog/{slug}.webp`: the post's `cover_image` is that path, `g_cover()` maps it to the G file, and the share image and JSON-LD use the original.
3. **Data file.** Add `'cover' => 'images/blog/{slug}.webp'` to each post in `database/data/daily-*.php`. `DailyBlogPostsTest` fails when a daily post has no cover or the G files are missing.
4. **Live posts.** Posts already on the live site are skipped by `import()`, so ship a migration that calls `StarterBlogPosts::fillCovers('<file>')` for the new files (example `2026_11_24_000001_daily_blog_covers.php`, skipped in tests). It sets `cover_image` only where it is blank and never overwrites a cover the owners set.

## Content mix and news (Владимир, 23.11.2026)
We want to be a source of information for small businesses, not only how-to texts. Each week has about 8 news articles, 7 advice articles and 6 practical articles.
- **News** covers recent real developments: Google Search and core updates, AI Overviews and AI Mode, ChatGPT, Gemini and Perplexity search, GEO, Google Ads and Meta Ads changes, Google Business Profile, and e-commerce and business news that matter to a small Bulgarian business.
  - Research each news item on the web the same week. Use only sources from the last 2 to 3 weeks: official Google, OpenAI or Meta blogs, Search Engine Land, Search Engine Journal, or Bulgarian media.
  - Name the source in a sentence and link it with an external https link. External source links are allowed on top of the 2 or 3 internal links.
  - Never invent dates, numbers or quotes. Quote numbers only as the source states them, with the source named.
  - Always explain what the news means for a small Bulgarian business and what to do now.
- **Advice** covers SEO, GEO, paid ads (category `reklama`), websites and email.

## Търсения от Search Console (25.11.2026)

The owners' Search Console report (last 3 months, Bulgaria) shows what people really type. Use it to choose topics and keywords for the weekly articles, in this order of priority.

1. **Brand.** People search for Creatium Lab by name. Articles that explain who we are and how we work help them confirm they found the right company.
2. **Домейн.** „избор на домейн“ and „домейн .bg“ (position 24). The article exists, so write related ones (регистрация, подновяване, имейл с името на фирмата).
3. **Сайт за малък бизнес.** „сайт за малък бизнес“ (position 28). Link to `/izrabotka-na-sait-za-malak-biznes`.
4. **GEO и видимост в ChatGPT.** „какво е geo“, „видимост в chatgpt“, „seo и ai видимост“ (positions 21 to 22). Link to `/uslugi/seo-i-ai-vidimost`.
5. **Отзиви в Google.** How to ask for them, how to answer them, what to do with a bad one.
6. **Позициониране.** What the business is known for and why a customer should pick it.
7. **Имейл кампании.** Welcome, reminder and abandoned cart emails, in plain words.
8. **„Близо до мен“ на български.** Local searches like „зъболекар близо до мен“ and how the Google profile answers them.
9. **Скорост на сайта.** Why a slow site loses customers on a phone and how to check it.

Rules that follow from this:

- **New articles target Bulgarian searches.** Pick the phrase a Bulgarian customer would type, put it in the title, the first paragraph and one heading, and use it naturally in the text. English phrases only where Bulgarians really use them (GEO, ChatGPT, Google Business Profile).
- Seasonal topics (for example Black Friday) go out early, before the date, because Google needs time to index them.
- Every article links to at least one service page and, when it fits, to an earlier article.
- Do not invent numbers or results. The owners' copy rules above still apply (no dashes, no colons).
