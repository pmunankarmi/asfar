/* Reorder complete sibling lists; changes are saved only on explicit request. */
jQuery(function ($) {
  var root = $('.mt-content-order');
  if (!root.length) return;
  var dirty = false;
  var status = $('#content-order-status');
  function changed() {
    dirty = true;
    status.text('Order changed. Click Save Order to keep it.');
  }
  root.find('.mt-order-list').sortable({
    handle: '.mt-order-handle',
    axis: 'y',
    placeholder: 'mt-order-placeholder',
    update: changed
  });
  root.on('click', '.mt-order-up, .mt-order-down', function () {
    var item = $(this).closest('.mt-order-item');
    var neighbor = $(this).hasClass('mt-order-up') ? item.prev() : item.next();
    if (!neighbor.length) return;
    if ($(this).hasClass('mt-order-up')) item.insertBefore(neighbor);
    else item.insertAfter(neighbor);
    this.focus();
    changed();
  });
  $('#save-content-order').on('click', function () {
    var button = $(this);
    var groups = {};
    root.find('.mt-order-list').each(function () {
      groups[this.dataset.parent] = $(this).children().map(function () { return Number(this.dataset.id); }).get();
    });
    button.prop('disabled', true);
    root.find('.mt-order-list').sortable('disable');
    root.find('.mt-order-up, .mt-order-down').prop('disabled', true);
    status.text('Saving order…');
    $.post(asfarContentOrder.url, {
      action: 'asfar_content_order', nonce: asfarContentOrder.nonce,
      scope: root.data('scope'), language: root.data('language'), groups: JSON.stringify(groups)
    }).done(function (response) {
      dirty = false;
      status.text(response.data);
    }).fail(function (request) {
      status.text(request.responseJSON && request.responseJSON.data || 'Could not save. Reload the page and try again.');
    }).always(function () {
      button.prop('disabled', false);
      root.find('.mt-order-list').sortable('enable');
      root.find('.mt-order-up, .mt-order-down').prop('disabled', false);
    });
  });
  window.addEventListener('beforeunload', function (event) {
    if (dirty) { event.preventDefault(); event.returnValue = ''; }
  });
});
