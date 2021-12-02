'use strict';

(function($) {
  $(function() {
    if (!$('.woobt-wrap').length) {
      return;
    }

    $('.woobt-wrap').each(function() {
      woobt_init($(this));
    });
  });

  $(document).on('woosq_loaded', function() {
    woobt_init($('#woosq-popup').find('.woobt-wrap'));
  });

  $(document).on('found_variation', function(e, t) {
    var $wrap = $(e['target']).closest('.woobt-wrap');
    var $products = $(e['target']).closest('.woobt-products');
    var $product = $(e['target']).closest('.woobt-product');
    var pricing = $products.attr('data-pricing');
    var price_html = t['price_html'];
    var display_price = t['display_price'];
    var display_regular_price = t['display_regular_price'];

    if (pricing == 'regular_price') {
      display_price = display_regular_price;
    }

    if ($product.length) {
      if ($product.hasClass('woobt-product-together')) {
        var new_price = $product.attr('data-new-price');

        if (new_price !== '100%') {
          if (isNaN(new_price)) {
            new_price = display_price * parseFloat(new_price) / 100;
          }

          $product.find('.woobt-price-ori').hide();
          $product.find('.woobt-price-new').
              html(woobt_price_html(display_price, new_price)).show();
        } else if (price_html !== '') {
          $product.find('.woobt-price-ori').hide();
          $product.find('.woobt-price-new').html(price_html).show();
        }
      } else {
        $products.attr('data-product-id', t['variation_id']);

        if (price_html !== '') {
          $product.find('.woobt-price-ori').hide();
          $product.find('.woobt-price-new').html(price_html).show();
        }
      }

      $product.attr('data-price', display_price);
      $product.attr('data-regular-price', display_regular_price);

      if (t['is_purchasable'] && t['is_in_stock']) {
        $product.attr('data-id', t['variation_id']);

        if ($product.hasClass('woobt-product-this')) {
          $wrap.find('.variation_id').attr('value', t['variation_id']);
        }

        // change attributes
        var attrs = {};

        $product.find('select[name^="attribute_"]').each(function() {
          var attr_name = $(this).attr('name');

          attrs[attr_name] = $(this).val();
        });

        $product.attr('data-attrs', JSON.stringify(attrs));
      } else {
        $product.attr('data-id', 0);
        $product.attr('data-attrs', '');

        if ($product.hasClass('woobt-product-this')) {
          $wrap.find('.variation_id').attr('value', 0);
        }
      }

      // change availability
      if (t['availability_html'] && t['availability_html'] !== '') {
        $product.find('.woobt-availability').
            html(t['availability_html']).show();
      } else {
        $product.find('.woobt-availability').html('').hide();
      }

      if (t['image']['url'] && t['image']['srcset']) {
        // change image
        $product.find('.woobt-thumb-ori').hide();
        $product.find('.woobt-thumb-new').
            html('<img src="' + t['image']['url'] + '" srcset="' +
                t['image']['srcset'] + '"/>').show();
      }

      // reset sku
      $('.product_meta .sku').text($products.attr('data-product-sku'));

      if (woobt_vars.change_image === 'no') {
        // prevent changing the main image
        $(e['target']).closest('.variations_form').trigger('reset_image');
      }
    } else if (woobt_vars.add_to_cart_button === 'main') {
      if ($(e['target']).closest('.woosb-product').length ||
          $(e['target']).closest('.woosg-product').length ||
          $(e['target']).closest('.woofs-product').length) {
        return;
      }

      var pid = $(e['target']).
          closest('.variations_form').
          attr('data-product_id');

      $wrap = $('.woobt-wrap-' + pid);
      $products = $('.woobt-products-' + pid);
      $products.attr('data-product-id', t['variation_id']);
      $products.attr('data-product-sku', t['sku']);

      // change this product price
      if ($products.find('.woobt-product-this').length) {
        $products.find('.woobt-product-this').
            attr('data-id', t['variation_id']);
        $products.find('.woobt-product-this').
            attr('data-price', display_price);
        $products.find('.woobt-product-this').
            attr('data-regular-price', display_regular_price);

        if (price_html !== '') {
          $products.find('.woobt-product-this .woobt-price-ori').hide();
          $products.find('.woobt-product-this .woobt-price-new').
              html(price_html).show();
        }
      }
    }

    woobt_init($wrap);
  });

  $(document).on('reset_data', function(e) {
    var $wrap = $(e['target']).closest('.woobt-wrap');
    var $products = $(e['target']).closest('.woobt-products');
    var $product = $(e['target']).closest('.woobt-product');

    if ($product.length) {
      $product.attr('data-id', 0);
      $product.attr('data-attrs', '');

      // reset stock
      $(e['target']).closest('.variations_form').find('p.stock').remove();

      // reset sku
      $('.product_meta .sku').text($products.attr('data-product-sku'));

      // reset availability
      $product.find('.woobt-availability').html('').hide();

      // reset thumb
      $product.find('.woobt-thumb-new').hide();
      $product.find('.woobt-thumb-ori').show();

      // reset price
      $product.find('.woobt-price-new').hide();
      $product.find('.woobt-price-ori').show();

      if ($product.hasClass('woobt-product-this')) {
        $products.attr('data-product-id', 0);
      }
    } else if (woobt_vars.add_to_cart_button === 'main') {
      if ($(e['target']).closest('.woosb-product').length ||
          $(e['target']).closest('.woosg-product').length ||
          $(e['target']).closest('.woofs-product').length) {
        return;
      }

      var pid = $(e['target']).
          closest('.variations_form').
          attr('data-product_id');

      $wrap = $('.woobt-wrap-' + pid);
      $products = $('.woobt-products-' + pid);
      $products.attr('data-product-id', 0);
      $products.attr('data-product-sku', $products.attr('data-product-o-sku'));

      // change this product price
      $products.find('.woobt-product-this').attr('data-id', 0);
    }

    woobt_init($wrap);
  });

  $(document).on('woovr_selected', function(e, selected, variations) {
    var $wrap = variations.closest('.woobt-wrap');
    var $products = variations.closest('.woobt-products');
    var $product = variations.closest('.woobt-product');
    var id = selected.attr('data-id');
    var pricing = $products.attr('data-pricing');
    var price_html = selected.attr('data-pricehtml');
    var display_price = selected.attr('data-price');
    var price = selected.attr('data-price');
    var regular_price = selected.attr('data-regular-price');
    var image_src = selected.attr('data-imagesrc');
    var purchasable = selected.attr('data-purchasable');
    var attrs = selected.attr('data-attrs');

    if (pricing == 'regular_price') {
      price = regular_price;
    }

    if ($product.length) {
      if (purchasable === 'yes') {
        // change data
        $product.attr('data-id', id);
        $product.attr('data-price', price);
        $product.attr('data-regular-price', regular_price);

        // change image
        if (image_src !== undefined && image_src !== '') {
          $product.find('.woobt-thumb-ori').hide();
          $product.find('.woobt-thumb-new').
              html('<img src="' + image_src + '"/>').show();
        }

        // change price
        var new_price = $product.attr('data-new-price');

        $product.find('.woobt-price-ori').hide();

        if (new_price != '100%') {
          if (isNaN(new_price)) {
            new_price = price * parseFloat(new_price) / 100;
          }

          $product.find('.woobt-price-new').
              html(woobt_price_html(display_price, new_price)).show();
        } else {
          $product.find('.woobt-price-new').html(price_html).show();
        }

        // change attributes
        $product.attr('data-attrs', attrs.replace(/\/$/, ''));

        // change separate add to cart
        if ($product.hasClass('woobt-product-this')) {
          $products.attr('data-product-id', id);
          $wrap.find('.variation_id').attr('value', id);
        }
      } else {
        // reset data
        $product.attr('data-id', 0);
        $product.attr('data-attrs', '');
        $product.attr('data-price', 0);
        $product.attr('data-regular-price', 0);

        // reset image
        $product.find('.woobt-thumb-ori').show();
        $product.find('.woobt-thumb-new').html('').hide();

        // reset price
        $product.find('.woobt-price-ori').show();
        $product.find('.woobt-price-new').html('').hide();

        // reset separate add to cart
        if ($product.hasClass('woobt-product-this')) {
          $products.attr('data-product-id', 0);
          $wrap.find('.variation_id').attr('value', 0);
        }
      }
    } else {
      var pid = variations.closest('.variations_form').attr('data-product_id');

      $wrap = $('.woobt-wrap-' + pid);
      $products = $('.woobt-products-' + pid);

      if (purchasable === 'yes') {
        $products.attr('data-product-id', id);

        // change this product price
        $products.find('.woobt-product-this').attr('data-price', price);
        $products.find('.woobt-product-this').
            attr('data-regular-price', regular_price);

        if (price_html !== '') {
          $products.find('.woobt-product-this .woobt-price-ori').hide();
          $products.find('.woobt-product-this .woobt-price-new').
              html(price_html).
              show();
        }
      } else {
        $products.attr('data-product-id', 0);
        $products.attr('data-product-sku',
            $products.attr('data-product-o-sku'));

        $products.find('.woobt-product-this').attr('data-price', 0);
        $products.find('.woobt-product-this').attr('data-regular-price', 0);
        $products.find('.woobt-product-this .woobt-price-new').hide();
        $products.find('.woobt-product-this .woobt-price-ori').show();
      }
    }

    woobt_init($wrap);
  });

  $(document).
      on('click touch',
          '.woobt-quantity-input-plus, .woobt-quantity-input-minus',
          function() {
            // get values
            var $qty = $(this).
                    closest('.woobt-quantity-input').
                    find('.woobt-qty'),
                val = parseFloat($qty.val()),
                max = parseFloat($qty.attr('max')),
                min = parseFloat($qty.attr('min')),
                step = $qty.attr('step');

            // format values
            if (!val || val === '' || val === 'NaN') {
              val = 0;
            }

            if (max === '' || max === 'NaN') {
              max = '';
            }

            if (min === '' || min === 'NaN') {
              min = 0;
            }

            if (step === 'any' || step === '' || step === undefined ||
                parseFloat(step) === 'NaN') {
              step = 1;
            } else {
              step = parseFloat(step);
            }

            // change the value
            if ($(this).is('.woobt-quantity-input-plus')) {
              if (max && (
                  max == val || val > max
              )) {
                $qty.val(max);
              } else {
                $qty.val((val + step).toFixed(woobt_decimal_places(step)));
              }
            } else {
              if (min && (
                  min == val || val < min
              )) {
                $qty.val(min);
              } else if (val > 0) {
                $qty.val((val - step).toFixed(woobt_decimal_places(step)));
              }
            }

            // trigger change event
            $qty.trigger('change');
          });

  $(document).
      on('click touch', '.single_add_to_cart_button:not(.wpcbn-btn)',
          function(e) {
            if ($(this).hasClass('woobt-disabled')) {
              e.preventDefault();
            }
          });

  $(document).on('change', '.woobt-checkbox', function() {
    var $wrap = $(this).closest('.woobt-wrap');
    var selection = $wrap.attr('data-selection');

    if (selection === 'single') {
      $wrap.find('.woobt-checkbox').
          not('.woobt-checkbox-this').
          not(this).
          prop('checked', false);
    }

    woobt_init($wrap);
  });

  $(document).on('change keyup mouseup', '.woobt-this-qty', function() {
    var val = $(this).val();
    var pid = $(this).closest('.woobt-wrap').attr('data-id');
    var $ids = $('.woobt-ids-' + pid);
    var $form = $ids.closest('form.cart').length ?
        $ids.closest('form.cart') :
        $ids.closest('.woobt-form');

    $(this).closest('.woobt-product-this').attr('data-qty', val);

    $form.find('input[name="quantity"]').val(val).trigger('change');
  });

  $(document).on('change keyup mouseup', '.woobt-qty', function() {
    var $this = $(this);
    var $wrap = $this.closest('.woobt-wrap');
    var $product = $this.closest('.woobt-product');
    var $checkbox = $product.find('.woobt-checkbox');
    var val = parseFloat($this.val());

    if ($checkbox.prop('checked')) {
      var min = parseFloat($this.attr('min'));
      var max = parseFloat($this.attr('max'));

      if (val < min) {
        $this.val(min);
      }

      if (val > max) {
        $this.val(max);
      }

      $product.attr('data-qty', $this.val());

      woobt_init($wrap);
    }
  });

  $(document).on('change', 'form.cart .qty', function() {
    var $this = $(this);
    var qty = parseFloat($this.val());

    if ($this.hasClass('woobt-qty')) {
      return;
    }

    if (!$this.closest('form.cart').find('.woobt-ids').length) {
      return;
    }

    var wrap_id = $this.closest('form.cart').find('.woobt-ids').attr('data-id');
    var $wrap = $('.woobt-wrap-' + wrap_id);
    var $products = $wrap.find('.woobt-products');
    var optional = $products.attr('data-optional');
    var sync_qty = $products.attr('data-sync-qty');

    $products.find('.woobt-product-this').attr('data-qty', qty);

    if ((optional !== 'on') && (sync_qty === 'on')) {
      $products.find('.woobt-product-together').each(function() {
        var _qty = parseFloat($(this).attr('data-qty-ori')) * qty;

        $(this).attr('data-qty', _qty);
        $(this).find('.woobt-qty-num .woobt-qty').html(_qty);
      });
    }

    woobt_init($wrap);
  });

  $(document).on('woosg_calc_price', function(e, total, total_html) {
    // change price for grouped product
    var $woosg = $('.woobt-products[data-product-type="woosg"]');
    if ($woosg.length) {
      $woosg.find('.woobt-product-this').
          attr('data-price', total).
          attr('data-regular-price', total);
      $woosg.find('.woobt-product-this .woobt-price-ori').hide();
      $woosg.find('.woobt-product-this .woobt-price-new').
          html(total_html).
          show();
    }
  });

  $(document).
      on('wooco_calc_price', function(e, total, total_formatted, total_html) {
        // change price for composite products
        var $wooco = $('.woobt-products[data-product-type="composite"]');

        if ($wooco.length) {
          $wooco.find('.woobt-product-this').
              attr('data-price', total).
              attr('data-regular-price', total);
          $wooco.find('.woobt-product-this .woobt-price-ori').hide();
          $wooco.find('.woobt-product-this .woobt-price-new').
              html(total_html).
              show();
        }
      });

  $(document).
      on('click touch', '.woobt-form .single_add_to_cart_button', function(e) {
        e.preventDefault();
        var $btn = $(this);
        var $form = $btn.closest('.woobt-form');
        var $wrap = $btn.closest('.woobt-wrap');
        // variable product
        var data = {};
        var attrs = {};

        $btn.addClass('loading');

        $wrap.find('.woobt-product-this select[name^=attribute]').
            each(function() {
              var attribute = $(this).attr('name');
              var attribute_value = $(this).val();

              attrs[attribute] = attribute_value;
            });

        data.action = 'woobt_add_all_to_cart';
        data.quantity = $form.find('input[name="quantity"]').val();
        data.product_id = $form.find('input[name="product_id"]').val();
        data.variation_id = $form.find('input[name="variation_id"]').val();
        data.woobt_ids = $form.find('input[name="woobt_ids"]').val();
        data.variation = attrs;

        $.post(woobt_vars.ajax_url, data, function(response) {
          if (!response) {
            return;
          }

          if (response.error && response.product_url) {
            window.location = response.product_url;
            return;
          }

          // Redirect to cart option
          if ((typeof wc_add_to_cart_params !== 'undefined') &&
              (wc_add_to_cart_params.cart_redirect_after_add === 'yes')) {
            window.location = wc_add_to_cart_params.cart_url;
            return;
          }

          // Trigger event so themes can refresh other areas.
          $(document.body).
              trigger('added_to_cart',
                  [
                    response.fragments,
                    response.cart_hash,
                    $btn]);
        });
      });
})(jQuery);

