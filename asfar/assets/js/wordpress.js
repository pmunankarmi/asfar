/* Interactions only. All visible content is rendered by PHP. */
(function () {
 'use strict';
 // Preserve bookmarks created while section IDs used an mt- prefix.
 function resolveLegacyAnchor() {
  var hash = window.location.hash.slice(1);
  if (hash.indexOf('mt-') !== 0) return;
  var target = document.getElementById(hash.slice(3));
  if (target) {
   history.replaceState(null, '', '#' + hash.slice(3));
   target.scrollIntoView();
  }
 }
 window.addEventListener('load', resolveLegacyAnchor);
 window.addEventListener('hashchange', resolveLegacyAnchor);
 // Leave a real gap below the film caption instead of using a fixed percentage.
 var hero = document.querySelector('.mt-hero');
 var captionTitle = document.getElementById('heroMorphName');
 var heroContent = hero && hero.querySelector('.mt-hero__in');
 if (hero && captionTitle && heroContent) {
  function positionHeroActions() {
   var gap = Math.max(28, Math.min(44, window.innerHeight * 0.05));
   var top = captionTitle.getBoundingClientRect().bottom - heroContent.getBoundingClientRect().top + gap;
   hero.style.setProperty('--hero-actions-top', top + 'px');
  }
  positionHeroActions();
  window.addEventListener('resize', positionHeroActions);
  if ('ResizeObserver' in window) {
   var captionObserver = new ResizeObserver(positionHeroActions);
   captionObserver.observe(captionTitle);
   captionObserver.observe(heroContent);
  }
  if (document.fonts) document.fonts.ready.then(positionHeroActions);
 }
 var reduced = matchMedia('(prefers-reduced-motion: reduce)').matches;
 var rtl = document.documentElement.dir === 'rtl';
 var form = document.getElementById("contactForm");
 if (form) {
  if (crypto.randomUUID) form.elements.request_key.value = crypto.randomUUID();
  form.addEventListener('submit', async function (event) {
   event.preventDefault();
   var note = form.querySelector(".mt-footer__form-note");
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
 var team = document.getElementById("dkTeam");
 if (team) {
  var tabs = [...team.querySelectorAll(".mt-dk-tab")];
  var groups = [...team.querySelectorAll('[data-team-group]')];
  var rail = team.querySelector(".mt-dk-team__rail"), shift = 0;
  function grid() { return team.querySelector("[data-team-group]:not([hidden]) .mt-dk-team__grid"); }
  function paint() { var g = grid(); if (!g) return; shift = Math.max(0, Math.min(shift, g.scrollWidth - g.parentElement.clientWidth)); g.style.transform = 'translateX(' + (rtl ? shift : -shift) + 'px)'; }
  tabs.forEach(function (tab) { tab.addEventListener('click', function () {
   tabs.forEach(function (t) { t.classList.toggle("mt-is-active", t === tab); t.setAttribute('aria-selected', t === tab ? 'true' : 'false'); });
   rail.classList.add("mt-is-out");
   setTimeout(function () { groups.forEach(function (g) { g.hidden = g.dataset.teamGroup !== tab.dataset.group; }); shift = 0; paint(); rail.classList.remove("mt-is-out"); }, reduced ? 0 : 600);
  }); });
  team.querySelectorAll('[data-dir]').forEach(function (button) { button.addEventListener('click', function () { var g = grid(); if (!g || !g.children.length) return; shift += Number(button.dataset.dir) * (g.children[0].clientWidth + (parseFloat(getComputedStyle(g).columnGap) || 0)); paint(); }); });
  addEventListener('resize', paint);
 }
 var map = document.getElementById("dkMap");
 if (map) {
  var panes = [...map.querySelectorAll(".mt-asfar-portfolio-pane")], backgrounds = [...map.querySelectorAll(".mt-dk-map__bg")], buttons = [...map.querySelectorAll(".mt-dk-map__dashes button")];
  var hinting = true;
  map.classList.add("mt-dk-map--hint");
  var current = 0, timer, swap, visible = false;
  function place() {
   var pane = panes[current], svg = map.querySelector('.mt-dk-map__stage svg'), pin = map.querySelector(".mt-dk-map__pin"), label = map.querySelector(".mt-dk-map__pinlab");
   if (!pane || !svg) return;
   var x = Number(pane.dataset.x), y = Number(pane.dataset.y), vb = svg.viewBox.baseVal, box = svg.getBoundingClientRect();
   var scale = Math.min(box.width / vb.width, box.height / vb.height);
   pin.hidden = label.hidden = !x && !y;
   pin.style.left = ((box.width - vb.width * scale) / 2 + x * scale) + 'px';
   pin.style.top = ((box.height - vb.height * scale) / 2 + y * scale) + 'px';
   label.style.left = (parseFloat(pin.style.left) - 16) + 'px'; label.style.top = pin.style.top; label.textContent = pane.dataset.label;
   map.dataset.hot = pane.dataset.hot;
   svg.querySelectorAll('[data-region]').forEach(function (region) { region.classList.toggle("mt-is-lit", !hinting && (pane.dataset.hot === 'all' || region.dataset.region === pane.dataset.hot)); });
  }
  function go(i) {
   if (!panes.length) return;
   i = (i + panes.length) % panes.length;
   if (i === current && !hinting) return;
   hinting = false; map.classList.remove("mt-dk-map--hint");
   current = (i + panes.length) % panes.length;
   place();
   clearTimeout(swap); map.classList.add("mt-is-swapping");
   backgrounds.forEach(function (b, n) { b.classList.toggle("mt-is-active", n === current); });
   buttons.forEach(function (b, n) { b.classList.toggle("mt-is-active", n === current); b.setAttribute('aria-selected', n === current ? 'true' : 'false'); });
   swap = setTimeout(function () { panes.forEach(function (p, n) { p.hidden = n !== current; }); place(); map.classList.remove("mt-is-swapping"); }, reduced ? 0 : 1000);
  }
  function pause() { clearInterval(timer); }
  function rearm() {
   pause();
   if (reduced || hinting || !visible || document.hidden || panes.length < 2) return;
   timer = setInterval(function () { go(current + 1); }, 8000);
  }
  buttons.forEach(function (b, n) { b.addEventListener('click', function () { go(n); rearm(); }); });
  map.querySelectorAll(".mt-invmap__region[data-region]").forEach(function (region) {
   var index = panes.findIndex(function (pane) { return pane.dataset.hot === region.dataset.region; });
   if (index < 0) return;
   region.classList.add("mt-dk-clickable"); region.setAttribute('role', 'button'); region.setAttribute('tabindex', '0');
   region.setAttribute('aria-label', panes[index].querySelector('h3').textContent.trim());
   function select() { go(index); rearm(); }
   region.addEventListener('click', select);
   region.addEventListener('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); event.stopPropagation(); select(); } });
  });
  if (panes.length) {
   place(); addEventListener('resize', place);
   if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
     visible = entries[0].isIntersecting && entries[0].intersectionRatio >= .35;
     if (visible && hinting) go(current);
     rearm();
    }, { threshold: .35 }).observe(map);
   }
   document.addEventListener('visibilitychange', rearm);
   var DK = window.__dk = window.__dk || {};
   DK.mapGo = go;
   DK.mapCommit = function () { go(current); };
   DK.mapIdx = function () { return current; };
   DK.mapCount = function () { return panes.length; };
   DK.mapPause = pause;
  }
 }
})();

// Map slides never capture page scrolling; autoplay and region buttons control the map.
