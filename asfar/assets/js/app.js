/* =====================================================================
   ASFAR — The Quiet Atlas — interactions
   Lenis smooth scroll · IO reveals · pinned Destinations atlas · counters
   ===================================================================== */
(function () {
  'use strict';
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var mqMobile = window.matchMedia('(max-width: 820px)');

  /* ---------- Lenis smooth scroll ---------- */
  var lenis = null;
  function initLenis() {
    if (reduced || typeof Lenis === 'undefined') return;
    lenis = new Lenis({ lerp: 0.085, wheelMultiplier: 1, smoothWheel: true });
    window.__lenis = lenis;
    function raf(t) { lenis.raf(t); requestAnimationFrame(raf); }
    requestAnimationFrame(raf);
  }
  function scrollToEl(target, offset) {
    offset = offset || 0;
    if (lenis) { lenis.scrollTo(target, { offset: offset, duration: 1.2 }); }
    else {
      var y = (typeof target === 'number') ? target
        : target.getBoundingClientRect().top + window.pageYOffset + offset;
      window.scrollTo({ top: y, behavior: reduced ? 'auto' : 'smooth' });
    }
  }

  /* ---------- in-page anchor links through Lenis ---------- */
  document.querySelectorAll('a[href^="#"]').forEach(function (a) {
    a.addEventListener('click', function (e) {
      var id = a.getAttribute('href');
      if (id.length < 2) { e.preventDefault(); return; }   // dead '#' — never jump to top
      var el = document.querySelector(id);
      if (!el) return;
      e.preventDefault();
      closeMenu();
      // land with the section title clear of the fixed nav bar
      var navH = nav ? nav.offsetHeight : 0;
      scrollToEl(el, el.id === 'top' ? 0 : -(navH + 14));
    });
  });

  /* ---------- scrollspy — current section marked in nav + overlay menu ---------- */
  (function () {
    var spyLinks = Array.prototype.slice.call(document.querySelectorAll('.nav__links a[href^="#"], .menu__links a[href^="#"]'));
    var ids = {};
    spyLinks.forEach(function (a) { ids[a.getAttribute('href').slice(1)] = 1; });
    var spySections = Object.keys(ids).map(function (id) { return document.getElementById(id); }).filter(Boolean);
    if (!spySections.length) return;
    function setSpy(id) {
      spyLinks.forEach(function (a) {
        var on = a.getAttribute('href') === '#' + id;
        a.classList.toggle('is-active', on);
        if (on) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
      });
    }
    var sio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) setSpy(en.target.id); });
    }, { rootMargin: '-38% 0px -56% 0px' });
    spySections.forEach(function (s) { sio.observe(s); });
  })();

  /* ---------- reveal-on-scroll ---------- */
  var io = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
    });
  }, { threshold: 0.18, rootMargin: '0px 0px -8% 0px' });
  document.querySelectorAll('.reveal, .reveal-img, .eyebrow, .geomap').forEach(function (el) { io.observe(el); });

  /* ---------- geo map: pin <-> list hover sync ---------- */
  (function () {
    var pins = Array.prototype.slice.call(document.querySelectorAll('.geopin'));
    var rows = Array.prototype.slice.call(document.querySelectorAll('#geoList li'));
    if (!pins.length) return;
    function hot(i, on) {
      var p = pins.find(function (x) { return x.getAttribute('data-i') === String(i); });
      var r = rows.find(function (x) { return x.getAttribute('data-i') === String(i); });
      if (p) p.classList.toggle('is-hot', on);
      if (r) r.classList.toggle('is-hot', on);
    }
    pins.concat(rows).forEach(function (el) {
      var i = el.getAttribute('data-i');
      el.addEventListener('mouseenter', function () { hot(i, true); });
      el.addEventListener('mouseleave', function () { hot(i, false); });
    });
  })();

  /* ---------- carousel — transform-driven track ----------
     Used by the news rail and the leadership rail. Deliberately NOT a
     scroll-snap container: with these cards the browser reported overflow
     (scrollWidth 3097 vs clientWidth 1308) but a scroll range of [0,0], so
     nothing could move it. Translating the track works in both writing
     directions and lets pointer events supply the swipe.
     Any element with [data-carousel] becomes a track; its buttons are the
     .js-carbtn inside the same [data-carousel-root]. ---------- */
  function initCarousel(track) {
    var root = track.closest('[data-carousel-root]') || track.parentElement;
    var cards = Array.prototype.slice.call(track.children);
    var btns = Array.prototype.slice.call(root.querySelectorAll('.js-carbtn'));
    if (!cards.length) return null;

    var index = 0, drag = null;
    function rtl() { return getComputedStyle(track).direction === 'rtl'; }
    function visible() {
      return cards.filter(function (c) { return getComputedStyle(c).display !== 'none'; });
    }
    function step() {
      var v = visible(); if (!v.length) return 0;
      var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
      return v[0].getBoundingClientRect().width + gap;
    }
    function perView() {
      var st = step(); if (!st) return 1;
      return Math.max(1, Math.round(track.parentElement.clientWidth / st));
    }
    function maxIndex() { return Math.max(0, visible().length - perView()); }
    function offsetFor(i) { return (rtl() ? 1 : -1) * i * step(); }
    function apply(px) { track.style.transform = 'translateX(' + px + 'px)'; }

    function go(i, animate) {
      index = Math.min(maxIndex(), Math.max(0, i));
      if (animate === false) track.classList.add('is-dragging');
      apply(offsetFor(index));
      /* forced reflow rather than rAF: with rAF deferred (backgrounded tab) the
         class was never removed, leaving the track stuck with transition:none
         and the grabbing cursor for the rest of the session. */
      if (animate === false) { void track.offsetHeight; track.classList.remove('is-dragging'); }
      btns.forEach(function (b) {
        var d = +b.getAttribute('data-dir');
        b.disabled = d < 0 ? index <= 0 : index >= maxIndex();
      });
      root.classList.toggle('is-static', maxIndex() === 0);
    }

    btns.forEach(function (b) {
      b.addEventListener('click', function () { go(index + (+b.getAttribute('data-dir'))); });
    });

    /* Capture the pointer only AFTER a real drag begins. Capturing on
       pointerdown routes the whole gesture to the track and suppresses the
       click on the card link underneath — which made every card unclickable. */
    var dragging = false;
    track.addEventListener('pointerdown', function (e) {
      if (e.button) return;
      drag = { x: e.clientX, base: offsetFor(index), moved: 0, id: e.pointerId };
      dragging = false;
    });
    track.addEventListener('pointermove', function (e) {
      if (!drag) return;
      drag.moved = e.clientX - drag.x;
      if (!dragging) {
        if (Math.abs(drag.moved) < 6) return;    // still a click, not a swipe
        dragging = true;
        track.classList.add('is-dragging');
        try { track.setPointerCapture(drag.id); } catch (err) {}
      }
      apply(drag.base + drag.moved);
    });
    function endDrag() {
      if (!drag) return;
      var moved = drag.moved, wasDrag = dragging;
      drag = null; dragging = false;
      track.classList.remove('is-dragging');
      if (!wasDrag) return;                      // plain click -> let the link work
      var dir = (rtl() ? 1 : -1) * Math.sign(moved);
      go(Math.abs(moved) > step() / 3 ? index + dir : index);
      var kill = function (ev) { ev.preventDefault(); ev.stopPropagation(); };
      track.addEventListener('click', kill, { capture: true, once: true });
      setTimeout(function () { track.removeEventListener('click', kill, true); }, 0);
    }
    track.addEventListener('pointerup', endDrag);
    track.addEventListener('pointercancel', endDrag);
    track.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight') go(index + (rtl() ? -1 : 1));
      else if (e.key === 'ArrowLeft') go(index + (rtl() ? 1 : -1));
      else return;
      e.preventDefault();
    });
    window.addEventListener('resize', function () { go(Math.min(index, maxIndex()), false); }, { passive: true });
    window.addEventListener('load', function () { go(index, false); });
    go(0, false);
    return { go: go, reset: function () { go(0, false); },
             state: function () { return { index: index, max: maxIndex(), step: step(), perView: perView() }; } };
  }

  var __carousels = {};
  document.querySelectorAll('[data-carousel]').forEach(function (t) {
    var api = initCarousel(t);
    if (api) __carousels[t.getAttribute('data-carousel')] = api;
  });
  window.__carousels = __carousels;

  /* ---------- counters + region-aware ledger ---------- */
  /* Deliberately ABOVE the invmap IIFE: render(def) runs inside that IIFE and calls
     applyLedger(), so animateCount / cio / the ledger helpers must already exist. */
  var _cseq = 0;
  /* the client's regional figures include values like 69,706 — group thousands
     so they read as authored. Locale-aware: Arabic keeps Western digits here
     (the site sets Latin numerals throughout) but gets the right separator. */
  function fmtCount(v, dec) {
    return Number(v.toFixed(dec)).toLocaleString('en-US',
      { minimumFractionDigits: dec, maximumFractionDigits: dec });
  }
  function animateCount(el) {
    var target = parseFloat(el.getAttribute('data-count'));
    var dec = parseInt(el.getAttribute('data-dec') || '0', 10);
    var gen = ++_cseq; el.__cgen = gen;        // supersedes any tween still in flight
    if (reduced) { el.textContent = fmtCount(target, dec); return; }
    var dur = 1500, start = null;
    function step(ts) {
      if (el.__cgen !== gen) return;           // a newer region switch took over
      if (!start) start = ts;
      var p = Math.min((ts - start) / dur, 1);
      var eased = 1 - Math.pow(1 - p, 3);      // ease-out cubic
      el.textContent = fmtCount(target * eased, dec);
      if (p < 1) requestAnimationFrame(step);
      else el.textContent = fmtCount(target, dec);
    }
    requestAnimationFrame(step);
  }
  /* NB the observer keeps observing (no unobserve) — we need isIntersecting on every
     later region switch to know whether to re-animate or just set the value. */
  var cio = new IntersectionObserver(function (entries) {
    entries.forEach(function (en) {
      var el = en.target;
      el.__cseen = en.isIntersecting;
      if (en.isIntersecting && !el.__cdone) { el.__cdone = 1; animateCount(el); }
    });
  }, { threshold: 0.6 });
  document.querySelectorAll('.stat__num').forEach(function (el) { cio.observe(el); });

  /* --- the 4 figures beside the map, optionally per-region --- */
  var _ledgerRows = null, _ledgerGlobals = null;
  function ledgerRows() {
    if (!_ledgerRows) _ledgerRows = Array.prototype.slice.call(document.querySelectorAll('.invmap__stats .stat'));
    return _ledgerRows;
  }
  /* the REAL portfolio totals, captured once from the authored markup */
  function ledgerGlobals() {
    if (_ledgerGlobals) return _ledgerGlobals;
    _ledgerGlobals = ledgerRows().map(function (row) {
      var n = row.querySelector('.stat__num'), sf = row.querySelector('.stat__suffix'),
          u = row.querySelector('.stat__unit'), l = row.querySelector('.stat__label');
      return { count: n ? n.getAttribute('data-count') : '0',
               dec: n ? (n.getAttribute('data-dec') || '') : '',
               suffix: sf ? sf.textContent : '',
               unit: u ? u.textContent : '', label: l ? l.textContent : '' };
    });
    return _ledgerGlobals;
  }
  function setStatValue(el, count, dec) {
    if (!el) return;
    el.setAttribute('data-count', count);
    if (dec) el.setAttribute('data-dec', dec); else el.removeAttribute('data-dec');
    if (el.__cseen) animateCount(el);                       // on screen -> re-count from 0
    else if (el.__cdone) { el.__cgen = ++_cseq; el.textContent = fmtCount(parseFloat(count), parseInt(dec || '0', 10)); }
    else { el.__cgen = ++_cseq; el.textContent = '0'; }      // not yet seen -> let the observer do it
  }
  /* A region's figures are used ONLY if all four rows are complete and real. Anything
     missing or placeholder -> fall back to the portfolio totals for ALL FOUR rows, so a
     half-filled region can never be read as region-specific data. */
  function rowOk(r) {
    if (!r || typeof r !== 'object') return false;
    if (r.count == null || !isFinite(parseFloat(r.count))) return false;
    if (typeof r.unit !== 'string' || !r.unit) return false;
    if (typeof r.label !== 'string' || !r.label) return false;
    if (r.unit.indexOf('PLACEHOLDER') === 0 || r.label.indexOf('PLACEHOLDER') === 0) return false;
    return true;
  }
  function regionOk(d) { return !!d && Array.isArray(d.stats) && d.stats.length === 4 && d.stats.every(rowOk); }

  /* The regional figures are authored in full (208,648 m2) while the portfolio
     totals are already abbreviated (339 ألف / 339 K), so switching region made
     the ledger jump between two number formats. Anything from 10,000 up is
     rendered in the same thousands form. The thousands word is lifted from the
     authored totals row rather than hard-coded, so each locale keeps its own.
     The exact figure stays available as a title attribute. */
  var _kWord;
  function kWord() {
    if (_kWord === undefined) {
      _kWord = '';
      ledgerGlobals().forEach(function (g) {
        if (g.suffix && g.suffix.trim()) _kWord = g.suffix.trim();
      });
    }
    return _kWord;
  }
  function abbreviate(count, suffix) {
    var n = parseFloat(count);
    if (!isFinite(n) || n < 10000 || !kWord()) return { count: count, suffix: suffix, exact: null };
    return { count: String(Math.round(n / 1000)), suffix: kWord(), exact: n };
  }
  /* deck p14 writes the unit INTO the figure ("86M SAR"); abbreviate the
     English unit words to match. Arabic units stay as authored. */

  /* deck p14 sets short title-case labels in the panel; data stays authored */

  function applyLedger(d) {
    var rows = ledgerRows(); if (!rows.length) return;
    var g = ledgerGlobals(), per = regionOk(d);
    if (d && d.stats && !per) console.warn('[asfar] incomplete per-region stats ignored for:', d.region);
    rows.forEach(function (row, i) {
      var src = per ? d.stats[i] : g[i]; if (!src) return;
      var ab = abbreviate(src.count, src.suffix || '');
      var numEl = row.querySelector('.stat__num');
      setStatValue(numEl, ab.count, src.dec || '');
      if (numEl) {
        if (ab.exact != null) numEl.setAttribute('title', fmtCount(ab.exact, 0));
        else numEl.removeAttribute('title');
      }
      var sf = row.querySelector('.stat__suffix'); if (sf) sf.textContent = ab.suffix;
      var u = row.querySelector('.stat__unit');
      if (u) {
        var ut = src.unit;
        /* deck: letter-units fuse to the number ("1.1B SAR"), word-units and
           M\u00B2 take a space ("570 Rooms", "339K M\u00B2") */
        u.textContent = (ut === 'M SAR' || ut === 'B SAR') ? ut : '\u00A0' + ut;
      }
      var l = row.querySelector('.stat__label');  if (l) l.textContent = src.label;
    });
  }

    /* ---------- investment map: faithful reference layout (map + marker + company + PR card + tabs) ---------- */
  (function () {
    var SVGNS = 'http://www.w3.org/2000/svg';
    var dataEl = document.getElementById('invData');
    var section = document.getElementById('projects');
    if (!dataEl || !section) return;
    var DATA; try { DATA = JSON.parse(dataEl.textContent); } catch (e) { return; }
    if (!DATA || !DATA.length) return;
    /* deck direction 04: the active region's photograph is the section's
       full-bleed backdrop (petrol-scrimmed), crossfading as the tabs change.
       Layers are built from DATA so the picture can never drift from the
       region it belongs to. */
    var bgHost = document.createElement('div');
    bgHost.className = 'invmap__bgs'; bgHost.setAttribute('aria-hidden', 'true');
    var bgLayers = {};
    DATA.forEach(function (e) {
      var b = document.createElement('div');
      b.className = 'invmap__bg';
      b.style.backgroundImage = 'url("' + e.img + '")';
      bgHost.appendChild(b); bgLayers[e.hot] = b;
    });
    section.insertBefore(bgHost, section.firstChild);

    var host = document.getElementById('invMap'),
        companyEl = document.getElementById('invCompany'), pillEl = document.getElementById('invRegion'),
        titleEl = document.getElementById('invTitle'), imgEl = document.getElementById('invImg'),
        bodyEl = document.getElementById('invBody'), moreEl = document.getElementById('invMore'),
        panel = document.getElementById('invPanel');
    var tabs = Array.prototype.slice.call(section.querySelectorAll('.invmap__tab'));
    var svgEl = null, regionPaths = [], cur = -1;

    /* deck grading: the sea slide (p14/Yanbu) runs ICE accents, the mountain
       slides (p15/Taif) run WARM gold — figures, region fill and pin follow
       the active region via CSS custom properties. */
    /* figures and the region fill are graded per photograph; the PIN is the
       one constant — brand gold on every location, so no two markers differ */
    var PIN = { ring: '#A6824A', dot: '#A6824A' };
    var ACCENTS = {
      warm: { a: '#DECEB4', f: '#EEE6DA', ring: PIN.ring, dot: PIN.dot },
      ice:  { a: '#CAE6EA', f: '#CAE6EA', ring: PIN.ring, dot: PIN.dot }
    };
    var REGION_TONE = { baha: 'warm', yanbu: 'ice', asir: 'warm', taif: 'warm', all: 'warm' };
    function applyAccent(d) {
      var t = ACCENTS[REGION_TONE[d.hot] || 'ice'];
      section.style.setProperty('--rg-a', t.a);
      section.style.setProperty('--rg-f', t.f);
      section.style.setProperty('--rg-ring', t.ring);
      section.style.setProperty('--rg-dot', t.dot);
    }

    /* ONE pin geometry for the whole map, so no two pins can differ in size.
       Paired with the single .inv-marker_* rule set in app.css. */
    var PIN_R = { halo: 26, pulse: 15, dot: 9.5 };

    /* ONE pin PER LOCATION, built from DATA. Previously there was a single
       shared province marker plus a separate set of city pins, which meant the
       portfolio-wide view showed a pin on whichever region came first in the
       SVG and none on the others. Now every location owns its pin and they are
       all the same object, so "select all" lights every location identically.
       Position comes from the authored e.mark when there is one (a city has no
       outline of its own, e.g. Yanbu inside Al-Madinah), otherwise from the
       centre of that region's path. */
    var pins = [];
    function buildPins() {
      DATA.forEach(function (e) {
        if (!e.hot || e.hot === 'all') return;
        var x, y, path = null;
        for (var i = 0; i < regionPaths.length; i++) {
          if (regionPaths[i].getAttribute('data-region') === e.hot) { path = regionPaths[i]; break; }
        }
        /* a region with its own outline gets the pin at the PROVINCE CENTRE
           (deck p14/p15); point-cities keep their authored coordinates */
        if (path && !e.point) {
          try { var b = path.getBBox(); x = b.x + b.width / 2; y = b.y + b.height / 2; }
          catch (err) {}
        }
        if (x === undefined && e.mark && e.mark.length === 2) { x = e.mark[0]; y = e.mark[1]; }
        if (x === undefined) return;
        var g = document.createElementNS(SVGNS, 'g');
        g.setAttribute('class', 'inv-pin');
        [['inv-marker_halo', PIN_R.halo], ['inv-marker_pulse', PIN_R.pulse], ['inv-marker_dot', PIN_R.dot]].forEach(function (m) {
          var c = document.createElementNS(SVGNS, 'circle');
          c.setAttribute('class', m[0]); c.setAttribute('r', m[1]);
          c.setAttribute('cx', x); c.setAttribute('cy', y);
          g.appendChild(c);
        });
        g.style.visibility = 'hidden';
        svgEl.appendChild(g);
        // inAll:false keeps a location off the portfolio-wide view — Taif is
        // flagged that way because it is not part of the strategic portfolio.
        pins.push({ hot: e.hot, point: !!e.point, inAll: e.inAll !== false, el: g, x: x, y: y });
      });
    }
    function showPins(pred) {
      pins.forEach(function (p) { p.el.style.visibility = pred(p.hot) ? '' : 'hidden'; });
    }

    /* ---- leader line: dashed elbow from the live region out to the side lede.
       Geometry is MEASURED off the live rects (never hard-coded), so it tracks
       the map at any size. .invmap__inner is direction:ltr in both languages,
       so the run is always map-left -> lede-right and needs no RTL mirror. ---- */
    var leaderEl = document.getElementById('invLeader');
    var leaderPath = leaderEl ? leaderEl.querySelector('path') : null;
    var leaderDot = leaderEl ? leaderEl.querySelector('circle') : null;
    var innerEl = section.querySelector('.invmap__inner');
    var middleEl = section.querySelector('.invmap__middle');
    var hotCur = null;

    function leaderOff() { if (leaderEl) leaderEl.classList.remove('is-on'); }

    function drawLeader(quiet) {
      if (!leaderEl || !leaderPath || !innerEl || !middleEl) return;
      // stacked layout (<=940px) hides the overlay entirely — nothing to point at
      if (!hotCur || window.innerWidth <= 940) return leaderOff();
      var ir = innerEl.getBoundingClientRect();
      // aim at the company title itself, not the column box — the box is taller
      // than its text and the line would otherwise end in empty space above it
      var anchorEl = section.querySelector('.invmap__company') || middleEl;
      var mr = anchorEl.getBoundingClientRect();
      var pr;
      try { pr = hotCur.getBoundingClientRect(); } catch (e) { return leaderOff(); }
      if (!ir.width || !ir.height || !pr.width || !mr.height) return leaderOff();

      leaderEl.setAttribute('viewBox', '0 0 ' + Math.round(ir.width) + ' ' + Math.round(ir.height));
      var sx = pr.left + pr.width / 2 - ir.left,
          sy = pr.top + pr.height / 2 - ir.top;
      // The label sits to the RIGHT of the map in LTR and to the LEFT in RTL (the
      // grid mirrors). Work out which, then aim at the anchor's NEAR edge and run
      // the elbow in that direction. The old code assumed right-of-map always, so
      // in RTL `ex - sx` went negative and the line silently switched itself off.
      var toRight = (mr.left + mr.width / 2) > (pr.left + pr.width / 2);
      var ex = (toRight ? mr.left - 10 : mr.right + 10) - ir.left,
          ey = mr.top - ir.top + mr.height / 2;
      if (Math.abs(ex - sx) < 24) return leaderOff();   // no room for a legible run
      var dir = toRight ? 1 : -1;
      var knee = Math.max(18, Math.min(46, Math.abs(ex - sx) * 0.28));
      leaderPath.setAttribute('d',
        'M' + sx.toFixed(1) + ' ' + sy.toFixed(1) +
        'L' + (ex - dir * knee).toFixed(1) + ' ' + ey.toFixed(1) +
        'L' + ex.toFixed(1) + ' ' + ey.toFixed(1));
      if (leaderDot) { leaderDot.setAttribute('cx', sx.toFixed(1)); leaderDot.setAttribute('cy', sy.toFixed(1)); }
      var len = 600; try { len = Math.ceil(leaderPath.getTotalLength()); } catch (e) {}
      leaderEl.style.setProperty('--leader-len', len);
      if (quiet) { leaderEl.classList.add('is-on'); return; }
      leaderEl.classList.remove('is-on');
      void leaderEl.offsetWidth;                     // reflow so the draw-on replays
      leaderEl.classList.add('is-on');
    }

    /* deck direction 04: name the active region ON the map, beside its pin.
       Regions with no outline of their own (Taif) keep the pin + label only. */
    var mapLabel = null;
    function placeMapLabel(d) {
      if (!svgEl) return;
      if (!mapLabel) {
        mapLabel = document.createElementNS(SVGNS, 'text');
        mapLabel.setAttribute('class', 'invmap__maplabel');
        svgEl.appendChild(mapLabel);
      }
      var pin = d && pins.filter(function (p) { return p.hot === d.hot; })[0];
      if (!pin) { mapLabel.style.visibility = 'hidden'; return; }
      var i = DATA.indexOf(d);
      var name = (tabs[i] && tabs[i].textContent) ? tabs[i].textContent.trim() : (d.region || '');
      /* The label belongs OUTSIDE the country, never over it: walk out from the
         pin along the roomier side until the point leaves every province path,
         then clear the outline by a margin. Falls back to a fixed offset if the
         walk never exits (it always does on this map). */
      var left = pin.x < 456;
      var dir = left ? -1 : 1;
      /* every province, not just the four tagged ones — regionPaths holds only
         [data-region] paths, so testing against it exits at a province border
         instead of the coastline and the label still lands on the country */
      var allRegions = svgEl.querySelectorAll('.invmap__region');
      function insideCountry(px, py) {
        if (!svgEl.createSVGPoint) return false;
        var pt = svgEl.createSVGPoint(); pt.x = px; pt.y = py;
        for (var k = 0; k < allRegions.length; k++) {
          try { if (allRegions[k].isPointInFill(pt)) return true; } catch (e) {}
        }
        return false;
      }
      var x = pin.x + dir * 46;
      for (var off = 26; off <= 420; off += 6) {
        if (!insideCountry(pin.x + dir * off, pin.y)) { x = pin.x + dir * (off + 30); break; }
      }
      mapLabel.setAttribute('x', x);
      mapLabel.setAttribute('text-anchor', left ? 'end' : 'start');
      mapLabel.setAttribute('y', pin.y + 5);
      mapLabel.textContent = name;
      mapLabel.style.visibility = '';
    }

    function paintMap(d) {
      // "all" = the portfolio-wide state (Strategic Investments): every location
      // shows at once instead of one being singled out.
      var all = d.hot === 'all';
      // the active region reads as a cream fill (deck direction 04)
      regionPaths.forEach(function (p) {
        p.classList.toggle('is-hot', !all && p.getAttribute('data-region') === d.hot);
      });
      placeMapLabel(all ? null : d);
      // No province is ever filled — the country stays one flat colour and the
      // PIN alone marks the selection, so a region and a city read identically.
      showPins(function (h) {
        if (!all) return h === d.hot;
        var p = pins.filter(function (x) { return x.hot === h; })[0];
        return !p || p.inAll;
      });
      hotCur = null;
      drawLeader(false);
    }
    window.addEventListener('resize', function () { drawLeader(true); }, { passive: true });
    function render(i) {
      var d = DATA[i]; if (!d || i === cur) return; cur = i;
      if (companyEl) companyEl.textContent = d.company;
      if (pillEl) pillEl.textContent = d.region;
      if (titleEl) titleEl.textContent = d.title;
      if (imgEl) { imgEl.src = d.img; imgEl.alt = d.title; }
      Object.keys(bgLayers).forEach(function (h) { bgLayers[h].classList.toggle('is-active', h === d.hot); });
      if (bodyEl) bodyEl.textContent = d.body;
      if (moreEl) moreEl.setAttribute('href', d.href || '#');
      tabs.forEach(function (t, k) { t.classList.toggle('is-active', k === i); t.setAttribute('aria-selected', k === i ? 'true' : 'false'); });
      if (typeof dashes !== 'undefined') dashes.forEach(function (d, k) { d.classList.toggle('is-active', k === i); });
      section.classList.toggle('is-active', !!d.hot);
      applyLedger(d);
      applyAccent(d);
      paintMap(d);
      if (panel) { panel.classList.remove('is-in'); requestAnimationFrame(function () { requestAnimationFrame(function () { panel.classList.add('is-in'); }); }); }
    }
    tabs.forEach(function (t) { t.addEventListener('click', function () { render(+t.getAttribute('data-i')); }); });
    /* deck p14 dash indicators, bottom-left — the labelled tabs remain the
       primary selector; the dashes mirror them and are clickable too */
    var dashHost = document.createElement('div');
    dashHost.className = 'invmap__dashes';
    var dashes = tabs.map(function (t, k) {
      var b = document.createElement('button');
      b.type = 'button';
      b.className = 'invmap__dash';
      b.textContent = t.textContent.trim();     // the region name rides the dash
      b.setAttribute('aria-label', t.textContent.trim());
      b.addEventListener('click', function () { render(k); });
      dashHost.appendChild(b);
      return b;
    });
    section.appendChild(dashHost);
    var def = 0; tabs.forEach(function (t, k) { if (t.classList.contains('is-active')) def = k; });
    render(def);
    if (host) {
      fetch(asfarSettings.assets + 'img/saudi-invest-map.svg').then(function (r) { return r.text(); }).then(function (svg) {
        host.innerHTML = svg;
        svgEl = host.querySelector('svg');
        regionPaths = Array.prototype.slice.call(host.querySelectorAll('[data-region]'));
        /* The map is deliberately NOT clickable — the tab bar is the only
           selector. Region click handlers were removed so the map never offers
           an affordance it does not have. */
        if (svgEl) buildPins();
        paintMap(DATA[cur]);
        // the injected SVG has not settled its final box yet, so the leader
        // geometry measured above is stale — re-measure once it has
        requestAnimationFrame(function () { drawLeader(true); });
        setTimeout(function () { drawLeader(true); }, 350);
      }).catch(function () {});
    }
  })();

  /* ---------- sectors triptych (cinematic image accordion) ---------- */
  (function () {
    var root = document.querySelector('.tript');
    if (!root) return;
    var panels = Array.prototype.slice.call(root.querySelectorAll('.tript__panel'));
    if (panels.length < 2) return;
    root.classList.add('tript--live');
    function setOpen(i) {
      panels.forEach(function (p, k) { p.classList.toggle('is-open', k === i); });
      root.setAttribute('data-open', i);
    }
    panels.forEach(function (p, k) {
      p.addEventListener('pointerenter', function () { setOpen(k); });
      p.addEventListener('focusin', function () { setOpen(k); });
      p.addEventListener('click', function () { var h = p.getAttribute('data-href'); if (h) window.location.href = h; });
    });
    root.addEventListener('pointerleave', function () { setOpen(0); });
    root.addEventListener('focusout', function (e) { if (!root.contains(e.relatedTarget)) setOpen(0); });
    root.addEventListener('keydown', function (e) {
      if (e.key !== 'ArrowLeft' && e.key !== 'ArrowRight') return;
      var cur = parseInt(root.getAttribute('data-open'), 10) || 0;
      var rtl = document.documentElement.getAttribute('dir') === 'rtl';
      var fwd = (e.key === 'ArrowRight') !== rtl;
      var next = Math.max(0, Math.min(panels.length - 1, cur + (fwd ? 1 : -1)));
      setOpen(next); panels[next].focus(); e.preventDefault();
    });
  })();

  /* hero on-load reveal */
  var hero = document.querySelector('.hero');
  if (hero) { requestAnimationFrame(function () { setTimeout(function () { hero.classList.add('is-in'); }, 120); }); }

  /* ---------- nav + parallax + atlas on scroll ---------- */
  var nav = document.getElementById('nav');
  var progress = document.getElementById('progress');
  var heroMedia = document.querySelector('.hero__scenes');
  var heroIn = document.querySelector('.hero__in');
  var interludeMedia = document.querySelector('.interlude__media');
  var interlude = document.querySelector('.interlude');

  /* atlas elements */
  var atlasTrack = document.getElementById('atlasTrack');
  var figs = Array.prototype.slice.call(document.querySelectorAll('.atlas__fig'));
  var names = Array.prototype.slice.call(document.querySelectorAll('#atlasNames li'));
  var detail = document.getElementById('atlasDetail');
  var regionEl = detail ? detail.querySelector('.atlas__region') : null;
  var blurbEl = detail ? detail.querySelector('.atlas__blurb') : null;
  var bar = document.getElementById('atlasBar');
  var countEl = document.getElementById('atlasCount');
  var current = -1;
  var N = figs.length;
  var atlasView = document.getElementById('atlasView');

  function setActive(i) {
    if (i === current || i < 0 || i >= N) return;
    current = i;
    figs.forEach(function (f, k) { f.classList.toggle('is-active', k === i); });
    names.forEach(function (n, k) { n.classList.toggle('is-active', k === i); });
    if (countEl) countEl.textContent = ('0' + (i + 1)).slice(-2);
    if (detail && regionEl && blurbEl) {
      detail.classList.add('is-swap');
      setTimeout(function () {
        regionEl.textContent = detail.querySelector('.atlas__region').getAttribute('data-region-' + i);
        blurbEl.textContent = blurbEl.getAttribute('data-blurb-' + i);
        detail.classList.remove('is-swap');
      }, 300);
    }
  }
  // wire clickable index
  names.forEach(function (li) {
    li.querySelector('button').addEventListener('click', function () {
      var i = parseInt(li.getAttribute('data-i'), 10);
      if (!atlasTrack) return;
      var rectTop = atlasTrack.getBoundingClientRect().top + window.pageYOffset;
      var span = atlasTrack.offsetHeight - window.innerHeight;
      var y = rectTop + ((i + 0.5) / N) * span;
      scrollToEl(y, 0);
    });
  });

  function updateAtlas() {
    if (!atlasTrack || mqMobile.matches) return;
    var rect = atlasTrack.getBoundingClientRect();
    var span = atlasTrack.offsetHeight - window.innerHeight;
    if (span <= 0) return;
    var p = Math.min(Math.max(-rect.top / span, 0), 1);
    if (rect.top > 0 || rect.bottom < window.innerHeight) {
      // outside the pinned region — still keep ends sensible
    }
    var idx = Math.min(N - 1, Math.floor(p * N));
    setActive(idx);
    if (bar) bar.style.width = (p * 100).toFixed(2) + '%';
  }

  var docEl = document.documentElement;
  function onScroll() {
    var y = window.pageYOffset;
    // progress
    if (progress) {
      var max = docEl.scrollHeight - window.innerHeight;
      progress.style.width = (max > 0 ? (y / max) * 100 : 0) + '%';
    }
    // nav state
    if (nav) {
      var past = y > window.innerHeight * 0.72;
      nav.classList.toggle('is-scrolled', past);
    }
    // hero: parallax background + content fade & lift on scroll (cinematic dissolve)
    if (heroMedia && !reduced && y < window.innerHeight * 1.25) {
      var hp = Math.min(y / window.innerHeight, 1);
      heroMedia.style.transform = 'translate3d(0,' + (y * 0.16).toFixed(1) + 'px,0) scale(' + (1 + hp * 0.06).toFixed(3) + ')';
      if (heroIn) {
        heroIn.style.transform = 'translate3d(0,' + (-y * 0.16).toFixed(1) + 'px,0)';
        heroIn.style.opacity = Math.max(0, 1 - hp * 1.3).toFixed(3);
      }
    }
    parallax();
    updateAtlas();
    updateFly();
  }

  /* ---------- signature morph fly-through (canvas frame-scrub) ---------- */
  var flyEl = document.getElementById('fly');
  var flyCanvas = document.getElementById('flyCanvas');
  var flyCtx = flyCanvas ? flyCanvas.getContext('2d') : null;
  var flyTotal = flyCanvas ? (parseInt(flyCanvas.getAttribute('data-frames'), 10) || 0) : 0;
  var flyFrames = [], flyReady = false, flyLoaded = 0, flyCur = -1, flyLm = -1;
  var flyMarks = [
    { name: 'The Mountains', region: 'Misted peaks of the Sarawat' },
    { name: 'The Highlands', region: 'Terraced rose country' },
    { name: 'The Oasis', region: 'Groves of a thousand springs' },
    { name: 'The Coast', region: 'Where the land meets the Red Sea' }
  ];
  if (flyEl && flyEl.getAttribute('data-marks')) {
    try { var _m = JSON.parse(flyEl.getAttribute('data-marks')); if (_m && _m.length) flyMarks = _m; } catch (e) {}
  }
  var flyNameEl = document.getElementById('flyName');
  var flyCountEl = document.getElementById('flyCount');
  var flyRegionEl = document.getElementById('flyRegion');
  function flyDraw(idx) {
    var img = flyFrames[idx]; if (!img || !flyCtx) return;
    var cw = flyCanvas.width, ch = flyCanvas.height;
    var ir = img.naturalWidth / img.naturalHeight, cr = cw / ch, dw, dh, dx, dy;
    if (ir > cr) { dh = ch; dw = ch * ir; dx = (cw - dw) / 2; dy = 0; }
    else { dw = cw; dh = cw / ir; dx = 0; dy = (ch - dh) / 2; }
    flyCtx.drawImage(img, dx, dy, dw, dh);
  }
  function flyResize() {
    if (!flyCanvas) return;
    var dpr = Math.min(2, window.devicePixelRatio || 1);
    flyCanvas.width = Math.round(flyCanvas.offsetWidth * dpr);
    flyCanvas.height = Math.round(flyCanvas.offsetHeight * dpr);
    if (flyReady) flyDraw(flyCur < 0 ? 0 : flyCur);
  }
  function updateFly() {
    if (!flyEl || !flyReady || reduced || mqMobile.matches) return;
    var span = flyEl.offsetHeight - window.innerHeight; if (span <= 0) return;
    var p = Math.min(Math.max(-flyEl.getBoundingClientRect().top / span, 0), 1);
    var f = Math.round(p * (flyTotal - 1));
    if (f !== flyCur) { flyCur = f; flyDraw(f); }
    var lm = Math.min(3, Math.round(p * 3));
    if (lm !== flyLm) {
      flyLm = lm;
      if (flyNameEl) flyNameEl.textContent = flyMarks[lm].name;
      if (flyRegionEl) flyRegionEl.textContent = flyMarks[lm].region;
      if (flyCountEl) flyCountEl.textContent = ('0' + (lm + 1)).slice(-2);
    }
  }
  var flyStarted = false;
  function flyPreload() {
    if (flyStarted || !flyCanvas || reduced || mqMobile.matches || flyTotal <= 0) return;
    flyStarted = true;
    flyResize();
    for (var i = 0; i < flyTotal; i++) {
      (function (i) {
        var img = new Image();
        img.onload = img.onerror = function () {
          if (img.naturalWidth) flyFrames[i] = img;
          if (++flyLoaded >= flyTotal) { flyReady = true; flyEl.classList.add('is-ready'); flyDraw(0); updateFly(); }
        };
        img.src = asfarSettings.assets + 'img/morph/f_' + ('00' + (i + 1)).slice(-3) + '.jpg';
      })(i);
    }
    window.addEventListener('resize', flyResize);
  }

  /* ---------- layered scroll parallax (depth) ---------- */
  var pxEls = Array.prototype.slice.call(document.querySelectorAll('[data-px]'));
  function parallax() {
    if (reduced) return;
    var vh = window.innerHeight;
    for (var i = 0; i < pxEls.length; i++) {
      var el = pxEls[i];
      var r = el.getBoundingClientRect();
      if (r.bottom < -100 || r.top > vh + 100) continue;
      var speed = parseFloat(el.getAttribute('data-px')) || 1;
      var rel = (r.top + r.height / 2 - vh / 2) / vh;   // ~ -1 .. 1
      var yy = (rel * -44 * speed).toFixed(1);
      // publish the offset as a custom property instead of writing `transform`
      // directly — overwriting transform clobbered any base the element already
      // had (e.g. .about__coins' translateY(-50%) centring), which meant parallax
      // could only ever be used on elements with no transform of their own.
      el.style.setProperty('--px-y', yy + 'px');
    }
  }

  var ticking = false;
  function onScrollRaf() {
    if (!ticking) { ticking = true; requestAnimationFrame(function () { onScroll(); ticking = false; }); }
  }
  window.addEventListener('scroll', onScrollRaf, { passive: true });
  window.addEventListener('resize', function () { current = -1; onScroll(); });

  /* ---------- overlay menu (all viewports) ---------- */
  var burger = document.getElementById('burger');
  var menu = document.getElementById('menu');
  var menuScrim = document.getElementById('menuScrim');
  function lockScroll(on) { var v = on ? 'hidden' : ''; document.documentElement.style.overflow = v; document.body.style.overflow = v; }
  function setInert(on) {
    ['main', '.footer'].forEach(function (sel) {
      var el = document.querySelector(sel);
      if (el) { try { el.inert = on; } catch (err) {} }
    });
  }
  function openMenu() {
    if (!menu) return;
    menu.classList.add('is-open'); menu.setAttribute('aria-hidden', 'false');
    burger.setAttribute('aria-expanded', 'true');
    burger.setAttribute('aria-label', burger.getAttribute('data-label-close') || '');
    burger.classList.add('is-active');
    if (nav) nav.classList.add('is-menu-open');
    if (menuScrim) menuScrim.classList.add('is-open');
    setInert(true); lockScroll(true);
    var first = menu.querySelector('a'); if (first) setTimeout(function () { first.focus(); }, 80);
  }
  function closeMenu() {
    if (!menu) return;
    var was = menu.classList.contains('is-open');
    menu.classList.remove('is-open'); menu.setAttribute('aria-hidden', 'true');
    if (burger) {
      burger.setAttribute('aria-expanded', 'false');
      burger.setAttribute('aria-label', burger.getAttribute('data-label-open') || '');
      burger.classList.remove('is-active');
      if (was) burger.focus();
    }
    if (nav) nav.classList.remove('is-menu-open');
    if (menuScrim) menuScrim.classList.remove('is-open');
    setInert(false); lockScroll(false);
  }
  if (menuScrim) menuScrim.addEventListener('click', closeMenu);
  if (burger) {
    burger.addEventListener('click', function () {
      if (menu.classList.contains('is-open')) closeMenu(); else openMenu();
    });
  }
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' && menu && menu.classList.contains('is-open')) closeMenu();
  });

  /* ---------- hero video ---------- */
  (function () {
    var v = document.getElementById('heroVid');
    if (!v || reduced) return;
    function ready() { v.classList.add('is-ready'); }
    function tryPlay() { var p = v.play(); if (p && p.catch) p.catch(function () {}); }
    v.addEventListener('loadeddata', tryPlay);
    v.addEventListener('canplay', tryPlay);
    v.addEventListener('playing', ready);
    if (v.readyState >= 2) { tryPlay(); if (v.readyState >= 3) ready(); }
  })();

  /* ---------- hero: rotating 3-scene banner (each scene has its own CTA) ---------- */
  (function () {
    var hero = document.getElementById('heroSlider');
    if (!hero) return;
    var scenes = Array.prototype.slice.call(hero.querySelectorAll('.hero__scene'));
    var slides = Array.prototype.slice.call(hero.querySelectorAll('.hero__slide'));
    var dots = Array.prototype.slice.call(hero.querySelectorAll('.hero__dot'));
    /* the deck skin keeps ONE film scene behind r8's three text slides, so the
       rotation is counted off whichever list is longer — otherwise n===1 and
       every dot resolves back to slide 0 */
    var n = Math.max(scenes.length, slides.length);
    /* a single scene means no rotation — but the film engine further down in
       this module must still initialise, so this is a flag, not an early out */
    var rotate = n >= 2;
    var i = 0, timer = null, DUR = 6000;
    function set(idx) {
      idx = (idx % n + n) % n;
      if (idx === i) return;
      [scenes, slides].forEach(function (set) {
        /* a lone scene has to stay lit — rotating it would blank the hero */
        if (set.length < 2) return;
        if (set[i]) set[i].classList.remove('is-active');
        if (set[idx]) set[idx].classList.add('is-active');
      });
      if (!window.__heroGoDest) dots.forEach(function (d, k) { d.classList.toggle('is-active', k === idx); d.setAttribute('aria-pressed', k === idx ? 'true' : 'false'); });
      i = idx;
    }
    window.__heroSlideGo = set;   /* fallback nav for mobile / reduced motion */
    function next() { set(i + 1); }
    var inView = true;
    function stop() { if (timer) { clearInterval(timer); timer = null; } }
    function start() { stop(); if (!rotate || hero.classList.contains('is-film')) return; if (!reduced && inView) timer = setInterval(next, DUR); }
    // pause on hover / when the tab is hidden
    hero.addEventListener('mouseenter', stop);
    hero.addEventListener('mouseleave', start);
    document.addEventListener('visibilitychange', function () { document.hidden ? stop() : start(); });
    // The carousel used to keep advancing while the user scrolled PAST the hero:
    // scene 1 crossfaded to scene 2 behind the scroll dissolve, and the outgoing
    // scene's kenburns transform snapped from ~scale(1.09) back to none. That is
    // the blink/glitch. Pause it whenever the hero is not on screen.
    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        entries.forEach(function (en) { inView = en.isIntersecting; inView ? start() : stop(); });
      }, { threshold: 0.12 }).observe(hero);
    }
    start();

    /* ---- "Morph" pill: turn the hero into a SCROLL-SCRUBBED canvas morph; caption tracks the frame ---- */
    var morphBtn = document.getElementById('heroMorphBtn');
    var hcanvas = document.getElementById('heroMorphCanvas');
    var hctx = hcanvas ? hcanvas.getContext('2d') : null;
    var HTOTAL = hcanvas ? (parseInt(hcanvas.getAttribute('data-frames'), 10) || 0) : 0;
    var hframes = [], hready = false, hloaded = 0, hcur = -1, hlm = -1, hstarted = false, morphOn = false;
    var hmarks = [];
    if (hero.getAttribute('data-morphmarks')) { try { var _hm = JSON.parse(hero.getAttribute('data-morphmarks')); if (_hm && _hm.length) hmarks = _hm; } catch (e) {} }
    var hNameEl = document.getElementById('heroMorphName');
    var hSubEl = document.getElementById('heroMorphSub');
    var hCtaEl = document.getElementById('heroMorphCta');
    function hDraw(idx) {
      var img = hframes[idx]; if (!img || !hctx) return;
      var cw = hcanvas.width, ch = hcanvas.height, ir = img.naturalWidth / img.naturalHeight, cr = cw / ch, dw, dh, dx, dy;
      if (ir > cr) { dh = ch; dw = ch * ir; dx = (cw - dw) / 2; dy = 0; } else { dw = cw; dh = cw / ir; dx = 0; dy = (ch - dh) / 2; }
      hctx.drawImage(img, dx, dy, dw, dh);
    }
    function hResize() {
      if (!hcanvas) return;
      var dpr = Math.min(2, window.devicePixelRatio || 1);
      hcanvas.width = Math.round(hcanvas.offsetWidth * dpr);
      hcanvas.height = Math.round(hcanvas.offsetHeight * dpr);
      if (hready) hDraw(hcur < 0 ? 0 : hcur);
    }
    function hUpdate() {
      if (!morphOn || !hready) return;
      var span = hero.offsetHeight - window.innerHeight; if (span <= 0) return;
      var p = Math.min(Math.max(-hero.getBoundingClientRect().top / span, 0), 1);
      var f = Math.round(p * (HTOTAL - 1));
      if (f !== hcur) { hcur = f; hDraw(f); }
      var lm = Math.min(hmarks.length - 1, Math.round(p * (hmarks.length - 1)));
      if (lm !== hlm) {
        hlm = lm;
        if (hNameEl) hNameEl.textContent = hmarks[lm].name;
        if (hSubEl) hSubEl.textContent = hmarks[lm].region || '';
        if (hCtaEl) { hCtaEl.textContent = hmarks[lm].cta; hCtaEl.setAttribute('href', hmarks[lm].href); }
      }
    }
    function hPreload(cb) {
      if (hstarted) { if (hready && cb) cb(); return; }
      hstarted = true; hResize();
      for (var k = 0; k < HTOTAL; k++) {
        (function (k) {
          var img = new Image();
          img.onload = img.onerror = function () {
            if (img.naturalWidth) hframes[k] = img;
            if (++hloaded >= HTOTAL) { hready = true; hDraw(0); hUpdate(); if (cb) cb(); }
          };
          img.src = asfarSettings.assets + 'img/morph/f_' + ('00' + (k + 1)).slice(-3) + '.jpg';
        })(k);
      }
      window.addEventListener('resize', hResize);
    }
    function toTop() {
      if (window.__lenis && window.__lenis.scrollTo) window.__lenis.scrollTo(0, { immediate: true });
      else window.scrollTo(0, 0);
    }
    function setMorph(on) {
      morphOn = on;
      toTop();                                     // start the scrub from frame 0
      hero.classList.toggle('is-morph', on);
      morphBtn.classList.toggle('is-on', on);
      morphBtn.setAttribute('aria-pressed', on ? 'true' : 'false');
      var txt = morphBtn.querySelector('.hero__morph-txt');
      if (txt) txt.textContent = on ? (morphBtn.getAttribute('data-label-on') || '') : (morphBtn.getAttribute('data-label-off') || '');
      if (on) { stop(); hPreload(); if (hready) { hResize(); hUpdate(); } }
      else { hlm = -1; start(); }
    }
    /* ---- AUTO-PLAYING FILM: the mid-page morph now lives IN the hero and
       plays on a clock — no scroll, no button. The statement slide (deck p7)
       holds the opening beats while the 273 frames load; then the hero fades
       into the journey and the captions track the landmarks (deck p8).
       Plays FORWARD ONLY, mountain to coast; at the end the hero returns
       to the statement slide and the cycle repeats — never in reverse.
       Skipped under prefers-reduced-motion and on mobile
       (the frames are 1200px desktop assets); those keep the video hero. ---- */
    (function () {
      if (!hcanvas || !hctx || HTOTAL <= 0 || reduced || mqMobile.matches) return;
      var FILM_MS = 17000;          // one mountain-to-coast pass
      var INTRO_MS = 5000;          // one statement beat before the film takes over
      var playing = false, p = 0, lastTs = null, filmInView = true;
      var born = Date.now();
      var filmTimer = null, stopped = false;   // user taking manual control halts the loop
      var heroDashes = Array.prototype.slice.call(hero.querySelectorAll('.hero__dot'));
      function syncDash(k) {
        heroDashes.forEach(function (d, j) {
          d.classList.toggle('is-active', j === k);
          d.setAttribute('aria-pressed', j === k ? 'true' : 'false');
        });
      }
      function caption(lm) {
        if (lm === hlm) return;
        hlm = lm;
        if (hNameEl) hNameEl.textContent = hmarks[lm].name;
        if (hSubEl) hSubEl.textContent = hmarks[lm].region || '';
        if (hCtaEl) { hCtaEl.textContent = hmarks[lm].cta; hCtaEl.setAttribute('href', hmarks[lm].href); }
        syncDash(lm);
      }
      function tick(ts) {
        if (!playing) return;
        /* hold the frame (and the clock) while the tab or hero is unseen */
        if (document.hidden || !filmInView) { lastTs = null; requestAnimationFrame(tick); return; }
        if (lastTs == null) lastTs = ts;
        p += (ts - lastTs) / FILM_MS; lastTs = ts;
        if (p > 1) p = 1;
        var f = Math.round(p * (HTOTAL - 1));
        if (f !== hcur) { hcur = f; hDraw(f); }
        caption(Math.min(hmarks.length - 1, Math.round(p * (hmarks.length - 1))));
        if (window.__heroFilm) { window.__heroFilm.frame = f; window.__heroFilm.p = p; }
        /* forward only — and at the coast the hero returns to the opening
           slide (canvas fades out, the video resumes), then the journey
           comes round again. Never plays in reverse. */
        if (p >= 1) { playing = false; if (window.__heroFilm) window.__heroFilm.playing = false; leave(); return; }
        requestAnimationFrame(tick);
      }
      function enter() {
        if (stopped || playing) return;
        playing = true;
        stop();
        hero.classList.add('is-film');
        var v = document.getElementById('heroVid');
        if (v) { try { v.pause(); } catch (e) {} }
        p = 0; lastTs = null; hlm = -1;
        hResize(); hDraw(0); caption(0);
        window.__heroFilm = { playing: true, frame: 0, p: 0, cycles: (window.__heroFilm && window.__heroFilm.cycles) || 0 };
        requestAnimationFrame(tick);
      }
      function leave() {
        hero.classList.remove('is-film');          // the statement slide is back
        var v = document.getElementById('heroVid');
        if (v) { try { var pr = v.play(); if (pr && pr.catch) pr.catch(function () {}); } catch (e) {} }
        if (window.__heroFilm) window.__heroFilm.cycles = (window.__heroFilm.cycles || 0) + 1;
        filmTimer = setTimeout(enter, INTRO_MS);   // and the journey comes round again
      }
      function begin() {
        filmTimer = setTimeout(enter, Math.max(0, INTRO_MS - (Date.now() - born)));
      }
      /* clicking a hero dash hands control to the slides: stop the film loop,
         drop out of film mode and let the statement slides show. */
      window.__heroFilmCancel = function () {
        stopped = true; playing = false;
        clearTimeout(filmTimer);
        hero.classList.remove('is-film');
        if (window.__heroFilm) window.__heroFilm.playing = false;
        var v = document.getElementById('heroVid');
        if (v) { try { var pr = v.play(); if (pr && pr.catch) pr.catch(function () {}); } catch (e) {} }
      };
      /* clicking a dash morphs the destination FILM IMAGE to that destination
         (Mountains / Highlands / Oasis / Coast) and shows its caption — the
         hero's visual journey, driven by hand instead of the auto loop. */
      var animRAF = null;
      function morphTo(target, cb) {
        cancelAnimationFrame(animRAF);
        var from = hcur < 0 ? 0 : hcur, dur = 780, t0 = null;
        function fr(ts) {
          if (t0 == null) t0 = ts;
          var u = Math.min(1, (ts - t0) / dur);
          var e = u < 0.5 ? 2 * u * u : 1 - Math.pow(-2 * u + 2, 2) / 2;   // easeInOutQuad
          var f = Math.round(from + (target - from) * e);
          if (f !== hcur) { hcur = f; hDraw(f); }
          if (u < 1) animRAF = requestAnimationFrame(fr); else if (cb) cb();
        }
        animRAF = requestAnimationFrame(fr);
      }
      window.__heroGoDest = function (k) {
        stopped = true; playing = false; clearTimeout(filmTimer);
        hero.classList.add('is-film');
        var v = document.getElementById('heroVid'); if (v) { try { v.pause(); } catch (e) {} }
        var target = Math.round((hmarks.length < 2 ? 0 : k / (hmarks.length - 1)) * (HTOTAL - 1));
        hlm = -1; caption(k);                 // caption() also lights dash k
        hResize();
        if (hready) morphTo(target);
        else hPreload(function () { morphTo(target); });
      };
      heroDashes.forEach(function (d, k) {
        d.addEventListener('click', function () { window.__heroGoDest(k); });
      });
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (en) {
          filmInView = en[0].isIntersecting;
        }, { threshold: 0.08 }).observe(hero);
      }
      var armed = false;
      function arm() { if (armed) return; armed = true; setTimeout(function () { hPreload(begin); }, 400); }
      if (document.readyState === 'complete') arm();
      else window.addEventListener('load', arm);
      setTimeout(arm, 3000);        // belt: never wait forever on a stalled load event
    })();
    /* mobile / reduced motion: the film never runs, so the dashes step the
       statement slides instead of the destination film. */
    if (!window.__heroGoDest) {
      var fbDashes = Array.prototype.slice.call(hero.querySelectorAll('.hero__dot'));
      fbDashes.forEach(function (d, k) {
        d.addEventListener('click', function () {
          if (window.__heroSlideGo) window.__heroSlideGo(k % Math.max(1, slides.length));
          fbDashes.forEach(function (dd, j) { dd.classList.toggle('is-active', j === k); dd.setAttribute('aria-pressed', j === k ? 'true' : 'false'); });
        });
      });
    }
  })();

  /* ---------- cinematic word-reveal headings ---------- */
  function splitWords(el) {
    if (el.dataset.split) return;
    el.dataset.split = '1';
    el.classList.add('rw');
    function walk(node) {
      var out = [];
      Array.prototype.forEach.call(node.childNodes, function (n) {
        if (n.nodeType === 3) {
          n.textContent.split(/(\s+)/).forEach(function (t) {
            if (t.length === 0) return;
            if (/^\s+$/.test(t)) { out.push(document.createTextNode(t)); }
            else {
              var w = document.createElement('span'); w.className = 'w';
              var wi = document.createElement('span'); wi.className = 'wi'; wi.textContent = t;
              w.appendChild(wi); out.push(w);
            }
          });
        } else if (n.nodeName === 'BR') { out.push(document.createElement('br')); }
        else { var c = n.cloneNode(false); walk(n).forEach(function (x) { c.appendChild(x); }); out.push(c); }
      });
      return out;
    }
    var kids = walk(el); el.innerHTML = '';
    kids.forEach(function (k) { el.appendChild(k); });
    // no per-word cascade — every word wipes in together (reveal style 9).
    // Set explicitly rather than omitted so nothing inherits a stray delay.
    Array.prototype.forEach.call(el.querySelectorAll('.wi'), function (wi) {
      wi.style.transitionDelay = '0s';
    });
  }
  if (!reduced) {
    // section headings + the sub-headings that previously only cross-faded.
    // Deliberately NOT applied to: .hero__title (has its own line treatment),
    // .faq__q (a <summary> — it is an interactive control, leave its DOM alone),
    // or body copy (word-splitting paragraphs reads as a gimmick, not as craft).
    document.querySelectorAll('.statement, .cta__title, .dhero__title, .pillar h3, .news__title')
      .forEach(function (el) { splitWords(el); io.observe(el); });
  }

  /* ---------- custom cursor ---------- */
  (function () {
    if (reduced || !window.matchMedia('(hover:hover)').matches) return;
    var cur = document.getElementById('cursor'); if (!cur) return;
    var x = window.innerWidth / 2, y = window.innerHeight / 2, cx = x, cy = y, shown = false;
    window.addEventListener('pointermove', function (e) {
      if (e.pointerType === 'touch') return;
      x = e.clientX; y = e.clientY;
      if (!shown) { shown = true; cur.classList.add('on'); }
    }, { passive: true });
    (function loop() { cx += (x - cx) * 0.22; cy += (y - cy) * 0.22; cur.style.left = cx + 'px'; cur.style.top = cy + 'px'; requestAnimationFrame(loop); })();
    var hotSel = 'a,button,[data-cursor],.geopin,.atlas__names button';
    document.addEventListener('mouseover', function (e) { if (e.target.closest(hotSel)) cur.classList.add('hot'); });
    document.addEventListener('mouseout', function (e) { if (e.target.closest(hotSel)) cur.classList.remove('hot'); });
  })();

  /* ---------- magnetic buttons ---------- */
  (function () {
    if (reduced || !window.matchMedia('(hover:hover)').matches) return;
    document.querySelectorAll('[data-magnetic]').forEach(function (btn) {
      btn.addEventListener('pointermove', function (e) {
        var r = btn.getBoundingClientRect();
        var mx = e.clientX - (r.left + r.width / 2), my = e.clientY - (r.top + r.height / 2);
        btn.style.transform = 'translate(' + (mx * 0.28).toFixed(1) + 'px,' + (my * 0.42).toFixed(1) + 'px)';
      });
      btn.addEventListener('pointerleave', function () { btn.style.transform = ''; });
    });
  })();

  /* ---------- loader + page-transition curtain ---------- */
  (function () {
    var loader = document.getElementById('loader'); if (!loader) return;

    /* The intro belongs to arriving at the HOME PAGE, nothing else.
         - inner pages (news / article / team) never show it
         - home shows it once per browser tab, so coming back from an article
           does not replay the 2.3s logo sequence
       A new tab or a fresh visit gets the intro again. The outgoing curtain
       still plays on every internal link, so transitions stay smooth. */
    var SEEN = 'asfar:intro';
    var isInner = document.body.classList.contains('page--inner');
    var seen = false;
    try { seen = sessionStorage.getItem(SEEN) === '1'; } catch (e) {}
    /* A wheel flick during the 2.3s curtain scrolled the page behind it, so the
       wipe handed the reader Strategic Investments instead of the hero. Hold
       the scroll for the length of the intro — by swallowing the gestures, not
       by setting overflow:hidden, which would collapse a classic scrollbar and
       shift the whole layout underneath the curtain. */
    var SCROLLKEYS = { 32: 1, 33: 1, 34: 1, 35: 1, 36: 1, 38: 1, 40: 1 };
    var holding = false;
    function eat(e) { e.preventDefault(); }
    function eatKey(e) { if (SCROLLKEYS[e.keyCode]) e.preventDefault(); }
    /* cancelling the gesture is not enough on its own — a scrollbar drag, a
       trackpad fling the compositor has already applied, or a focus jump all
       move the page anyway. Pin the position too; the curtain is opaque, so
       the snap-back is never seen. */
    function pin() { if (holding && (window.scrollY || 0) !== 0) window.scrollTo(0, 0); }
    function hold() {
      if (holding) return;
      holding = true;
      window.addEventListener('wheel', eat, { passive: false });
      window.addEventListener('touchmove', eat, { passive: false });
      window.addEventListener('keydown', eatKey, { passive: false });
      window.addEventListener('scroll', pin, { passive: true });
    }
    function release() {
      if (!holding) return;
      holding = false;
      window.removeEventListener('wheel', eat, { passive: false });
      window.removeEventListener('touchmove', eat, { passive: false });
      window.removeEventListener('keydown', eatKey, { passive: false });
      window.removeEventListener('scroll', pin, { passive: true });
      if ((window.scrollY || 0) !== 0) window.scrollTo(0, 0);
    }
    function reveal() { setTimeout(function () { release(); loader.classList.add('is-out'); }, 2300); }
    if (isInner || seen) {
      loader.classList.add('is-instant', 'is-out');
    } else {
      /* only when the visit really starts at the top — an arriving #hash owns
         the scroll position and must not be fought */
      if (!location.hash && (window.scrollY || 0) < 4) hold();
      try { sessionStorage.setItem(SEEN, '1'); } catch (e) {}
      if (document.readyState === 'complete') reveal(); else window.addEventListener('load', reveal);
      setTimeout(reveal, 3600); // safety
    }
    if (reduced) return;
    document.querySelectorAll('a[href]').forEach(function (a) {
      var href = a.getAttribute('href');
      if (!href || href.charAt(0) === '#' || href.indexOf('http') === 0 || href.indexOf('mailto') === 0 || href.indexOf('tel') === 0) return;
      if (a.getAttribute('target') === '_blank') return;
      a.addEventListener('click', function (e) {
        if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey || e.defaultPrevented) return;
        e.preventDefault();
        /* is-instant sets display:none so the arriving page shows no intro — but
           it also made the outgoing CURTAIN unpaintable, so a click sat dead for
           640ms and then jumped. Drop it before animating. */
        loader.classList.remove('is-instant');
        loader.classList.remove('is-out'); loader.classList.add('is-in');
        requestAnimationFrame(function () { requestAnimationFrame(function () {
          loader.classList.remove('is-in'); loader.classList.add('is-cover');
        }); });
        setTimeout(function () { window.location.href = href; }, 640);
      });
    });
  })();

  /* ---------- leadership: group tabs filter the member grid ---------- */
  (function () {
    var tablist = document.querySelector('.team__tabs');
    if (!tablist) return;
    var tabs = Array.prototype.slice.call(tablist.querySelectorAll('.team__tab'));
    var members = Array.prototype.slice.call(document.querySelectorAll('.team__grid .member'));
    if (!tabs.length || !members.length) return;
    function apply(group) {
      members.forEach(function (m) {
        var groups = (m.getAttribute('data-group') || '').split(/\s+/);
        m.hidden = groups.indexOf(group) === -1;
      });
    }
    tabs.forEach(function (t) {
      t.addEventListener('click', function () {
        tabs.forEach(function (x) { x.classList.remove('is-active'); x.setAttribute('aria-selected', 'false'); });
        t.classList.add('is-active');
        t.setAttribute('aria-selected', 'true');
        apply(t.getAttribute('data-group'));
        // the rail's visible-card set just changed -> re-measure and go home
        if (window.__carousels && window.__carousels.team) window.__carousels.team.reset();
      });
    });
    var active = tablist.querySelector('.team__tab.is-active') || tabs[0];
    apply(active.getAttribute('data-group'));
  })();

  /* ---------- video lightbox (hero "Watch film" → plays the source with sound) ---------- */
  (function () {
    var triggers = Array.prototype.slice.call(document.querySelectorAll('[data-video]'));
    if (!triggers.length) return;
    var box = null, vid = null;
    function build(src) {
      box = document.createElement('div');
      box.className = 'vlb';
      box.setAttribute('role', 'dialog');
      box.setAttribute('aria-modal', 'true');
      var frame = document.createElement('div');
      frame.className = 'vlb__frame';
      var btn = document.createElement('button');
      btn.className = 'vlb__close';
      btn.type = 'button';
      /* the Arabic page must not announce an English control name */
      btn.setAttribute('aria-label',
        asfarSettings.close);
      btn.innerHTML = '&times;';
      vid = document.createElement('video');
      vid.setAttribute('controls', '');
      vid.setAttribute('playsinline', '');
      vid.src = src;
      frame.appendChild(btn);
      frame.appendChild(vid);
      box.appendChild(frame);
      document.body.appendChild(box);
      box.addEventListener('click', function (e) {
        if (e.target === box || e.target === btn) close();
      });
    }
    function open(src) {
      if (!box) build(src);
      else if (vid.src !== src) vid.src = src;
      box.classList.add('is-open');
      document.body.style.overflow = 'hidden';
      try { vid.currentTime = 0; } catch (e) {}
      var p = vid.play(); if (p && p.catch) p.catch(function () {});
    }
    function close() {
      if (!box) return;
      box.classList.remove('is-open');
      document.body.style.overflow = '';
      vid.pause();
    }
    triggers.forEach(function (t) {
      t.addEventListener('click', function (e) {
        e.preventDefault();
        open(t.getAttribute('data-video') || t.getAttribute('href'));
      });
    });
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && box && box.classList.contains('is-open')) close();
    });
  })();

  /* ---------- boot ---------- */
  initLenis();
  setActive(0);
  parallax();
  // defer the heavy morph-frame preload so the hero (image + video) wins first paint;
  // frames start streaming as the visitor approaches the section, with a load fallback.
  if (flyEl && flyCanvas) {
    flyResize();
    var fio = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) { if (en.isIntersecting) { flyPreload(); fio.disconnect(); } });
    }, { rootMargin: '250% 0px 250% 0px' });
    fio.observe(flyEl);
    window.addEventListener('load', function () { setTimeout(flyPreload, 1800); });
  }
  onScroll();
})();

