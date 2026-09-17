const {chromium}=require('/Users/puskar/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');const assert=require('node:assert/strict');
(async()=>{const browser=await chromium.launch({headless:true,executablePath:'/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});const page=await browser.newPage({viewport:{width:1440,height:1000}});const errors=[];page.on('pageerror',e=>errors.push(e.message));
for(const lang of ['en','ar']){
 await page.goto('http://127.0.0.1:8878/'+lang+'.html',{waitUntil:'networkidle'});
 assert.equal(await page.locator('.mt-hero__dot').count(),2);
 await page.locator('.mt-hero__dot').first().click();assert.equal(await page.locator('#heroSlider').evaluate(e=>e.classList.contains('mt-is-film')),false);
 await page.locator('.mt-hero__dot').nth(1).click();await page.waitForFunction(()=>document.querySelector('#heroSlider').classList.contains('mt-is-film'));
 assert.equal(await page.locator('.mt-hero__dot').nth(1).getAttribute('aria-pressed'),'true');
 await page.evaluate(()=>window.scrollTo(0,0));await page.waitForTimeout(2400);
 await page.screenshot({path:'/private/tmp/amv4-'+lang+'-hero.png'});
 await page.locator('.mt-hero__dot').first().click();
 assert.equal(await page.locator('.mt-hero__dot').first().getAttribute('aria-pressed'),'true');
 assert.equal(await page.locator('#dkMap').evaluate(e=>e.classList.contains('mt-dk-map--hint')),true);
 assert.equal(await page.locator('.mt-dk-clickable').count(),3);
 await page.emulateMedia({reducedMotion:'reduce'});await page.reload({waitUntil:'networkidle'});
 await page.locator('.mt-dk-clickable[data-region="asir"]').click();await page.waitForTimeout(100);assert.equal(await page.locator('#dkMap').getAttribute('data-hot'),'asir');
 await page.locator('.mt-dk-clickable[data-region="yanbu"]').focus();await page.keyboard.press('Enter');await page.waitForTimeout(1200);
 assert.equal(await page.locator('#dkMap').getAttribute('data-hot'),'yanbu');
 assert.equal(await page.locator('#dkMap').evaluate(e=>e.classList.contains('mt-dk-map--hint')),false);
 assert.equal(await page.locator('.mt-dk-clickable').first().evaluate(e=>getComputedStyle(e).pointerEvents),'fill');
 await page.emulateMedia({reducedMotion:'no-preference'});
 console.log('PASS '+lang+' two banner modes, map hint/selection and pointer targeting');
}
await page.setViewportSize({width:390,height:844});await page.emulateMedia({reducedMotion:'reduce'});await page.goto('http://127.0.0.1:8878/ar.html',{waitUntil:'networkidle'});await page.locator('.mt-hero__dot').nth(1).click();assert.equal(await page.locator('.mt-hero__dot').nth(1).getAttribute('aria-pressed'),'true');assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth));await page.waitForTimeout(1000);assert.equal(await page.locator('.mt-hero__slide.mt-is-active').evaluate(e=>getComputedStyle(e).opacity),'1');await page.screenshot({path:'/private/tmp/amv4-ar-mobile.png'});console.log('PASS mobile reduced motion navigation and page width');assert.deepEqual(errors,[]);await browser.close();})().catch(e=>{console.error(e);process.exit(1)});