function woobt_init($wrap) {
  woobt_check_ready($wrap);
  woobt_calc_price($wrap);
  woobt_save_ids($wrap);

  if (woobt_vars.counter !== 'hide') {
    woobt_update_count($wrap);
  }

  jQuery(document).trigger('woobt_init', [$wrap]);
}

function woobt_check_ready($wrap) {
  var pid = $wrap.attr('data-id');
  var $products = $wrap.find('.woobt-products');
  var $alert = $wrap.find('.woobt-alert');
  var $ids = jQuery('.woobt-ids-' + pid);
  var $form = $ids.closest('form.cart').length ?
      $ids.closest('form.cart') :
      $ids.closest('.woobt-form');
  var $btn = $form.find('.single_add_to_cart_button:not(.wpcbn-btn)');
  var is_selection = false;
  var selection_name = '';
  var optional = $products.attr('data-optional');

  if ((woobt_vars.add_to_cart_button === 'main') && (optional === 'on') &&
      ($products.find('.woobt-product-this:not(.woobt-hide-this)').length >
          0)) {
    $form.find('.quantity').hide();
  }

  if ((woobt_vars.position === 'before') &&
      (woobt_vars.add_to_cart_button === 'main') &&
      ($products.attr('data-product-type') === 'variable') &&
      ($products.attr('data-variables') === 'no' ||
          woobt_vars.variation_selector === 'wpc_radio')) {
    $products.closest('.woobt-wrap').insertAfter($ids);
    $products.find('.woobt-qty').removeClass('qty');
  }

  $products.find('.woobt-product').each(function() {
    var $this = jQuery(this);
    var $images = $this.closest('.woobt-wrap').find('.woobt-images');
    var _checked = $this.find('.woobt-checkbox').prop('checked');
    var _id = parseInt($this.attr('data-id'));
    var _qty = parseInt($this.attr('data-qty'));
    var _pid = parseInt($this.attr('data-pid'));

    if ($this.hasClass('woobt-hide-this')) {
      return true;
    }

    if (!_checked) {
      $this.addClass('woobt-hide');

      if ($images.length) {
        $images.find('.woobt-image-' + _pid).addClass('woobt-image-hide');
      }
    } else {
      $this.removeClass('woobt-hide');

      if ($images.length) {
        $images.find('.woobt-image-' + _pid).removeClass('woobt-image-hide');
      }
    }

    if (_checked && (_id == 0) && (_qty > 0)) {
      is_selection = true;

      if (selection_name === '') {
        selection_name = $this.attr('data-name');
      }
    }
  });

  if (is_selection) {
    $btn.addClass('woobt-disabled woobt-selection');
    $alert.html(woobt_vars.alert_selection.replace('[name]',
        '<strong>' + selection_name + '</strong>')).slideDown();

    jQuery(document).trigger('woobt_check_ready', [false, is_selection]);
  } else {
    $btn.removeClass('woobt-disabled woobt-selection');
    $alert.html('').slideUp();

    // ready
    jQuery(document).trigger('woobt_check_ready', [true, is_selection]);
  }
}

