<?php


if(!$product){
	echo 'Please provide product object';
	return;
}


$args = array(
	'min_value' => apply_filters( 'woocommerce_quantity_input_min', $product->get_min_purchase_quantity(), $product ),
	'max_value' => apply_filters( 'woocommerce_quantity_input_max', $product->get_max_purchase_quantity(), $product ),
	'step' 		=> apply_filters( 'woocommerce_quantity_input_step', 1, $product ),
	'pattern' 	=> apply_filters( 'woocommerce_quantity_input_pattern', has_filter( 'woocommerce_stock_amount', 'intval' ) ? '[0-9]*' : '' ),
	'input_value' => 1, //placeholder
);

$args = apply_filters( 'woocommerce_quantity_input_args', $args, $product );


$args['max_value'] = 0 < $args['max_value'] ? $args['max_value'] : '';

$max_value 		= $args['max_value'];
$min_value 		= $args['min_value'];
$step      		= $args['step'];
$pattern   		= $args['pattern'];
$input_value 	= !isset( $input_value ) ? $min_value : $input_value;

	
$input_html = '<input type="number" class="xoo-wsc-qty" max="'.esc_attr( 0 < $max_value ? $max_value : '' ).'" min="'.esc_attr($min_value).'" step="'.esc_attr( $step ).'" value="'.$input_value.'" pattern="'.esc_attr( $pattern ).'" >';

?>

<div class="xoo-wsc-qtybox" style="margin-right: 10px;">
	<span class="xoo-wsc-minus  xoo-wsc-chng">-</span>
	<?php echo $input_html; ?>
	<span class="xoo-wsc-plus xoo-wsc-chng">+</span>
</div>