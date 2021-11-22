<?php

/**
 * Admin Part of Plugin, dashboard and options.
 *
 * @package    WooCommerce Side Cart
 */
class xoo_wsc_General_Settings extends xoo_wsc_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0 
	 * @access   private
	 * @var      string    $xoo_wsc    The ID of this plugin.
	 */
	private $xoo_wsc;

	/**
	 * The ID of General Settings.
	 *
	 * @since    1.0.0 
	 * @access   private
	 * @var      string    $group    The ID of General Settings.
	 */
	private $group;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $xoo_wsc     The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $xoo_wsc ) {

		$this->xoo_wsc = $xoo_wsc;
		$this->group = $xoo_wsc.'-gl';
	}

	/**
	 * Creates our settings sections with fields etc. 
	 *
	 * @since    1.0.0
	 */
	public function settings_api_init(){
		
		// register_setting( $option_group, $option_name, $settings_sanitize_callback );
		register_setting(
			$this->group . '-options',
			$this->group . '-options',
			array( $this, 'settings_sanitize' )
		);

		// add_settings_section( $id, $title, $callback, $menu_slug );
		add_settings_section(
			$this->group . '-sc-options', // section
			'',
			array( $this, 'sc_options_section' ),
			$this->group // Side Cart Section
		);

		add_settings_section(
			$this->group . '-bk-options', // section
			'',
			array( $this, 'bk_options_section' ),
			$this->group // Cart Basket Section
		);

		add_settings_section(
			$this->group . '-sp-options', // section
			'',
			array( $this, 'sp_options_section' ),
			$this->group // Suggest Basket Section
		);


		/*
		 =============================================
		 ============= Side Cart Fields ==============
		 =============================================
		*/

		// add_settings_field( $id, $title, $callback, $menu_slug, $section, $args );
		add_settings_field(
			'sc-auto-open',
			 __( 'Auto Open', 'side-cart-woocommerce' ),
			array( $this, 'sc_auto_open' ),
			$this->group,
			$this->group . '-sc-options' // Auto Open Side Cart
		);

		add_settings_field(
			'sc-ajax-atc',
			 __( 'Ajax Add to Cart', 'side-cart-woocommerce' ),
			array( $this, 'sc_ajax_atc' ),
			$this->group,
			$this->group . '-sc-options' // ajax add to cart
		);

		add_settings_field(
			'sc-atc-icons',
			 __( 'Loading Icon', 'side-cart-woocommerce' ),
			array( $this, 'sc_atc_icons' ),
			$this->group,
			$this->group . '-sc-options' // show icons on add to cart
		);


		add_settings_field(
			'sc-atc-reset',
			 __( 'Reset Cart Form', 'side-cart-woocommerce' ),
			array( $this, 'sc_atc_reset' ),
			$this->group,
			$this->group . '-sc-options' // Reset cart form
		);

		add_settings_field(
			'sc-update-qty',
			 __( 'Update Quantity', 'side-cart-woocommerce' ),
			array( $this, 'sc_update_qty' ),
			$this->group,
			$this->group . '-sc-options' // Update quantity
		);

		add_settings_field(
			'sc-total-sales',
			 __( 'Total Sales', 'side-cart-woocommerce' ),
			array( $this, 'sc_total_sales' ),
			$this->group,
			$this->group . '-sc-options' // Total sales
		);


		add_settings_field(
			'sc-tnotify',
			 __( 'Notification', 'side-cart-woocommerce' ),
			array( $this, 'sc_notify' ),
			$this->group,
			$this->group . '-sc-options' // Notification
		);


		add_settings_field(
			'sc-show-shptax',
			 __( 'Show Shipping & Tax', 'side-cart-woocommerce' ),
			array( $this, 'sc_show_shptax' ),
			$this->group,
			$this->group . '-sc-options' // Shipping tax
		);


		add_settings_field(
			'sc-show-coupon',
			 __( 'Show Coupon', 'side-cart-woocommerce' ),
			array( $this, 'sc_show_coupon' ),
			$this->group,
			$this->group . '-sc-options' // Show coupon
		);

		add_settings_field(
			'sc-show-pec',
			 __( 'Paypal Express checkout', 'side-cart-woocommerce' ),
			array( $this, 'sc_show_pec' ),
			$this->group,
			$this->group . '-sc-options' //Paypal Express checkout
		);


		add_settings_field(
			'sc-head-text',
			 __( 'Head Title', 'side-cart-woocommerce' ),
			array( $this, 'sc_head_text' ),
			$this->group,
			$this->group . '-sc-options' // Cart Head Text
		);

		add_settings_field(
			'sc-subtotal-text',
			 __( 'Subtotal', 'side-cart-woocommerce' ),
			array( $this, 'sc_subtotal_text' ),
			$this->group,
			$this->group . '-sc-options' // Cart Head Text
		);

		add_settings_field(
			'sc-shipping-text',
			 __( 'Shipping Text', 'side-cart-woocommerce' ),
			array( $this, 'sc_shipping_text' ),
			$this->group,
			$this->group . '-sc-options' // Shipping Text
		);

		add_settings_field(
			'sc-cart-text',
			 __( 'Cart Button Text', 'side-cart-woocommerce' ),
			array( $this, 'sc_cart_text' ),
			$this->group,
			$this->group . '-sc-options' // Cart Button Text
		);

		add_settings_field(
			'sc-checkout-text',
			 __( 'Checkout Button Text', 'side-cart-woocommerce' ),
			array( $this, 'sc_checkout_text' ),
			$this->group,
			$this->group . '-sc-options' // Checkout Button Text
		);

		add_settings_field(
			'sc-continue-text',
			 __( 'Continue Button Text', 'side-cart-woocommerce' ),
			array( $this, 'sc_continue_text' ),
			$this->group,
			$this->group . '-sc-options' // Continue Button Text
		);

		add_settings_field(
			'sc-cont-btn-url',
			 __( 'Cotinue Button URL', 'side-cart-woocommerce' ),
			array( $this, 'sc_cont_btn_url' ),
			$this->group,
			$this->group . '-sc-options' // Continue Button URL
		);

		add_settings_field(
			'sc-empty-cart-text',
			 __( 'Empty Cart Button', 'side-cart-woocommerce' ),
			array( $this, 'sc_empty_cart_text' ),
			$this->group,
			$this->group . '-sc-options' // Empty Cart Button Text
		);

		add_settings_field(
			'sc-empty-text',
			 __( 'Cart Is Empty Text', 'side-cart-woocommerce' ),
			array( $this, 'sc_empty_text' ),
			$this->group,
			$this->group . '-sc-options' // Cart Is Empty Text
		);


		add_settings_field(
			'sc-show-price',
			 __( 'Product Price', 'side-cart-woocommerce' ),
			array( $this, 'sc_show_price' ),
			$this->group,
			$this->group . '-sc-options' // Product Price
		);

		add_settings_field(
			'sc-show-ptotal',
			 __( 'Product total', 'side-cart-woocommerce' ),
			array( $this, 'sc_show_ptotal' ),
			$this->group,
			$this->group . '-sc-options' // Product total
		);


		/*
		 =============================================
		 ============= Cart Basket Fields ============
		 =============================================
		*/

		add_settings_field(
			'bk-show-basket',
			 __( 'Enable Basket', 'side-cart-woocommerce' ),
			array( $this, 'bk_show_basket' ),
			$this->group,
			$this->group . '-bk-options' // Cart Basket
		);

		add_settings_field(
			'bk-show-basket-mobile',
			 __( 'Basket on mobile', 'side-cart-woocommerce' ),
			array( $this, 'bk_show_basket_mobile' ),
			$this->group,
			$this->group . '-bk-options' // Cart Basket
		);

		add_settings_field(
			'bk-hide-basket-pages',
			 __( 'Hide Basket Pages', 'side-cart-woocommerce' ),
			array( $this, 'bk_hide_basket_pages' ),
			$this->group,
			$this->group . '-bk-options' // Cart Basket
		);

		add_settings_field(
			'bk-show-bkcount',
			 __( 'Product Count', 'side-cart-woocommerce' ),
			array( $this, 'bk_show_bkcount' ),
			$this->group,
			$this->group . '-bk-options' // Product Count
		);

		add_settings_field(
			'bk-count-type',
			 __( 'Count Type', 'side-cart-woocommerce' ),
			array( $this, 'bk_count_type' ),
			$this->group,
			$this->group . '-bk-options' // Count Type
		);

		add_settings_field(
			'bk-flyto-anim',
			 __( 'FlyTo Cart Animation', 'side-cart-woocommerce' ),
			array( $this, 'bk_flyto_anim' ),
			$this->group,
			$this->group . '-bk-options' // Flyto Cart
		);



		/*
		 =============================================
		 ============= Suggested Products Fields ============
		 =============================================
		*/

		 add_settings_field(
			'sp-enable',
			 __( 'Enable', 'side-cart-woocommerce' ),
			array( $this, 'sp_enable' ),
			$this->group,
			$this->group . '-sp-options' // Enable
		);


		 add_settings_field(
			'sp-enable-mobile',
			 __( 'Enable on mobile devices', 'side-cart-woocommerce' ),
			array( $this, 'sp_enable_mobile' ),
			$this->group,
			$this->group . '-sp-options' // Enable on mobile
		);

		 add_settings_field(
			'sp-type',
			 __( 'Type', 'side-cart-woocommerce' ),
			array( $this, 'sp_type' ),
			$this->group,
			$this->group . '-sp-options' // Type
		);

		 add_settings_field(
			'sp-count',
			 __( 'Number of suggested products', 'side-cart-woocommerce' ),
			array( $this, 'sp_count' ),
			$this->group,
			$this->group . '-sp-options' // Count
		);


		


		 add_settings_field(
			'sp-title',
			 __( 'Title', 'side-cart-woocommerce' ),
			array( $this, 'sp_title' ),
			$this->group,
			$this->group . '-sp-options' // Count
		);


	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @return 		mixed 						The settings section
	 */
	public function sc_options_section() {
		$this->get_section_markup('Side Cart');

	} 


	/**
	 * Creates a basket section
	 *
	 * @since 		1.0.0
	 * @return 		mixed 						The settings section
	 */
	public function bk_options_section() {
		$this->get_section_markup('Cart Basket');
	} 


	
	/**
	 * Creates a Suggested Products section
	 *
	 * @since 		1.0.0
	 * @return 		mixed 						The settings section
	 */
	public function sp_options_section() {
		$this->get_section_markup('Suggested Products');
	}  


	/*
	 =============================================
	 ============= Side Cart Section =============
	 =============================================
	*/

	/**
	 * Enable Bar Field
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_auto_open() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-auto-open']) ? $options['sc-auto-open'] : 1;
		$id 		= $this->group.'-options[sc-auto-open]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Auto open side cart when item is added to cart.</label> <?php
	}


	/**
	 * Enable Ajax add to cart
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_ajax_atc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-ajax-atc']) ? $options['sc-ajax-atc'] : 1;
		$id 		= $this->group.'-options[sc-ajax-atc]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Add to cart without page refresh.</label> <?php
	}



	/**
	 * Show icons while adding to cart
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_atc_icons() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-atc-icons']) ? $options['sc-atc-icons'] : 1;
		$id 		= $this->group.'-options[sc-atc-icons]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Show preloader/check icon while adding to cart.</label> <?php
	}


	/**
	 * Reset cart form
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_atc_reset() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-atc-reset']) ? $options['sc-atc-reset'] : 0;
		$id 		= $this->group.'-options[sc-atc-reset]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Resets quantity , removes "View cart" button.</label> <?php
	} 


	/**Update Quantity
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_update_qty() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-update-qty']) ? $options['sc-update-qty'] : 1;
		$id 		= $this->group.'-options[sc-update-qty]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Update quantity in the sidecart.</label> <?php
	}



	/**Total Sales
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_total_sales() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-total-sales']) ? $options['sc-total-sales'] : 1;
		$id 		= $this->group.'-options[sc-total-sales]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Show number of times a product has been purchased.</label> <?php
	}


	/**Enable Notification
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_notify() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-notify']) ? $options['sc-notify'] : 1;
		$id 		= $this->group.'-options[sc-notify]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Show notification on cart update.</label> <?php
	}


	/**Show Shipping & Tax
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_show_shptax() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-show-shptax']) ? $options['sc-show-shptax'] : 1;
		$id 		= $this->group.'-options[sc-show-shptax]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Shipping & Tax in footer.</label><?php
	}


	/**Show coupons
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_show_coupon() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-show-coupon']) ? $options['sc-show-coupon'] : 'always_show';
		$id 		= $this->group.'-options[sc-show-coupon]';
		?>
		<select name="<?php echo $id; ?>">
			<option value="always_show" <?php selected( $option, "always_show" ); ?>>Always show</option>
			<option value="toggle_show" <?php selected( $option, "toggle_show" ); ?>>Toggle Show</option>
			<option value="disable" <?php selected( $option, "disable" ); ?>>Disable</option>
		</select><?php
	}


	/**Show Paypal Express checkout
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_show_pec() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-show-pec']) ? $options['sc-show-pec'] : 0;
		$id 		= $this->group.'-options[sc-show-pec]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Paypal Express checkout.</label>
		<p class="description"><a href="https://wordpress.org/plugins/woocommerce-gateway-paypal-express-checkout/" target="_blank">Woocommerce paypal express checkout plugin</a></p>
		<?php
	}




	/**
	 * Head Title
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_head_text() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-head-text']) ? $options['sc-head-text'] : __('Your Cart','side-cart-woocommerce');

		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $this->group; ?>-options[sc-head-text]" />
		<?php
	}


	/**
	 * Subtotal text
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_subtotal_text() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-subtotal-text']) ? $options['sc-subtotal-text'] : __('Subtotal:','side-cart-woocommerce');

		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $this->group; ?>-options[sc-subtotal-text]" />
		<?php
	}


	/**
	 * Shipping text
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_shipping_text() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-shipping-text']) ? $options['sc-shipping-text'] : __('To find out your shipping cost , Please proceed to checkout.','side-cart-woocommerce');

		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $this->group; ?>-options[sc-shipping-text]" />
		<?php
	}


	/**
	 * Cart text
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_cart_text() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-cart-text']) ? $options['sc-cart-text'] : __('View Cart','side-cart-woocommerce');

		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $this->group; ?>-options[sc-cart-text]" />
		<?php
	}

	/**
	 * Checkout text
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_checkout_text() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-checkout-text']) ? $options['sc-checkout-text'] : __('Checkout','side-cart-woocommerce');

		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $this->group; ?>-options[sc-checkout-text]" />
		<?php
	}


	/**
	 * Continue text
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_continue_text() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-continue-text']) ? $options['sc-continue-text'] : __('Continue Shopping','side-cart-woocommerce');

		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $this->group; ?>-options[sc-continue-text]" />
		<p class="description">Leave empty to disable</p>
		<?php
	}


	/**
	 * Continue Shopping Button
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_cont_btn_url() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-cont-btn-url']) ? $options['sc-cont-btn-url'] : "#";

		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $this->group; ?>-options[sc-cont-btn-url]" />
		<p class="description">Use "#" for the same page</p>
		<?php
	}

	/**
	 * Empty cart button text
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_empty_cart_text() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-empty-cart-text']) ? $options['sc-empty-cart-text'] : __('Empty Cart','side-cart-woocommerce');

		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $this->group; ?>-options[sc-empty-cart-text]" />
		<?php
	}

	/**
	 * Cart is empty text
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_empty_text() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-empty-text']) ? $options['sc-empty-text'] : __('Your cart is empty.','side-cart-woocommerce');

		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $this->group; ?>-options[sc-empty-text]" />
		<?php
	}


	/**
	 * Product Price
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_show_price() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-show-price']) ? $options['sc-show-price'] : 1;
		$id 		= $this->group.'-options[sc-show-price]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Show Product Price.</label>
		<?php
	}


	/**
	 * Product Total
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sc_show_ptotal() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sc-show-ptotal']) ? $options['sc-show-ptotal'] : 1;
		$id 		= $this->group.'-options[sc-show-ptotal]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Show Product Total.</label>
		<?php
	}


	/*
	 =============================================
	 ============ Cart Basket Section ============
	 =============================================
	*/


	/**
	 * Enable Cart Basket
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_show_basket() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-show-basket']) ? $options['bk-show-basket'] : 1;
		$id 		= $this->group.'-options[bk-show-basket]';
		?>
		<select name="<?php echo $id; ?>">
			<option value="always_show" <?php selected( $option, "always_show" ); ?>>Always show</option>
			<option value="hide_empty" <?php selected( $option, "hide_empty" ); ?>>Hide when empty</option>
			<option value="hide_always" <?php selected( $option, "hide_always" ); ?>>Always hide</option>
		</select>
		<?php
	}


	/**
	 * Basket on mobile devices
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_show_basket_mobile() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-show-basket-mobile']) ? $options['bk-show-basket-mobile'] : 1;
		$id 		= $this->group.'-options[bk-show-basket-mobile]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Show basket on mobile device (smartphone,tablet).</label>
		<?php
	}


	/**
	 * Hide Basket on pages
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_hide_basket_pages() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-hide-basket-pages']) ? $options['bk-hide-basket-pages'] : '';
		$id 		= $this->group.'-options[bk-hide-basket-pages]';
		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $id; ?>" />
		<label for="<?php echo $id; ?>">Do not show basket on pages.</label>
		<p class="description">Use post type/page id/slug separated by comma. For eg: post,contact-us,about-us</p>
		<?php
	}


	/**
	 * Product Count
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_show_bkcount() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-show-bkcount']) ? $options['bk-show-bkcount'] : 1;
		$id 		= $this->group.'-options[bk-show-bkcount]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Show Product Count.</label>
		<?php
	}


	/**
	 * Count Type
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_count_type() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-count-type']) ? $options['bk-count-type'] : 'qty_count';
		$id 		= $this->group.'-options[bk-count-type]';
		?>
		<select name="<?php echo $id; ?>">
			<option value="qty_count" <?php selected( $option, "qty_count" ); ?>>Total Quantity Count</option>
			<option value="item_count" <?php selected( $option, "item_count" ); ?>>Total Items Count</option>
		</select>
		<?php
	}


	/**
	 * Fly to Cart Animation
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_flyto_anim() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-flyto-anim']) ? $options['bk-flyto-anim'] : 1;
		$id 		= $this->group.'-options[bk-flyto-anim]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Enable Flyto cart animation when product is added to cart.</label>
		<?php
	}


	/*
	 =============================================
	 ============ Suggested Products Section ============
	 =============================================
	*/


	 /**
	 * Enable
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sp_enable() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sp-enable']) ? $options['sp-enable'] : 1;
		$id 		= $this->group.'-options[sp-enable]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Show suggested products.</label>
		<?php
	}


	/**
	 * Enable
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sp_enable_mobile() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sp-enable-mobile']) ? $options['sp-enable-mobile'] : 0;
		$id 		= $this->group.'-options[sp-enable-mobile]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Show suggested products on mobile.</label>
		<?php
	}


	/**
	 * Suggested products type
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sp_type() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sp-type']) ? $options['sp-type'] : 'cross_sells';
		$id 		= $this->group.'-options[sp-type]';
		?>
		<select name="<?php echo $id; ?>">
			<option value="cross_sells" <?php selected( $option, "cross_sells" ); ?>>Cross-Sells</option>
			<option value="up_sells" <?php selected( $option, "up_sells" ); ?>>Up-Sells</option>
			<option value="related" <?php selected( $option, "related" ); ?>>Related Products</option>
		</select>
		<?php
	}


	/**
	 * Suggested Products Count
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sp_count() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sp-count']) ? $options['sp-count'] : 5;
		$id 		= $this->group.'-options[sp-count]';
		?>
		<input type="number" value="<?php echo $option; ?>" name="<?php echo $id; ?>" />
		<?php
	}






	/**
	 * Suggested Products title
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sp_title() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sp-title']) ? $options['sp-title'] :  __('Products you may like','side-cart-woocommerce');
		$id 		= $this->group.'-options[sp-title]';
		?>
		<input type="text" value="<?php echo $option; ?>" name="<?php echo $id; ?>" />
		<?php
	}




}