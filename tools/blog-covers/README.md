# Корици на статиите

Две ръчно рисувани от HTML серии корици, един и същ списък от 28 сцени (по една на статия).

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
