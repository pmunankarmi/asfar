/* Interactions only. All visible content is rendered by PHP. */
(function () {
 'use strict';
 var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
 var rtl = document.documentElement.dir === 'rtl';
 var form = document.getElementById('contactForm');
 if (form) {
  if (crypto.randomUUID) form.elements.request_key.value = crypto.randomUUID();
  form.addEventListener('submit', async function (event) {
   event.preventDefault();
   var note = form.querySelector('.footer__form-note');
   var button = form.querySelector('[type="submit"]');
   if (button.disabled) return;
   if (!form.checkValidity()) { note.textContent = asfarSettings.invalid; form.reportValidity(); return; }
   button.disabled = true;
   try {
    var response = await fetch(asfarSettings.endpoint, { method: 'POST', body: new FormData(form), credentials: 'same-origin' });
    var result = await response.json();
    note.textContent = result.data && result.data.message || asfarSettings.error;
    if (result.success) { form.reset(); form.elements.request_key.value = crypto.randomUUID(); }
   } catch (error) { note.textContent = asfarSettings.error; }
   finally { button.disabled = false; }
  });
 }
 var team = document.getElementById('dkTeam');
 if (team) {
  var tabs = [...team.querySelectorAll('.dk-tab')];
  var groups = [...team.querySelectorAll('[data-team-group]')];
  var rail = team.querySelector('.dk-team__rail'), shift = 0;
  function grid() { return team.querySelector('[data-team-group]:not([hidden]) .dk-team__grid'); }
  function paint() { var g = grid(); if (!g) return; shift = Math.max(0, Math.min(shift, g.scrollWidth - g.parentElement.clientWidth)); g.style.transform = 'translateX(' + (rtl ? shift : -shift) + 'px)'; }
  tabs.forEach(function (tab) { tab.addEventListener('click', function () {
   tabs.forEach(function (t) { t.classList.toggle('is-active', t === tab); t.setAttribute('aria-selected', t === tab ? 'true' : 'false'); });
   rail.classList.add('is-out');
   setTimeout(function () { groups.forEach(function (g) { g.hidden = g.dataset.teamGroup !== tab.dataset.group; }); shift = 0; paint(); rail.classList.remove('is-out'); }, reduced ? 0 : 600);
  }); });
  team.querySelectorAll('[data-dir]').forEach(function (button) { button.addEventListener('click', function () { var g = grid(); if (!g || !g.children.length) return; shift += Number(button.dataset.dir) * (g.children[0].clientWidth + (parseFloat(getComputedStyle(g).columnGap) || 0)); paint(); }); });
  addEventListener('resize', paint);
 }
 var map = document.getElementById('dkMap');
 if (map) {
  var panes = [...map.querySelectorAll('.asfar-portfolio-pane')], backgrounds = [...map.querySelectorAll('.dk-map__bg')], buttons = [...map.querySelectorAll('.dk-map__dashes button')];
  var current = 0, timer, swap, pinned = false, finished = false, lastStep = 0;
  var canPin = !reduced && matchMedia('(hover: hover) and (pointer: fine)').matches;
  function place() {
   var pane = panes[current], svg = map.querySelector('svg'), pin = map.querySelector('.dk-map__pin'), label = map.querySelector('.dk-map__pinlab');
   if (!pane || !svg) return;
   var x = Number(pane.dataset.x), y = Number(pane.dataset.y), vb = svg.viewBox.baseVal, box = svg.getBoundingClientRect();
   var scale = Math.min(box.width / vb.width, box.height / vb.height);
   pin.hidden = label.hidden = !x && !y;
   pin.style.left = ((box.width - vb.width * scale) / 2 + x * scale) + 'px';
   pin.style.top = ((box.height - vb.height * scale) / 2 + y * scale) + 'px';
   label.style.left = (parseFloat(pin.style.left) - 16) + 'px'; label.style.top = pin.style.top; label.textContent = pane.dataset.label;
   map.dataset.hot = pane.dataset.hot;
   svg.querySelectorAll('[data-region]').forEach(function (region) { region.classList.toggle('is-lit', pane.dataset.hot === 'all' || region.dataset.region === pane.dataset.hot); });
  }
  function go(i) {
   if (!panes.length) return;
   current = (i + panes.length) % panes.length;
   clearTimeout(swap); map.classList.add('is-swapping');
   backgrounds.forEach(function (b, n) { b.classList.toggle('is-active', n === current); });
   buttons.forEach(function (b, n) { b.classList.toggle('is-active', n === current); b.setAttribute('aria-selected', n === current ? 'true' : 'false'); });
   swap = setTimeout(function () { panes.forEach(function (p, n) { p.hidden = n !== current; }); place(); map.classList.remove('is-swapping'); }, reduced ? 0 : 1000);
  }
  function pause() { clearInterval(timer); }
  buttons.forEach(function (b, n) { b.addEventListener('click', function () { pause(); go(n); }); });
  map.querySelectorAll('.invmap__region[data-region]').forEach(function (region) {
   var index = panes.findIndex(function (pane) { return pane.dataset.hot === region.dataset.region; });
   if (index < 0) return;
   region.classList.add('dk-clickable'); region.setAttribute('role', 'button'); region.setAttribute('tabindex', '0');
   region.setAttribute('aria-label', panes[index].querySelector('h3').textContent.trim());
   function select() { pause(); go(index); }
   region.addEventListener('click', select);
   region.addEventListener('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); event.stopPropagation(); select(); } });
  });
  function release() {
   if (!pinned) return;
   pinned = false; finished = true;
   document.documentElement.classList.remove('dk-maplock');
   if (window.__lenis) window.__lenis.start();
  }
  function step(direction) {
   if (Date.now() - lastStep < 1400) return;
   var next = current + direction;
   if (next < 0 || next >= panes.length) { release(); return; }
   lastStep = Date.now(); go(next);
  }
  if (canPin && panes.length > 1) {
   addEventListener('scroll', function () {
    if (pinned || finished || document.querySelector('.menu.is-open')) return;
    var box = map.getBoundingClientRect(), nav = document.querySelector('.nav');
    if (box.height > innerHeight || box.top > (nav ? nav.offsetHeight : 0) + 10 || box.bottom < innerHeight * .55) return;
    pinned = true; pause(); go(0); lastStep = 0;
    var target = scrollY + box.top - Math.max(0, (innerHeight - box.height) / 2);
    if (window.__lenis) { window.__lenis.scrollTo(target, {immediate:true,force:true}); window.__lenis.stop(); }
    else scrollTo(0, target);
    document.documentElement.classList.add('dk-maplock');
   }, {passive:true});
   addEventListener('wheel', function (event) { if (pinned && Math.abs(event.deltaY) > 0) { event.preventDefault(); step(event.deltaY > 0 ? 1 : -1); } }, {passive:false});
   addEventListener('keydown', function (event) {
    if (!pinned) return;
    if (['Escape','Tab','Home','End'].includes(event.key)) { release(); return; }
    if (['INPUT','TEXTAREA','SELECT','BUTTON','A'].includes(event.target.tagName)) return;
    if (['ArrowDown','PageDown',' '].includes(event.key)) { event.preventDefault(); step(1); }
    else if (['ArrowUp','PageUp'].includes(event.key)) { event.preventDefault(); step(-1); }
   });
   addEventListener('resize', release);
   addEventListener('hashchange', release);
   document.addEventListener('click', function (event) { if (event.target.closest('a, #burger')) release(); });
  }
  if (panes.length) {
   place(); addEventListener('resize', place);
   if (!reduced && 'IntersectionObserver' in window) new IntersectionObserver(function (entries) { clearInterval(timer); if (entries[0].isIntersecting && !pinned && !canPin) timer = setInterval(function () { go(current + 1); }, 8000); }).observe(map);
  }
 }
})();
