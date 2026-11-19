// Renders the Kreo stills (webp with alpha) from the same three.js modules the site uses (resources/js/kreo/*).
//
//   node tools/kreo/render.mjs                         writes public/images/kreo/*.webp (light, and -dark for the dark theme)
//   node tools/kreo/render.mjs --preview <dir>         also writes PNG previews on the light and dark page colours
//   node tools/kreo/render.mjs --only story|team       renders one group
//
// Needs: npm install (three), php (the step titles are read through artisan tinker), Playwright with Chromium.
import { createServer } from 'node:http';
import { execFileSync } from 'node:child_process';
import { mkdirSync, readFileSync, statSync, writeFileSync } from 'node:fs';
import { dirname, extname, join, resolve } from 'node:path';
import { fileURLToPath } from 'node:url';

const root = resolve(dirname(fileURLToPath(import.meta.url)), '../..');
const args = process.argv.slice(2);
const opt = (name) => { const i = args.indexOf(name); return i >= 0 ? args[i + 1] : null; };
const outDir = resolve(root, opt('--out') || 'public/images/kreo');
const previewDir = opt('--preview') ? resolve(opt('--preview')) : null;
const only = opt('--only');
const MAX_BYTES = 66 * 1024;

const { chromium } = await import(process.env.PLAYWRIGHT_MODULE || 'playwright');

// Text of the whiteboard and the name plates comes from the config, so the images always say what the site says.
const php = `
$s = fn ($lang) => config($lang === 'en' ? 'creatium_en.service_pages' : 'creatium.service_pages');
$out = [];
foreach (['bg', 'en'] as $lang) {
    $p = $s($lang);
    $out[$lang] = [
        'titles' => array_column($p['website']['g_process'], 'title'),
        'labels' => array_slice(array_column($p['ai_agents']['g_vis']['v2']['modules'], 'name'), 0, 2) + [2 => $p['ai_agents']['g_vis']['v2']['modules'][3]['name']],
    ];
}
echo json_encode($out, JSON_UNESCAPED_UNICODE);`;
const texts = JSON.parse(execFileSync('php', ['artisan', 'tinker', '--execute=' + php], { cwd: root }).toString().trim().split('\n').pop());

const types = { '.html': 'text/html', '.js': 'text/javascript', '.mjs': 'text/javascript', '.woff2': 'font/woff2', '.webp': 'image/webp', '.png': 'image/png', '.json': 'application/json' };
const server = createServer((req, res) => {
    const file = join(root, decodeURIComponent(new URL(req.url, 'http://x').pathname));
    try {
        if (!file.startsWith(root) || !statSync(file).isFile()) throw new Error('no');
        res.writeHead(200, { 'content-type': types[extname(file)] || 'application/octet-stream' });
        res.end(readFileSync(file));
    } catch { res.writeHead(404); res.end(); }
}).listen(0);
const base = `http://127.0.0.1:${server.address().port}`;

const browser = await chromium.launch({ args: ['--use-gl=angle', '--use-angle=swiftshader', '--enable-unsafe-swiftshader', '--ignore-gpu-blocklist'] });

async function open(params, w, h) {
    const page = await browser.newPage({ viewport: { width: w, height: h } });
    page.on('pageerror', (e) => console.error('page error', e.message));
    page.on('console', (m) => m.type() === 'error' && console.error('console', m.text()));
    await page.goto(`${base}/tools/kreo/render.html?` + new URLSearchParams({ ...params, w, h }));
    await page.waitForFunction('window.ready === true', null, { timeout: 60000 });

    return page;
}

async function exportWebp(page, t) {
    for (const q of [0.88, 0.8, 0.72, 0.64, 0.56, 0.48, 0.4]) {
        const url = await page.evaluate(([tt, qq]) => window.exportImage(tt, qq), [t, q]);
        const buf = Buffer.from(url.split(',')[1], 'base64');
        if (buf.length <= MAX_BYTES) return buf;
    }
    throw new Error('image stays above ' + MAX_BYTES + ' bytes');
}

mkdirSync(outDir, { recursive: true });
const written = [];
const save = (name, buf) => { writeFileSync(join(outDir, name), buf); written.push(`${name} ${(buf.length / 1024).toFixed(1)} KB`); };

async function preview(params, w, h, t, name) {
    if (!previewDir) return;
    mkdirSync(previewDir, { recursive: true });
    for (const dark of [0, 1]) {
        const page = await open({ ...params, dark }, w, h);
        await page.evaluate(([tt, d]) => { document.body.style.background = d ? '#0B0C0F' : '#ECEEF0'; window.draw(tt); }, [t, dark]);
        await page.screenshot({ path: join(previewDir, `${name}-${dark ? 'dark' : 'light'}.png`) });
        await page.close();
    }
}

const site = '/public/images/demos/restorant.webp';
const sizes = [[960, 720], [480, 360]];

if (only !== 'team') {
    for (let scene = 0; scene < 4; scene++) {
        // The first scene has the plan written on the board, so it exists once per language; the others carry no text.
        for (const lang of scene === 0 ? ['bg', 'en'] : [null]) {
            const data = JSON.stringify({ titles: texts[lang || 'bg'].titles, site });
            for (const dark of [0, 1]) {
                for (const [w, h] of sizes) {
                    const page = await open({ kind: 'story', data, dark }, w, h);
                    save(`kreo-${scene + 1}${lang ? '-' + lang : ''}-${w}${dark ? '-dark' : ''}.webp`, await exportWebp(page, scene));
                    await page.close();
                }
            }
            await preview({ kind: 'story', data }, 960, 720, scene, `scene-${scene + 1}${lang ? '-' + lang : ''}`);
        }
    }
}

if (only !== 'story') {
    for (const lang of ['bg', 'en']) {
        const data = JSON.stringify({ labels: texts[lang].labels });
        for (const dark of [0, 1]) {
            for (const [w, h] of [[1200, 720], [600, 360]]) {
                const page = await open({ kind: 'team', data, dark }, w, h);
                save(`team-${lang}-${w}${dark ? '-dark' : ''}.webp`, await exportWebp(page, 0));
                await page.close();
            }
        }
        await preview({ kind: 'team', data }, 1200, 720, 0, `team-${lang}`);
    }
}

await browser.close();
server.close();
console.log(written.join('\n'));
