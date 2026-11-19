# Share image (Open Graph and Twitter card)

The picture social networks and messengers show when a page of creatiumlab.com is shared. Design G: light surface `#ECEEF0`,
ink `#0C0E12`, one orange accent `#E8431B`, the G logo (`public/images/logo-g.png`), the headline of the home hero in Geologica light,
a hairline frame and the contour line motif of the hero (the same noise field as `resources/js/g-home.js`, frozen, drawn as SVG).
No claims and no numbers.

| File | Language | Used on |
| --- | --- | --- |
| `public/images/og-g.png` | Bulgarian | every page without its own share image (root URLs) |
| `public/images/og-g-en.png` | English | every page under `/en` |

Both are 1200 x 630 and under 300 KB. `App\Support\Schema::shareImage()` picks the file; `layouts/public` writes `og:image`, `og:image:width/height/type/alt`
and `twitter:image(:alt)`; the Organization JSON-LD `image` and the blog posts without a cover use it too. A post or SEO page with its own
image (admin) keeps it. The Organization `logo` is `images/logo-g.png` in design G.

Images are cached for a year and social networks cache by URL: when the picture changes, give the file a new name
(`og-g-2.png`) and change `Schema::shareImage()`, then ask Facebook (Sharing Debugger) and LinkedIn (Post Inspector) to refresh.

## Rebuild

```bash
node tools/og/render.mjs
```

Needs Playwright (the import path at the top of `render.mjs`) and the fonts in `resources/fonts/g/`. `template.html` draws one image from the query string
(`title`, `accent`, `lang`); `render.mjs` holds the two headlines and writes the PNG files. The headlines mirror `home_hero.title` and
`home_hero.accent` in `config/creatium.php` and `config/creatium_en.php`: change them together (`tests/Feature/GNoOldDesignTest.php` fails when they drift).
The old `public/images/og-image.png` is not referenced any more.
