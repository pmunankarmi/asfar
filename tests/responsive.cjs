/** Run after amv4-preview.py; ASFAR_TEST_URL points to its local preview server. */
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || '/Users/puskar/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({ headless: true, executablePath: process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome' });
 try {
  const page = await browser.newPage();
  await page.addInitScript(() => sessionStorage.setItem('asfar:intro', '1'));
  page.on('pageerror', error => console.error('Browser:', error.message));
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
     assert.ok(await page.locator('.mt-dk-vision__motif').evaluate(e => getComputedStyle(e).display === 'none'), 'Mobile Vision decoration overlaps copy');
     const layout = await page.evaluate(() => {
      const box = selector => document.querySelector(selector).getBoundingClientRect();
      const pagination = box('.mt-dk-map__dashes');
      const panel = box('.mt-dk-map__panel');
      const wave = box('#dkMap > .mt-dk-wave--tight');
      const grid = document.querySelector('.mt-dk-team__grid');
      const cards = [...grid.children].slice(0, 2).map(e => e.getBoundingClientRect());
      const member = grid.firstElementChild;
      return {
       clear: pagination.top >= panel.bottom && pagination.bottom <= wave.top,
       columns: cards.length === 2 && Math.abs(cards[0].top - cards[1].top) < 2 && Math.abs(cards[0].left - cards[1].left) > 20,
       captionBelow: member.querySelector('.mt-dk-mem__txt').getBoundingClientRect().top >= member.querySelector('.mt-dk-mem__ph').getBoundingClientRect().bottom,
       newsPadding: parseFloat(getComputedStyle(document.querySelector('.mt-dk-news')).paddingBottom),
       teamPadding: parseFloat(getComputedStyle(document.querySelector('.mt-dk-team')).paddingTop)
      };
     });
     assert.ok(layout.clear, `${lang} ${width}: pagination must clear panel and wave`);
     assert.ok(layout.columns && layout.captionBelow, `${lang} ${width}: two team columns with captions below portraits`);
     assert.ok(layout.newsPadding >= 64 && layout.teamPadding >= 56, 'News/Team spacing');
     const proportions = await page.evaluate(() => {
      const dimensions = selector => { const r = document.querySelector(selector).getBoundingClientRect(); return {width:r.width,height:r.height,ratio:r.width/r.height}; };
      return {
       featured: dimensions('.mt-dk-feat__ph'), featuredCard:dimensions('.mt-dk-feat'),
       member: dimensions('.mt-dk-mem__ph'), sector:dimensions('.mt-dk-sector'),
       news: dimensions('.mt-dk-newscard'), newsPhoto:dimensions('.mt-dk-newscard__ph'),
       featuredCaption:document.querySelector('.mt-dk-feat__txt').getBoundingClientRect().top >= document.querySelector('.mt-dk-feat__ph').getBoundingClientRect().bottom,
       about:parseFloat(getComputedStyle(document.querySelector('.mt-dk-about__card p')).fontSize),
       newsTitle:parseFloat(getComputedStyle(document.querySelector('.mt-dk-newscard__title')).fontSize)
      };
     });
     assert.ok(Math.abs(proportions.featured.width-proportions.featuredCard.width)<2, 'Featured portrait fills mobile card');
     assert.ok(proportions.featured.width>proportions.member.width*1.8 && proportions.featuredCaption,'Featured portrait is larger with caption below');
     for(const [key,ratio] of [['featured',186/231.05],['member',114/113],['sector',307/394.1],['news',311/313.1],['newsPhoto',311/270.1]]) {
      assert.ok(Math.abs(proportions[key].ratio-ratio)<.015, `${lang} ${width}: ${key} retains desktop proportions`);
     }
     assert.ok(proportions.about>=16 && proportions.newsTitle>=14,'Readable mobile About and News text');

     const tabs = page.locator('.mt-dk-map__dashes button');
     await tabs.nth(1).scrollIntoViewIfNeeded();
     await page.waitForTimeout(100);
     await tabs.nth(1).click();
     await page.waitForFunction(() => document.querySelectorAll('.mt-dk-map__dashes button')[1].getAttribute('aria-selected') === 'true');
     await page.waitForFunction(() => !document.querySelectorAll('.mt-asfar-portfolio-pane')[1].hidden);
     assert.equal(await tabs.nth(1).evaluate(e => getComputedStyle(e).backgroundClip), 'content-box', 'Active pagination keeps its dash shape');
     if (width === 390 && lang === 'ar') {
      for (const [selector, name] of [['.mt-dk-vision-band', 'vision'], ['#dkMap', 'portfolio'], ['#dkTeam', 'team'], ['#about', 'about'], ['#dkNews', 'news'], ['#dkSectors', 'sectors'], ['#impact', 'impact'], ['#faq', 'faq'], ['#contact', 'footer']]) {
       await page.locator(selector + ':visible').screenshot({ path: `/private/tmp/asfar-responsive-${name}.png` });
      }
     }
    }
   }
   console.log(`PASS English/Arabic responsive layout at ${width}px`);
  }
 } finally { await browser.close(); }
})().catch(error => { console.error(error); process.exit(1); });
