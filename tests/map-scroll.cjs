const fs = require('node:fs');
const vm = require('node:vm');
const assert = require('node:assert/strict');
const source = fs.readFileSync('asfar/assets/js/wordpress.js', 'utf8');
const script = source.slice(source.indexOf('/* ---------- map scroll-lock'));
let index = 0, time = 2000, stopped = false, paused = false;
const events = {}, classes = new Set();
const root = { getBoundingClientRect: () => ({ top: 80, bottom: 734, height: 654 }), addEventListener() {} };
const window = {
  innerHeight: 720, scrollY: 2000, matchMedia: () => ({ matches: false }),
  __dk: { mapGo: i => { index = i; }, mapCount: () => 4, mapIdx: () => index, mapPause: () => { paused = true; }, mapCommit() {} },
  __lenis: { scrollTo() {}, stop: () => { stopped = true; }, start: () => { stopped = false; } },
  addEventListener: (name, fn) => { events[name] = fn; }
};
vm.runInNewContext(script, {
  window, matchMedia: window.matchMedia, Date: { now: () => time },
  document: { getElementById: () => root, querySelector: () => ({ offsetHeight: 84 }), documentElement: { classList: { add: c => classes.add(c), remove: c => classes.delete(c) } } }
});
events.scroll();
assert.ok(stopped && paused && classes.has('mt-dk-maplock'));
function wheel(delta) { events.wheel({ deltaY: delta, preventDefault() {} }); }
wheel(100); assert.equal(index, 1);
wheel(100); assert.equal(index, 1, 'Rapid wheel events wait for the region transition');
time += 1400; wheel(100); assert.equal(index, 2);
time += 1400; wheel(100); assert.equal(index, 3);
time += 1400; wheel(100); assert.ok(!stopped && !classes.has('mt-dk-maplock'));
events.scroll(); assert.equal(stopped, false, 'Completed walkthrough does not lock again');
console.log('PASS: staging pin, gesture timing, region order, release and no repeat lock');
