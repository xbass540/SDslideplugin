<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}


$show_coupon 		= isset($options['sc-show-coupon']) ? $options['sc-show-coupon']: 'always_show'; //Show coupon 
$subtotal_txt 		= isset($options['sc-subtotal-text']) ? $options['sc-subtotal-text']: __("Subtotal:",'side-cart-woocommerce'); //Subtotal Text
$shipping_txt 		= isset($options['sc-shipping-text']) ? $options['sc-shipping-text']: __("To find out your shipping cost , Please proceed to checkout.",'side-cart-woocommerce'); // Shipping Text
$cart_txt 			= isset($options['sc-cart-text']) ? $options['sc-cart-text'] : __("View Cart",'side-cart-woocommerce'); //Cart Text
$chk_txt 			= isset($options['sc-checkout-text']) ? $options['sc-checkout-text']: __("Checkout",'side-cart-woocommerce'); //Checkout Text
$cont_txt 			= isset($options['sc-continue-text']) ? $options['sc-continue-text'] :__( "Continue Shopping",'side-cart-woocommerce'); //Continue Text

$cont_btn_url 		= isset($options['sc-cont-btn-url']) ? $options['sc-cont-btn-url'] : "#"; //Continue button url

$show_shptax		= isset($options['sc-show-shptax']) ? $options['sc-show-shptax']: 1; //Show shipping tax


$tax_enabled  = wc_tax_enabled() && WC()->cart->get_cart_tax() !== '';
$has_shipping = WC()->cart->needs_shipping() && WC()->cart->show_shipping();
$has_discount = WC()->cart->has_discount();


$theme_button_styling = isset($sy_options['scf-btn-ts']) ? $sy_options['scf-btn-ts']: 'false'; //default button styling
$default_btn_classes = $theme_button_styling != 'false' ? 'button btn' : '';

?>

<?php if(!WC()->cart->is_empty()): ?>

	<div class="xoo-wsc-footer-a">

		<div class="xoo-wsc-tools">

			<div class="xoo-wsc-subtotal xoo-wsc-tool">
				<span class="xoo-wsc-tools-label"><?php echo $subtotal_txt; ?></span>
				<span class="xoo-wsc-tools-value"><?php echo WC()->cart->get_cart_subtotal(); ?></span>
			</div>

			<?php if($tax_enabled  && $show_shptax == 1): ?>
				<div class="xoo-wsc-tax xoo-wsc-tool">
					<span class="xoo-wsc-tools-label"><?php _e('Tax','side-cart-woocommerce'); ?></span>
					<span class="xoo-wsc-tools-value"><?php echo WC()->cart->get_cart_tax(); ?></span>
				</div>
			<?php endif; ?>

			<?php if($has_shipping && $show_shptax == 1): ?>
				<div class="xoo-wsc-shipping xoo-wsc-tool">
					<span class="xoo-wsc-tools-label"><?php _e('Shipping','side-cart-woocommerce'); ?></span>
					<span class="xoo-wsc-tools-value"><?php echo WC()->cart->get_cart_shipping_total(); ?></span>
				</div>
			<?php endif; ?>

			<?php if($has_discount): ?>
				<div class="xoo-wsc-discount xoo-wsc-tool">
					<span class="xoo-wsc-tools-label"><?php _e('Discount','side-cart-woocommerce'); ?></span>
					<span class="xoo-wsc-tools-value"><?php echo wc_price(WC()->cart->get_discount_total()); ?></span>
				</div>
			<?php endif; ?>

			<?php if($tax_enabled || $has_shipping || $has_discount): ?>
				<div class="xoo-wsc-total xoo-wsc-tool">
					<span class="xoo-wsc-tools-label"><?php _e('Total','side-cart-woocommerce'); ?></span>
					<span class="xoo-wsc-tools-value"><?php echo WC()->cart->get_total(); ?></span>
				</div>
			<?php endif; ?>


		</div>

		<?php if(wc_coupons_enabled() && $show_coupon != 'disable'): ?>

			<div class="xoo-wsc-coupon-container">

				<?php if($show_coupon == 'toggle_show'): ?>
					<a class="xoo-wsc-coupon-trigger active"><?php _e('Apply a promo code','side-cart-woocommerce'); ?></a>
				<?php endif; ?>

				<div class="xoo-wsc-coupon <?php echo $show_coupon == 'always_show' ? 'active' : ''; ?>">
					<input type="text" id="xoo-wsc-coupon-code" placeholder="<?php _e('Enter your promo code','side-cart-woocommerce'); ?>">
					<span class="xoo-wsc-coupon-submit <?php echo $default_btn_classes; ?>"><?php _e('APPLY','side-cart-woocommerce'); ?></span>
				</div>

				<?php $coupons = WC()->cart->get_coupons();
					if(!empty($coupons)): ?>

						<ul class="xoo-wsc-applied-coupons">
							<?php foreach ($coupons as $code => $coupon): ?>
								<li class="xoo-wsc-remove-coupon" data-coupon="<?php echo $code; ?>"><?php echo $code; ?></li>
							<?php endforeach; ?>
						</ul>

				<?php endif; ?>
			</div>

		<?php endif; ?>


		<?php if(!empty($shipping_txt)): ?>
			<span class="xoo-wsc-shiptxt"><?php esc_attr_e($shipping_txt,'side-cart-woocommerce'); ?></span>
		<?php endif; ?>
	</div>

<?php endif; ?>

<div class="xoo-wsc-footer-b">
	<?php $hide_btns = WC()->cart->is_empty() ? 'style="display: none;"' : '';?>

	<?php do_action('xoo_wsc_before_footer_btns'); ?>

	<?php if(!empty($cart_txt)): ?>
	<a href="<?php echo wc_get_cart_url(); ?>" class="xoo-wsc-ft-btn xoo-wsc-chkt <?php echo $default_btn_classes; ?>" <?php echo $hide_btns; ?>><?php echo esc_attr__($cart_txt,'side-cart-woocommerce'); ?></a>
	<?php endif; ?>

	<?php if(!empty($chk_txt)): ?>
	<a  href="<?php echo wc_get_checkout_url(); ?>" class="xoo-wsc-ft-btn xoo-wsc-cart <?php echo $default_btn_classes; ?>" <?php echo $hide_btns; ?>><?php echo esc_attr__($chk_txt,'side-cart-woocommerce'); ?></a>
	<?php endif; ?>

	<?php if(!empty($cont_txt)): ?>
	<a  href="<?php echo $cont_btn_url; ?> " class="xoo-wsc-ft-btn xoo-wsc-cont <?php echo $default_btn_classes; ?>"><?php echo esc_attr__($cont_txt,'side-cart-woocommerce'); ?></a>
	<?php endif; ?>


	<?php do_action('xoo_wsc_after_footer_btns'); ?>
	
</div>




</div>
