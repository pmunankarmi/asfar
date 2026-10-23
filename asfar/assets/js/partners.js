/* Swiper is bundled locally; keep the grid when there are ten or fewer logos. */
(function () {
 'use strict';
 document.querySelectorAll('[data-partner-slider]').forEach(function (element) {
  var card = element.closest('.mt-dk-partners__card');
  new Swiper(element, {
   wrapperClass: 'mt-partner-track', slideClass: 'mt-partner-slide',
   slidesPerView: 2, slidesPerGroup: 2, spaceBetween: 24,
   speed: matchMedia('(prefers-reduced-motion: reduce)').matches ? 0 : 400,
   watchOverflow: true, keyboard: { enabled: true, onlyInViewport: true },
   navigation: { prevEl: card.querySelector('.mt-partner-prev'), nextEl: card.querySelector('.mt-partner-next') },
   pagination: { el: card.querySelector('.mt-partner-pagination'), clickable: true },
   a11y: { enabled: true },
   breakpoints: { 640: { slidesPerView: 3, slidesPerGroup: 3 }, 1024: { slidesPerView: 5, slidesPerGroup: 5 } }
  });
 });
})();
