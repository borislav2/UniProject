// Рисува кориците на статиите от cover.html в public/images/blog/{slug}.png (после се конвертират в WebP).
//   node tools/blog-covers/render.mjs [slug ...]
// Иска Playwright (npm i -g playwright, NODE_PATH=$(npm root -g)) и Chromium. Пуска локален сървър, защото cover.html зарежда icons.json и шрифта.
import { createServer } from 'node:http';
import { readFile } from 'node:fs/promises';
import { extname, join, resolve } from 'node:path';
import { createRequire } from 'node:module';

// Глобално инсталиран Playwright се намира през NODE_PATH=$(npm root -g).
const { chromium } = createRequire(import.meta.url)('playwright');

const root = resolve(import.meta.dirname, '../..');
const types = { '.html': 'text/html', '.json': 'application/json', '.woff2': 'font/woff2' };
const server = createServer(async (req, res) => {
    try {
        const path = join(root, decodeURIComponent(new URL(req.url, 'http://x').pathname));
        if (!path.startsWith(root)) throw new Error('outside');
        const body = await readFile(path);
        res.writeHead(200, { 'Content-Type': types[extname(path)] ?? 'application/octet-stream' });
        res.end(body);
    } catch {
        res.writeHead(404).end();
    }
}).listen(0);
const base = `http://127.0.0.1:${server.address().port}/tools/blog-covers/cover.html`;

const browser = await chromium.launch({ executablePath: process.env.CHROMIUM_PATH });
const page = await browser.newPage({ viewport: { width: 1200, height: 675 } });
await page.goto(base + '?slug=_');
await page.waitForFunction(() => window.SLUGS);
const slugs = process.argv.slice(2).length ? process.argv.slice(2) : await page.evaluate(() => window.SLUGS);
for (const slug of slugs) {
    await page.goto(`${base}?slug=${slug}`);
    await page.waitForSelector('body[data-ready]');
    await page.screenshot({ path: join(root, 'public/images/blog', `${slug}.png`) });
    console.log('cover', slug);
}
await browser.close();
server.close();
