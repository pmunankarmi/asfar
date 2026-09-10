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
  var current = 0, timer, swap;
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
   current = (i + panes.length) % panes.length;
   clearTimeout(swap); map.classList.add('is-swapping');
   backgrounds.forEach(function (b, n) { b.classList.toggle('is-active', n === current); });
   buttons.forEach(function (b, n) { b.classList.toggle('is-active', n === current); b.setAttribute('aria-selected', n === current ? 'true' : 'false'); });
   swap = setTimeout(function () { panes.forEach(function (p, n) { p.hidden = n !== current; }); place(); map.classList.remove('is-swapping'); }, reduced ? 0 : 1000);
  }
  buttons.forEach(function (b, n) { b.addEventListener('click', function () { go(n); }); });
  if (panes.length) {
   place(); addEventListener('resize', place);
   if (!reduced && 'IntersectionObserver' in window) new IntersectionObserver(function (entries) { clearInterval(timer); if (entries[0].isIntersecting) timer = setInterval(function () { go(current + 1); }, 8000); }).observe(map);
  }
 }
})();
