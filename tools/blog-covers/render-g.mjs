// Рисува кориците на статиите в дизайн G от cover-g.html в PNG, после render-g.php ги превръща в WebP (виж README.md).
//   node tools/blog-covers/render-g.mjs [-o папка] [slug ...]
// Playwright се зарежда през пътя в PLAYWRIGHT_PATH (напр. /opt/node22/lib/node_modules/playwright/index.mjs) или, без него,
// през NODE_PATH=$(npm root -g). Пуска локален сървър, защото cover-g.html зарежда icons.json и шрифтовете от node_modules.
import { createServer } from 'node:http';
import { readFile, mkdir } from 'node:fs/promises';
import { extname, join, resolve } from 'node:path';
import { createRequire } from 'node:module';

const { chromium } = process.env.PLAYWRIGHT_PATH
    ? await import(process.env.PLAYWRIGHT_PATH)
    : createRequire(import.meta.url)('playwright');

const args = process.argv.slice(2);
let out = join(import.meta.dirname, '.out-g');
if (args[0] === '-o') { out = resolve(args[1]); args.splice(0, 2); }
await mkdir(out, { recursive: true });

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
const base = `http://127.0.0.1:${server.address().port}/tools/blog-covers/cover-g.html`;

const browser = await chromium.launch({ executablePath: process.env.CHROMIUM_PATH });
const page = await browser.newPage({ viewport: { width: 1200, height: 675 } });
await page.goto(base + '?slug=_');
await page.waitForFunction(() => window.SLUGS);
const slugs = args.length ? args : await page.evaluate(() => window.SLUGS);
for (const slug of slugs) {
    await page.goto(`${base}?slug=${slug}`);
    await page.waitForSelector('body[data-ready]');
    await page.screenshot({ path: join(out, `${slug}.png`) });
    console.log('cover', slug);
}
await browser.close();
server.close();
