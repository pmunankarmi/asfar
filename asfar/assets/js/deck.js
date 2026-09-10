/* ============================================================
   ASFAR — deck-skin behaviours + slideshow flow engine.

   The Prezlab deck plays as a full-viewport slideshow: each
   advance MORPHS the whole view into the next state. This file
   reproduces that flow on the website. Timings are the deck's
   own transition spec, read from each slide's XML — not taste:

     morph  slow 2000ms (p159:morph byObject)
        → hero scene slide-in · scroll between section states ·
          map region change · sector strip · FAQ accordion
     cover  up 1250ms (p:cover, slide 14)  → news page 2
     cover  up 1750ms (p:cover, slides 16/18) → team, partners
     fade   med 700ms (p:fade, slide 17)   → biography overlay

   The page scrolls natively — the deck's transitions live where a
   website wants them: the self-playing hero film, the map's region
   morph (auto-advancing while in view), the sector strip, news
   paging, the FAQ accordion and the biography overlay.

   rAF is never used to sequence a transition — it is deferred
   in backgrounded tabs and has burned this project repeatedly.
   Class flips land after a forced reflow instead. */
(function () {
  'use strict';
  var MORPH = 2000, COVER_NEWS = 1250, COVER_PAGE = 1750;
  var reduced = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var rtl = document.documentElement.dir === 'rtl';
  var DK = window.__dk = {};

  function reflow(el) { void el.offsetHeight; }
  function clamp(v, a, b) { return Math.max(a, Math.min(b, v)); }

  /* the morph's cubic-bezier(.45,.05,.25,1), solved for JS scroll animation */
  function bez(p1x, p1y, p2x, p2y) {
    function f(t, a, b) { var u = 1 - t; return 3 * u * u * t * a + 3 * u * t * t * b + t * t * t; }
    return function (x) {
      var t = x;
      for (var i = 0; i < 6; i++) {
        var cx = f(t, p1x, p2x) - x;
        var dx = 3 * (1 - t) * (1 - t) * p1x + 6 * (1 - t) * t * (p2x - p1x) + 3 * t * t * (1 - p2x);
        if (Math.abs(dx) < 1e-6) break;
        t -= cx / dx; t = clamp(t, 0, 1);
      }
      return f(t, p1y, p2y);
    };
  }
  var EASE = bez(.45, .05, .25, 1);

  /* ---------- scroll entrances ---------- */
  (function () {
    var els = document.querySelectorAll('.dk-rise');
    if (!els.length) return;
    if (!('IntersectionObserver' in window) || reduced) {
      els.forEach(function (e) { e.classList.add('is-in'); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add('is-in'); io.unobserve(en.target); }
      });
    }, { rootMargin: '0px 0px -12% 0px' });
    els.forEach(function (e) { io.observe(e); });
  })();

  /* ---------- HERO: deck layer over the auto-playing film ----------
     app.js runs the hero as recorded by the deck's animated slide: a 5s
     statement beat, then the 17s canvas film through the four
     destinations, looping (hero gets .is-film while playing, progress in
     window.__heroFilm). This layer adds the deck's slide-2 chrome:
     - the tree photo behind the statement beat (slide 2's background),
     - the film sliding in horizontally over it — the slide 2 -> 3 morph —
       driven by the .is-film class at the morph's own 2000ms,
     - the five dash indicators (statement + four destinations). */
  (function () {
    var hero = document.getElementById('heroSlider');
    if (!hero) return;
    var dashes = Array.prototype.slice.call(hero.querySelectorAll('.dk-dashes span, .dk-dashes button'));
    function paint(i) {
      dashes.forEach(function (d, n) { d.classList.toggle('is-active', n === i); });
    }
    var nav = document.querySelector('header.nav');
    paint(0);
    setInterval(function () {
      var f = window.__heroFilm;
      var filmOn = hero.classList.contains('is-film');
      /* slide 3: a solid #154751 band sits behind the nav while the film runs.
         The film LOOPS (leave() re-enters after INTRO_MS), so keying the band
         on it alone leaves the bar solid at the very top of the page once the
         first cycle has run — scroll down, come back, and the menu never goes
         away again. The top of the page is slide 2, whose nav is transparent
         with the hairline, so the band is held off there. */
      var atPageTop = (window.scrollY || 0) < 6;
      if (nav) nav.classList.toggle('is-band',
        !atPageTop && filmOn && window.scrollY < hero.offsetHeight * .5);
      if (!filmOn || !f || !f.playing) { paint(0); return; }
      paint(1 + Math.min(3, Math.round((f.p || 0) * 3)));
    }, 300);
  })();

  /* ---------- sector strip (morph 2000ms) ---------- */
  (function () {
    var root = document.getElementById('dkSectors');
    if (!root) return;
    var track = root.querySelector('.dk-sectors__track');
    var cards = Array.prototype.slice.call(track.children);
    if (!cards.length) return;
    var prev = root.querySelector('[data-dir="-1"]');
    var next = root.querySelector('[data-dir="1"]');
    var current = 0;

    function step() {
      var gap = parseFloat(getComputedStyle(track).columnGap || getComputedStyle(track).gap) || 0;
      return cards[0].getBoundingClientRect().width + gap;
    }
    /* How far the track can travel: stop when the LAST card sits flush with
       the content edge. Measuring the rail is wrong — it now overhangs the
       wrap to the viewport edge, so its width is larger than what is on
       screen and the rail stopped early, clipping the final card. */
    function maxShift() {
      var rail = track.parentElement.getBoundingClientRect();
      var gutter = parseFloat(getComputedStyle(root.querySelector('.dk-wrap') || root).paddingInlineStart) || 0;
      var avail = rtl ? (rail.right - gutter) : (window.innerWidth - gutter - rail.left);
      var trackW = cards.length * step() - (step() - cards[0].getBoundingClientRect().width);
      return Math.max(0, trackW - avail);
    }
    function maxIndex() {
      return Math.ceil(maxShift() / step() - 0.01);
    }
    /* how many whole cards fit in the visible window */
    function perView() {
      var s = step(), w = cards[0].getBoundingClientRect().width;
      var rail = track.parentElement.getBoundingClientRect();
      var gutter = parseFloat(getComputedStyle(root.querySelector('.dk-wrap') || root).paddingInlineStart) || 0;
      var avail = rtl ? (rail.right - gutter) : (window.innerWidth - gutter - rail.left);
      return Math.max(1, Math.floor((avail - w) / s) + 1);
    }
    /* The slab sits on the leading card — the first one on the left (the
       first on the right in Arabic) — and stays there; paging the rail moves
       it to whichever card is now leading. Hover takes over, in CSS. */
    var lit = 0;

    function paint() {
      var shift = Math.min(current * step(), maxShift());
      track.style.transform = 'translateX(' + ((rtl ? 1 : -1) * shift) + 'px)';
      cards.forEach(function (c, n) { c.classList.toggle('is-label', n === lit); });
      if (prev) prev.disabled = current <= 0;
      if (next) next.disabled = current >= maxIndex();
    }
    function go(i) {
      current = clamp(i, 0, maxIndex());
      lit = current;
      paint();
    }
    if (prev) prev.addEventListener('click', function () { go(current - 1); });
    if (next) next.addEventListener('click', function () { go(current + 1); });
    window.addEventListener('resize', paint, { passive: true });

    paint();
    DK.secGo = go;
    DK.secIdx = function () { return current; };
  })();

  /* ---------- news rail (morph 2000ms, slides 12–13) ----------
     The deck's home news is ONE row of cards; the arrows slide the strip
     laterally at the morph's 2000ms (slide 12 -> 13 moves it ~2.5 cards;
     two cards per click reads the same). The 1250ms cover-up belongs to
     the View All News page change, not to in-section paging. */
  (function () {
    var root = document.getElementById('dkNews');
    if (!root) return;
    var strip = root.querySelector('.dk-news__strip');
    if (!strip) return;
    var cards = Array.prototype.slice.call(strip.children);
    if (!cards.length) return;
    var prev = root.querySelector('[data-dir="-1"]');
    var next = root.querySelector('[data-dir="1"]');
    var current = 0;

    function pitch() {
      var gap = parseFloat(getComputedStyle(strip).columnGap || getComputedStyle(strip).gap) || 0;
      return cards[0].getBoundingClientRect().width + gap;
    }
    function perView() {
      var gap = parseFloat(getComputedStyle(strip).columnGap || getComputedStyle(strip).gap) || 0;
      var vw = strip.parentElement.getBoundingClientRect().width;
      /* n cards span n*pitch - gap, so add the gap back before dividing --
         without it a rail sized for exactly four reports three */
      return Math.max(1, Math.floor((vw + gap + 2) / pitch()));
    }
    function maxIndex() { return Math.max(0, cards.length - perView()); }
    function paint() {
      strip.style.transform = 'translateX(' + ((rtl ? 1 : -1) * current * pitch()) + 'px)';
      if (prev) prev.disabled = current <= 0;
      if (next) next.disabled = current >= maxIndex();
    }
    function go(i) { current = clamp(i, 0, maxIndex()); paint(); }
    /* the rail now holds exactly one view of cards, so a click is a page */
    if (prev) prev.addEventListener('click', function () { go(current - perView()); });
    if (next) next.addEventListener('click', function () { go(current + perView()); });
    window.addEventListener('resize', paint, { passive: true });
    paint();
    DK.newsGo = go;
  })();

  /* ---------- FAQ accordion (morph 2000ms) ---------- */
  (function () {
    var list = document.getElementById('dkFaq');
    if (!list) return;
    var items = Array.prototype.slice.call(list.querySelectorAll('.dk-faq__item'));
    function setH(wrap, h) { wrap.style.height = h + 'px'; }
    /* ⚠ the opening listener MUST be dropped when the row is closed again
       before its own transition finishes. Opening #1 and then #3 300ms later
       left #1's listener armed; the CLOSE transition ended, the stale handler
       fired and set height:auto — so a row with no .is-open sat 148 tall with
       its answer showing through and no panel behind it. */
    function clearPending(w) {
      if (w._faqDone) { w.removeEventListener('transitionend', w._faqDone); w._faqDone = null; }
      if (w._faqTimer) { clearTimeout(w._faqTimer); w._faqTimer = null; }
    }
    function closeItem(o) {
      var w = o.querySelector('.dk-faq__awrap');
      clearPending(w);
      setH(w, w.scrollHeight); reflow(w);
      o.classList.remove('is-open');
      setH(w, 0);
      o.querySelector('.dk-faq__btn').setAttribute('aria-expanded', 'false');
    }
    function toggle(item) {
      var btn = item.querySelector('.dk-faq__btn');
      var wrap = item.querySelector('.dk-faq__awrap');
      var open = item.classList.contains('is-open');
      items.forEach(function (o) {
        if (o !== item && o.classList.contains('is-open')) closeItem(o);
      });
      if (open) { closeItem(item); return; }
      clearPending(wrap);
      item.classList.add('is-open');
      setH(wrap, wrap.scrollHeight);
      btn.setAttribute('aria-expanded', 'true');
      /* settle to auto so the answer reflows with the viewport instead of
         holding the pixel height it opened at */
      var settle = function () {
        if (!item.classList.contains('is-open')) return;
        wrap.style.height = 'auto';
        clearPending(wrap);
      };
      var done = function (e) {
        if (e.target !== wrap || e.propertyName !== 'height') return;
        settle();
      };
      wrap._faqDone = done;
      /* a rapid second click can swallow the transitionend entirely */
      wrap._faqTimer = setTimeout(settle, MORPH + 120);
      wrap.addEventListener('transitionend', done);
    }
    items.forEach(function (item) {
      item.querySelector('.dk-faq__btn').addEventListener('click', function () { toggle(item); });
    });
    DK.faqOpen = function (i) {
      var it = items[i];
      if (it && !it.classList.contains('is-open')) toggle(it);
    };
    DK.faqIsOpen = function (i) { return items[i] && items[i].classList.contains('is-open'); };
    DK.faqCloseAll = function () { items.forEach(function (o) { if (o.classList.contains('is-open')) closeItem(o); }); };
  })();

  /* ---------- back to top ---------- */
  (function () {
    var t = document.getElementById('dkTop');
    if (!t) return;
    t.addEventListener('click', function (e) {
      e.preventDefault();
      if (window.__lenis && window.__lenis.scrollTo) window.__lenis.scrollTo(0, { duration: 1.4 });
      else window.scrollTo({ top: 0, behavior: reduced ? 'auto' : 'smooth' });
    });
  })();

})();



