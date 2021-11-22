<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


//Show/Hide total sales
function xoo_wsc_total_sales_display($_product){
	$args = array(
		'_product' => $_product
	);
	wc_get_template('xoo-wsc-total-sales.php',$args,'',XOO_WSC_PATH.'/public/partials/global/');
}


//Integerate paypal express checkout
function xoo_wsc_paypal_checkout(){

	$gl_options = get_option('xoo-wsc-gl-options');

	$pec = isset( $gl_options['sc-show-pec']) ? $gl_options['sc-show-pec'] : 0;

	if(WC()->cart->is_empty() || !class_exists('WC_Gateway_PPEC_Cart_Handler') || $pec != 1) return;

	echo '<div class="widget_shopping_cart">';

	$pc_handler = new WC_Gateway_PPEC_Cart_Handler();

	$pc_handler->display_mini_paypal_button();

	echo '</div>';
}
add_action('xoo_wsc_after_footer_btns','xoo_wsc_paypal_checkout',5);