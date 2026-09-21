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

  // Share mouse/touch dragging between the investment and news rails.
  // A click still opens a news card; only a horizontal drag cancels the click.
  function enableRailDragging(track, getIndex, go, getPitch, getMaxShift) {
    var gesture = null;
    var suppressClick = false;

    track.addEventListener('dragstart', function (event) { event.preventDefault(); });
    track.addEventListener('pointerdown', function (event) {
      if (!event.isPrimary || event.button !== 0) return;
      suppressClick = false;
      gesture = {
        pointer: event.pointerId,
        x: event.clientX,
        y: event.clientY,
        index: getIndex(),
        shift: Math.min(getIndex() * getPitch(), getMaxShift()),
        distance: 0,
        dragging: false
      };
    });

    track.addEventListener('pointermove', function (event) {
      if (!gesture || gesture.pointer !== event.pointerId) return;
      var distanceX = event.clientX - gesture.x;
      var distanceY = event.clientY - gesture.y;
      if (!gesture.dragging) {
        if (Math.abs(distanceY) > Math.abs(distanceX) && Math.abs(distanceY) > 8) {
          gesture = null;
          return;
        }
        if (Math.abs(distanceX) < 8) return;
        gesture.dragging = true;
        track.setPointerCapture(event.pointerId);
        track.classList.add('mt-is-dragging');
        track.style.transition = 'none';
      }
      event.preventDefault();
      gesture.distance = distanceX * (rtl ? 1 : -1);
      var shift = clamp(gesture.shift + gesture.distance, 0, getMaxShift());
      track.style.transform = 'translateX(' + (rtl ? shift : -shift) + 'px)';
    });

    function finish(event) {
      if (!gesture || gesture.pointer !== event.pointerId) return;
      var completed = gesture;
      gesture = null;
      track.classList.remove('mt-is-dragging');
      track.style.transition = '';
      if (track.hasPointerCapture(event.pointerId)) track.releasePointerCapture(event.pointerId);
      if (!completed.dragging) return;
      suppressClick = true;
      var steps = Math.round(completed.distance / getPitch());
      if (!steps && Math.abs(completed.distance) > 30) steps = Math.sign(completed.distance);
      go(completed.index + (event.type === 'pointercancel' ? 0 : steps));
    }

    track.addEventListener('pointerup', finish);
    track.addEventListener('pointercancel', finish);
    track.addEventListener('lostpointercapture', finish);
    track.addEventListener('click', function (event) {
      if (!suppressClick) return;
      event.preventDefault();
      event.stopImmediatePropagation();
      suppressClick = false;
    }, true);
  }


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
    var els = document.querySelectorAll(".mt-dk-rise");
    if (!els.length) return;
    if (!('IntersectionObserver' in window) || reduced) {
      els.forEach(function (e) { e.classList.add("mt-is-in"); });
      return;
    }
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (en) {
        if (en.isIntersecting) { en.target.classList.add("mt-is-in"); io.unobserve(en.target); }
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
    var hero = document.getElementById("heroSlider");
    if (!hero || !hero.querySelector("#heroMorphCanvas")) return;
    /* The hero dashes are the SLIDE navigation, owned entirely by app.js
       (3 statement slides, clickable). This layer used to repaint them as
       film-progress every 300ms, which fought the click navigation and left
       the wrong dash lit. It now only manages the nav band behind the film. */
    var nav = document.querySelector("header.mt-nav");
    setInterval(function () {
      var filmOn = hero.classList.contains("mt-is-film");
      var atPageTop = (window.scrollY || 0) < 6;
      if (nav) nav.classList.toggle("mt-is-band",
        !atPageTop && filmOn && window.scrollY < hero.offsetHeight * .5);
    }, 300);
  })();

  /* ---------- sector strip (morph 2000ms) ---------- */
  (function () {
    var root = document.getElementById("dkSectors");
    if (!root) return;
    var track = root.querySelector(".mt-dk-sectors__track");
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
      var gutter = parseFloat(getComputedStyle(root.querySelector(".mt-dk-wrap") || root).paddingInlineStart) || 0;
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
      var gutter = parseFloat(getComputedStyle(root.querySelector(".mt-dk-wrap") || root).paddingInlineStart) || 0;
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
      cards.forEach(function (c, n) { c.classList.toggle("mt-is-label", n === lit); });
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
    enableRailDragging(track, function () { return current; }, go, step, maxShift);
    window.addEventListener('resize', function () { go(current); }, { passive: true });

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
    var root = document.getElementById("dkNews");
    if (!root) return;
    var strip = root.querySelector(".mt-dk-news__strip");
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
    enableRailDragging(strip, function () { return current; }, go, pitch, function () { return maxIndex() * pitch(); });
    window.addEventListener('resize', function () { go(current); }, { passive: true });
    paint();
    DK.newsGo = go;
  })();

  /* ---------- FAQ accordion (morph 2000ms) ---------- */
  (function () {
    var list = document.getElementById("dkFaq");
    if (!list) return;
    var items = Array.prototype.slice.call(list.querySelectorAll(".mt-dk-faq__item"));
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
      var w = o.querySelector(".mt-dk-faq__awrap");
      clearPending(w);
      setH(w, w.scrollHeight); reflow(w);
      o.classList.remove("mt-is-open");
      setH(w, 0);
      o.querySelector(".mt-dk-faq__btn").setAttribute('aria-expanded', 'false');
    }
    function toggle(item) {
      var btn = item.querySelector(".mt-dk-faq__btn");
      var wrap = item.querySelector(".mt-dk-faq__awrap");
      var open = item.classList.contains("mt-is-open");
      items.forEach(function (o) {
        if (o !== item && o.classList.contains("mt-is-open")) closeItem(o);
      });
      if (open) { closeItem(item); return; }
      clearPending(wrap);
      item.classList.add("mt-is-open");
      setH(wrap, wrap.scrollHeight);
      btn.setAttribute('aria-expanded', 'true');
      /* settle to auto so the answer reflows with the viewport instead of
         holding the pixel height it opened at */
      var settle = function () {
        if (!item.classList.contains("mt-is-open")) return;
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
      item.querySelector(".mt-dk-faq__btn").addEventListener('click', function () { toggle(item); });
    });
    DK.faqOpen = function (i) {
      var it = items[i];
      if (it && !it.classList.contains("mt-is-open")) toggle(it);
    };
    DK.faqIsOpen = function (i) { return items[i] && items[i].classList.contains("mt-is-open"); };
    DK.faqCloseAll = function () { items.forEach(function (o) { if (o.classList.contains("mt-is-open")) closeItem(o); }); };
  })();

  /* ---------- back to top ---------- */
  (function () {
    var t = document.getElementById("dkTop");
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

/* Keep sections in document flow so controls and dividers cannot cross headings. */
(function () {
  function atTop() {
    document.documentElement.classList.toggle('mt-is-attop', (window.scrollY || 0) < 6);
  }
  addEventListener('scroll', atTop, { passive: true });
  addEventListener('pageshow', atTop);
  atTop();
})();
