(function () {
  var tabs = [].slice.call(document.querySelectorAll('.mt-sp-tab'));
  if (!tabs.length) return;
  var panels = tabs.map(function (t) { return document.getElementById(t.getAttribute('aria-controls')); });
  function select(i) {
    tabs.forEach(function (t, k) {
      var on = k === i;
      t.setAttribute('aria-selected', on ? 'true' : 'false');
      t.tabIndex = on ? 0 : -1;
      if (panels[k]) panels[k].hidden = !on;
    });
  }
  tabs.forEach(function (t, i) {
    t.addEventListener('click', function () { select(i); });
    t.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowRight' || e.key === 'ArrowLeft') {
        e.preventDefault();
        var dir = e.key === 'ArrowRight' ? 1 : -1;
        var ni = (i + dir + tabs.length) % tabs.length;
        tabs[ni].focus(); select(ni);
      }
    });
  });
  select(0);
})();

