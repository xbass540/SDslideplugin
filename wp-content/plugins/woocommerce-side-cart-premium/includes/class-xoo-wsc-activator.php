<?php
/**
 * Fired during plugin activation.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      1.0.0
 * @package    WooCommerce Side Cart
 */
class xoo_wsc_Activator {

	/**
	 * Disable woocommerce ajax add to cart option.
	 *
	 * Store user setting for "Enable ajax add to cart" in an option & disable ajax add to cart.
	 *
	 * @since    1.0.0
	 */
	public static function activate() {
		
		update_option('woocommerce_enable_ajax_add_to_cart','yes');
	
	}

}