/* ---------- footer contact form ----------
   Static site: there is no backend to POST to, so rather than silently
   swallowing a message we compose a prefilled email. Swap this for a real
   endpoint (Formspree / Netlify Forms / own API) when one exists.
   All user-facing strings come off data-* attributes so each locale
   renders only its own language. */
(function () {
  var form = document.getElementById('contactForm');
  if (!form) return;
  var note = form.querySelector('.footer__form-note');


  function fieldOf(el) { return el.closest ? el.closest('.ffield') : null; }

  form.addEventListener('input', function (e) {
    var f = fieldOf(e.target); if (f) f.classList.remove('is-bad');
  });

  /* member profile overlay moved to top level — it lived inside the
     contact-form IIFE, whose `if (!form) return;` silenced it the moment the
     deck-skin footer replaced the old form. */
  

  /* ---- disclosure: the footer shows an icon, the form opens over the page ----
     The panel is absolutely positioned, so opening it never grows the footer.
     Closes on Escape and on outside click. Deliberately NOT on scroll: the panel
     is absolutely positioned inside the trigger, so it travels with the footer
     and never drifts — and a scroll-close would slam it shut on mobile the
     moment focusing a field raises the keyboard. */
  var wrap = form.closest ? form.closest('[data-enquire]') : null;
  var btn = document.getElementById('enquireBtn');
  if (!wrap || !btn) return;

  function setOpen(open) {
    wrap.classList.toggle('is-open', open);
    btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      /* focus() is a no-op while the panel is still visibility:hidden, and the
         next frame is too early — the transition has not resolved yet. Wait for
         transitionend, with a timer fallback for reduced-motion / no transition. */
      var first = form.querySelector('input,textarea');
      if (first) {
        var done = false;
        var give = function () {
          if (done) return;
          done = true;
          form.removeEventListener('transitionend', give);
          first.focus({ preventScroll: true });
        };
        form.addEventListener('transitionend', give);
        setTimeout(give, 340);
      }
    }
  }
  function close(refocus) {
    if (!wrap.classList.contains('is-open')) return;
    setOpen(false);
    if (refocus) btn.focus();
  }
  /* the "Partner with us" nav link opens THIS footer form instead of launching
     an email client */
  window.__openEnquire = function () { setOpen(true); };
  document.addEventListener('click', function (e) {
    var a = e.target && e.target.closest ? e.target.closest('[data-partner]') : null;
    if (!a) return;
    /* the generic in-page anchor handler already scrolls to #contact and closes
       the menu; wait for that, then open and focus the form */
    setTimeout(function () { setOpen(true); }, 620);
  });

  btn.addEventListener('click', function () {
    setOpen(!wrap.classList.contains('is-open'));
  });
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape' || e.keyCode === 27) close(true);
  });
  document.addEventListener('click', function (e) {
    if (!wrap.contains(e.target)) close(false);
  });
})();