function woobt_calc_price($wrap) {
  var pid = $wrap.attr('data-id');
  var $additional = $wrap.find('.woobt-additional');
  var $total = $wrap.find('.woobt-total');
  var $products = $wrap.find('.woobt-products');
  var $product_this = $products.find('.woobt-product-this');
  var count = 0, total = 0;
  var additional_html = '', total_html = '';
  var discount = parseFloat($products.attr('data-discount'));
  var ori_price_suffix = $products.attr('data-product-price-suffix');
  var ori_price = parseFloat($product_this.attr('data-price'));
  var ori_price_regular = parseFloat($product_this.attr('data-regular-price'));
  var ori_qty = parseFloat($product_this.attr('data-qty'));
  var total_ori = ori_price * ori_qty;
  var total_ori_regular = ori_price_regular * ori_qty;
  var $price = jQuery('.woobt-price-' + pid);
  var show_price = $products.attr('data-show-price');
  var fix = Math.pow(10, Number(woobt_vars.price_decimals) + 1);

  if ((woobt_vars.change_price === 'yes_custom') &&
      (woobt_vars.price_selector != null) &&
      (woobt_vars.price_selector !== '')) {
    $price = jQuery(woobt_vars.price_selector);
  }

  $products.find('.woobt-product-together').each(function() {
    var $this = jQuery(this);
    var _checked = $this.find('.woobt-checkbox').prop('checked');
    var _id = parseInt($this.attr('data-id'));
    var _qty = parseFloat($this.attr('data-qty'));
    var _price = $this.attr('data-new-price');
    var _price_suffix = $this.attr('data-price-suffix');
    var _sale_price = parseFloat($this.attr('data-price'));
    var _regular_price = parseFloat($this.attr('data-regular-price'));
    var _total_ori = 0, _total_ori_regular = 0, _total = 0;

    _total_ori = _qty * _sale_price;
    _total_ori_regular = _qty * _sale_price;

    if (isNaN(_price)) {
      // is percent
      if (_price == '100%') {
        _total_ori = _qty * _regular_price;
        _total_ori_regular = _qty * _regular_price;
        _total = _qty * _sale_price;
      } else {
        _total = _total_ori * parseFloat(_price) / 100;
      }
    } else {
      _total = _qty * _price;
    }

    if (show_price === 'total') {
      $this.find('.woobt-price-ori').hide();
      $this.find('.woobt-price-new').
          html(woobt_price_html(_total_ori, _total) + _price_suffix).
          show();
    }

    // calc total
    if (_checked && (_qty > 0) && (_id > 0)) {
      count++;
      total += _total;
      total_ori_regular += _total_ori_regular;
    }
  });

  total = Math.round(total * fix) / fix;

  if ($product_this.length) {
    var _id = parseInt($product_this.attr('data-id'));
    var _qty = parseFloat($product_this.attr('data-qty'));

    if (_qty > 0 && _id > 0) {
      var _price_suffix = $product_this.attr('data-price-suffix');
      var _sale_price = parseFloat($product_this.attr('data-price'));
      var _regular_price = parseFloat($product_this.attr('data-regular-price'));
      var _total_ori = 0, _total_ori_regular = 0, _total = 0;

      if (show_price !== 'total') {
        _qty = 1;
      }

      _total_ori_regular = _qty * _regular_price;
      _total_ori = _qty * _regular_price;
      _total = _qty * _sale_price;

      if (total > 0 && parseFloat($product_this.attr('data-id')) > 0) {
        var _price = $product_this.attr('data-new-price');

        _total_ori = _qty * _sale_price;

        if (isNaN(_price)) {
          // is percent
          if (_price !== '100%') {
            _total = _total_ori * parseFloat(_price) / 100;
          }
        } else {
          _total = _qty * _price;
        }
      }

      $product_this.find('.woobt-price-ori').hide();
      $product_this.find('.woobt-price-new').
          html(woobt_price_html(_total_ori_regular, _total) + _price_suffix).
          show();
    } else {
      $product_this.find('.woobt-price-new').hide();
      $product_this.find('.woobt-price-ori').show();
    }
  }

  if (count > 0) {
    if (isNaN(discount)) {
      discount = 0;
    }

    total_ori = total_ori * (100 - discount) / 100 + total;

    $additional.html(
        woobt_vars.additional_price_text + ' ' + woobt_format_price(total) +
        ori_price_suffix).
        slideDown();
    $total.html(
        woobt_vars.total_price_text + ' ' + woobt_format_price(total_ori) +
        ori_price_suffix).
        slideDown();
  } else {
    $additional.html('').slideUp();
    $total.html('').slideUp();
  }

  // change the main price
  if ((woobt_vars.change_price !== 'no') &&
      (woobt_vars.add_to_cart_button !== 'separate')) {
    if (parseInt($products.attr('data-product-id')) > 0 && count > 0) {
      $price.html(
          woobt_price_html(total_ori_regular, total_ori) + ori_price_suffix);
    } else {
      $price.html($products.attr('data-product-price-html'));
    }
  }

  jQuery(document).
      trigger('woobt_calc_price', [total, total_ori, total_ori_regular]);

  if ($wrap.find('.woobt-wrap').length) {
    $wrap.find('.woobt-wrap').attr('data-total', total);
  } else {
    $wrap.attr('data-total', total);
  }
}

