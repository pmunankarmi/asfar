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

/* Match the reference map scroll walkthrough. */
(function () {
  var root = document.getElementById('dkMap');
  var DK = window.__dk;
  if (!root || !DK || !DK.mapGo) return;
  if (window.matchMedia && matchMedia('(prefers-reduced-motion: reduce)').matches) return;
  var html = document.documentElement;
  function lenis() { return window.__lenis || null; }
  function navH() { var n = document.querySelector('.mt-nav'); return n ? n.offsetHeight : 0; }

  var N = (DK.mapCount && DK.mapCount()) || 4;
  var armed = false, done = false, lastStep = 0;
  var seen = {};
  function idx() { return DK.mapIdx ? DK.mapIdx() : 0; }
  function markSeen() { seen[idx()] = true; }
  function allSeen() { for (var i = 0; i < N; i++) if (!seen[i]) return false; return true; }

  function engage() {
    if (armed || done) return;
    armed = true;
    if (DK.mapPause) DK.mapPause();
    if (DK.mapCommit) DK.mapCommit();   // pinning the map commits the hint state and lights the first area
    markSeen();
    var vh = window.innerHeight, r = root.getBoundingClientRect();
    var docTop = r.top + (window.scrollY || 0);
    var target = Math.max(0, docTop - Math.max(0, (vh - r.height) / 2));
    var l = lenis();
    if (l && l.scrollTo) l.scrollTo(target, { immediate: true, force: true });
    else window.scrollTo(0, target);
    if (l && l.stop) l.stop();
    html.classList.add('mt-dk-maplock');
  }
  function release(dir) {
    if (!armed) return;
    armed = false;
    var l = lenis();
    if (l && l.start) l.start();
    html.classList.remove('mt-dk-maplock');
    if (dir > 0) done = true;                 // finished going down: never re-lock
  }
  function step(dir) {
    var now = Date.now();
    if (now - lastStep < 1400) return;        // one region per gesture; long enough for the region morph to finish and be read
    var cur = idx(), nx = cur + dir;
    if (nx < 0) { release(-1); return; }        // up past the first -> let the page rise
    if (nx > N - 1) { if (allSeen()) { release(1); } return; }  // down past last (all seen) -> continue
    lastStep = now;
    DK.mapGo(nx);
    markSeen();
  }

  // engage when the section's top reaches the nav line while scrolling down
  function check() {
    if (armed || done) return;
    var r = root.getBoundingClientRect();
    if (r.top <= navH() + 10 && r.bottom > window.innerHeight * 0.55) engage();
  }
  window.addEventListener('scroll', check, { passive: true });

  window.addEventListener('wheel', function (e) {
    if (!armed) return;
    e.preventDefault();
    step(e.deltaY > 0 ? 1 : -1);
  }, { passive: false });

  window.addEventListener('keydown', function (e) {
    if (!armed) return;
    if (e.key === 'ArrowDown' || e.key === 'PageDown' || e.key === ' ' || e.key === 'Spacebar') { e.preventDefault(); step(1); }
    else if (e.key === 'ArrowUp' || e.key === 'PageUp') { e.preventDefault(); step(-1); }
  }, { passive: false });

  var ty = 0;
  window.addEventListener('touchstart', function (e) { if (armed) ty = e.touches[0].clientY; }, { passive: true });
  window.addEventListener('touchmove', function (e) {
    if (!armed) return;
    e.preventDefault();
    var dy = ty - e.touches[0].clientY;
    if (Math.abs(dy) > 26) { step(dy > 0 ? 1 : -1); ty = e.touches[0].clientY; }
  }, { passive: false });

  // clicking a region / dash while locked also counts it as seen
  root.addEventListener('click', function () { if (armed) markSeen(); });
})();
