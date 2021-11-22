<?php
/**
 * @author    StoreApps
 * @package   WooCommerce Stock Manager
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WCM_Save {

	/**
	 * Save data for one product
	 *
	 * @since     1.2.2
	 */
	public static function save_one( $data, $product_id ){ 

		$values = self::prepare_one_data( $data );

		if( !empty( $values ) ){

			self::save_data( $values, $product_id );

		}

	}

	/**
	 * Save data for one item (usedn in foreach, for save all button)
	 *
	 * @since     1.2.2
	 */
	public static function save_one_item( $data, $product_id ){ 

		$values = self::prepare_data( $data, $product_id );

		if( !empty( $values ) ){

			self::save_data( $values, $product_id );

		}

	}

	/**
	 * Prepare data
	 *
	 * @since     1.2.2
	 */
	public static function prepare_data( $data, $item ){ 

		$values  = array();
		$defaults = self::get_default();

		foreach( $defaults as $default ){

			if( isset( $data[$default] ) ) {

				$values[$default]  = sanitize_text_field( $data[$default] );

			}

		}

	   return $values;

	}

	/**
	 * Prepare one data
	 *
	 * @since     1.2.2
	 */
	public static function prepare_one_data( $data ){ 

		$values  = array();

		$defaults = self::get_default();

		foreach( $defaults as $default ){

			if( isset( $data[$default] ) ) {

				$values[$default]  = sanitize_text_field( $data[$default] );

			}

		}

		return $values;
 

	}

	/**
	 * Get default
	 *
	 * @since     1.2.2
	 */
	public static function get_default(){ 

		$values = array(
			'sku',
			'manage_stock',
			'stock_status',
			'backorders',
			'stock',
			'tax_status',
			'tax_class',
			'shipping_class',
			'weight',
			'regular_price',
			'sales_price',
		);

		return $values;

	}

	/**
	 * Save data
	 *
	 * @since     1.2.2
	 */
	public static function save_data( $data, $product_id ) {

		$display_option = get_option( 'wsm_display_option' );

		$_product = wc_get_product( $product_id );
		// use is_a instead of instanceof to cover all product types.
		if ( is_a( $_product, 'WC_Product' ) ) {

			if( isset( $data['sku'] ) ) {
				$_product->set_sku( $data['sku'] );
			}
			
			if( isset( $data['manage_stock'] ) ) {
				$_product->set_manage_stock( $data['manage_stock'] );
			}

			if( isset( $data['backorders'] ) ) {
				$_product->set_backorders( $data['backorders'] );
			}

			if( isset( $data['stock_status'] ) ) {
				$_product->set_stock_status( $data['stock_status'] );
			}
			
			if( isset( $data['stock'] ) ) {
				$_product->set_stock_quantity( $data['stock'] );
			}
			
			if( isset( $data['tax_status'] ) ) {
				$_product->set_tax_status( $data['tax_status'] );
			}
			
			if( isset( $data['tax_class'] ) ) {
				$_product->set_tax_class( $data['tax_class'] );
			}
			
			if( isset( $data['shipping_class'] ) ) {
				$_product->set_shipping_class_id( $data['shipping_class'] );
			}
			
			if( isset( $data['weight'] ) ){
				$_product->set_weight( $data['weight'] );
			}
			
			 /*   if( !empty( $data['regular_price'] ) ){
					$price = sanitize_text_field($data['regular_price']);
					if( !empty( $data['sales_price'] ) ){
						$sale_price   = sanitize_text_field($data['sales_price']);
						wsm_save_price( $product_id, $price, $sale_price );
					}else{
						wsm_save_price( $product_id, $price );
					}
				}   
	*/
			if( isset( $data['regular_price'] ) ) {
				if( !empty( $data['regular_price'] ) ){
					$_product->set_price( $data['regular_price'] );
					$_product->set_regular_price( $data['regular_price'] );                            
				}
				else{
					$_product->set_price( '' );
					$_product->set_regular_price( '' );
				}
			}        
			
			if( isset( $data['sales_price'] ) ) {
				if( !empty( $data['sales_price'] ) ){
					$_product->set_sale_price( $data['sales_price'] );
				} else {
					$_product->set_sale_price( '' );
				}
			}        
			
			$_product->save();

			wc_delete_product_transients( $product_id );

		}

	}

}//End class
