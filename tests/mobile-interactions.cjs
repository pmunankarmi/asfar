/** Native touch input, arrow synchronization, and native PHP team tab fixture. */
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || '/Users/puskar/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert = require('node:assert/strict');
(async () => {
 const browser = await chromium.launch({headless:true, executablePath:process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 try {
  const context = await browser.newContext({viewport:{width:390,height:844},isMobile:true,hasTouch:true,reducedMotion:'reduce'});
  const page = await context.newPage();
  await page.addInitScript(()=>sessionStorage.setItem('asfar:intro','1'));
  const errors=[];page.on('pageerror',e=>{errors.push(e.message);console.error('Browser:',e.message)});
  const cdp=await context.newCDPSession(page);
  async function swipe(x,y,dx,dy) {
   await cdp.send('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[{x,y}]});
   for(let i=1;i<=12;i++) {
    await cdp.send('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x:x+dx*i/12,y:y+dy*i/12}]});
    await page.waitForTimeout(25);
   }
   await cdp.send('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});
   await page.waitForTimeout(600);
  }
  for(const motion of ['reduce','no-preference']) for(const lang of ['en','ar']) {
   await page.emulateMedia({reducedMotion:motion});
   await page.goto((process.env.ASFAR_TEST_URL || 'http://127.0.0.1:8878/')+lang+'.html',{waitUntil:'load'});
   await page.waitForFunction(()=>window.__dk && window.__dk.newsGo && document.querySelector('#dkTeam > .mt-dk-wrap > .mt-dk-tabs'));
   await page.waitForFunction(()=>document.querySelector('#loader').classList.contains('mt-is-out'));
   for(const [root,rail] of [['#dkSectors','.mt-dk-sectors__rail'],['#dkNews','.mt-dk-news__rail']]) {
    const el=page.locator(rail);await el.scrollIntoViewIfNeeded();
    let box=await el.boundingBox();const y=Math.min(700,Math.max(120,box.y+box.height/2));
    await swipe(lang==='ar'?90:300,y,lang==='ar'?200:-200,0);
    const offset=await el.evaluate(e=>Math.abs(e.scrollLeft));
    assert.ok(offset>40,lang+' '+rail+' native finger swipe');
    const prev=page.locator(root+' [data-dir="-1"]');assert.equal(await prev.isEnabled(),true);
    await prev.click();await page.waitForTimeout(200);
    assert.ok(await el.evaluate(e=>Math.abs(e.scrollLeft))<offset, 'Arrow follows finger position');
    await page.locator(root+' [data-dir="1"]').click();await page.waitForTimeout(200);
    assert.ok(await el.evaluate(e=>Math.abs(e.scrollLeft))>30,'Next arrow still works');
    await el.scrollIntoViewIfNeeded();box=await el.boundingBox();
    const before=await page.evaluate(()=>scrollY);
    await swipe(195,Math.min(650,Math.max(200,box.y+100)),0,-130);
    assert.ok(await page.evaluate(()=>scrollY)>before+20,'Vertical page scrolling across slider');
   }
   const team=page.locator('#dkTeam');await team.scrollIntoViewIfNeeded();
   await page.locator('[data-team-group]:not([hidden]) .mt-dk-mem').last().scrollIntoViewIfNeeded();
   const tabs=page.locator('#dkTeam > .mt-dk-wrap > .mt-dk-tabs');
   assert.ok((await tabs.boundingBox()).y>=0 && (await tabs.boundingBox()).y<180,'Tabs remain near viewport top');
   await tabs.locator('button').nth(1).click();
   await page.waitForFunction(()=>!document.querySelectorAll('[data-team-group]')[1].hidden);
   await page.waitForFunction(()=>{
    const photo=document.querySelector('[data-team-group]:not([hidden]) .mt-dk-feat__ph').getBoundingClientRect();
    const tabs=document.querySelector('#dkTeam > .mt-dk-wrap > .mt-dk-tabs').getBoundingClientRect();
    return photo.top>=tabs.bottom-2 && photo.top<450;
   });
   const portrait=await page.locator('[data-team-group]:not([hidden]) .mt-dk-feat__ph').boundingBox();
   assert.ok(portrait.y>=(await tabs.boundingBox()).y+(await tabs.boundingBox()).height-2 && portrait.y<450,'Switch returns to first member');
   await page.screenshot({path:'/private/tmp/asfar-1.4.27-team-'+lang+'.png'});
   await page.setViewportSize({width:1440,height:1000});
   await page.waitForFunction(()=>document.querySelector('.mt-dk-team__foot > .mt-dk-tabs'));
   assert.equal(await page.locator('.mt-dk-news__rail').evaluate(e=>Math.abs(e.scrollLeft)),0,'Desktop rail scroll reset');
   if(motion==='reduce') for(const [rail,track] of [['.mt-dk-sectors__rail','.mt-dk-sectors__track'],['.mt-dk-news__rail','.mt-dk-news__strip']]) {
    await page.evaluate(()=>{window.__dk.secGo(0);window.__dk.newsGo(0)});
    await page.locator(rail).scrollIntoViewIfNeeded();
    const b=await page.locator(rail).boundingBox();
    const x=lang==='ar'?b.x+b.width-100:b.x+200,y=b.y+b.height/2;
    await page.mouse.move(x,y);await page.mouse.down();await page.mouse.move(x+(lang==='ar'?180:-180),y,{steps:12});await page.mouse.up();
    assert.ok(await page.locator(track).evaluate(e=>Math.abs(new DOMMatrix(getComputedStyle(e).transform).m41))>30,'Desktop mouse dragging retained');
   }
   await page.setViewportSize({width:390,height:844});
   console.log('PASS '+lang+' '+motion+': real touch swipes, vertical scroll, arrows, sticky team tabs and group return');
  }
  assert.deepEqual(errors,[]);
 } finally { await browser.close(); }
})().catch(e=>{console.error(e);process.exit(1)});
