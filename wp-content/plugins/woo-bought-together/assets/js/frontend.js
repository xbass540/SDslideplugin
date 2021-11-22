'use strict';

(function($) {
  $(document).ready(function() {
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
    var display_price = t['display_price'];
    var display_regular_price = t['display_regular_price'];

    if (pricing == 'regular_price') {
      display_price = display_regular_price;
    }

    if ($product.length) {
      var new_price = $product.attr('data-new-price');

      if (new_price != '100%') {
        if (isNaN(new_price)) {
          new_price = display_price * parseFloat(new_price) / 100;
        }

        $product.find('.woobt-price-ori').hide();
        $product.find('.woobt-price-new').
            html(woobt_price_html(display_price, new_price)).show();
      } else {
        if (t['price_html'] !== '') {
          $product.find('.woobt-price-ori').hide();
          $product.find('.woobt-price-new').html(t['price_html']).show();
        }
      }

      if (t['is_purchasable'] && t['is_in_stock']) {
        $product.attr('data-id', t['variation_id']);
        $product.attr('data-price', display_price);
        $product.attr('data-regular-price', display_regular_price);
      } else {
        $product.attr('data-id', 0);
        $product.attr('data-price', 0);
        $product.attr('data-regular-price', 0);
      }

      // change availability
      if (t['availability_html'] && t['availability_html'] !== '') {
        $product.find('.woobt-availability').
            html(t['availability_html']).
            show();
      } else {
        $product.find('.woobt-availability').html('').hide();
      }

      if (t['image']['url'] && t['image']['srcset']) {
        // change image
        $product.find('.woobt-thumb-ori').hide();
        $product.find('.woobt-thumb-new').
            html('<img src="' + t['image']['url'] + '" srcset="' +
                t['image']['srcset'] + '"/>').
            show();
      }

      // reset sku
      $('.product_meta .sku').text($products.attr('data-product-sku'));

      if (woobt_vars.change_image === 'no') {
        // prevent changing the main image
        $(e['target']).closest('.variations_form').trigger('reset_image');
      }
    } else {
      $wrap = $(e['target']).
          closest(woobt_vars.summary_selector).
          find('.woobt-wrap');
      $products = $(e['target']).
          closest(woobt_vars.summary_selector).
          find('.woobt-products');
      $products.attr('data-product-id', t['variation_id']);
      $products.attr('data-product-sku', t['sku']);
      $products.attr('data-product-price', display_price);
    }

    woobt_init($wrap);
  });

  $(document).on('reset_data', function(e) {
    var $wrap = $(e['target']).closest('.woobt-wrap');
    var $products = $(e['target']).closest('.woobt-products');
    var $product = $(e['target']).closest('.woobt-product');

    if ($product.length) {
      $product.attr('data-id', 0);
      $product.attr('data-price', 0);
      $product.attr('data-regular-price', 0);

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
    } else {
      $wrap = $(e['target']).
          closest(woobt_vars.summary_selector).
          find('.woobt-wrap');
      $products = $(e['target']).
          closest(woobt_vars.summary_selector).
          find('.woobt-products');
      $products.attr('data-product-id', 0);
      $products.attr('data-product-price', 0);
      $products.attr('data-product-sku', $products.attr('data-product-o-sku'));
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
      } else {
        // reset data
        $product.attr('data-id', 0);
        $product.attr('data-price', 0);
        $product.attr('data-regular-price', 0);

        // reset image
        $product.find('.woobt-thumb-ori').show();
        $product.find('.woobt-thumb-new').html('').hide();

        // reset price
        $product.find('.woobt-price-ori').show();
        $product.find('.woobt-price-new').html('').hide();
      }
    } else {
      $wrap = variations.closest(woobt_vars.summary_selector).
          find('.woobt-wrap');
      $products = variations.closest(woobt_vars.summary_selector).
          find('.woobt-products');

      if (purchasable === 'yes') {
        $products.attr('data-product-id', id);
        $products.attr('data-product-price', price);
      } else {
        $products.attr('data-product-id', 0);
        $products.attr('data-product-price', 0);
        $products.attr('data-product-sku',
            $products.attr('data-product-o-sku'));
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
                qty_val = parseFloat($qty.val()),
                max = parseFloat($qty.attr('max')),
                min = parseFloat($qty.attr('min')),
                step = $qty.attr('step');

            // format values
            if (!qty_val || qty_val === '' || qty_val === 'NaN') {
              qty_val = 0;
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
                  max == qty_val || qty_val > max
              )) {
                $qty.val(max);
              } else {
                $qty.val((qty_val + step).toFixed(woobt_decimal_places(step)));
              }
            } else {
              if (min && (
                  min == qty_val || qty_val < min
              )) {
                $qty.val(min);
              } else if (qty_val > 0) {
                $qty.val((qty_val - step).toFixed(woobt_decimal_places(step)));
              }
            }

            // trigger change event
            $qty.trigger('change');
          });

  $(document).on('click touch', '.single_add_to_cart_button', function(e) {
    if ($(this).hasClass('woobt-disabled')) {
      e.preventDefault();
    }
  });

  $(document).on('change', '.woobt-checkbox', function() {
    var $wrap = $(this).closest('.woobt-wrap');

    woobt_init($wrap);
  });

  $(document).on('change keyup mouseup', '.woobt-this-qty', function() {
    var this_val = $(this).val();

    $(this).closest('.woobt-product-this').attr('data-qty', this_val);
    $(this).
        closest(woobt_vars.summary_selector).
        find('form.cart .quantity .qty').
        val(this_val).
        trigger('change');
  });

  $(document).on('change keyup mouseup', '.woobt-qty', function() {
    var $this = $(this);
    var $wrap = $this.closest('.woobt-wrap');
    var $product = $this.closest('.woobt-product');
    var $checkbox = $product.find('.woobt-checkbox');
    var this_val = parseFloat($this.val());

    if ($checkbox.prop('checked')) {
      var this_min = parseFloat($this.attr('min'));
      var this_max = parseFloat($this.attr('max'));

      if (this_val < this_min) {
        $this.val(this_min);
      }

      if (this_val > this_max) {
        $this.val(this_max);
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
    if ($('.woobt_products[data-product-type="woosg"]').length) {
      $('.woobt_products[data-product-type="woosg"]').
          find('.woobt-product-this').
          attr('data-price', total).
          attr('data-regular-price', total);
      $('.woobt_products[data-product-type="woosg"]').
          find('.woobt-product-this .woobt-price-ori').hide();
      $('.woobt_products[data-product-type="woosg"]').
          find('.woobt-product-this .woobt-price-new').html(total_html).show();
    }
  });

  $(document).
      on('wooco_calc_price', function(e, total, total_formatted, total_html) {
        // change price for composite products
        if ($('.woobt_products[data-product-type="composite"]').length) {
          $('.woobt_products[data-product-type="composite"]').
              find('.woobt-product-this').
              attr('data-price', total).
              attr('data-regular-price', total);
          $('.woobt_products[data-product-type="composite"]').
              find('.woobt-product-this .woobt-price-ori').hide();
          $('.woobt_products[data-product-type="composite"]').
              find('.woobt-product-this .woobt-price-new').
              html(total_html).
              show();
        }
      });
})(jQuery);

function woobt_init($wrap) {
  var wrap_id = $wrap.attr('data-id');

  if (wrap_id !== undefined && parseInt(wrap_id) > 0) {
    var container = woobt_container(wrap_id);
    var $container = $wrap.closest(container);

    woobt_check_ready($container);
    woobt_calc_price($container);
    woobt_save_ids($container);

    if (woobt_vars.counter !== 'hide') {
      woobt_update_count($container);
    }
  }

  jQuery(document).trigger('woobt_init', [$wrap]);
}

function woobt_check_ready($wrap) {
  var $products = $wrap.find('.woobt-products');
  var $alert = $wrap.find('.woobt-alert');
  var $ids = $wrap.find('.woobt-ids');
  var $btn = $wrap.find('.single_add_to_cart_button');
  var is_selection = false;
  var selection_name = '';
  var optional = $products.attr('data-optional');

  if ((
      optional === 'on'
  ) && (
      $products.find('.woobt-product-this').length > 0
  )) {
    jQuery('form.cart > .quantity').hide();
    jQuery('form.cart .woocommerce-variation-add-to-cart > .quantity').hide();
  }

  if ((woobt_vars.position === 'before') &&
      ($products.attr('data-product-type') === 'variable') &&
      ($products.attr('data-variables') === 'no' ||
          woobt_vars.variation_selector === 'wpc_radio')) {
    $products.closest('.woobt-wrap').insertAfter($ids);
    $products.find('.woobt-qty').removeClass('qty');
  }

  $products.find('.woobt-product-together').each(function() {
    var $this = jQuery(this);
    var _checked = $this.find('.woobt-checkbox').prop('checked');
    var _id = parseInt($this.attr('data-id'));

    if (!_checked) {
      $this.addClass('woobt-hide');
    } else {
      $this.removeClass('woobt-hide');
    }

    if (_checked && (_id == 0)) {
      is_selection = true;

      if (selection_name === '') {
        selection_name = $this.attr('data-name');
      }
    }
  });

  if (is_selection) {
    $btn.addClass('woobt-disabled woobt-selection');
    $alert.html(woobt_vars.alert_selection.replace('[name]',
        '<strong>' + selection_name + '</strong>')).
        slideDown();
  } else {
    $btn.removeClass('woobt-disabled woobt-selection');
    $alert.html('').slideUp();
  }
}

function woobt_calc_price($wrap) {
  var $products = $wrap.find('.woobt-products');
  var $product_this = $products.find('.woobt-product-this');
  var $total = $wrap.find('.woobt-total');
  var $btn = $wrap.find('.single_add_to_cart_button');
  var count = 0, total = 0;
  var total_html = '';
  var discount = parseFloat($products.attr('data-discount'));
  var ori_price = parseFloat($products.attr('data-product-price'));
  var ori_price_suffix = $products.attr('data-product-price-suffix');
  var ori_qty = parseFloat($btn.closest('form.cart').find('input.qty').val());
  var total_ori = ori_price * ori_qty;
  var price_selector = woobt_vars.summary_selector + ' > .price';
  var show_price = $products.attr('data-show-price');
  var fix = Math.pow(10, Number(woobt_vars.price_decimals) + 1);

  if ((woobt_vars.change_price === 'yes_custom') &&
      (woobt_vars.price_selector != null) &&
      (woobt_vars.price_selector !== '')) {
    price_selector = woobt_vars.price_selector;
  }

  $products.find('.woobt-product-together').each(function() {
    var $this = jQuery(this);
    var _checked = $this.find('.woobt-checkbox').prop('checked');
    var _id = parseInt($this.attr('data-id'));
    var _qty = parseFloat($this.attr('data-qty'));
    var _price = $this.attr('data-new-price');
    var _price_suffix = $this.attr('data-price-suffix');
    var _price_ori = $this.attr('data-price');
    var _regular_price = $this.attr('data-regular-price');
    var _total_ori = 0, _total = 0;

    if ((_qty > 0) && (_id > 0)) {
      _total_ori = _qty * _price_ori;

      if (isNaN(_price)) {
        // is percent
        if (_price == '100%') {
          _total_ori = _qty * _regular_price;
          _total = _qty * _price_ori;
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
      if (_checked) {
        count++;
        total += _total;
      }
    }
  });

  total = Math.round(total * fix) / fix;

  if ($product_this.length && (show_price === 'total')) {
    var _qty = parseFloat($product_this.attr('data-qty'));
    var _price_suffix = $product_this.attr('data-price-suffix');

    if (total > 0) {
      var _price = $product_this.attr('data-new-price');
      var _price_ori = $product_this.attr('data-price');
      var _total_ori = _qty * _price_ori,
          _total = _qty * _price;

      $product_this.find('.woobt-price-ori').hide();
      $product_this.find('.woobt-price-new').
          html(woobt_price_html(_total_ori, _total) + _price_suffix).
          show();
    } else {
      var _price = $product_this.attr('data-price');
      var _regular_price = $product_this.attr('data-regular-price');
      var _total_ori = _qty * _regular_price,
          _total = _qty * _price;

      $product_this.find('.woobt-price-ori').hide();
      $product_this.find('.woobt-price-new').
          html(woobt_price_html(_total_ori, _total) + _price_suffix).
          show();
    }
  }

  if (count > 0) {
    total_html = woobt_format_price(total);
    $total.html(
        woobt_vars.total_price_text + ' ' + total_html + ori_price_suffix).
        slideDown();

    if (isNaN(discount)) {
      discount = 0;
    }

    total_ori = total_ori * (100 - discount) / 100 + total;
  } else {
    $total.html('').slideUp();
  }

  // change the main price
  if (woobt_vars.change_price !== 'no') {
    if (parseInt($products.attr('data-product-id')) > 0) {
      jQuery(price_selector).
          html(woobt_format_price(total_ori) + ori_price_suffix);
    } else {
      jQuery(price_selector).
          html($products.attr('data-product-price-html'));
    }
  }

  jQuery(document).
      trigger('woobt_calc_price',
          [total, total_html, total_ori, ori_price_suffix]);

  $wrap.find('.woobt-wrap').attr('data-total', total);
}

function woobt_save_ids($wrap) {
  var $products = $wrap.find('.woobt-products');
  var $ids = $wrap.find('.woobt-ids');
  var items = new Array();

  $products.find('.woobt-product-together').each(function() {
    var $this = jQuery(this);
    var _checked = $this.find('.woobt-checkbox').prop('checked');
    var _id = parseInt($this.attr('data-id'));
    var _qty = parseFloat($this.attr('data-qty'));
    var _price = $this.attr('data-new-price');

    if (_checked && (_qty > 0) && (_id > 0)) {
      items.push(_id + '/' + _price + '/' + _qty);
    }
  });

  if (items.length > 0) {
    $ids.val(items.join(','));
  } else {
    $ids.val('');
  }
}

function woobt_update_count($wrap) {
  var $products = $wrap.find('.woobt-products');
  var $btn = $wrap.find('.single_add_to_cart_button');
  var qty = 0;
  var num = 1;

  $products.find('.woobt-product-together').each(function() {
    var $this = jQuery(this);
    var _checked = $this.find('.woobt-checkbox').prop('checked');
    var _id = parseInt($this.attr('data-id'));
    var _qty = parseFloat($this.attr('data-qty'));

    if (_checked && (_qty > 0) && (_id > 0)) {
      qty += _qty;
      num++;
    }
  });

  if ($btn.closest('form.cart').find('input.qty').length) {
    qty += parseFloat(
        $btn.closest('form.cart').find('input.qty').val());
  }

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

function woobt_container(id) {
  if (jQuery('.woobt-wrap-' + id).closest('#product-' + id).length) {
    return '#product-' + id;
  }

  if (jQuery('.woobt-wrap-' + id).closest('.product.post-' + id).length) {
    return '.product.post-' + id;
  }

  if (jQuery('.woobt-wrap-' + id).closest('div.product').length) {
    return 'div.product';
  }

  return 'body.single-product';
}