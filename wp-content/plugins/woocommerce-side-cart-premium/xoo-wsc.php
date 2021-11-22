<?php

/**
 * The plugin bootstrap file
 *
 * @since             1.1.2
 * @package    WooCommerce Side Cart
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Side Cart Premium
 * Plugin URI:        http://xootix.com
 * Description:       Woo Side Cart shows all the items added to cart in a side popup.The plugin is ajax based.
 * Version:           2.2
 * Author:            XootiX
 * Author URI:        http://xootix.com/
 * License:           LICENSE.txt
 * Text Domain:       side-cart-woocommerce
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//Global variable
define("XOO_WSC_URL", plugins_url('/',__FILE__));

//Path
define("XOO_WSC_PATH",plugin_dir_path(__FILE__));

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-xoo-sc-activator.php
 */
function activate_xoo_wsc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xoo-wsc-activator.php';
	xoo_wsc_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-plugin-name-deactivator.php
 */
function deactivate_xoo_wsc() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-xoo-wsc-deactivator.php';
	xoo_wsc_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_xoo_wsc' );
register_deactivation_hook( __FILE__, 'deactivate_xoo_wsc' );

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_xoo_wsc() {

	$plugin = new xoo_wsc();
	$plugin->run();

}

/**
 * Check if WooCommerce is activated
 *
 * @since    1.0.0
 */
function xoo_wsc_init(){
	if ( function_exists( 'WC' ) ) {

		/*The core plugin class that is used to define internationalization,
		admin-specific hooks, and public-facing site hooks.*/

		require plugin_dir_path( __FILE__ ) . 'includes/class-xoo-wsc.php';
		run_xoo_wsc();
	}
	else{
		add_action( 'admin_notices', 'xoo_wsc_install_wc_notice' );
	}
}
add_action('plugins_loaded','xoo_wsc_init');


/**
 * WooCommerce not activated admin notice
 *
 * @since    1.0.0
 */
function xoo_wsc_install_wc_notice(){
	?>
	<div class="error">
		<p><?php _e( 'WooCommerce Side Cart is enabled but not effective. It requires WooCommerce in order to work.', 'side-cart-woocommerce' ); ?></p>
	</div>
	<?php
}


//Menu Shortcode
function xoo_wsc_cart_shortcode_func(){
	ob_start();
	wc_get_template('xoo-wsc-shortcode.php','','',XOO_WSC_PATH.'/public/partials/');
	$html = ob_get_clean();
	return $html;
}
add_shortcode('xoo_wsc_cart','xoo_wsc_cart_shortcode_func');


function xoo_wsc_suggested_products_enabled(){
	$gl_options = get_option('xoo-wsc-gl-options');
	$enable = isset( $gl_options['sp-enable']) ? $gl_options['sp-enable'] : 1;
	$enable_mobile = isset( $gl_options['sp-enable-mobile']) ? $gl_options['sp-enable-mobile'] : 0;

	if($enable != 1 || ($enable_mobile != 1 && wp_is_mobile())){
		return false;
	}
	else{
		return true;
	}
}


add_action('xoo_wsc_sp_left_actions','woocommerce_template_loop_product_link_open',10);
add_action('xoo_wsc_sp_left_actions','woocommerce_template_loop_product_thumbnail',20);
add_action('xoo_wsc_sp_left_actions','woocommerce_template_loop_product_link_close',30);

//Suggested product hooks
function xoo_wsc_sp_title(){
	echo '<span class="xoo-wsc-sp-title">'.get_the_title().'</span>';
}
add_action('xoo_wsc_sp_right_actions','xoo_wsc_sp_title',10);
add_action('xoo_wsc_sp_right_actions','woocommerce_template_loop_price',20);
add_action('xoo_wsc_sp_right_actions','woocommerce_template_loop_add_to_cart',40);