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
 // Use the same title-to-button gap for the opening slide and film caption.
 var hero = document.querySelector('.mt-hero');
 var captionTitle = hero && hero.querySelector('.mt-hero__morph-lead');
 var heroContent = hero && hero.querySelector('.mt-hero__in');
 if (hero && heroContent) {
  var slideTitles = hero.querySelectorAll('.mt-hero__slide .mt-hero__title');
  function positionHeroActions() {
   var gap = Math.max(28, Math.min(44, window.innerHeight * 0.05));
   var origin = heroContent.getBoundingClientRect().top;
   var openingTitle = hero.querySelector('.mt-hero__slide.mt-is-active .mt-hero__title') || slideTitles[0];
   if (openingTitle) {
    hero.style.setProperty('--hero-opening-actions-top', (openingTitle.getBoundingClientRect().bottom - origin + gap) + 'px');
   }
   if (captionTitle) {
    hero.style.setProperty('--hero-actions-top', (captionTitle.getBoundingClientRect().bottom - origin + gap) + 'px');
   }
  }
  positionHeroActions();
  window.addEventListener('resize', positionHeroActions);
  hero.addEventListener('transitionend', positionHeroActions);
  if ('ResizeObserver' in window) {
   var captionObserver = new ResizeObserver(positionHeroActions);
   slideTitles.forEach(function (title) { captionObserver.observe(title); });
   if (captionTitle) captionObserver.observe(captionTitle);
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
   if (!form.checkValidity()) { note.innerHTML = asfarSettings.invalid; form.reportValidity(); return; }
   button.disabled = true;
   try {
    var response = await fetch(asfarSettings.endpoint, { method: 'POST', body: new FormData(form), credentials: 'same-origin' });
    var result = await response.json();
    note.innerHTML = result.data && result.data.message || asfarSettings.error;
    if (result.success) { form.reset(); form.elements.request_key.value = crypto.randomUUID(); }
   } catch (error) { note.innerHTML = asfarSettings.error; }
   finally { button.disabled = false; }
  });
 }
 var team = document.getElementById("dkTeam");
 if (team) {
  var tabs = [...team.querySelectorAll(".mt-dk-tab")];
  var groups = [...team.querySelectorAll('[data-team-group]')];
  var rail = team.querySelector(".mt-dk-team__rail"), shift = 0;
  var tablist = team.querySelector('.mt-dk-tabs'), stage = team.querySelector('.mt-dk-team__stage');
  var tabHome = document.createComment('Team tabs desktop position');
  if (tablist) tablist.before(tabHome);
  function mobileTeam() { return matchMedia('(max-width: 960px)').matches; }
  function positionTeamTabs() {
   if (!tablist || !stage) return;
   var nav = document.querySelector('.mt-nav');
   var top = nav ? nav.getBoundingClientRect().bottom : 0;
   team.style.setProperty('--team-tabs-top', Math.max(0, top) + 'px');
   if (mobileTeam()) stage.before(tablist);
   else tabHome.after(tablist);
  }
  function showTeamStart() {
   if (!mobileTeam() || !tablist) return;
   positionTeamTabs();
   var top = parseFloat(team.style.getPropertyValue('--team-tabs-top')) || 0;
   window.scrollTo({ top: Math.max(0, window.scrollY + stage.getBoundingClientRect().top - top - tablist.offsetHeight - 12), behavior: reduced ? 'instant' : 'smooth' });
  }
  positionTeamTabs();
  addEventListener('resize', positionTeamTabs);
  function grid() { return team.querySelector("[data-team-group]:not([hidden]) .mt-dk-team__grid"); }
  function paint() { var g = grid(); if (!g) return; shift = Math.max(0, Math.min(shift, g.scrollWidth - g.parentElement.clientWidth)); g.style.transform = 'translateX(' + (rtl ? shift : -shift) + 'px)'; }
  tabs.forEach(function (tab) { tab.addEventListener('click', function () {
   tabs.forEach(function (t) { t.classList.toggle("mt-is-active", t === tab); t.setAttribute('aria-selected', t === tab ? 'true' : 'false'); });
   rail.classList.add("mt-is-out");
   setTimeout(function () { groups.forEach(function (g) { g.hidden = g.dataset.teamGroup !== tab.dataset.group; }); shift = 0; paint(); rail.classList.remove("mt-is-out"); showTeamStart(); }, reduced ? 0 : 600);
  }); });
  team.querySelectorAll('[data-dir]').forEach(function (button) { button.addEventListener('click', function () { var g = grid(); if (!g || !g.children.length) return; shift += Number(button.dataset.dir) * (g.children[0].clientWidth + (parseFloat(getComputedStyle(g).columnGap) || 0)); paint(); }); });
  addEventListener('resize', paint);
 }
 var map = document.getElementById("dkMap");
 if (map) {
  var panes = [...map.querySelectorAll(".mt-asfar-portfolio-pane")], backgrounds = [...map.querySelectorAll(".mt-dk-map__bg")], buttons = [...map.querySelectorAll(".mt-dk-map__dashes button")];
  var hinting = false;
  var current = 0;
  // Keep all panes in the grid so changing projects never changes section height.
  function showPane(index) {
   panes.forEach(function (pane, n) {
    pane.hidden = false;
    pane.inert = n !== index;
    pane.setAttribute("aria-hidden", n === index ? "false" : "true");
    pane.classList.toggle("mt-is-active", n === index);
   });
  }
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
   svg.querySelectorAll('[data-region]').forEach(function (region) { region.classList.toggle("mt-is-lit", !hinting && ((pane.dataset.hot === 'all' && panes.some(function (p) { return p.dataset.hot === region.dataset.region; })) || region.dataset.region === pane.dataset.hot)); });
  }
  function go(i) {
   if (!panes.length) return;
   i = (i + panes.length) % panes.length;
   if (i === current && !hinting) return;
   hinting = false; map.classList.remove("mt-dk-map--hint");
   current = (i + panes.length) % panes.length;
   place();
   showPane(current);
   backgrounds.forEach(function (b, n) { b.classList.toggle("mt-is-active", n === current); });
   buttons.forEach(function (b, n) { b.classList.toggle("mt-is-active", n === current); b.setAttribute('aria-selected', n === current ? 'true' : 'false'); });

  }
  // Projects change only when the visitor selects a pagination button or region.
  buttons.forEach(function (b, n) { b.addEventListener('click', function () { go(n); }); });
  map.querySelectorAll(".mt-invmap__region[data-region]").forEach(function (region) {
   var index = panes.findIndex(function (pane) { return pane.dataset.hot === region.dataset.region; });
   if (index < 0) return;
   region.classList.add("mt-dk-clickable"); region.setAttribute('role', 'button'); region.setAttribute('tabindex', '0');
   region.setAttribute('aria-label', panes[index].querySelector('h3').textContent.trim());
   function select() { go(index); }
   region.addEventListener('click', select);
   region.addEventListener('keydown', function (event) { if (event.key === 'Enter' || event.key === ' ') { event.preventDefault(); event.stopPropagation(); select(); } });
  });
  if (panes.length) {
   map.classList.add("mt-map-ready");
   showPane(current);
   place(); addEventListener('resize', place);
  }
 }
})();
