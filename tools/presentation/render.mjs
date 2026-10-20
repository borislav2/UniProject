// Записва презентацията на Creatium Lab като PDF в public/files (български и английски).
//   php artisan serve --port=8123   (в друг терминал, с изградени стилове: npm run build)
//   NODE_PATH=$(npm root -g) node tools/presentation/render.mjs [http://127.0.0.1:8123]
// Иска Playwright (npm i -g playwright) и Chromium (CHROMIUM_PATH, ако не е стандартният).
// Пуснете го отново, когато се сменят текстовете, пакетите или цените, и качете новите PDF-и.
import { createRequire } from 'node:module';
import { join, resolve } from 'node:path';

const { chromium } = createRequire(import.meta.url)('playwright');
const root = resolve(import.meta.dirname, '../..');
const base = (process.argv[2] || 'http://127.0.0.1:8123').replace(/\/$/, '');

const files = [
    ['/prezentatsiya', 'creatium-lab-prezentatsiya.pdf'],
    ['/en/presentation', 'creatium-lab-presentation.pdf'],
];

const browser = await chromium.launch({ executablePath: process.env.CHROMIUM_PATH });
const page = await browser.newPage({ viewport: { width: 1600, height: 900 } });
for (const [path, file] of files) {
    await page.goto(base + path, { waitUntil: 'networkidle' });
    await page.evaluate(() => document.fonts.ready);
    await page.pdf({ path: join(root, 'public/files', file), width: '1600px', height: '900px', printBackground: true, preferCSSPageSize: true });
    console.log('pdf', file);
}
await browser.close();
