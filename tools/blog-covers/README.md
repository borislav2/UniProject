# Корици на статиите

Две ръчно рисувани от HTML серии корици. `cover.html` (стар дизайн) има 28 сцени, `cover-g.html` има същите 28 плюс 21 нови сцени, по една на всяка ежедневна статия от `database/data/daily-2026-11-{a,b,c}.php` (23 до 29.11.2026). Старите сцени не се пипат.

| Дизайн | Шаблон | Рисуване | Резултат |
|---|---|---|---|
| Текущ (стар) | `cover.html` | `node tools/blog-covers/render.mjs [slug ...]` | `public/images/blog/{slug}.webp` (1200x675), PNG се превръща в WebP |
| G | `cover-g.html` | `render-g.mjs` + `render-g.php` | `public/images/blog/g/{slug}.webp` (1200x675) и `{slug}-640.webp` (640x360, за картите) |

Старите файлове не се пипат: сайтът в текущия дизайн остава байт по байт същият.

## Дизайн G

`cover-g.html` ползва същите сцени като `cover.html` (същата идея на всяка корица), но през G: светла повърхност (#ECEEF0, карти #F7F8F9), мастило #0C0E12, фини рамки, остри ъгли, един оранжев акцент (#E8431B), без сенки, светлини и преливки. Шрифтове Geologica (заглавия, лек), Onest (текст), JetBrains Mono (малки етикети), от `node_modules/@fontsource`.
`recolor()` превежда старите цветове към G, `finish()` маха заоблените ъгли (кръговете остават) и слага Geologica на големите числа и заглавия. Текстовете следват правилото на собствениците (без тирета и двоеточия, освен часове и адреси). Изключение е кодът в `gotov-li-e-saytat-vi-za-ai-asistentite`, който показва истински `robots.txt`.

```bash
npm ci
PLAYWRIGHT_PATH=/opt/node22/lib/node_modules/playwright/index.mjs \
  node tools/blog-covers/render-g.mjs -o /tmp/covers-g [slug ...]   # PNG в папката (без slug: всички)
php tools/blog-covers/render-g.php /tmp/covers-g 88                 # WebP, 1200 и 640, в public/images/blog/g
```

Без `PLAYWRIGHT_PATH` се ползва глобалният `playwright` (`NODE_PATH=$(npm root -g)`). Новa корица: добавете сцена в `scenes` на двата шаблона със същия slug (името на файла в `images/blog`).

## Как G избира корицата

`g_cover($post, $small = false)` в `app/helpers.php`: ако `cover_image` е `images/blog/<име>.webp` и съществува `public/images/blog/g/<име>.webp`, връща него (за карти 640 версията, със srcset през `g_cover_srcset()`), иначе оригиналния път. Качените корици (`uploads/blog/...`) не се променят. Ползва се само от `resources/views/g/blog/_card.blade.php` и `show.blade.php`; BlogPosting JSON-LD пази оригиналната корица. Тест: `tests/Feature/BlogCoversGTest.php`.

## Ежедневните статии (21 нови сцени)

Сцените на седмица 1 са само в `cover-g.html` (блоковете в края на `scenes`, след коментара „Ежедневни статии“; помощниците `gk`, `gc`, `gl`, `gline`, `gav` са преди `scenes`). Всяка е различна и показва идеята на статията: разбивка на цената, входяща поща с тема на писмо, графика от Search Console, календар на резервните копия, карта с няколко обекта и т.н. Само икони от `icons.json`. Текстовете без тирета и двоеточия (освен часове и адреси) и без измислена статистика.

**Всяка нова ежедневна статия трябва да има сцена и корица**, иначе блогът я показва без снимка. Стъпките за нова седмица са в `tools/blog/BRIEF.md` (раздел „Covers“): сцена, рисуване, `public/images/blog/{slug}.webp` (копие на 1200 версията, това е `cover_image`), `'cover'` във файла с данните и миграция с `StarterBlogPosts::fillCovers()` за статиите, които вече са на живия сайт (пълни само празна корица). Тест: `DailyBlogPostsTest`.
