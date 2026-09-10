const {chromium}=require('/Users/puskar/.cache/codex-runtimes/codex-primary-runtime/dependencies/node/node_modules/playwright');
const fs=require('fs');
(async()=>{
 const browser=await chromium.launch({headless:true,executablePath:"/Applications/Google Chrome.app/Contents/MacOS/Google Chrome"});
 const page=await browser.newPage({viewport:{width:1440,height:1000},reducedMotion:'reduce'});
 const errors=[];page.on('pageerror',e=>errors.push(e.message));
 await page.goto('http://127.0.0.1:8877/',{waitUntil:'networkidle'});
 console.log('EN title',await page.title());
 console.log('Visible headline',await page.locator('h1').first().textContent());
 console.log('News cards',await page.locator('.dk-newscard').count(),'Team',await page.locator('.member--opens').count());
 await page.locator('img').evaluateAll(es=>es.forEach(e=>e.loading='eager')); await page.waitForTimeout(1200); console.log('Broken loaded images',await page.locator('img').evaluateAll(es=>es.filter(e=>e.getAttribute('src')&&e.complete&&!e.naturalWidth).map(e=>e.src))); console.log('App loaded',await page.evaluate(()=>typeof window.__dk));
 await page.screenshot({path:'/private/tmp/asfar-en.png',fullPage:true}); await page.screenshot({path:'/private/tmp/asfar-en-hero.png'});
 const ar=await page.locator('.nav__lang a').getAttribute('href');
 await page.goto(ar,{waitUntil:'networkidle'});
 console.log('AR direction',await page.locator('html').getAttribute('dir'));
 console.log('AR headline',await page.locator('h1').first().textContent());
 await page.setViewportSize({width:390,height:844});
 await page.locator('img').evaluateAll(es=>es.forEach(e=>e.loading='eager')); await page.waitForTimeout(1000); await page.screenshot({path:'/private/tmp/asfar-ar-mobile.png',fullPage:true}); await page.screenshot({path:'/private/tmp/asfar-ar-hero.png'});
 console.log('Mobile widths',await page.evaluate(()=>[innerWidth,document.documentElement.scrollWidth]));

 const urls=JSON.parse(fs.readFileSync('/private/tmp/asfar-urls.json','utf8'));
 const failures=[];
 for(const [slug,url] of Object.entries(urls)){const r=await page.request.get(url);const html=await r.text();if(r.status()!==200||!html.includes('</html>')||html.includes('There has been a critical error'))failures.push([slug,r.status()]);}
 console.log('All 44 page smoke failures',failures);
 await page.goto(urls.team,{waitUntil:'networkidle'});
 await page.locator('.member__name').first().click();
 console.log('Team biography opens',await page.locator('.mexp').evaluate(e=>e.classList.contains('is-open')));
 await page.keyboard.press('Escape');
 console.log('Team biography closes',await page.locator('.mexp').evaluate(e=>!e.classList.contains('is-open')));
 await page.goto('http://127.0.0.1:8877/?p=999999');
 console.log('404 template',await page.locator('h1').textContent());
 console.log('JS errors',errors);
 await browser.close();
})().catch(e=>{console.error(e);process.exit(1)});