function woobt_save_ids($wrap) {
  var pid = $wrap.attr('data-id');
  var $products = $wrap.find('.woobt-products');
  var sync_qty = $products.attr('data-sync-qty');
  var $ids = jQuery('.woobt-ids-' + pid);
  var items = [];

  $products.find('.woobt-product-together').each(function() {
    var $this = jQuery(this);
    var checked = $this.find('.woobt-checkbox').prop('checked');
    var id = parseInt($this.attr('data-id'));
    var qty = parseFloat($this.attr('data-qty'));
    var qty_ori = parseFloat($this.attr('data-qty-ori'));
    var price = $this.attr('data-new-price');
    var attrs = $this.attr('data-attrs');

    if (checked && (qty > 0) && (id > 0)) {
      if (attrs !== undefined) {
        attrs = encodeURIComponent(attrs);
      } else {
        attrs = '';
      }

      if (sync_qty === 'on') {
        items.push(id + '/' + price + '/' + qty_ori + '/' + attrs);
      } else {
        items.push(id + '/' + price + '/' + qty + '/' + attrs);
      }
    }
  });

  if (items.length > 0) {
    $ids.val(items.join(','));
  } else {
    $ids.val('');
  }
}

function woobt_update_count($wrap) {
  var pid = $wrap.attr('data-id');
  var $products = $wrap.find('.woobt-products');
  var $ids = jQuery('.woobt-ids-' + pid);
  var $form = $ids.closest('form.cart').length ?
      $ids.closest('form.cart') :
      $ids.closest('.woobt-form');
  var $btn = $form.find('.single_add_to_cart_button:not(.wpcbn-btn)');
  var qty = 0;
  var num = 0;

  $products.find('.woobt-product').each(function() {
    var $this = jQuery(this);
    var _checked = $this.find('.woobt-checkbox').prop('checked');
    var _id = parseInt($this.attr('data-id'));
    var _qty = parseFloat($this.attr('data-qty'));

    if (_checked && (_qty > 0) && (_id > 0)) {
      qty += _qty;
      num++;
    }
  });

  if (num > 1) {
    if (woobt_vars.counter === 'individual') {
      $btn.text(woobt_vars.add_to_cart + ' (' + num + ')');
    } else {
      $btn.text(woobt_vars.add_to_cart + ' (' + qty + ')');
    }
  } else {
    $btn.text(woobt_vars.add_to_cart);
  }

  jQuery(document.body).trigger('woobt_update_count', [num, qty]);
}

