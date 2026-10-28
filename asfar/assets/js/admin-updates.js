/* Display release availability without reloading an editor or losing unsaved work. */
(function ($) {
 'use strict';
 $(document).on('heartbeat-tick.asfar', function (event, data) {
  var update = data.asfar_theme_update;
  var notice = document.querySelector('.mt-theme-update-notice');
  if (!update) { if (notice) notice.remove(); return; }
  if (!notice) {
   notice = document.createElement('div');
   notice.className = 'notice notice-info mt-theme-update-notice';
   var paragraph = document.createElement('p');
   paragraph.appendChild(document.createElement('span'));
   paragraph.appendChild(document.createTextNode(' '));
   var link = document.createElement('a');
   link.textContent = 'View theme update';
   paragraph.appendChild(link);
   notice.appendChild(paragraph);
   var heading = document.querySelector('.wrap h1');
   if (heading) heading.insertAdjacentElement('afterend', notice);
   else document.querySelector('#wpbody-content').prepend(notice);
  }
  notice.querySelector('span').textContent = update.message;
  notice.querySelector('a').href = update.url;
 });
})(jQuery);
