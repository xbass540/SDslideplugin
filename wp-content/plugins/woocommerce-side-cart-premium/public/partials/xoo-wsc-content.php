<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$empty_cart_txt = isset( $options['sc-empty-text']) ? $options['sc-empty-text'] : __('Your cart is empty.','side-cart-woocommerce');
$empty_cart_img = isset( $sy_options['scb-empimg']) ? $sy_options['scb-empimg'] : null;

?>

<?php if(WC()->cart->is_empty()): ?>
	<div class="xoo-wsc-empty-cart">
		<?php if($empty_cart_img): ?>
			<img src="<?php echo $empty_cart_img; ?>" alt="Empty Cart">
		<?php else: ?>
			<span class="xoo-wsc-ecnt"><?php esc_attr_e($empty_cart_txt,'side-cart-woocommerce'); ?></span>
		<?php endif; ?>
	</div>
<?php else: ?>
	
	<div class="xoo-wsc-content">
		<?php
		$show_price 	 = isset( $options['sc-show-price']) ? $options['sc-show-price'] : 1;
		$show_ptotal 	 = isset( $options['sc-show-ptotal']) ? $options['sc-show-ptotal'] : 1;
		$update_quantity = isset( $options['sc-update-qty']) ? $options['sc-update-qty'] : 1;

		do_action( 'woocommerce_before_calculate_totals', WC()->cart );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {

				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );


				
				$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );

				

				
				$product_name =  apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_title() ), $cart_item, $cart_item_key );
				
										

				$product_price = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );

				$product_subtotal = apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key );

				$attributes = '';

				//Variation
				$attributes .= $_product->is_type('variable') || $_product->is_type('variation')  ? wc_get_formatted_variation($_product) : '';
				// Meta data
				if(version_compare( WC()->version , '3.3.0' , "<" )){
					$attributes .=  WC()->cart->get_item_data( $cart_item );
				}
				else{
					$attributes .=  wc_get_formatted_cart_item_data( $cart_item );
				}

				//Woocommerce Bundled Products
				$bundled_parent  = $bundled_child = null;
				$extra_classes 	 = array();
				if(isset($cart_item['bundled_items'])){
					$bundled_parent = true;
					$extra_classes[] = 'xoo-wsc-bundled-parent';
				}
				elseif(isset($cart_item['bundled_by'])){
					$bundled_child = true;
					$extra_classes[] = 'xoo-wsc-bundled-child';
				}


				//Mix and match
				if(isset($cart_item['mnm_contents'])){
					$bundled_parent = true;
					$extra_classes[] = 'xoo-wsc-mnm-parent';
				}
				elseif(isset($cart_item['mnm_container'])){
					$bundled_child = true;
					$extra_classes[] = 'xoo-wsc-mnm-child';
				}

				//Composite products
				if(isset($cart_item['composite_children'])){
					$bundled_parent = true;
					$extra_classes[] = 'xoo-wsc-comp-parent';
				}
				elseif(isset($cart_item['composite_parent'])){
					$bundled_child = true;
					$extra_classes[] = 'xoo-wsc-comp-child';
				}

				if($bundled_parent){
					$extra_classes[] = 'xoo-wsc-is-parent';
				}
				elseif($bundled_child){
					$extra_classes[] = 'xoo-wsc-is-child';
				}

		?>

				<div class="xoo-wsc-product <?php echo implode(' ',$extra_classes); ?>" data-xoo_wsc="<?php echo $cart_item_key; ?>">

					<div class="xoo-wsc-img-col">
						<?php do_action('xoo_wsc_before_product_image'); ?>
						<a href="<?php echo $product_permalink; ?>"><?php echo $thumbnail; ?></a>
						<?php do_action('xoo_wsc_after_product_image'); ?>
					</div>

					<div class="xoo-wsc-sum-col">

						<?php do_action('xoo_wsc_before_product_summary',$_product); ?>

						<a href="<?php echo $product_permalink ?>" class="xoo-wsc-pname"><?php echo $product_name; ?></a>

						<?php if(!$bundled_child): ?>
							<a class="xoo-wsc-remove xoo-wsc-icon-trash"></a>
						<?php endif; ?>

						<?php echo $attributes ? $attributes : ''; ?> 

						<?php if(!$bundled_child): ?>

							<?php if($update_quantity == 1): ?>

								<?php if($show_price == 1): ?>
									<div class="xoo-wsc-price">
										<span><?php _e('Price:','side-cart-woocommerce') ?></span> <?php echo $product_price; ?>
									</div>

								<?php endif; ?>

								<div class="xoo-wsc-psrow">

									<?php if(!$_product->is_sold_individually()){
										$args = array(
											'input_value' => $cart_item['quantity'],
											'product' => $_product	
										);
										wc_get_template('xoo-wsc-quantity-increment.php',$args,'',XOO_WSC_PATH.'/public/partials/global/');
									}

									?>
							

									<?php if($show_ptotal == 1): ?>
										<span class="xoo-wsc-ptotal"><?php echo $product_subtotal; ?></span>
									<?php endif; ?>
								</div>

							<?php else: ?>
								
								<div class="xoo-wsc-price">
									<span><?php echo $cart_item['quantity']; ?></span> X <span><?php echo $product_price; ?></span>

									<?php if($show_ptotal == 1): ?>
										= <span><?php echo $product_subtotal; ?></span>
									<?php endif; ?>
								</div>

							<?php endif; ?>

						<?php endif; ?>

						<?php do_action('xoo_wsc_after_product_summary',$_product); ?>

					</div>
				</div>
			<?php } ?>
		<?php } ?>
	</div>

	<div class="xoo-wsc-updating">
		<span class="xoo-wsc-icon-spinner2" aria-hidden="true"></span>
		<span class="xoo-wsc-uopac"></span>
	</div>
<?php endif; ?>
