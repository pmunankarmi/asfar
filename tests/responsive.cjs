/** Run after amv4-preview.py; ASFAR_TEST_URL points to its local preview server. */
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || '/Users/puskar/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({ headless: true, executablePath: process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' });
 try {
  const page = await browser.newPage();
  await page.emulateMedia({ reducedMotion: 'reduce' });
  const base = process.env.ASFAR_TEST_URL || 'http://127.0.0.1:8878/';
  for (const width of [320, 390, 600, 768, 960, 1024, 1440]) {
   await page.setViewportSize({ width, height: 900 });
   for (const lang of ['en', 'ar']) {
    await page.goto(new URL(lang + '.html', base).href, { waitUntil: 'load' });
    await page.evaluate(() => document.fonts.ready);
    assert.ok(await page.evaluate(() => document.documentElement.scrollWidth <= innerWidth), `${lang} ${width}: page overflow`);
    if (width <= 960) {
     for (const [parent, child] of [['.mt-dk-about__row', '.mt-dk-about__card'], ['.mt-dk-vision__row', '.mt-dk-vision'], ['.mt-dk-vision', '.mt-dk-vision__tan']]) {
      const sizes = await page.evaluate(([parent, child]) => [document.querySelector(parent).getBoundingClientRect().width, document.querySelector(child).getBoundingClientRect().width], [parent, child]);
      assert.ok(Math.abs(sizes[0] - sizes[1]) < 2, `${lang} ${width}: ${child} must fill its row`);
     }
     const buttons = await page.locator('.mt-hero__cta--fixed .mt-btn').evaluateAll(elements => elements.map(e => { const r = e.getBoundingClientRect(); return { left: r.left, right: r.right, height: r.height }; }));
     assert.ok(buttons.length, 'Hero actions missing');
     for (const r of buttons) assert.ok(r.left >= 17 && r.right <= width - 17 && r.height >= 48, `${lang} ${width}: hero action margins/touch height`);
    }
   }
   console.log(`PASS English/Arabic responsive layout at ${width}px`);
  }
 } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exit(1); });
