/* Update homepage selection without opening the post editor. */
jQuery(function ($) {
  $(document).on('change', '.mt-homepage-toggle', function () {
    var checkbox = $(this);
    var enabled = this.checked;
    var status = checkbox.siblings('.mt-homepage-status');
    $('.mt-homepage-toggle').prop('disabled', true);
    status.text(' Saving…');
    $.post(asfarHomepagePosts.url, {
      action: 'asfar_homepage_post', nonce: asfarHomepagePosts.nonce,
      post_id: checkbox.data('post-id'), enabled: enabled ? '1' : '0'
    }).done(function (response) {
      response.data.ids.forEach(function (id) {
        $('.mt-homepage-toggle[data-post-id="' + id + '"]').prop('checked', response.data.enabled);
      });
      status.text(' Saved');
    }).fail(function (request) {
      checkbox.prop('checked', !enabled);
      status.text(' ' + (request.responseJSON && request.responseJSON.data || 'Could not save. Try again.'));
    }).always(function () {
      $('.mt-homepage-toggle').each(function () { this.disabled = this.dataset.readonly === 'true'; });
    });
  });
  $('.mt-homepage-toggle').each(function () { this.dataset.readonly = String(this.disabled); });
});
