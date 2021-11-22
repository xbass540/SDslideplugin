<?php

/**
 * Side Cart HTML
 *
 * @since      1.0.0
*/

global $post;


//User Settings
$options 	= get_option('xoo-wsc-gl-options');
$sy_options = get_option('xoo-wsc-sy-options');

$show_notification 	= isset( $options['sc-notify']) ? $options['sc-notify'] : 1; //Show Notification
$show_basket		= isset( $options['bk-show-basket']) ? $options['bk-show-basket'] : 'always_show'; //Show Basket
$show_basket_mobile	= isset( $options['bk-show-basket-mobile']) ? $options['bk-show-basket-mobile'] : 1; //Show Basket on mobile device
$hide_basket_pages 	= trim(isset( $options['bk-hide-basket-pages']) ? $options['bk-hide-basket-pages'] : ''); //Hide basket on pages
$show_count 		= isset( $options['bk-show-bkcount']) ? $options['bk-show-bkcount'] : 1; //Show Count
$count_type 		= isset( $options['bk-count-type']) ? $options['bk-count-type'] : 'qty_count'; //Count Type
$bk_cubi 		    = isset( $sy_options['bk-cubi']) ? $sy_options['bk-cubi'] : ''; // Custom basket icon
$head_title 		= isset($options['sc-head-text']) ? $options['sc-head-text']: __("Your Cart",'side-cart-woocommerce'); //Head Title

if(!$bk_cubi){
	$bk_bit	= isset( $sy_options['bk-bit']) ? $sy_options['bk-bit'] : 'xoo-wsc-icon-basket1'; // Basket Icon Type
}


$sp_pos = isset( $sy_options['sp-pos']) ? $sy_options['sp-pos'] : 'above_totals'; // Suggested Product Option

//Footer text
$subtotal_txt 		= isset($options['sc-subtotal-text']) ? $options['sc-subtotal-text']: __("Subtotal:",'side-cart-woocommerce'); //Subtotal Text
$shipping_txt 		= isset($options['sc-shipping-text']) ? $options['sc-shipping-text']: __("To find out your shipping cost , Please proceed to checkout.",'side-cart-woocommerce'); // Shipping Text
$cart_txt 			= isset($options['sc-cart-text']) ? $options['sc-cart-text'] : __("View Cart",'side-cart-woocommerce'); //Cart Text
$chk_txt 			= isset($options['sc-checkout-text']) ? $options['sc-checkout-text']: __("Checkout",'side-cart-woocommerce'); //Checkout Text
$cont_txt 			= isset($options['sc-continue-text']) ? $options['sc-continue-text'] :__( "Continue Shopping",'side-cart-woocommerce'); //Continue Text

?>
<div class="xoo-wsc-modal">

	<?php if($show_basket != 'hide_always'): ?>
		<?php
			$hide_basket = false;

			//On mobile device
			if(wp_is_mobile() && $show_basket_mobile != 1){
				$hide_basket = true;
			}

			//Hide on pages
			if(isset($hide_basket_pages) && $hide_basket === false){
				foreach (explode(',',$hide_basket_pages) as $page) {
					//Check for page ID
					if($page && is_page($page)){
						$hide_basket = true;
						break;
					}
					
					//Check for post type
					if($post){
						if($page == $post->ID || $page == $post->post_type){
							$hide_basket = true;
							break;
						}
					}
					
				}
			}

			$show_basket_style = $hide_basket === false  && $show_basket == 'hide_empty' && WC()->cart->is_empty()? 'display:none;' : '';

		?>
		<div class="xoo-wsc-basket <?php if( $hide_basket ) echo 'xoo-wsc-basket-hidden'; ?>" style="<?php echo $show_basket_style; ?>">

			<?php if($show_count == 1): ?>
				<?php
				if($count_type == 'qty_count'){
					$count_value = WC()->cart->get_cart_contents_count();
				}
				elseif($count_type == 'item_count'){
					$count_value = count(WC()->cart->get_cart());
				}
			?>

				<span class="xoo-wsc-items-count"><?php echo $count_value ?></span>
			<?php endif; ?>

			<?php if($bk_cubi): ?>
				<img src="<?php echo $bk_cubi;?>" class="xoo-wsc-bki">
			<?php else: ?>
				<span class="<?php echo $bk_bit; ?> xoo-wsc-bki"></span>
			<?php endif; ?>

		</div>
	<?php endif; ?>

	<div class="xoo-wsc-opac"></div>
	<div class="xoo-wsc-container">

		<span class="xoo-wsc-block-cart"></span>

		<div class="xoo-wsc-header">

			<?php if($show_notification == 1): ?>
				<div class="xoo-wsc-notification-bar"></div>
			<?php endif; ?>

			<span class="xoo-wsc-ctxt"><?php esc_attr_e($head_title,'side-cart-woocommerce'); ?></span>
			<span class="xoo-wsc-icon-cross xoo-wsc-close"></span>

		</div>

		<div class="xoo-wsc-body"></div>

		<div class="xoo-wsc-footer">

			<?php if($sp_pos == 'above_totals'): ?>
				<div class="xoo-wsc-related-products"></div>
			<?php endif; ?>
				
			<div class="xoo-wsc-footer-content"></div>

			<?php if($sp_pos == 'at_bottom'): ?>
				<div class="xoo-wsc-related-products"></div>
			<?php endif; ?>

		</div>
	</div>
</div>

<div class="xoo-wsc-notice-box" style="display: none;">
	<div>
	  <span class="xoo-wsc-notice"></span>
	</div>
</div>