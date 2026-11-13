/* Save a single move, so rows on other pages keep their relative order. */
jQuery(function ($) {
  var list = $('#the-list');
  if (!list.find('.mt-list-order-handle').length) return;
  var status = $('<p role="status" aria-live="polite"></p>').insertBefore(list.closest('table'));
  var originalRows;
  list.sortable({
    items: '> tr:has(.mt-list-order-handle)',
    handle: '.mt-list-order-handle',
    axis: 'y',
    placeholder: 'mt-list-order-placeholder',
    helper: function (event, row) {
      row.children().each(function () { $(this).width($(this).width()); });
      return row;
    },
    start: function () { originalRows = list.children().get(); },
    update: function (event, ui) {
      var previous = ui.item.prevAll('tr:has(.mt-list-order-handle)').first();
      var next = ui.item.nextAll('tr:has(.mt-list-order-handle)').first();
      var target = previous.length ? previous : next;
      list.sortable('disable');
      status.text('Saving order…');
      $.post(asfarListOrder.url, {
        action: 'asfar_list_order',
        nonce: asfarListOrder.nonce,
        scope: asfarListOrder.scope,
        moved: ui.item.find('.mt-list-order-handle').data('id'),
        target: target.find('.mt-list-order-handle').data('id'),
        placement: previous.length ? 'after' : 'before'
      }).done(function () {
        status.text('Order saved. Reloading the list…');
        var url = new URL(window.location.href);
        url.searchParams.delete('orderby');
        url.searchParams.delete('order');
        window.location.assign(url.toString());
      }).fail(function (request) {
        list.append(originalRows);
        list.sortable('enable');
        status.text(request.responseJSON && request.responseJSON.data || 'Could not save the order. Reload and try again.');
      });
    }
  });
});
