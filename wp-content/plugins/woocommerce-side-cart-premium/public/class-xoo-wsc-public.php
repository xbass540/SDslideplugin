<?php

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @since      1.0.0
 *
 * @package    WooCommerce Side Cart
 */

class xoo_wsc_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $xoo_wsc    The ID of this plugin.
	 */
	private $xoo_wsc;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $xoo_wsc    The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $xoo_wsc, $version ) {

		$this->xoo_wsc = $xoo_wsc;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		if(xoo_wsc_suggested_products_enabled()){
			wp_enqueue_style( $this->xoo_wsc.'-slider', XOO_WSC_URL . 'lib/lightslider/css/lightslider.css', array(), $this->version, 'all' );
		}

		wp_enqueue_style( $this->xoo_wsc, plugin_dir_url( __FILE__ ) . 'css/xoo-wsc-public.css', array(), $this->version, 'all' );
		wp_enqueue_style( $this->xoo_wsc.'-fonts', XOO_WSC_URL . 'lib/fonts/xoo-wsc-fonts.css', array(), $this->version, 'all' );

		$options = get_option('xoo-wsc-sy-options');
		$gl_options = get_option('xoo-wsc-gl-options');

		/*
		* User Style Options
		*/

		//Head
		$sch_bgc 	= isset( $options['sch-bgc']) ? $options['sch-bgc'] : '#ffffff'; // BG Color
		$sch_fc 	= isset( $options['sch-fc']) ? $options['sch-fc'] : '#000000'; // Text Color
		$sch_fs 	= isset( $options['sch-fs']) ? $options['sch-fs'] : 20; // Font Size
		$sch_bs 	= isset( $options['sch-bs']) ? $options['sch-bs'] : 1; // Border size
		$sch_bc 	= isset( $options['sch-bc']) ? $options['sch-bc'] : '#eeeeee'; // Border color
		$sch_ps 	= isset( $options['sch-ps']) ? $options['sch-ps'] : '10px 20px'; // Padding
		$sch_cis	= isset( $options['sch-cis']) ? $options['sch-cis'] : 20; // Close Cart Icon size

		//Body
		$scb_cw		= !empty( $options['scb-cw']) ? $options['scb-cw'] : 300; // Container Width
		$scb_bgc 	= isset( $options['scb-bgc']) ? $options['scb-bgc'] : '#ffffff'; // BG Color
		$scb_fc 	= isset( $options['scb-fc']) ? $options['scb-fc'] : '#000000'; // Text Color
		$scb_fs 	= isset( $options['scb-fs']) ? $options['scb-fs'] : 14; // Font Size
		$scb_imgw 	= isset( $options['scb-imgw']) ? $options['scb-imgw'] : 35; // Product Images width
		$scb_sumw   = 100-($scb_imgw+5);
		$scb_rfc 	= isset( $options['scb-rfc']) ? $options['scb-rfc'] : '#000000'; // Remove Text color
		$scb_ptfc 	= isset( $options['scb-ptfc']) ? $options['scb-ptfc'] : '#000000'; // Product Title Color
		$scb_ptfs 	= isset( $options['scb-ptfs']) ? $options['scb-ptfs'] : 16; // Product title Font Size
		$scb_prbc 	= isset( $options['scb-prbc']) ? $options['scb-prbc'] : '#eeeeee'; // Product row border color
		$scb_prbs 	= isset( $options['scb-prbs']) ? $options['scb-prbs'] : 1; // Product row border size

		//Footer
		$scf_bgc 	 = isset( $options['scf-bgc']) ? $options['scf-bgc'] : '#ffffff';//BG Color
		$scf_bm 	 = isset( $options['scf-bm']) ? $options['scf-bm'] : 4; // buttons margin
		$scb_btn_ts	 = isset( $options['scf-btn-ts']) ? $options['scf-btn-ts']: 'false'; //default button styling


		if($scb_btn_ts == 'false'){
			$scf_btn_bgc = isset( $options['scf-btn-bgc']) ? $options['scf-btn-bgc'] : '#777'; // button background color
			$scf_btn_tc  = isset( $options['scf-btn-tc']) ? $options['scf-btn-tc'] : '#fff'; // button text color
			$scf_btn_pd  = isset( $options['scf-btn-pd']) ? $options['scf-btn-pd'] : '5'; // button padding top bottom
		}

		//Basket
		$bk_show  	= isset( $gl_options['bk-show-basket']) ? $gl_options['bk-show-basket'] : 'always_show'; //Show Basket
		$bk_pos 	= isset( $options['bk-pos']) ? $options['bk-pos'] : 'bottom_fixed'; // Basket Position
		$bk_bbgc 	= isset( $options['bk-bbgc']) ? $options['bk-bbgc'] : '#ffffff'; // Basket Background Color
		$bk_bfc 	= isset( $options['bk-bfc']) ? $options['bk-bfc'] : '#000000'; // basket Icon Color
		$bk_bfs 	= isset( $options['bk-bfs']) ? $options['bk-bfs'] : 35; // Basket Icon size
		$bk_cbgc 	= isset( $options['bk-cbgc']) ? $options['bk-cbgc'] : '#cc0086'; // Count background Color
		$bk_cfc 	= isset( $options['bk-cfc']) ? $options['bk-cfc'] : '#ffffff'; // Count font color

		//Suggested product
		$sp_imgw	= isset( $options['sp-imgw']) ? $options['sp-imgw'] : 75; // Thumbnail size
		$sp_bgc		= isset( $options['sp-bgc']) ? $options['sp-bgc'] : '#fff'; // Thumbnail size

		$inline_style = '';

		switch ($bk_pos) {
			case 'top_fixed':
				$bk_pos_type = 'fixed';
				$bk_pos_dir  = 'top';
				break;
			
			case 'bottom_fixed':
				$bk_pos_type = 'fixed';
				$bk_pos_dir  = 'bottom';
				break;

			case 'top':
				$bk_pos_type = 'absolute';
				$bk_pos_dir  = 'top';
				break;
		}

		if($bk_show == 'hide_empty' && WC()->cart->is_empty()){
			$inline_style .= '.xoo-wsc-basket{display: none;}';
		}

		if($scb_btn_ts == 'false'){
			$inline_style .= ".xoo-wsc-footer a.xoo-wsc-ft-btn{
				background-color: {$scf_btn_bgc};
				color: {$scf_btn_tc};
				padding-top: {$scf_btn_pd}px;
				padding-bottom: {$scf_btn_pd}px;
			}

			.xoo-wsc-coupon-submit{
				background-color: {$scf_btn_bgc};
				color: {$scf_btn_tc};
			}";
		}


		$inline_style .= "
			.xoo-wsc-header{
				background-color: {$sch_bgc};
				color: {$sch_fc};
				border-bottom-width: {$sch_bs}px;
				border-bottom-color: {$sch_bc};
				border-bottom-style: solid;
				padding: {$sch_ps};
			}
			.xoo-wsc-ctxt{
				font-size: {$sch_fs}px;
			}
			.xoo-wsc-close{
				font-size: {$sch_cis}px;
			}
			.xoo-wsc-container{
				width: {$scb_cw}px;
			}
			.xoo-wsc-body{
				background-color: {$scb_bgc};
				font-size: {$scb_fs}px;
				color: {$scb_fc};
			}
			input[type='number'].xoo-wsc-qty{
				background-color: {$scb_bgc};
			}
			.xoo-wsc-qtybox{
				border-color: {$scb_fc};
			}
			.xoo-wsc-chng{
				border-color: {$scb_fc};
			}
			a.xoo-wsc-remove{
				color: {$scb_rfc};
			}
			a.xoo-wsc-pname{
				color: {$scb_ptfc};
				font-size: {$scb_ptfs}px;
			}
			.xoo-wsc-img-col{
				width: {$scb_imgw}%;
			}
			.xoo-wsc-sum-col{
				width: {$scb_sumw}%;
			}
			.xoo-wsc-product{
				border-top-style: solid;
				border-top-color: {$scb_prbc};
				border-top-width: {$scb_prbs}px;
			}
			.xoo-wsc-basket{
				background-color: {$bk_bbgc};
				{$bk_pos_dir}: 12px;
				position: {$bk_pos_type};
			}
			.xoo-wsc-basket .xoo-wsc-bki{
				color: {$bk_bfc};
				font-size: {$bk_bfs}px;
			}
			.xoo-wsc-basket img.xoo-wsc-bki{
				width: {$bk_bfs}px;
				height: {$bk_bfs}px;
			}
			.xoo-wsc-items-count{
				background-color: {$bk_cbgc};
				color: {$bk_cfc};
			}
			.xoo-wsc-footer{
				background-color: {$scf_bgc};
			}
			.xoo-wsc-footer a.xoo-wsc-ft-btn{
				margin: {$scf_bm}px 0;
			}
			.xoo-wsc-wp-item img.size-shop_catalog, .xoo-wsc-rp-item img.wp-post-image{
				width: {$sp_imgw}px;
			}
			li.xoo-wsc-rp-item{
				background-color: {$sp_bgc};
			}
		";


		$cont_height = isset( $options['scb-ch']) ? $options['scb-ch'] : 'full_screen';
		$cont_open 	 = isset( $options['scb-open']) ? $options['scb-open'] : 'right';


		if( $cont_height === "auto_adjust" ){

			$inline_style .= ".xoo-wsc-footer{
				position: relative;
			}";

			if($bk_pos === "bottom_fixed"){
				
				$inline_style .= ".xoo-wsc-container{
					bottom: 0;
				}";

			}
			else{
				$inline_style .= ".xoo-wsc-container{
					top: 0;
				}";
			}
		}
		else{
			$inline_style .= ".xoo-wsc-footer{
				position: absolute;
			}
			.xoo-wsc-container{
				top: 0;
				bottom: 0;
			}";
		}


		if( $cont_open === "left" ){
			$inline_style .= "
				.xoo-wsc-basket{
					left: 0;
				}
				.xoo-wsc-basket, .xoo-wsc-container{
					transition-property: left;
				}
				.xoo-wsc-items-count{
					right: -15px;
				}
				.xoo-wsc-container{
					left: -{$scb_cw}px;
				}
				.xoo-wsc-modal.xoo-wsc-active .xoo-wsc-basket{
					left: {$scb_cw}px;
				}
				.xoo-wsc-modal.xoo-wsc-active .xoo-wsc-container{
					left: 0;
				}
			";
		}
		else{
			$inline_style .= "
				.xoo-wsc-basket{
					right: 0;
				}
				.xoo-wsc-basket, .xoo-wsc-container{
					transition-property: right;
				}
				.xoo-wsc-items-count{
					left: -15px;
				}
				.xoo-wsc-container{
					right: -{$scb_cw}px;
				}
				.xoo-wsc-modal.xoo-wsc-active .xoo-wsc-basket{
					right: {$scb_cw}px;
				}
				.xoo-wsc-modal.xoo-wsc-active .xoo-wsc-container{
					right: 0;
				}
			";
		}
		
		wp_add_inline_style($this->xoo_wsc,$inline_style);

		//Custom CSS from user settings
		$av_options = get_option('xoo-wsc-av-options');
		if(isset($av_options['custom-css']) && !empty($av_options['custom-css'])){
			wp_add_inline_style($this->xoo_wsc,$av_options['custom-css']);
		}

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		//User Options
		$gl_options = get_option('xoo-wsc-gl-options');
		$av_options = get_option('xoo-wsc-av-options');
		$sy_options = get_option('xoo-wsc-sy-options');

		$ajax_atc 	 = isset( $gl_options['sc-ajax-atc']) ? $gl_options['sc-ajax-atc'] : 1;

		$atc_icons = isset( $gl_options['sc-atc-icons']) ? $gl_options['sc-atc-icons'] : 1;

		$show_basket = isset( $gl_options['bk-show-basket']) ? $gl_options['bk-show-basket'] : 'always_show'; //Show Basket

		$atc_reset 	 = isset( $gl_options['sc-atc-reset']) ? $gl_options['sc-atc-reset'] : 0;
		

		$flyto_anim = isset( $gl_options['bk-flyto-anim']) ? $gl_options['bk-flyto-anim'] : 1;
		$custom_btn_class = isset( $av_options['custom-btn-class']) ? $av_options['custom-btn-class'] : '';

		$trigger_class = isset($av_options['trigger-class']) ? $av_options['trigger-class'] : null;
		$flytoanim_img_class = isset($av_options['flytocart-class']) ? $av_options['flytocart-class'] : null;

		$cont_height = isset( $sy_options['scb-ch']) ? $sy_options['scb-ch'] : 'full_screen';

		$pec 		 = isset( $gl_options['sc-show-pec']) ? $gl_options['sc-show-pec'] : 0;

		//Check if item added to cart
		if($ajax_atc != 1 && isset($_POST['add-to-cart'])){
			$added_to_cart = true;
		}
		else{
			$added_to_cart = false;
		}

		$auto_open_cart = isset( $gl_options['sc-auto-open']) ? $gl_options['sc-auto-open'] : 1;
		$sp_enabled = xoo_wsc_suggested_products_enabled();

		if($sp_enabled){
			wp_enqueue_script( $this->xoo_wsc.'-lightslider', XOO_WSC_URL . 'lib/lightslider/js/lightslider.js', array( 'jquery' ), $this->version, true );
		}

		if($flyto_anim == 1){
			wp_enqueue_script('jquery-ui', 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.12.1/jquery-ui.min.js');
		}


		//Check if wc-add-to-cart is enqueued
		if (!wp_script_is('wc-add-to-cart', 'enqueued' )) {
	       	wp_enqueue_script( 'wc-add-to-cart',WC()->plugin_url().'/assets/js/frontend/add-to-cart.min.js', array('jquery'), WC_VERSION,true );
	     }

	     if( !wp_script_is( 'wc-cart-fragments', 'enqueued' ) ){
			wp_enqueue_script( 'wc-cart-fragments',WC()->plugin_url().'/assets/js/frontend/cart-fragments.min.js', array('jquery','js-cookie'), WC_VERSION,true );
		}


		//Paypal express checkout
		if( $pec == 1 ){
			wp_enqueue_script( 'wc-gateway-ppec-smart-payment-buttons' );
		}

		wp_enqueue_script( $this->xoo_wsc, plugin_dir_url( __FILE__ ) . 'js/xoo-wsc-public.js', array( 'jquery' ), $this->version, true );

		//When website is loaded for the first time , user ID is set to 0 by wordpress so nonce is created on the basis of 0 ID,
		//after adding to cart , WC changes user id , therefore wp_verify_nonce returns false.
		// If user id is 0 , skip coupon nonce , we will generate it later , once the item is added to cart.
		$uid = apply_filters( 'nonce_user_logged_out', 0, false );
		if( !is_user_logged_in() && !$uid){
			$remove_coupon_nonce = $apply_coupon_nonce = false;
		}
		else{
			$apply_coupon_nonce = wp_create_nonce('apply-coupon');
			$remove_coupon_nonce = wp_create_nonce('remove-coupon');
		}
		

		wp_localize_script($this->xoo_wsc,'xoo_wsc_localize',array(
			'adminurl'			  => admin_url().'admin-ajax.php',
			'wc_ajax_url' 		  => WC_AJAX::get_endpoint( "%%endpoint%%" ),
			'flyto_anim'	 	  => $flyto_anim,
			'custom_btn'		  => $custom_btn_class,
			'ajax_atc'			  => $ajax_atc,
			'added_to_cart' 	  => $added_to_cart,
			'auto_open_cart'	  => $auto_open_cart,
			'atc_icons'  		  => $atc_icons,
			'sp_enabled' 		  => $sp_enabled,
			'apply_coupon_nonce'  => $apply_coupon_nonce,
			'remove_coupon_nonce' => $remove_coupon_nonce,
			'show_basket' 		  => $show_basket,
			'trigger_class'		  => $trigger_class,
			'notification_time'	  => apply_filters('xoo_wsc_notification_time',3000),
			'cont_height'		  => $cont_height,
			'atc_reset' 		  => $atc_reset,
			'flytoanim_img_class' => $flytoanim_img_class
			)
		);
	}

	public function create_nonces(){

		$actions = array(
			'apply-coupon',
			'remove-coupon'
		);

		$nonces = array();

		foreach ($actions as $action) {
			$nonces[$action] = wp_create_nonce( $action );
		}

		wp_send_json( $nonces );
	}


	/**
	* Prevent cart redirect. WC Option Redirect to the cart page after successful addition
	*
	* @since 1.1.0
	* @param mixed $value
	* @param string $option
	* @return mixed
	*/
	public function prevent_cart_redirect($value){

		$gl_options 	= get_option('xoo-wsc-gl-options');
		$cart_redirect  = isset( $gl_options['sc-cart-redirect']) ? $gl_options['sc-cart-redirect'] : 0;

		if(!is_admin() && !$cart_redirect) {
			return 'no';
		}

		return $value;
	}


	/**
	 * Execute Template functions
	 *
	 * @since    1.0.0
	 */


	public function template_functions(){

		$gl_options 	= get_option('xoo-wsc-gl-options');
		$total_sales 	= isset( $gl_options['sc-total-sales']) ? $gl_options['sc-total-sales'] : 1;

		if($total_sales == 1){
			add_action('xoo_wsc_before_product_summary','xoo_wsc_total_sales_display',5);
		}
	}
	

}
