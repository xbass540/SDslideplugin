'use strict';

(function($) {
  var woobtTimeout = null;

  $(document).ready(function() {
    woobt_settings();

    // options page
    woobt_options();

    // arrange
    woobt_arrange();
  });

  $(document).on('change', 'select[name="_woobt_change_price"]', function() {
    woobt_options();
  });

  // set optional
  $(document).on('click touch', '#woobt_custom_qty', function() {
    if ($(this).is(':checked')) {
      $('.woobt_tr_show_if_custom_qty').show();
      $('.woobt_tr_hide_if_custom_qty').hide();
    } else {
      $('.woobt_tr_show_if_custom_qty').hide();
      $('.woobt_tr_hide_if_custom_qty').show();
    }
  });

  // search input
  $(document).on('keyup', '#woobt_keyword', function() {
    if ($('#woobt_keyword').val() != '') {
      $('#woobt_loading').show();

      if (woobtTimeout != null) {
        clearTimeout(woobtTimeout);
      }

      woobtTimeout = setTimeout(woobt_ajax_get_data, 300);

      return false;
    }
  });

  // actions on search result items
  $(document).on('click touch', '#woobt_results li', function() {
    $(this).children('span.remove').html('×');
    $('#woobt_selected ul').append($(this));
    $('#woobt_results').hide();
    $('#woobt_keyword').val('');
    woobt_get_ids();
    woobt_arrange();

    return false;
  });

  // change qty of each item
  $(document).on('keyup change click', '#woobt_selected input', function() {
    woobt_get_ids();

    return false;
  });

  // actions on selected items
  $(document).on('click touch', '#woobt_selected span.remove', function() {
    $(this).parent().remove();
    woobt_get_ids();

    return false;
  });

  // hide search result box if click outside
  $(document).on('click touch', function(e) {
    if ($(e.target).closest($('#woobt_results')).length == 0) {
      $('#woobt_results').hide();
    }
  });

  $(document).on('woobtDragEndEvent', function() {
    woobt_get_ids();
  });

  function woobt_settings() {
    // hide search result box by default
    $('#woobt_results').hide();
    $('#woobt_loading').hide();

    // show or hide limit
    if ($('#woobt_custom_qty').is(':checked')) {
      $('.woobt_tr_show_if_custom_qty').show();
      $('.woobt_tr_hide_if_custom_qty').hide();
    } else {
      $('.woobt_tr_show_if_custom_qty').hide();
      $('.woobt_tr_hide_if_custom_qty').show();
    }
  }

  function woobt_options() {
    if ($('select[name="_woobt_change_price"]').val() == 'yes_custom') {
      $('input[name="_woobt_change_price_custom"]').show();
    } else {
      $('input[name="_woobt_change_price_custom"]').hide();
    }
  }

  function woobt_arrange() {
    $('#woobt_selected li').arrangeable({
      dragEndEvent: 'woobtDragEndEvent',
      dragSelector: '.move',
    });
  }

  function woobt_get_ids() {
    var woobt_ids = new Array();

    $('#woobt_selected li').each(function() {
      if (!$(this).hasClass('woobt_default')) {
        woobt_ids.push($(this).attr('data-id') + '/' +
            $(this).find('.price input').val() + '/' +
            $(this).find('.qty input').val());
      }
    });

    if (woobt_ids.length > 0) {
      $('#woobt_ids').val(woobt_ids.join(','));
    } else {
      $('#woobt_ids').val('');
    }
  }

  function woobt_ajax_get_data() {
    // ajax search product
    woobtTimeout = null;

    var data = {
      action: 'woobt_get_search_results',
      woobt_keyword: $('#woobt_keyword').val(),
      woobt_id: $('#woobt_id').val(),
      woobt_ids: $('#woobt_ids').val(),
    };

    $.post(ajaxurl, data, function(response) {
      $('#woobt_results').show();
      $('#woobt_results').html(response);
      $('#woobt_loading').hide();
    });
  }
})(jQuery);