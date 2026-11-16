/** Run amv4-preview.py, then serve the repository with PHP at ASFAR_PHP_URL. */
const {chromium}=require(process.env.PLAYWRIGHT_MODULE || '/Users/puskar/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const assert=require('node:assert/strict');
const path=require('node:path');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:process.env.CHROME_PATH || '/Applications/Google Chrome.app/Contents/MacOS/Google Chrome'});
 try {
  const context=await browser.newContext({viewport:{width:390,height:844},hasTouch:true,isMobile:true,reducedMotion:'reduce'});
  const page=await context.newPage();const errors=[];page.on('pageerror',e=>errors.push(e.message));
  const base=process.env.ASFAR_PHP_URL || 'http://127.0.0.1:8879';
  await page.route('**/team-*.html',route=>route.fulfill({path:path.join(process.env.ASFAR_PREVIEW_DIR || '/private/tmp/amv4-preview',new URL(route.request().url()).pathname.split('/').pop())}));
  await page.route('**/theme/**',route=>route.continue({url:route.request().url().replace('/theme/','/asfar/')}));
  await page.route('**/media/img/**',route=>route.continue({url:route.request().url().replace('/media/img/','/media/asfar-media/img/')}));
  for(const width of [320,390,600,768,960,1440])for(const lang of ['en','ar']){
   await page.setViewportSize({width,height:844});await page.goto(base+'/team-'+lang+'.html',{waitUntil:'load'});
   assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Team page overflow');
   assert.ok(await page.locator('.mt-teampage img').evaluateAll(images=>images.every(img=>img.complete&&img.naturalWidth>0)), 'Supplied team portraits load');
   if(width<=960){
    const groups=await page.locator('.mt-teampage__grid').evaluateAll(grids=>grids.map(g=>{
     const cards=[...g.children], a=cards[0].getBoundingClientRect(), b=cards[1].getBoundingClientRect();
     return {columns:Math.abs(a.top-b.top)<2&&Math.abs(a.left-b.left)>20,cards:cards.map(c=>{const p=c.querySelector('.mt-member__ph').getBoundingClientRect(),t=c.querySelector('.mt-member__txt').getBoundingClientRect();return {ratio:p.width/p.height,full:Math.abs(p.width-c.getBoundingClientRect().width)<2,below:t.top>=p.bottom}})};
    }));
    for(const g of groups){assert.ok(g.columns,'Two columns in every team group');for(const c of g.cards)assert.ok(c.full&&c.below&&Math.abs(c.ratio-114/113)<.015,'Homepage portrait proportions/captions');}
   }
   if(width===390)await page.screenshot({path:'/private/tmp/asfar-1.4.29-team-'+lang+'.png',fullPage:true});
  }
  console.log('PASS native Team page: both languages, six widths, every group, portrait proportions and captions');
  const cdp=await context.newCDPSession(page);
  for(const dir of ['ltr','rtl'])for(const width of [390,768,1440])for(const count of [0,1,2,9,11]){
   await page.setViewportSize({width,height:844});await page.goto(base+'/tests/partners-preview.php?dir='+dir+'&count='+count,{waitUntil:'load'});
   assert.equal(await page.locator('.mt-partner-slide').count(),count);
   assert.ok(await page.evaluate(()=>document.documentElement.scrollWidth<=innerWidth),'Partners page overflow');
   if(count>1){
    const slider=page.locator('[data-partner-slider]');await page.waitForFunction(()=>document.querySelector('[data-partner-slider]').swiper?.initialized);
    const per=width>=1024?5:width>=640?3:2;
    assert.equal(await slider.evaluate(e=>e.swiper.params.slidesPerView),per);
    if(count>per){
     await page.locator('.mt-partner-next').click();assert.equal(await slider.evaluate(e=>e.swiper.activeIndex),1,'Next arrow advances one logo');
     await page.locator('.mt-partner-prev').click();assert.equal(await slider.evaluate(e=>e.swiper.activeIndex),0);
     if(width===390&&count===9){
      await slider.scrollIntoViewIfNeeded();const b=await slider.boundingBox();const x=dir==='rtl'?b.x+40:b.x+b.width-40,y=b.y+b.height/2;
      await cdp.send('Input.dispatchTouchEvent',{type:'touchStart',touchPoints:[{x,y}]});
      for(let i=1;i<=10;i++){await cdp.send('Input.dispatchTouchEvent',{type:'touchMove',touchPoints:[{x:x+(dir==='rtl'?160:-160)*i/10,y}]});await page.waitForTimeout(25);}
      await cdp.send('Input.dispatchTouchEvent',{type:'touchEnd',touchPoints:[]});
      await page.waitForTimeout(200);assert.ok(await slider.evaluate(e=>e.swiper.activeIndex)>0,'Real finger swipe');
      await page.screenshot({path:'/private/tmp/asfar-1.4.29-partners-'+dir+'.png'});
     }
    }
   }else assert.equal(await page.locator('[data-partner-slider]').count(),0,'Zero/one logo fallback');
  }
  assert.deepEqual(errors,[]);console.log('PASS Partners: 0/1/2/9/11 logos, LTR/RTL, 2/3/5 visible logos, arrows and real touch input');
 }finally{await browser.close();}
})().catch(e=>{console.error(e);process.exit(1)});
