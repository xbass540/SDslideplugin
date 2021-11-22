<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

$sy_options = get_option('xoo-wsc-sy-options');//Style options
$options 	= get_option('xoo-wsc-gl-options');
$count_type = isset( $options['bk-count-type']) ? $options['bk-count-type'] : 'qty_count'; //Count Type
$cart_items_total = wc_price(WC()->cart->subtotal);

$bk_cubi 	= isset( $sy_options['bk-cubi']) ? $sy_options['bk-cubi'] : ''; // Custom basket icon
if( !$bk_cubi ){
	$bk_bit	= isset( $sy_options['bk-bit']) ? $sy_options['bk-bit'] : 'xoo-wsc-icon-basket1'; // Basket Icon Type
}


$html  = '<a class="xoo-wsc-sc-cont">';

if( $bk_cubi ){
	$html .= '<img src="'.$bk_cubi.'" class="xoo-wsc-sc-icon">';
}
else{
	$html .= '<span class="xoo-wsc-sc-icon '.$bk_bit.'"></span>';
}

if($count_type == 'qty_count'){
	$count_value = WC()->cart->get_cart_contents_count();
}
elseif($count_type == 'item_count'){
	$count_value = count(WC()->cart->get_cart());
}

$items_txt = $count_value === 1 ? __('item','side-cart-woocommerce') : __('items','side-cart-woocommerce');
$html .= '<span class="xoo-wsc-sc-count">'.$count_value.'</span>'.' '.$items_txt.' - ';

$html .= '<span class="xoo-wsc-sc-total">'.$cart_items_total.'</span>';
	
$html .= '</a>';

echo $html;
