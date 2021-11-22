jQuery(document).ready(function($){
	'use strict';
	var focus_qty;


	//Block cart on fragment refresh
	$(document.body).on('wc_fragment_refresh',block_cart);

	//Inititate slider
	function slider_start(){
		$("body #lightSlider").lightSlider({
			item: 1,
		}); 
	}

	//Unblock cart
	var fadenotice = null;
	$(document.body).on('wc_fragments_refreshed wc_fragments_loaded updated_checkout',function(){
		
		unblock_cart();
		
		if(xoo_wsc_localize.sp_enabled && $('.xoo-wsc-related-products .lSSlideOuter').length <= 0){
			slider_start();
		}

		push_notification();

		content_height();

		if( xoo_wsc_localize.show_basket === "hide_empty" ){
			$('.xoo-wsc-body .xoo-wsc-product').length ? $('.xoo-wsc-basket').show() : $('.xoo-wsc-basket').hide();
		}

	});

	$(document.body).on('xoo_wsc_cart_updated',update_cartChk);


	function update_cartChk(){
		//Refresh checkout page
		if( window.wc_checkout_params && wc_checkout_params.is_checkout === "1" ){
			if( $( 'form.checkout' ).length === 0 ){
				location.reload();
				return;
			}
			$(document.body).trigger("update_checkout");
		}

		//Refresh Cart page
		if( window.wc_add_to_cart_params && window.wc_add_to_cart_params.is_cart && wc_add_to_cart_params.is_cart === "1" ){
			$(document.body).trigger("wc_update_cart");
		}
	}

	// refresh fragment on document load
	$( document.body ).trigger( 'wc_fragment_refresh' );


	//Block Cart
	function block_cart(){
		$('.xoo-wsc-updating').show();
	}

	//Unblock cart
	function unblock_cart(){
		$('.xoo-wsc-updating').hide();
	}

	function push_notification(){
		var notification_el = $('.xoo-wsc-notification-bar');

		if(notification_el.length && notification_el.children().length > 0){
			notification_el.slideDown('slow');
			clearTimeout(fadenotice);
			fadenotice = setTimeout(function(){
				notification_el.slideUp('slow',function(){
					notification_el.html('');
				});
			},parseInt(xoo_wsc_localize.notification_time))
		}
	}


	//Toggle Side Cart
	function toggle_sidecart(toggle_type){
		var toggle_element = $('.xoo-wsc-modal , body, html'),
			toggle_class   = 'xoo-wsc-active';

		if(toggle_type == 'show'){
			toggle_element.addClass(toggle_class);
		}
		else if(toggle_type == 'hide'){
			toggle_element.removeClass(toggle_class);
		}
		else{
			toggle_element.toggleClass('xoo-wsc-active');
		}

		unblock_cart();
	}

	$('body').on('click','.xoo-wsc-basket,.xoo-wsc-sc-cont', function(e){
		e.preventDefault();
		e.stopImmediatePropagation();
		toggle_sidecart();
	});

	if(xoo_wsc_localize.trigger_class){
		$('.'+xoo_wsc_localize.trigger_class).on('click',function(e){
			e.preventDefault();
			e.stopImmediatePropagation();
			toggle_sidecart();
		});
	}


	//Reset cart button/form
	function reset_cart(atc_btn){
		$('.xoo-wsc-icon-atc',atc_btn).remove();
		var qty_elem = atc_btn.parents('form.cart').find('.qty');
		if(qty_elem.length > 0) qty_elem.val(qty_elem.attr('min') || 1);
		$('.added_to_cart').remove();
	}

	//Auto open Side Cart when item added to cart without ajax
	(function(){
		if(xoo_wsc_localize.added_to_cart){
			var toggled = false;
			$(document).on('wc_fragments_refreshed',function(){
				if(!toggled){
					setTimeout(toggle_sidecart,1,'show');
					toggled = true;
				}
			})
		}
	}());


	
	$(document).on('added_to_cart',function(event,fragments,hash,atc_btn){

		var cart = $('.xoo-wsc-basket');

		if(xoo_wsc_localize.show_basket != 'always_hide'){
			cart.show();
		}

		//Auto open with ajax
		var opensidecart = function(){
			if(xoo_wsc_localize.auto_open_cart == 1){
				setTimeout(toggle_sidecart,1,'show');
			}
		}

		if(xoo_wsc_localize.flyto_anim == 1){
			fly_to_cart(atc_btn,opensidecart);
		}
		else{
			opensidecart();
		}

		//Copuon nonce fix
		if( !xoo_wsc_localize.apply_coupon_nonce ){
			//Send ajax request to set coupon
			create_coupon_nonce();
		}

		if(xoo_wsc_localize.atc_reset == 1){
			reset_cart(atc_btn);
		}

		update_cartChk();

	});
	
	function create_coupon_nonce(){
		$.ajax({
			url: xoo_wsc_localize.adminurl,
			type: 'POST',
			data: {
				action: 'xoo_wsc_create_nonces'
			},
			success: function(response){

				if( response['apply-coupon'] ){
					xoo_wsc_localize.apply_coupon_nonce = response['apply-coupon'];
				}

				if( response['remove-coupon'] ){
					xoo_wsc_localize.remove_coupon_nonce = response['remove-coupon'];
				}
			}
		})
	}

	//Close Side Cart
	function close_sidecart(e){
		$.each(e.target.classList,function(key,value){
			if(value != 'xoo-wsc-container' && (value == 'xoo-wsc-close' || value == 'xoo-wsc-opac' || value == 'xoo-wsc-basket' || value == 'xoo-wsc-cont')){
				$('.xoo-wsc-modal , body, html').removeClass('xoo-wsc-active');
			}
		})
	}

	$('body').on('click','.xoo-wsc-close , .xoo-wsc-opac',function(e){
		e.preventDefault();
		close_sidecart(e);
	});

	$('body').on('click','.xoo-wsc-cont',function(e){
		var link = $.trim($(this).attr('href'));
		if( link == "#" || !link){
			e.preventDefault();
			close_sidecart(e);
		}
	});

	//Set Cart content height
	function content_height(){
		var header = $('.xoo-wsc-header').outerHeight(), 
			footer = $('.xoo-wsc-footer').outerHeight(),
			screen = window.innerHeight,
			$cont  = $('.xoo-wsc-container');


		if( xoo_wsc_localize.cont_height == "auto_adjust" ){
			$cont.css({"top": "", "bottom": ""});
			var body_height = 'calc(100% - '+(header+footer)+'px)';
			if( $cont.outerHeight() > screen ){
				$cont.css({"top": "0", "bottom": "0"});
			}
		}
		else{
			var body_height = screen-(header+footer);
		}


		$('.xoo-wsc-body').css('height',body_height);

	};

	content_height();

	$(window).resize(function(){
    	content_height();
	});
	


	//Add to cart function
	function add_to_cart(atc_btn,product_data){

		// Trigger event.
		$( document.body ).trigger( 'adding_to_cart', [ atc_btn, product_data ] );

		$.ajax({
			url: xoo_wsc_localize.wc_ajax_url.toString().replace( '%%endpoint%%', 'xoo_wsc_add_to_cart' ),
			type: 'POST',
			data: $.param(product_data),
		    success: function(response){
		    	
		    	add_to_cart_button_check_icon(atc_btn);

				if(response.fragments){
					// Trigger event so themes can refresh other areas.
					$( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash, atc_btn ] );
				}
				else if(response.error){
					show_notice('error',response.error);
					toggle_sidecart();
				}
				else{
					console.log(response);
				}
		
		    }
		})
	}


	//Update cart
	function update_cart(cart_key,new_qty){
		block_cart();

		var endpoint = 'xoo_wsc_update_cart';
		endpoint += new_qty > 0 ? '&xoo_wsc_qty_update' : '';

		$.ajax({
			url: xoo_wsc_localize.wc_ajax_url.toString().replace( '%%endpoint%%', endpoint ),
			type: 'POST',
			data: {
				cart_key: cart_key,
				new_qty: new_qty
			},
			success: function(response){
				if(response.fragments){
					var fragments = response.fragments,
						cart_hash =  response.cart_hash;

					//Set fragments
			   		$.each( response.fragments, function( key, value ) {
						$( key ).replaceWith( value );
						$( key ).stop( true ).css( 'opacity', '1' ).unblock();
					});

			   		if(wc_cart_fragments_params){
				   		var cart_hash_key = wc_cart_fragments_params.ajax_url.toString() + '-wc_cart_hash';
						//Set cart hash
						sessionStorage.setItem( wc_cart_fragments_params.fragment_name, JSON.stringify( fragments ) );
						localStorage.setItem( cart_hash_key, cart_hash );
						sessionStorage.setItem( cart_hash_key, cart_hash );
					}

					$(document.body).trigger('wc_fragments_loaded');
					$(document.body).trigger('xoo_wsc_cart_updated');
				}
				else{
					//Print error
					show_notice('error',response.error);
				}
			}

		})
	}

	
	//Plus minus buttons
	$(document).on('click', '.xoo-wsc-chng' ,function(){
		var _this = $(this);
		var qty_element = _this.siblings('.xoo-wsc-qty');
		qty_element.trigger('focusin');
		var input_qty = parseFloat(qty_element.val());

		var step = parseFloat(qty_element.attr('step'));
		var min_value = parseFloat(qty_element.attr('min'));
		var max_value = parseFloat(qty_element.attr('max'));

		if(_this.hasClass('xoo-wsc-plus')){
			var new_qty	  = input_qty + step;
		
			if(new_qty > max_value && max_value > 0){
				alert('Maximum Quantity: '+max_value);
				return;
			}
		}
		else if(_this.hasClass('xoo-wsc-minus')){
			
			var new_qty = input_qty - step;
			if(new_qty === 0){
				_this.parents('.xoo-wsc-product').find('.xoo-wsc-remove').trigger('click');
				return;
			}
			else if(new_qty < min_value){
				return;
			} 
			else if(input_qty < 0){
				alert('Invalid');
				return;
			}
		}
		var cart_key = _this.parents('.xoo-wsc-product').data('xoo_wsc');
		update_cart(cart_key,new_qty);
	})

	//Save Quantity on focus
	$(document).on('focusin','.xoo-wsc-qty',function(){
		focus_qty = $(this).val();
	})


	//Qty input on change
	$(document).on('change','.xoo-wsc-qty',function(e){
		var _this = $(this);
		var new_qty = parseFloat($(this).val());
		var step = parseFloat($(this).attr('step'));
		var min_value = parseFloat($(this).attr('min'));
		var max_value = parseFloat($(this).attr('max'));
		var invalid  = false;

		var cart_key = _this.parents('.xoo-wsc-product').data('xoo_wsc');
	
		if(new_qty === 0){
			_this.parents('.xoo-wsc-product').find('.xoo-wsc-remove').trigger('click');
			return;
		}
		//Check If valid number
		else if(isNaN(new_qty)  || new_qty < 0){
			invalid = true;
		}

		//Check maximum quantity
		else if(new_qty > max_value && max_value > 0){
			alert('Maximum Quantity: '+max_value);
			invalid = true;
		}

		//Check Minimum Quantity
		else if(new_qty < min_value){
			invalid = true;
		}

		//Check Step
		else if((new_qty % step) !== 0){
			alert('Quantity can only be purchased in multiple of '+step);
			invalid = true;
		}

		//Update if everything is fine.
		else{
			update_cart(cart_key,new_qty);
		}

		if(invalid === true){
			$(this).val(focus_qty);
		}
	})


	//Remove item from cart
	$(document).on('click','.xoo-wsc-remove',function(e){
		e.preventDefault();
		var product_row = $(this).parents('.xoo-wsc-product');
		var cart_key = product_row.data('xoo_wsc');
		update_cart(cart_key,0);
	})

	//Add to cart on single page
	$(document).on('submit','form.cart',function(e){

		if( xoo_wsc_localize.ajax_atc != 1 ) return;

		if( $(this).closest('.product').hasClass('product-type-external') ) return;

		e.preventDefault();
		block_cart();
		var form = $(this);
		var atc_btn  = form.find( 'button[type="submit"]');

		add_to_cart_button_loading_icon(atc_btn);

		var product_data = form.serializeArray();

		//Check for woocommerce custom quantity code 
		//https://docs.woocommerce.com/document/override-loop-template-and-show-quantities-next-to-add-to-cart-buttons/
		var has_product_id = false;
		$.each(product_data,function(key,form_item){
			if(form_item.name === 'product_id' || form_item.name === 'add-to-cart'){
				if(form_item.value){
					has_product_id = true;
					return false;
				}
			}
		})

		//If no product id found , look for the form action URL
		if(!has_product_id){
			var is_url = form.attr('action').match(/add-to-cart=([0-9]+)/);
			var product_id = is_url ? is_url[1] : false; 
		}

		// if button as name add-to-cart get it and add to form
        if( atc_btn.attr('name') && atc_btn.attr('name') == 'add-to-cart' && atc_btn.attr('value') ){
            var product_id = atc_btn.attr('value');
        }

        if(product_id){
        	product_data.push({name: 'add-to-cart', value: product_id});
        }


        product_data.push({name: 'action', value: 'xoo_wsc_add_to_cart'});

		add_to_cart(atc_btn,product_data);//Ajax add to cart
	})


	//Notice Function
	function show_notice(notice_type,notice){
	 	$('.xoo-wsc-notice').html(notice).attr('class','xoo-wsc-notice').addClass('xoo-wsc-nt-'+notice_type);
	 	$('.xoo-wsc-notice-box').fadeIn('fast');
	 	clearTimeout(fadenotice);
	 	var fadenotice = setTimeout(function(){
	 		$('.xoo-wsc-notice-box').fadeOut('slow');
	 	},2000);
	};

	//Add to cart preloader
	function add_to_cart_button_loading_icon(atc_btn){
		if(xoo_wsc_localize.atc_icons != 1) return;

		if(atc_btn.find('.xoo-wsc-icon-atc').length !== 0){
			atc_btn.find('.xoo-wsc-icon-atc').attr('class','xoo-wsc-icon-spinner2 xoo-wsc-icon-atc xoo-wsc-active');
		}
		else{
			atc_btn.append('<span class="xoo-wsc-icon-spinner2 xoo-wsc-icon-atc xoo-wsc-active"></span>');
		}
	}

	//Add to cart check icon
	function add_to_cart_button_check_icon(atc_btn){
		if(xoo_wsc_localize.atc_icons != 1) return;
		// Check icon
   		atc_btn.find('.xoo-wsc-icon-atc').attr('class','xoo-wsc-icon-checkmark xoo-wsc-icon-atc');
	}


	//Show Promo input
	$(document).on('click','.xoo-wsc-coupon-trigger',function(){
		$('.xoo-wsc-coupon').toggleClass('active');
		$(this).toggleClass('active');
	})


	//Add promo vode
	$(document).on('click','.xoo-wsc-coupon-submit',function(e){

		var coupon 		= $('#xoo-wsc-coupon-code');
		var coupon_code = (coupon.val()).trim();

		if(!coupon_code.length){
			return;
		}

		$('.xoo-wsc-block-cart').show();

		$(this).addClass('active');

		var data = {
			security: xoo_wsc_localize.apply_coupon_nonce,
			coupon_code: coupon_code
		}

		$.ajax({
			url: xoo_wsc_localize.wc_ajax_url.toString().replace( '%%endpoint%%', 'apply_coupon' ),
			type: 'POST',
			data: data,
			success: function(response){
				show_notice('error',response);
				$( document.body ).trigger( 'applied_coupon', [ coupon_code ] );
				$( document.body ).trigger( 'wc_fragment_refresh' );
			},
			complete: function(){
				$('.xoo-wsc-block-cart').hide();
			}
		})

	})


	//Remove promo code
	$(document).on('click','.xoo-wsc-remove-coupon',function(e){

		var coupon = $(this).attr('data-coupon');

		if(!coupon.length){
			e.preventDefault();
		}

		$(this).css("pointer-events","none");

		block_cart();

		var data = {
			security: xoo_wsc_localize.remove_coupon_nonce,
			coupon: coupon
		}

		$.ajax({
			url: xoo_wsc_localize.wc_ajax_url.toString().replace( '%%endpoint%%', 'remove_coupon' ),
			type: 'POST',
			data: data,
			success: function(response){
				show_notice('error',response);
				$( document.body ).trigger( 'removed_coupon', [ coupon ] );
				$( document.body ).trigger( 'wc_fragment_refresh' );
				
			},
			complete: function(){
				$('.xoo-wsc-block-cart').hide();
			}
		})

	})



	//Undo
	$(document).on('click','.xoo-wsc-undo-item',function(){
		
		var cart_key = $(this).data('xoo_ckey');
		if(!cart_key) return;

		block_cart();

		$.ajax({
			url: xoo_wsc_localize.wc_ajax_url.toString().replace( '%%endpoint%%', 'xoo_wsc_undo_item' ),
			type: 'POST',
			data: {
				cart_key: cart_key,
			},
			success: function(response){
				if(response.fragments){
					$( document.body ).trigger( 'added_to_cart', [ response.fragments, response.cart_hash] );
				}
				else if(response.error){
					show_notice('error',response.error)
				}
				else{
					console.log(response);
				}
				unblock_cart();
			}
		})
	})


	function fly_to_cart(atc_btn,callback){ 

        var cart = $('.xoo-wsc-basket');

        if(cart.length < 1){
        	cart = $('.xoo-wsc-sc-cont');
        }

    

        if(atc_btn.parents('form.cart').length !== 0){
	  		var imgtodrag = $('.woocommerce-product-gallery');
	  	}
	  	else{
	  		var imgtodrag = atc_btn.parents('.product');
	  	}

	    if(!imgtodrag.length && xoo_wsc_localize.flytoanim_img_class ){
        	var imgtodrag = $( '.'+xoo_wsc_localize.flytoanim_img_class );
        }


	  	if(imgtodrag.length === 0 || cart.length === 0){
	  		callback();
	  		return;
	  	} // Exit if image/cart not found

       
        var imgclone = imgtodrag.clone()
            .offset({
            top: imgtodrag.offset().top,
            left: imgtodrag.offset().left
        })
         
        	.addClass('xoo-wsc-fly-animating')
            .appendTo($('body'))
            .animate({
            'top': cart.offset().top - 10,
                'left': cart.offset().left - 10,
                'width': 75,
                'height': 75
        }, 1000, 'easeInOutExpo');
        
        setTimeout(function () {
            cart.effect("shake", {
                times: 1
            }, 200, setTimeout(function(){
            	callback();
            },200));
        }, 1500);

        imgclone.animate({
            'width': 0,
                'height': 0
        }, function () {
            $(this).detach();
        });

	}
})