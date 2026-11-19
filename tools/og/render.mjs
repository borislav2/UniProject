// Renders the design G share images (1200 x 630) to public/images/og-g.png (Bulgarian) and og-g-en.png (English).
// Run from the repository root: node tools/og/render.mjs   (needs Playwright, import path below).
// The headline texts mirror config home_hero.title and home_hero.accent (config/creatium.php and creatium_en.php);
// tests/Feature/GNoOldDesignTest.php fails when they drift apart.
import { chromium } from '/opt/node22/lib/node_modules/playwright/index.mjs';
import { readFileSync, writeFileSync, mkdtempSync, statSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { join, resolve } from 'node:path';
import { pathToFileURL } from 'node:url';

const root = resolve(new URL('../..', import.meta.url).pathname);
const VARIANTS = [
    { lang: 'bg', file: 'og-g.png', title: 'Дигитални решения, изградени с мисъл за вашия успех.', accent: 'успех' },
    { lang: 'en', file: 'og-g-en.png', title: 'Digital solutions built with your success in mind.', accent: 'success' },
];

const dir = mkdtempSync(join(tmpdir(), 'og-'));
const html = readFileSync(join(root, 'tools/og/template.html'), 'utf8').replaceAll('__ROOT__', pathToFileURL(root).href);
const page = join(dir, 'og.html');
writeFileSync(page, html);

const browser = await chromium.launch();
const ctx = await browser.newContext({ viewport: { width: 1200, height: 630 }, deviceScaleFactor: 1 });
for (const v of VARIANTS) {
    const p = await ctx.newPage();
    const url = pathToFileURL(page).href + '?' + new URLSearchParams({ title: v.title, accent: v.accent, lang: v.lang });
    await p.goto(url, { waitUntil: 'load' });
    await p.evaluate(() => document.fonts.ready);
    await p.waitForTimeout(300);
    const out = join(root, 'public/images', v.file);
    await p.screenshot({ path: out, type: 'png', clip: { x: 0, y: 0, width: 1200, height: 630 } });
    console.log(v.file, Math.round(statSync(out).size / 1024) + ' KB');
    await p.close();
}
await browser.close();
