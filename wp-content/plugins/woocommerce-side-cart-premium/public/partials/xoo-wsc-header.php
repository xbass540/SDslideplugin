<?php

$head_title = isset($options['sc-head-text']) ? $options['sc-head-text']: __("Your Cart",'side-cart-woocommerce'); //Head Title
$show_notification 	= isset( $options['sc-notify']) ? $options['sc-notify'] : 1; //Show Notification

?>

<?php do_action('xoo_wsc_cart_before_head'); ?>

<?php if($show_notification == 1): ?>
	<div class="xoo-wsc-notification-bar"></div>
<?php endif; ?>

<span class="xoo-wsc-ctxt"><?php esc_attr_e($head_title,'side-cart-woocommerce'); ?></span>
<span class="xoo-wsc-icon-cross xoo-wsc-close"></span>

<?php do_action('xoo_wsc_cart_after_head'); ?>