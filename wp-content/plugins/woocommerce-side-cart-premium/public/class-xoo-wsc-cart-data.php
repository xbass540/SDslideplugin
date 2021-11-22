<?php
class xoo_wsc_Cart_Data{
	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0 
	 * @access   private
	 * @var      string    $xoo_wsc    The ID of this plugin.
	 */
	private $xoo_wsc;

	public $action;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $xoo_wsc    The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $xoo_wsc ) {

		$this->xoo_wsc = $xoo_wsc;

	}

	
	/**
	 * Get Side Cart HTML
	 *
	 * @since     1.0.0
	 * @return    string 
	 */

	public function get_cart_markup(){
		wc_get_template('xoo-wsc-markup.php','','',XOO_WSC_PATH.'/public/partials/');
	}


	/**
	 * Get Side Cart Content
	 *
	 * @since     1.0.0
	 */

	public function get_cart_content(){
		$cart_data 	= WC()->cart->get_cart(); 
		$options 	= get_option('xoo-wsc-gl-options');
		$sy_options = get_option('xoo-wsc-sy-options');

		$args_content = array(
			'options' => $options,
			'sy_options' => $sy_options
		);

		ob_start();
		wc_get_template('xoo-wsc-content.php',$args_content,'',XOO_WSC_PATH.'/public/partials/');
		return ob_get_clean();
	}


	//On add to cart , set action
	public function on_add_to_cart(){
		$this->action = 'add';
	}


	/**
	 * Add product to cart
	 *
	 * @since     1.0.0
	 */


	public function xoo_wsc_add_to_cart_ajax(){

		if(!isset($_POST['action']) || $_POST['action'] != 'xoo_wsc_add_to_cart' || !isset($_POST['add-to-cart'])){
			die();
		}
		
		// get woocommerce error notice
		$error = wc_get_notices( 'error' );
		$html = '';

		if( $error ){
			// print notice
			ob_start();
			foreach( $error as $value ) {
				wc_print_notice( $value['notice'], 'error' );
			}

			$js_data =  array(
				'error' => ob_get_clean()
			);

			wc_clear_notices(); // clear other notice
			wp_send_json($js_data);
		}
		
		else{
			// trigger action for added to cart in ajax
			do_action( 'woocommerce_ajax_added_to_cart', intval( $_POST['add-to-cart'] ) );
			wc_clear_notices(); // clear other notice
			WC_AJAX::get_refreshed_fragments();	
		}

		die();
	}



	/**
	 * Update product quantity in cart.
	 *
	 * @since     1.0.0
	 */

	public function update_cart_ajax(){

		//Form Input Values
		$cart_key = sanitize_text_field($_POST['cart_key']);
		$new_qty = (int) $_POST['new_qty'];

		if(!is_numeric($new_qty) || $new_qty < 0 || !$cart_key){

			wp_send_json(array('error' => __('Something went wrong','side-cart-woocommerce')));
		}
		

		$cart_success = $new_qty === 0 ? WC()->cart->remove_cart_item($cart_key) : WC()->cart->set_quantity($cart_key,$new_qty);
		
		if($cart_success){
			$this->action = $new_qty === 0 ? 'remove' : 'update';
			WC_AJAX::get_refreshed_fragments();
		}
		else{
			if(wc_notice_count('error') > 0){
	    		echo wc_print_notices();
			}
		}
		die();
	}


	//Undo
	public function undo_item(){
		$cart_key = sanitize_text_field($_POST['cart_key']);
		if(!$cart_key) return;

		$cart_success = WC()->cart->restore_cart_item($cart_key);

		if($cart_success){
			$this->action = 'undo';
			WC_AJAX::get_refreshed_fragments();
		}
		else{
			if(wc_notice_count('error') > 0){
	    		echo wc_print_notices();
			}
		}
		die();

	}


	//Get notice
	public function get_notice(){

		if(!$this->action) return;

		switch ($this->action) {
			case 'add':
				$notice = __('Item added','side-cart-woocommerce');
				break;
			
			case 'update':
				$notice = __('Item updated','side-cart-woocommerce');
				break;

			case 'remove':
				$cart_key = sanitize_text_field($_POST['cart_key']);
				$notice = __('Item removed','side-cart-woocommerce');
				$notice .= $cart_key ? '<span class="xoo-wsc-undo-item" data-xoo_ckey="'.$cart_key.'">'.__('Undo?','side-cart-woocommerce').'</span>' : null;  
				break;

			case 'undo':
				$notice = __('Item added back','side-cart-woocommerce');
				break;
		}

		return '<span class="xoo-wsc-icon-check_circle"></span>'.$notice;
	}


	/**
	Set fragments
	**/

	public function set_ajax_fragments($fragments){

		WC()->cart->calculate_totals();

		//Get User Settings
		$options = get_option('xoo-wsc-gl-options');
		$show_count = isset( $options['bk-show-bkcount']) ? $options['bk-show-bkcount'] : 1;
		$count_type = isset( $options['bk-count-type']) ? $options['bk-count-type'] : 'qty_count'; //Count Type

		
		if($count_type == 'qty_count'){
			$count_value = WC()->cart->get_cart_contents_count();
		}
		elseif($count_type == 'item_count'){
			$count_value = count(WC()->cart->get_cart());
		}

		$notification = isset( $options['sc-notify']) ? $options['sc-notify'] : 1; // Notification

		$cart_content = $this->get_cart_content();
		$suggested_products = $this->get_suggested_products();
		$cart_footer = $this->get_cart_footer_content();

		$cart_subtotal = wc_price(WC()->cart->subtotal);

		//Cart content
		$fragments['div.xoo-wsc-body'] = '<div class="xoo-wsc-body">'.$cart_content.'</div>';

		//Total Count
		$fragments['span.xoo-wsc-items-count'] = '<span class="xoo-wsc-items-count">'.$count_value.'</span>';

		//Cart footer
		$fragments['div.xoo-wsc-footer-content'] = '<div class="xoo-wsc-footer-content">'.$cart_footer.'</div>';

		//Push notification
		if($this->action && $notification == 1){
			$fragments['div.xoo-wsc-notification-bar'] = '<div class="xoo-wsc-notification-bar">'.$this->get_notice().'</div>';
		}


		//Suggested products
		if(!isset($_GET['xoo_wsc_qty_update'])){
			$fragments['div.xoo-wsc-related-products'] = '<div class="xoo-wsc-related-products">'.$suggested_products.'</div>';
		}


		ob_start();
		wc_get_template('xoo-wsc-shortcode.php','','',XOO_WSC_PATH.'/public/partials/');
		$fragments['a.xoo-wsc-sc-cont'] = ob_get_clean();
		
		return $fragments;
	}

	//Get suggested products
	public static function get_suggested_products(){

		$gl_options = get_option('xoo-wsc-gl-options');
		$enable = isset( $gl_options['sp-enable']) ? $gl_options['sp-enable'] : 1;
		$enable_mobile = isset( $gl_options['sp-enable-mobile']) ? $gl_options['sp-enable-mobile'] : 0;
		if($enable != 1 || ($enable_mobile != 1 && wp_is_mobile())) return;

		$type 		 = isset( $gl_options['sp-type']) ? $gl_options['sp-type'] : 'cross_sells';
		$items_count = isset( $gl_options['sp-count']) ? $gl_options['sp-count'] : 5;
		$title  	 = isset( $gl_options['sp-title']) ? $gl_options['sp-title'] :  __('Products you may like','side-cart-woocommerce');
		$cart 		 = WC()->cart->get_cart();
		$cart_is_empty = WC()->cart->is_empty();

		$suggested_products = array();
		$exclude_ids = array();

		if(!$cart_is_empty){
			foreach ($cart as $cart_item) {
				$exclude_ids[] = $cart_item['product_id'];
			}

			switch ($type) {
			case 'cross_sells':
				$suggested_products = WC()->cart->get_cross_sells();
				break;

			case 'up_sells':

				$last_cart_item = end($cart);
				$product_id 	= $last_cart_item['product_id'];
				$variation_id 	= $last_cart_item['variation_id'];

				if($variation_id){
					$product = wc_get_product($product_id);
					$suggested_products = $product->get_upsell_ids();
				}
				else{
					$suggested_products = $last_cart_item['data']->get_upsell_ids();
				}
				break;

			case 'related':
				$cart_rand = shuffle($cart);

				foreach ($cart as $cart_item) {
					if(count($suggested_products) >= $items_count)
						break;


					$product_id = $cart_item['variation_id'] ? $cart_item['variation_id'] : $cart_item['product_id'];
					$related_products 	= wc_get_related_products($product_id,$items_count,$exclude_ids);
					$suggested_products = array_merge($suggested_products,$related_products);
				}
				break;
			}

		}


		$items_count = count($suggested_products) !== 0 ? count($suggested_products)  : $items_count;

		$args = array(
			'suggested_products' => $suggested_products,
			'items_count'		=> $items_count,
			'exclude_ids'		=> $exclude_ids,
			'title' 			=> $title
		);


		$args = apply_filters( 'xoo_wsc_suggested_product_args', $args );

		ob_start();
		wc_get_template('xoo-wsc-suggested-products.php',$args,'',XOO_WSC_PATH.'/public/partials/');
		return ob_get_clean();

	}


	public function get_cart_footer_content(){
		$options 	= get_option('xoo-wsc-gl-options');
		$sy_options = get_option('xoo-wsc-sy-options');

		$args = array(
			'options' => $options,
			'sy_options' => $sy_options
		);

		ob_start();
		wc_get_template('xoo-wsc-footer.php',$args,'',XOO_WSC_PATH.'/public/partials/');
		return ob_get_clean();
	}
}
?>