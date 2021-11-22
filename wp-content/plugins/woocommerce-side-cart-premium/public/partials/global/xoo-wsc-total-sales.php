<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$total_sales = $_product->get_total_sales();

if($total_sales > apply_filters('xoo_wsc_total_sales_display_count',0)): ?>
	<span class="xoo-wsc-total-sales"><?php echo $total_sales.'+ '; ?><?php _e('shoppers have bought this','side-cart-woocommerce'); ?></span>
<?php endif; ?>