function woobt_format_money(number, places, symbol, thousand, decimal) {
  number = number || 0;
  places = !isNaN(places = Math.abs(places)) ? places : 2;
  symbol = symbol !== undefined ? symbol : '$';
  thousand = thousand !== undefined ? thousand : ',';
  decimal = decimal !== undefined ? decimal : '.';

  var negative = number < 0 ? '-' : '',
      i = parseInt(number = Math.abs(+number || 0).toFixed(places), 10) + '',
      j = 0;

  if (i.length > 3) {
    j = i.length % 3;
  }

  return symbol + negative + (
      j ? i.substr(0, j) + thousand : ''
  ) + i.substr(j).replace(/(\d{3})(?=\d)/g, '$1' + thousand) + (
      places ? decimal + Math.abs(number - i).toFixed(places).slice(2) : ''
  );
}

function woobt_format_price(total) {
  var total_html = '<span class="woocommerce-Price-amount amount">';
  var total_formatted = woobt_format_money(total, woobt_vars.price_decimals,
      '', woobt_vars.price_thousand_separator,
      woobt_vars.price_decimal_separator);

  switch (woobt_vars.price_format) {
    case '%1$s%2$s':
      // left
      total_html += '<span class="woocommerce-Price-currencySymbol">' +
          woobt_vars.currency_symbol + '</span>' + total_formatted;
      break;
    case '%1$s %2$s':
      // left with space
      total_html += '<span class="woocommerce-Price-currencySymbol">' +
          woobt_vars.currency_symbol + '</span> ' + total_formatted;
      break;
    case '%2$s%1$s':
      // right
      total_html += total_formatted +
          '<span class="woocommerce-Price-currencySymbol">' +
          woobt_vars.currency_symbol + '</span>';
      break;
    case '%2$s %1$s':
      // right with space
      total_html += total_formatted +
          ' <span class="woocommerce-Price-currencySymbol">' +
          woobt_vars.currency_symbol + '</span>';
      break;
    default:
      // default
      total_html += '<span class="woocommerce-Price-currencySymbol">' +
          woobt_vars.currency_symbol + '</span> ' + total_formatted;
  }

  total_html += '</span>';

  return total_html;
}

function woobt_price_html(regular_price, sale_price) {
  var price_html = '';

  if (parseFloat(sale_price) < parseFloat(regular_price)) {
    price_html = '<del>' + woobt_format_price(regular_price) +
        '</del> <ins>' +
        woobt_format_price(sale_price) + '</ins>';
  } else {
    price_html = woobt_format_price(regular_price);
  }

  return price_html;
}

function woobt_decimal_places(num) {
  var match = ('' + num).match(/(?:\.(\d+))?(?:[eE]([+-]?\d+))?$/);

  if (!match) {
    return 0;
  }

  return Math.max(
      0,
      // Number of digits right of decimal point.
      (match[1] ? match[1].length : 0)
      // Adjust for scientific notation.
      - (match[2] ? +match[2] : 0));
}