/* nav height -> --navh. The nav is position:fixed, so a section that is exactly
   100svh has its top 90px sitting UNDER it — which is what hid the map's title.
   Full-screen sections reserve this much at the top. --navh was already
   referenced by .article__aside but nothing ever set it. */
(function () {
  var n = document.getElementById('nav');
  if (!n) return;
  /* Measure the SCROLLED height, not the current one. The nav shrinks once the
     page moves (a smaller logo under .is-scrolled), and the map section — the
     only consumer — is always viewed scrolled. Publishing the live height gave
     110px at the top of the page, which showed up as dead space above the map
     title; it would also relayout the section mid-scroll as the nav shrank. */
  function set() {
    var had = n.classList.contains('is-scrolled');
    /* .nav__logo-img animates its height (56 -> 44), so toggling the class and
       reading offsetHeight returns the PRE-transition value. Kill the transition
       for the measurement, otherwise --navh comes back 110 instead of 90. */
    var logos = n.querySelectorAll('.nav__logo-img');
    var i;
    for (i = 0; i < logos.length; i++) logos[i].style.transition = 'none';
    if (!had) n.classList.add('is-scrolled');
    var h = n.offsetHeight;
    if (!had) n.classList.remove('is-scrolled');
    for (i = 0; i < logos.length; i++) logos[i].style.transition = '';
    document.documentElement.style.setProperty('--navh', h + 'px');
  }
  set();
  window.addEventListener('load', set);
  setTimeout(set, 700);
  window.addEventListener('resize', set);
})();

