<?php

if(!defined('WPINC')){
	return;
}


$args = array(
	'post_type'            	=> 'product',
	'post_status'    		=> 'publish',
	'ignore_sticky_posts'  	=> 1,
	'no_found_rows'       	=> 1,
	'posts_per_page'       	=> $items_count,
	'post__not_in'        	=> $exclude_ids,
	'orderby'             	=> 'rand',
	'meta_query'			=> array(
			array(
	        'key' => '_stock_status',
	        'value' => 'instock',
	        'compare' => '=',
	    )
	)
);

if(!empty($suggested_products)){
	$args['post__in'] = $suggested_products;
}


$products = new WP_Query( $args );




if ( $products->have_posts() ) :
	?>
		<div class="xoo-wsc-rp-cont">
			<span class="xoo-wsc-rp-title"><?php echo $title; ?></span>
			<ul class="xoo-wsc-rp-products" id="lightSlider">
				<?php 
				while ( $products->have_posts() ) : $products->the_post();
				global $product;
				$product_link = $product->get_permalink();
				?>
				<li class="xoo-wsc-rp-item lslide">

					<div class="xoo-wsc-rp-left-area">
						<?php do_action('xoo_wsc_sp_left_actions'); ?>
					</div>

					<div class="xoo-wsc-rp-right-area">
						<?php do_action('xoo_wsc_sp_right_actions'); ?>
					</div>

					
				</li>
			<?php endwhile; ?>
			</ul>
		</div>
<?php endif; ?>


<?php wp_reset_postdata(); ?>