/* Sectors -> News: a single, static curved divider. The earlier version
   animated the divider up the screen with a second static twin behind it,
   which showed a lighter cream band between the two wave layers mid-scroll.
   One wave, one clean colour transition (cream -> card), no climb, no twin. */

/* ---------- section covers: each new slide rises over the last ----------
   slide16.xml and slide18.xml both carry
     <p:transition spd="slow" p14:dur="1750"><p:cover dir="u"/>
   so THE ASFAR TEAM covers the news section and OUR PARTNERS covers the team.
   Each section is pulled up by one lift (a negative top margin, in CSS) and
   pushed back down here by exactly the same amount, so at rest it sits where
   it always did and there is never a gap to see through. Over the last 0.62
   of a viewport before it lands, that push unwinds to zero — the leading edge
   crosses a whole viewport while the page scrolls 0.62 of one, which is what
   reads as a cover. Each edge carries the main menu, cloned from the real nav,
   the same way the article page-slide brings the incoming page's nav up.

   Everything is a pure function of scrollY: no sequencing, no timers, so a
   backgrounded tab (where rAF is deferred) cannot strand a section mid-slide.
   rAF only coalesces scroll events, and every wake re-reads. */
(function () {
  var reduced = window.matchMedia &&
    window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  var narrow = window.matchMedia && window.matchMedia('(max-width:960px)');
  var lift = 0, ticking = false;

  function clamp01(v) { return v < 0 ? 0 : (v > 1 ? 1 : v); }

  /* The covers used to carry a cloned menu bar on their leading edge so the
     slide read like the article page-slide. It duplicated the real fixed nav
     for no gain, so the covers now come up bare. */
  /* The cover effect is a chain: THE ASFAR TEAM covers the news section, and
     OUR PARTNERS covers the team. With the team section hidden, that chain is
     broken — leaving it on pulled OUR PARTNERS up over the news section with a
     negative margin, so its straight top edge painted over the news wave. When
     the team is not present, disable the covers entirely so every section sits
     in normal flow and the section waves show. */
  var teamEl = document.getElementById('dkTeam');
  var teamOn = teamEl && !teamEl.hidden && teamEl.getClientRects().length;
  var covers = (teamOn ? ['dkTeam', 'partners'] : []).map(function (id) {
    var el = document.getElementById(id);
    if (!el || el.hidden || !el.getClientRects().length) return null;
    return { el: el, shift: 0 };
  }).filter(Boolean);
  if (!covers.length) return;

  function measure() {
    var off = reduced || (narrow && narrow.matches);
    lift = off ? 0 : (window.innerHeight || 0) * 0.38;
    covers.forEach(function (c) {
      c.el.style.setProperty('--dk-tlift', lift.toFixed(1) + 'px');
      if (off) {
        c.shift = 0;
        c.el.style.transform = '';
      }
    });
  }

  function tick() {
    ticking = false;
    var vh = window.innerHeight || 1;
    covers.forEach(function (c) {
      if (!lift) {
        if (c.shift) { c.shift = 0; c.el.style.transform = ''; }
        return;
      }
      var span = vh - lift;
      /* getBoundingClientRect() reports the TRANSFORMED box, so the push we
         applied has to come back OFF to recover the layout position */
      var natTop = c.el.getBoundingClientRect().top - c.shift;
      var q = clamp01(1 - natTop / span);
      c.shift = (1 - q) * lift;
      var moving = c.shift > 0.4;
      c.el.style.transform = moving
        ? 'translate3d(0,' + c.shift.toFixed(1) + 'px,0)'
        : '';
      /* only while the slide is travelling — once it lands the cloned bar sits
         exactly under the real fixed nav, so dropping it cannot be seen */
    });
  }

  /* slide 2's hairline under the hero nav belongs to a slide at rest */
  function atTop() {
    document.documentElement.classList.toggle('is-attop', (window.scrollY || 0) < 6);
  }

  function onScroll() { atTop(); if (!ticking) { ticking = true; requestAnimationFrame(tick); } }
  addEventListener('scroll', onScroll, { passive: true });
  addEventListener('resize', function () { measure(); tick(); });
  addEventListener('visibilitychange', function () { if (!document.hidden) tick(); });
  addEventListener('pageshow', function () { measure(); atTop(); tick(); });
  if (narrow && narrow.addEventListener) {
    narrow.addEventListener('change', function () { measure(); tick(); });
  }
  measure();
  atTop();
  tick();
})();