/* footer height -> --footer-h, so the FAQ + footer form one exact screen.
   Re-measured on resize and (where supported) when the footer itself
   changes height (e.g. the contact panel wrapping at another width). */
(function () {
  var f = document.querySelector('.footer');
  if (!f) return;
  function set() {
    document.documentElement.style.setProperty('--footer-h', f.offsetHeight + 'px');
  }
  set();
  window.addEventListener('resize', set);
  if (window.ResizeObserver) { try { new ResizeObserver(set).observe(f); } catch (e) {} }
})();

/* ---------- member profile: an overlay grown from the card ----------
     Opens from a card's name button or its portrait. The panel floats over the
     section rather than resizing a card, so the grid never reflows and every
     member gets an identical panel wherever they sit in the rail. Everything
     shown comes off the card's data-* attributes, which are written from
     WordPress fields — so a biography added there appears here with no
     code change. Nothing is invented: a member with no bio yet gets an explicit
     "to follow" line. */
  (function () {
    var cards = document.querySelectorAll('.member--opens');
    if (!cards.length) return;
    var rtl = document.documentElement.dir === 'rtl';
    var COPY = asfarSettings;

    var panel = null, veil = null, current = null, lastFocus = null;

    function build() {
      panel = document.createElement('div');
      panel.className = 'mexp';
      panel.id = 'memberProfile';
      panel.setAttribute('role', 'region');
      panel.setAttribute('aria-label', COPY.label);
      panel.innerHTML =
        '<div class="mexp__in">' +
          '<span class="mexp__ph"></span>' +
          '<h3 class="mexp__name"></h3>' +
          '<p class="mexp__role"></p>' +
          '<p class="mexp__text"></p>' +
          '<button type="button" class="mexp__close">×</button>' +
        '</div>';
      panel.querySelector('.mexp__close').setAttribute('aria-label', COPY.close);
      veil = document.createElement('div');
      veil.className = 'mexp__veil';
      panel.querySelector('.mexp__close').addEventListener('click', function () { close(); });
      veil.addEventListener('click', function () { close(); });
    }

    function initials(name) {
      var parts = (name || '').trim().split(/\s+/), picks = [];
      for (var i = 0; i < parts.length && picks.length < 2; i++) {
        var t = parts[i];
        if (!rtl && /^(al|el|bin|ibn|dr)\.?$/i.test(t)) continue;
        if (rtl && t.indexOf('ال') === 0 && t.length > 2) picks.push(t.charAt(2));
        else if (!rtl && t.length > 3 && t.slice(0, 2).toLowerCase() === 'al' &&
                 t.charAt(2) === t.charAt(2).toUpperCase()) picks.push(t.charAt(2));
        else picks.push(t.charAt(0));
      }
      return rtl ? picks.join(' ') : picks.join('').toUpperCase();
    }

    function fill(card) {
      var name = card.getAttribute('data-name') || '';
      var role = card.getAttribute('data-role') || '';
      var photo = card.getAttribute('data-photo') || '';
      var bio = (card.getAttribute('data-bio') || '').trim();
      var ph = panel.querySelector('.mexp__ph');
      if (photo) {
        ph.className = 'mexp__ph';
        ph.replaceChildren(); var portrait = document.createElement('img'); portrait.src = photo; portrait.alt = ''; ph.appendChild(portrait);
      } else {
        ph.className = 'mexp__ph mexp__ph--empty';
        ph.innerHTML = '<i aria-hidden="true">' + initials(name) + '</i>';
      }
      panel.querySelector('.mexp__name').textContent = name;
      panel.querySelector('.mexp__role').textContent = role;
      var txt = panel.querySelector('.mexp__text');
      txt.textContent = bio || COPY.empty;
      txt.className = bio ? 'mexp__text' : 'mexp__text mexp__text--empty';
    }

    /* Centre the panel across the section and align it to the row the card is
       in, then point its transform-origin at that card so the growth starts
       from the portrait that was clicked. Centring is what makes every member
       get the same panel in the same place — anchoring it to the card itself
       put it half off-screen for anyone near the end of the rail. */
    function place(card) {
      var host = card.closest('.team.section') || card.closest('.teampage');
      if (!host) host = card.closest('section') || document.body;
      if (panel.parentNode !== host) { host.appendChild(veil); host.appendChild(panel); }
      var hr = host.getBoundingClientRect(), cr = card.getBoundingClientRect();
      var pw = panel.offsetWidth, ph = panel.offsetHeight;
      var left = Math.round((hr.width - pw) / 2);
      var top = Math.round((cr.top - hr.top) + (cr.height - ph) / 2);
      top = Math.max(12, Math.min(top, Math.round(hr.height - ph - 12)));
      panel.style.left = left + 'px';
      panel.style.top = top + 'px';
      /* Clamp the origin to the panel itself. A card far along the rail sits
         hundreds of pixels outside the panel box, and scaling from a point that
         far away throws the panel across the screen instead of growing it. At
         the edge it still grows from the side the card is on. */
      var ox = cr.left + cr.width / 2 - hr.left - left;
      var oy = cr.top + cr.height / 2 - hr.top - top;
      panel.style.setProperty('--mexp-ox', Math.round(Math.max(0, Math.min(pw, ox))) + 'px');
      panel.style.setProperty('--mexp-oy', Math.round(Math.max(0, Math.min(ph, oy))) + 'px');
    }

    function mark(card, open) {
      if (!card) return;
      card.classList.toggle('is-open', open);
      var btn = card.querySelector('.member__name');
      if (btn) btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    }

    function open(card) {
      if (!panel) build();
      fill(card);
      place(card);
      mark(current, false);
      current = card;
      mark(card, true);
      lastFocus = document.activeElement;
      /* force a reflow, then flip the classes synchronously. Doing it inside
         requestAnimationFrame left the panel stuck invisible whenever rAF was
         deferred, i.e. in a backgrounded tab. */
      void panel.offsetHeight;
      veil.classList.add('is-open');
      panel.classList.add('is-open');
      var btn = panel.querySelector('.mexp__close');
      if (btn) btn.focus({ preventScroll: true });
    }

    function close() {
      if (!panel || !current) return;
      panel.classList.remove('is-open');
      veil.classList.remove('is-open');
      mark(current, false);
      current = null;
      if (lastFocus && lastFocus.focus) lastFocus.focus({ preventScroll: true });
    }

    document.addEventListener('click', function (e) {
      var t = e.target.closest('.member__name, .member--opens .member__ph');
      if (!t) return;
      var card = t.closest('.member--opens');
      if (!card) return;
      e.preventDefault();
      if (card === current) close();
      else open(card);
    });
    document.addEventListener('keydown', function (e) {
      if ((e.key === 'Escape' || e.keyCode === 27) && current) close();
    });
    /* paging the rail or switching tab moves the card out from under the
       panel, so the panel goes with it */
    document.addEventListener('click', function (e) {
      if (!current) return;
      if (e.target.closest('.team__tab, .js-carbtn, .team__navbtn')) close();
    });
    window.addEventListener('resize', function () {
      if (current) place(current);
    }, { passive: true });
  })();


