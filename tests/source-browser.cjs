const {chromium}=require(process.env.PLAYWRIGHT_MODULE || '/Users/puskar/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert=require('node:assert/strict'); const urls=require(process.env.ASFAR_TEST_URLS || '/private/tmp/asfar-urls.json');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 const page=await browser.newPage({viewport:{width:1440,height:1000},reducedMotion:'reduce'}); const errors=[];page.on('pageerror',e=>errors.push(e.message));
 for(const key of ['index','index-ar']){
  await page.goto(urls[key],{waitUntil:'networkidle'});
  assert.equal(await page.locator(".mt-hero__slide").count(),3);assert.equal(await page.locator(".mt-hero__dot").count(),4);
  await page.locator(".mt-hero__dot").nth(2).click(); await page.waitForTimeout(50);
  assert.equal(await page.locator(".mt-hero__dot").nth(2).getAttribute('aria-pressed'),'true');
  assert.equal(await page.locator(".mt-hero__cta--fixed button").isVisible(),true);
  assert.equal(await page.locator(".mt-dk-clickable").count(),3);
  await page.locator(".mt-dk-clickable[data-region=\"yanbu\"]").focus();await page.keyboard.press('Enter'); await page.waitForTimeout(50);
  assert.equal(await page.locator("#dkMap").getAttribute('data-hot'),'yanbu');
  assert.equal(await page.locator(".mt-asfar-portfolio-pane:not([hidden])").count(),1);
  const mask=await page.locator(".mt-dk-about__pif").evaluate(e=>getComputedStyle(e).maskImage);assert.ok(mask.includes('/uploads/'));
  await page.locator("#about").screenshot({path:'/private/tmp/asfar-latest-about-'+key+'.png'});
  console.log('PASS',key,'banner buttons, navigation, keyboard map selection, uploaded PIF artwork');
 }
 await page.goto('http://127.0.0.1:8877/',{waitUntil:'networkidle'});assert.equal(await page.locator('html').getAttribute('lang'),'ar');console.log('PASS Arabic homepage is default');
 await page.emulateMedia({reducedMotion:'no-preference'});
 await page.goto(urls.index,{waitUntil:'networkidle'});await page.locator(".mt-hero__dot").nth(3).click();await page.waitForTimeout(2400);
 assert.ok((await page.locator("#heroSlider").getAttribute('class')).includes("mt-is-film"));
 assert.equal(await page.locator("#heroMorphName").textContent(),'The Coast');
 await page.screenshot({path:'/private/tmp/asfar-latest-film.png'});
 await page.evaluate(()=>{const m=document.querySelector("#dkMap");window.__lenis?.scrollTo(m.offsetTop-50,{immediate:true,force:true});if(!window.__lenis)window.scrollTo(0,m.offsetTop-50);});await page.waitForTimeout(1600);
 const pinned=await page.locator('html').evaluate(e=>e.classList.contains("mt-dk-maplock"));
 console.log('Map pinned',pinned); assert.equal(pinned,true); if(pinned){await page.keyboard.press('Escape');assert.equal(await page.locator('html').evaluate(e=>e.classList.contains("mt-dk-maplock")),false);}
 assert.deepEqual(errors,[]);console.log('PASS animated destination selection, escape from portfolio scroll, no JS errors');await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