/* -- opening the media centre / an article: the deck's cover-up ----------
   p:cover dir="u", 1250ms. In the deck the WHOLE incoming slide rises over
   the outgoing one -- not a blank panel. So the destination is fetched into
   a full-viewport iframe parked below the fold, and only once it has fired
   load (menu, photos and all) does it slide up into place; the real
   navigation is committed underneath it, so the page it lands on is the
   page that just slid in. Lives in app.js because the article pages load
   app.js but not deck.js. Sequenced with reflow + class flips, never rAF --
   rAF is deferred in a backgrounded tab. */
(function () {
  var reduced = window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (reduced) return;
  var COVER = 1250;        /* the deck's cover duration */
  var SETTLE = 120;        /* one beat at rest so the arriving view paints */
  var STALL  = 2600;       /* never strand the click if the fetch hangs */
  var busy = false;

  function slideTo(href) {
    if (busy) return;
    busy = true;
    var frame = document.createElement('iframe');
    frame.className = 'dk-slide';
    frame.setAttribute('aria-hidden', 'true');
    frame.setAttribute('tabindex', '-1');
    frame.setAttribute('scrolling', 'no');
    document.documentElement.classList.add('dk-sliding');
    document.body.appendChild(frame);

    var started = false;
    function run() {
      if (started) return;
      started = true;
      void frame.offsetHeight;             /* force layout before the flip */
      frame.classList.add('dk-slide--anim');
      void frame.offsetHeight;
      frame.classList.add('is-up');
      /* commit the navigation once the arriving view owns the screen; the
         document underneath swaps while the frame still covers it, so the
         hand-over is invisible */
      setTimeout(function () { location.href = href; }, COVER + 80);
    }
    frame.addEventListener('load', function () {
      /* the panel rises with its own fixed nav on its leading edge, which read
         as a second menu bar climbing the screen. Same origin, so switch it off
         inside the frame — the real page shows its nav once it has landed. */
      try {
        var doc = frame.contentDocument;
        if (doc && doc.head) {
          var st = doc.createElement('style');
          st.textContent = '.nav{opacity:0!important;visibility:hidden!important}';
          doc.head.appendChild(st);
        }
      } catch (err) {}
      setTimeout(run, SETTLE);
    });
    setTimeout(run, STALL);
    frame.src = href;
  }

  /* When the cover-up commits the navigation, this page keeps the .dk-slide
     iframe and html.dk-sliding (overflow:hidden). On BACK the browser restores
     this page from bfcache with both still in place: the leftover iframe covers
     the screen and the frozen overflow kills scrolling, so the page looks stuck.
     Clear that state every time the page is shown or regains focus. */
  function clearSlideState() {
    document.documentElement.classList.remove('dk-sliding');
    var f = document.querySelector('.dk-slide');
    if (f && f.parentNode) f.parentNode.removeChild(f);
    busy = false;
  }
  window.addEventListener('pageshow', clearSlideState);
  window.addEventListener('popstate', clearSlideState);
  document.addEventListener('visibilitychange', function () {
    if (!document.hidden) clearSlideState();
  });

  document.addEventListener('click', function (e) {
    if (e.defaultPrevented || e.button !== 0) return;
    if (e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) return;
    var a = e.target && e.target.closest ? e.target.closest('a[href]') : null;
    if (!a || a.hasAttribute('download')) return;
    if (a.target && a.target !== '_self') return;
    var raw = a.getAttribute('href') || '';
    if (!a.closest('.dk-news, .newspage, .newsarticle, .dk-article')) return;          /* articles + the news index */
    var url;
    try { url = new URL(a.href, location.href); } catch (err) { return; }
    if (url.origin !== location.origin) return;
    if (url.pathname === location.pathname) return;
    e.preventDefault();
    slideTo(url.href);
  }, true);
})();
