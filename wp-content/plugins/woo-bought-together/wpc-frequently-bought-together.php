<?php
/*
Plugin Name: WPC Frequently Bought Together for WooCommerce
Plugin URI: https://wpclever.net/
Description: Increase your sales with personalized product recommendations.
Version: 3.5.4
Author: WPClever
Author URI: https://wpclever.net
Text Domain: woobt
Domain Path: /languages/
Requires at least: 4.0
Tested up to: 5.7
WC requires at least: 3.0
WC tested up to: 5.1
*/

defined( 'ABSPATH' ) || exit;

! defined( 'WOOBT_VERSION' ) && define( 'WOOBT_VERSION', '3.5.4' );
! defined( 'WOOBT_URI' ) && define( 'WOOBT_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WOOBT_SUPPORT' ) && define( 'WOOBT_SUPPORT', 'https://wpclever.net/support??utm_source=support&utm_medium=woobt&utm_campaign=wporg' );
! defined( 'WOOBT_REVIEWS' ) && define( 'WOOBT_REVIEWS', 'https://wordpress.org/support/plugin/woo-bought-together/reviews/?filter=5' );
! defined( 'WOOBT_CHANGELOG' ) && define( 'WOOBT_CHANGELOG', 'https://wordpress.org/plugins/woo-bought-together/#developers' );
! defined( 'WOOBT_DISCUSSION' ) && define( 'WOOBT_DISCUSSION', 'https://wordpress.org/support/plugin/woo-bought-together' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WOOBT_URI );

include 'includes/wpc-dashboard.php';
include 'includes/wpc-menu.php';
include 'includes/wpc-kit.php';

if ( ! function_exists( 'woobt_init' ) ) {
	add_action( 'plugins_loaded', 'woobt_init', 11 );

	function woobt_init() {
		// Load textdomain
		load_plugin_textdomain( 'woobt', false, basename( __DIR__ ) . '/languages/' );

		if ( ! function_exists( 'WC' ) || ! version_compare( WC()->version, '3.0', '>=' ) ) {
			add_action( 'admin_notices', 'woobt_notice_wc' );

			return;
		}

		if ( ! class_exists( 'WPCleverWoobt' ) && class_exists( 'WC_Product' ) ) {
			class WPCleverWoobt {
				protected static $types = array( 'simple', 'woosb', 'bundle', 'subscription' );

				function __construct() {
					// Menu
					add_action( 'admin_menu', array( $this, 'woobt_admin_menu' ) );

					// Enqueue frontend scripts
					add_action( 'wp_enqueue_scripts', array( $this, 'woobt_wp_enqueue_scripts' ) );

					// Enqueue backend scripts
					add_action( 'admin_enqueue_scripts', array( $this, 'woobt_admin_enqueue_scripts' ) );

					// Backend AJAX search
					add_action( 'wp_ajax_woobt_get_search_results', array( $this, 'woobt_get_search_results' ) );

					// Shortcode
					add_shortcode( 'woobt', array( $this, 'woobt_shortcode' ) );
					add_shortcode( 'woobt_items', array( $this, 'woobt_shortcode' ) );

					// Product data tabs
					add_filter( 'woocommerce_product_data_tabs', array( $this, 'woobt_product_data_tabs' ), 10, 1 );

					// Product data panels
					add_action( 'woocommerce_product_data_panels', array( $this, 'woobt_product_data_panels' ) );
					add_action( 'woocommerce_process_product_meta', array( $this, 'woobt_save_option_field' ) );

					// Add to cart button & form
					add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'woobt_add_to_cart_button' ) );

					switch ( get_option( '_woobt_position', 'before' ) ) {
						case 'before':
							add_action( 'woocommerce_before_add_to_cart_form', array(
								$this,
								'woobt_add_to_cart_form'
							) );
							break;
						case 'after':
							add_action( 'woocommerce_after_add_to_cart_form', array(
								$this,
								'woobt_add_to_cart_form'
							) );
							break;
						case 'below_title':
							add_action( 'woocommerce_single_product_summary', array(
								$this,
								'woobt_add_to_cart_form'
							), 6 );
							break;
						case 'below_price':
							add_action( 'woocommerce_single_product_summary', array(
								$this,
								'woobt_add_to_cart_form'
							), 11 );
							break;
						case 'below_excerpt':
							add_action( 'woocommerce_single_product_summary', array(
								$this,
								'woobt_add_to_cart_form'
							), 21 );
							break;
						case 'below_meta':
							add_action( 'woocommerce_single_product_summary', array(
								$this,
								'woobt_add_to_cart_form'
							), 41 );
							break;
					}

					// Add to cart
					add_filter( 'woocommerce_add_to_cart_sold_individually_found_in_cart', array(
						$this,
						'woobt_individually_found_in_cart'
					), 10, 2 );
					add_filter( 'woocommerce_add_to_cart_validation', array(
						$this,
						'woobt_add_to_cart_validation'
					), 10, 2 );
					add_action( 'woocommerce_add_to_cart', array( $this, 'woobt_add_to_cart' ), 10, 6 );
					add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woobt_add_cart_item_data' ), 10, 2 );
					add_filter( 'woocommerce_get_cart_item_from_session', array(
						$this,
						'woobt_get_cart_item_from_session'
					), 10, 2 );

					// Cart contents
					add_filter( 'woocommerce_get_cart_contents', array( $this, 'woobt_get_cart_contents' ), 10, 1 );

					// Cart item
					add_filter( 'woocommerce_cart_item_name', array( $this, 'woobt_cart_item_name' ), 10, 2 );
					add_filter( 'woocommerce_cart_item_price', array( $this, 'woobt_cart_item_price' ), 10, 2 );

					if ( get_option( '_woobt_cart_quantity', 'yes' ) === 'no' ) {
						add_filter( 'woocommerce_cart_item_quantity', array(
							$this,
							'woobt_cart_item_quantity'
						), 10, 3 );
						add_action( 'woocommerce_after_cart_item_quantity_update', array(
							$this,
							'woobt_update_cart_item_quantity'
						), 10, 2 );
					}

					add_action( 'woocommerce_cart_item_removed', array( $this, 'woobt_cart_item_removed' ), 10, 2 );

					// Order item
					add_action( 'woocommerce_checkout_create_order_line_item', array(
						$this,
						'woobt_add_order_item_meta'
					), 10, 3 );
					add_filter( 'woocommerce_order_item_name', array( $this, 'woobt_cart_item_name' ), 10, 2 );

					// Admin order
					add_filter( 'woocommerce_hidden_order_itemmeta', array(
						$this,
						'woobt_hidden_order_item_meta'
					), 10, 1 );
					add_action( 'woocommerce_before_order_itemmeta', array(
						$this,
						'woobt_before_order_item_meta'
					), 10, 1 );

					// Add settings link
					add_filter( 'plugin_action_links', array( $this, 'woobt_action_links' ), 10, 2 );
					add_filter( 'plugin_row_meta', array( $this, 'woobt_row_meta' ), 10, 2 );

					// Admin
					add_filter( 'display_post_states', array( $this, 'woobt_display_post_states' ), 10, 2 );

					// Search filters
					if ( get_option( '_woobt_search_sku', 'no' ) === 'yes' ) {
						add_filter( 'pre_get_posts', array( $this, 'woobt_search_sku' ), 99 );
					}

					if ( get_option( '_woobt_search_exact', 'no' ) === 'yes' ) {
						add_action( 'pre_get_posts', array( $this, 'woobt_search_exact' ), 99 );
					}

					if ( get_option( '_woobt_search_sentence', 'no' ) === 'yes' ) {
						add_action( 'pre_get_posts', array( $this, 'woobt_search_sentence' ), 99 );
					}
				}

				function woobt_admin_menu() {
					add_submenu_page( 'wpclever', esc_html__( 'WPC Frequently Bought Together', 'woobt' ), esc_html__( 'Bought Together', 'woobt' ), 'manage_options', 'wpclever-woobt', array(
						&$this,
						'woobt_admin_menu_content'
					) );
				}

				function woobt_admin_menu_content() {
					add_thickbox();
					$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'settings';
					?>
                    <div class="wpclever_settings_page wrap">
                        <h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Frequently Bought Together', 'woobt' ) . ' ' . WOOBT_VERSION; ?></h1>
                        <div class="wpclever_settings_page_desc about-text">
                            <p>
								<?php printf( /* translators: %s is the stars */ esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'woobt' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                                <br/>
                                <a href="<?php echo esc_url( WOOBT_REVIEWS ); ?>"
                                   target="_blank"><?php esc_html_e( 'Reviews', 'woobt' ); ?></a> | <a
                                        href="<?php echo esc_url( WOOBT_CHANGELOG ); ?>"
                                        target="_blank"><?php esc_html_e( 'Changelog', 'woobt' ); ?></a>
                                | <a href="<?php echo esc_url( WOOBT_DISCUSSION ); ?>"
                                     target="_blank"><?php esc_html_e( 'Discussion', 'woobt' ); ?></a>
                            </p>
                        </div>
                        <div class="wpclever_settings_page_nav">
                            <h2 class="nav-tab-wrapper">
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woobt&tab=how' ); ?>"
                                   class="<?php echo $active_tab === 'how' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'How to use?', 'woobt' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woobt&tab=settings' ); ?>"
                                   class="<?php echo $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'Settings', 'woobt' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woobt&tab=premium' ); ?>"
                                   class="<?php echo $active_tab === 'premium' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>"
                                   style="color: #c9356e">
									<?php esc_html_e( 'Premium Version', 'woobt' ); ?>
                                </a>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-kit' ) ); ?>"
                                   class="nav-tab">
									<?php esc_html_e( 'Essential Kit', 'woobt' ); ?>
                                </a>
                            </h2>
                        </div>
                        <div class="wpclever_settings_page_content">
							<?php if ( $active_tab === 'how' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>
										<?php esc_html_e( 'When adding/editing the product you can choose Bought Together tab then add some products with the new price.', 'woobt' ); ?>
                                    </p>
                                    <p>
                                        <img src="<?php echo esc_url( WOOBT_URI ); ?>assets/images/how-01.jpg"/>
                                    </p>
                                </div>
							<?php } elseif ( $active_tab === 'settings' ) { ?>
                                <form method="post" action="options.php">
									<?php wp_nonce_field( 'update-options' ) ?>
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'General', 'woobt' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Pricing method', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_pricing">
                                                    <option
                                                            value="sale_price" <?php echo esc_attr( get_option( '_woobt_pricing', 'sale_price' ) === 'sale_price' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'from Sale price', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="regular_price" <?php echo esc_attr( get_option( '_woobt_pricing', 'sale_price' ) === 'regular_price' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'from Regular price ', 'woobt' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Calculate prices from the sale price (default) or regular price of products.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Default products', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_default">
                                                    <option
                                                            value="upsells" <?php echo esc_attr( get_option( '_woobt_default', 'none' ) === 'upsells' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Upsells', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="related" <?php echo esc_attr( get_option( '_woobt_default', 'none' ) === 'related' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Related', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="related_upsells" <?php echo esc_attr( get_option( '_woobt_default', 'none' ) === 'related_upsells' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Related & Upsells', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="none" <?php echo esc_attr( get_option( '_woobt_default', 'none' ) === 'none' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'None', 'woobt' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Default products when don\'t specified any products.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Position', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_position">
                                                    <option
                                                            value="before" <?php echo esc_attr( get_option( '_woobt_position', 'before' ) === 'before' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Above add to cart button', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="after" <?php echo esc_attr( get_option( '_woobt_position', 'before' ) === 'after' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Under add to cart button', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="below_title" <?php echo esc_attr( get_option( '_woobt_position', 'before' ) === 'below_title' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Under the title', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="below_price" <?php echo esc_attr( get_option( '_woobt_position', 'before' ) === 'below_price' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Under the price', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="below_excerpt" <?php echo esc_attr( get_option( '_woobt_position', 'before' ) === 'below_excerpt' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Under the excerpt', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="below_meta" <?php echo esc_attr( get_option( '_woobt_position', 'before' ) === 'below_meta' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Under the meta', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="none" <?php echo esc_attr( get_option( '_woobt_position', 'before' ) === 'none' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'None (hide it)', 'woobt' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Choose the position to show the products list.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Exclude unpurchasable', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_exclude_unpurchasable">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_exclude_unpurchasable', 'no' ) === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'woobt' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_exclude_unpurchasable', 'no' ) === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'woobt' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Exclude unpurchasable products from the list.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show thumbnail', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_show_thumb">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_show_thumb', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_show_thumb', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show price', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_show_price">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_show_price', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Price', 'woobt' ); ?>
                                                    </option>
                                                    <option value="total" <?php echo esc_attr( get_option( '_woobt_show_price', 'yes' ) === 'total' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Total', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_show_price', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show short description', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_show_description">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_show_description', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_show_description', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show plus/minus button', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_plus_minus">
                                                    <option
                                                            value="yes" <?php echo( get_option( '_woobt_plus_minus', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo( get_option( '_woobt_plus_minus', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Show the plus/minus button for the quantity input.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Variations selector', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_variations_selector">
                                                    <option
                                                            value="default" <?php echo esc_attr( get_option( '_woobt_variations_selector', 'default' ) === 'default' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Default', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="wpc_radio" <?php echo esc_attr( get_option( '_woobt_variations_selector', 'default' ) === 'wpc_radio' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Use WPC Variations Radio Buttons', 'woobt' ); ?>
                                                    </option>
                                                </select> <span class="description">If you choose "Use WPC Variations Radio Buttons", please install <a
                                                            href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=wpc-variations-radio-buttons&TB_iframe=true&width=800&height=550' ) ); ?>"
                                                            class="thickbox"
                                                            title="Install WPC Variations Radio Buttons">WPC Variations Radio Buttons</a> to make it work.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Link to individual product', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_link">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_link', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open in the same tab', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="yes_blank" <?php echo esc_attr( get_option( '_woobt_link', 'yes' ) === 'yes_blank' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open in the new tab', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="yes_popup" <?php echo esc_attr( get_option( '_woobt_link', 'yes' ) === 'yes_popup' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open quick view popup', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_link', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select> <span class="description">If you choose "Open quick view popup", please install <a
                                                            href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=woo-smart-quick-view&TB_iframe=true&width=800&height=550' ) ); ?>"
                                                            class="thickbox" title="Install WPC Smart Quick View">WPC Smart Quick View</a> to make it work.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show "this item"', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_show_this_item">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_show_this_item', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_show_this_item', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'The main product will be listed on the top of the list.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Change image', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_change_image">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_change_image', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_change_image', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Change the main product image when choosing the variation of variable products.', 'woobt' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Change price', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_change_price">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_change_price', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="yes_custom" <?php echo esc_attr( get_option( '_woobt_change_price', 'yes' ) === 'yes_custom' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, custom selector', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_change_price', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select>
                                                <input type="text" name="_woobt_change_price_custom"
                                                       value="<?php echo get_option( '_woobt_change_price_custom', '.summary > .price' ); ?>"
                                                       placeholder=".summary > .price"/>
                                                <span class="description">
											<?php esc_html_e( 'Change the main product price when choosing the variation or quantity of products. It uses JavaScript to change product price so it is very dependent on theme’s HTML. If it cannot find and update the product price, please contact us and we can help you find the right selector or adjust the JS file.', 'woobt' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Counter', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_counter">
                                                    <option
                                                            value="individual" <?php echo esc_attr( get_option( '_woobt_counter', 'individual' ) === 'individual' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Count the individual products', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="qty" <?php echo esc_attr( get_option( '_woobt_counter', 'individual' ) === 'qty' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Count the product quantities', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="hide" <?php echo esc_attr( get_option( '_woobt_counter', 'individual' ) === 'hide' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Hide', 'woobt' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Counter on the add to cart button.', 'woobt' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Additional price text', 'woobt' ); ?></th>
                                            <td>
                                                <input type="text" name="_woobt_total_price_text"
                                                       value="<?php echo get_option( '_woobt_total_price_text', esc_html__( 'Additional price:', 'woobt' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Additional price:', 'woobt' ); ?>"/>
                                                <span class="description"><?php esc_html_e( 'Leave blank to use the default text and can be translated.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Responsive', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_responsive">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_responsive', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_responsive', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Change the layout for small screen devices.', 'woobt' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Cart & Checkout', 'woobt' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Change quantity', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_cart_quantity">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_cart_quantity', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_cart_quantity', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">
											<?php esc_html_e( 'Buyer can change the quantity of associated products or not? If not, the quantities will sync with the main product.', 'woobt' ); ?>
										</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Associated text', 'woobt' ); ?></th>
                                            <td>
                                                <input type="text" name="_woobt_associated_text"
                                                       value="<?php echo get_option( '_woobt_associated_text', '' ); ?>"
                                                       placeholder="<?php /* translators: %s is the main product name */
												       esc_attr_e( '(bought together %s)', 'woobt' ); ?>"/>
                                                <span class="description"><?php /* translators: %s is the main product name */
													esc_html_e( 'The text behind associated products. Use "%s" for the main product name.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Search', 'woobt' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search limit', 'woobt' ); ?></th>
                                            <td>
                                                <input name="_woobt_search_limit" type="number" min="1"
                                                       max="500"
                                                       value="<?php echo get_option( '_woobt_search_limit', 10 ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search by SKU', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_search_sku">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_search_sku', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_search_sku', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search by ID', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_search_id">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_search_id', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_search_id', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Search by ID when only entered the numeric.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search exact', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_search_exact">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_search_exact', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_search_exact', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Match whole product title or content?', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Search sentence', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_search_sentence">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_search_sentence', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_search_sentence', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'Do a phrase search?', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Accept same products', 'woobt' ); ?></th>
                                            <td>
                                                <select name="_woobt_search_same">
                                                    <option
                                                            value="yes" <?php echo esc_attr( get_option( '_woobt_search_same', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woobt' ); ?>
                                                    </option>
                                                    <option
                                                            value="no" <?php echo esc_attr( get_option( '_woobt_search_same', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woobt' ); ?>
                                                    </option>
                                                </select> <span
                                                        class="description"><?php esc_html_e( 'If yes, a product can be added many times.', 'woobt' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
                                                <input type="submit" name="submit" class="button button-primary"
                                                       value="<?php esc_html_e( 'Update Options', 'woobt' ); ?>"/>
                                                <input type="hidden" name="action" value="update"/>
                                                <input type="hidden" name="page_options"
                                                       value="_woobt_pricing,_woobt_default,_woobt_position,_woobt_exclude_unpurchasable,_woobt_show_thumb,_woobt_show_price,_woobt_show_description,_woobt_plus_minus,_woobt_variations_selector,_woobt_link,_woobt_show_this_item,_woobt_change_image,_woobt_change_price,_woobt_change_price_custom,_woobt_counter,_woobt_total_price_text,_woobt_responsive,_woobt_cart_quantity,_woobt_associated_text,_woobt_search_limit,_woobt_search_sku,_woobt_search_id,_woobt_search_exact,_woobt_search_sentence,_woobt_search_same"/>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab === 'premium' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>Get the Premium Version just $29! <a
                                                href="https://wpclever.net/downloads/woocommerce-bought-together?utm_source=pro&utm_medium=woobt&utm_campaign=wporg"
                                                target="_blank">https://wpclever.net/downloads/woocommerce-bought-together</a>
                                    </p>
                                    <p><strong>Extra features for Premium Version:</strong></p>
                                    <ul style="margin-bottom: 0">
                                        <li>- Add a variable product or a specific variation of a product.</li>
                                        <li>- Get the lifetime update & premium support.</li>
                                    </ul>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}

				function woobt_wp_enqueue_scripts() {
					wp_enqueue_style( 'woobt-frontend', WOOBT_URI . 'assets/css/frontend.css' );
					wp_enqueue_script( 'woobt-frontend', WOOBT_URI . 'assets/js/frontend.js', array( 'jquery' ), WOOBT_VERSION, true );

					$total_price_text = get_option( '_woobt_total_price_text' );

					if ( empty( $total_price_text ) ) {
						$total_price_text = esc_html__( 'Additional price:', 'woobt' );
					}

					wp_localize_script( 'woobt-frontend', 'woobt_vars', array(
							'version'                  => WOOBT_VERSION,
							'total_price_text'         => $total_price_text,
							'add_to_cart'              => esc_html__( 'Add to cart', 'woobt' ),
							'position'                 => get_option( '_woobt_position', 'before' ),
							'summary_selector'         => apply_filters( 'woobt_summary_selector', '.summary' ),
							'change_image'             => get_option( '_woobt_change_image', 'yes' ),
							'change_price'             => get_option( '_woobt_change_price', 'yes' ),
							'price_selector'           => get_option( '_woobt_change_price_custom', '' ),
							'counter'                  => get_option( '_woobt_counter', 'individual' ),
							'variation_selector'       => ( class_exists( 'WPClever_Woovr' ) && ( get_option( '_woobt_variations_selector', 'default' ) === 'wpc_radio' ) ) ? 'wpc_radio' : 'default',
							'price_format'             => get_woocommerce_price_format(),
							'price_suffix'             => ( $suffix = get_option( 'woocommerce_price_display_suffix' ) ) && wc_tax_enabled() ? $suffix : '',
							'price_decimals'           => wc_get_price_decimals(),
							'price_thousand_separator' => wc_get_price_thousand_separator(),
							'price_decimal_separator'  => wc_get_price_decimal_separator(),
							'currency_symbol'          => get_woocommerce_currency_symbol(),
							'alert_selection'          => esc_html__( 'Please select some product options for [name] before adding to the cart.', 'woobt' ),
						)
					);
				}

				function woobt_admin_enqueue_scripts() {
					wp_enqueue_style( 'hint', WOOBT_URI . 'assets/css/hint.css' );
					wp_enqueue_style( 'woobt-backend', WOOBT_URI . 'assets/css/backend.css' );
					wp_enqueue_script( 'dragarrange', WOOBT_URI . 'assets/js/drag-arrange.min.js', array( 'jquery' ), WOOBT_VERSION, true );
					wp_enqueue_script( 'woobt-backend', WOOBT_URI . 'assets/js/backend.js', array( 'jquery' ), WOOBT_VERSION, true );
				}

				function woobt_action_links( $links, $file ) {
					static $plugin;

					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}

					if ( $plugin === $file ) {
						$settings         = '<a href="' . admin_url( 'admin.php?page=wpclever-woobt&tab=settings' ) . '">' . esc_html__( 'Settings', 'woobt' ) . '</a>';
						$links['premium'] = '<a href="' . admin_url( 'admin.php?page=wpclever-woobt&tab=premium' ) . '">' . esc_html__( 'Premium Version', 'woobt' ) . '</a>';
						array_unshift( $links, $settings );
					}

					return (array) $links;
				}

				function woobt_row_meta( $links, $file ) {
					static $plugin;

					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}

					if ( $plugin === $file ) {
						$row_meta = array(
							'support' => '<a href="' . esc_url( WOOBT_SUPPORT ) . '" target="_blank">' . esc_html__( 'Support', 'woobt' ) . '</a>',
						);

						return array_merge( $links, $row_meta );
					}

					return (array) $links;
				}

				function woobt_display_post_states( $states, $post ) {
					if ( 'product' == get_post_type( $post->ID ) ) {
						if ( $ids = $this->woobt_get_ids( $post->ID, 'edit' ) ) {
							$items = $this->woobt_get_items( $ids, $post->ID );

							if ( ! empty( $items ) ) {
								$count    = count( $items );
								$states[] = apply_filters( 'woobt_post_states', '<span class="woobt-state">' . sprintf( /* translators: %s is the count */ esc_html__( 'Associate (%s)', 'woobt' ), $count ) . '</span>', $count, $post->ID );
							}
						}
					}

					return $states;
				}

				function woobt_cart_item_removed( $cart_item_key, $cart ) {
					if ( isset( $cart->removed_cart_contents[ $cart_item_key ]['woobt_keys'] ) ) {
						$keys = $cart->removed_cart_contents[ $cart_item_key ]['woobt_keys'];

						foreach ( $keys as $key ) {
							unset( $cart->cart_contents[ $key ] );
						}
					}
				}

				function woobt_cart_item_name( $item_name, $item ) {
					if ( isset( $item['woobt_parent_id'] ) && ! empty( $item['woobt_parent_id'] ) ) {
						$associated_text = get_option( '_woobt_associated_text', '' );

						if ( empty( $associated_text ) ) {
							$associated_text = /* translators: %s is the main product name */
								esc_html__( '(bought together %s)', 'woobt' );
						}

						if ( strpos( $item_name, '</a>' ) !== false ) {
							$name = sprintf( $associated_text, '<a href="' . get_permalink( $item['woobt_parent_id'] ) . '">' . get_the_title( $item['woobt_parent_id'] ) . '</a>' );
						} else {
							$name = sprintf( $associated_text, get_the_title( $item['woobt_parent_id'] ) );
						}

						$item_name .= ' <span class="woobt-item-name">' . apply_filters( 'woobt_item_name', $name, $item ) . '</span>';
					}

					return $item_name;
				}

				function woobt_cart_item_price( $price, $cart_item ) {
					if ( isset( $cart_item['woobt_parent_id'], $cart_item['woobt_price'] ) ) {
						return wc_price( wc_get_price_to_display( $cart_item['data'], array( 'price' => $cart_item['woobt_price'] ) ) );
					}

					return $price;
				}

				function woobt_cart_item_quantity( $quantity, $cart_item_key, $cart_item ) {
					// add qty as text - not input
					if ( isset( $cart_item['woobt_parent_id'] ) ) {
						return $cart_item['quantity'];
					}

					return $quantity;
				}

				function woobt_update_cart_item_quantity( $cart_item_key, $quantity = 0 ) {
					if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['woobt_keys'] ) ) {
						foreach ( WC()->cart->cart_contents[ $cart_item_key ]['woobt_keys'] as $key ) {
							if ( isset( WC()->cart->cart_contents[ $key ] ) ) {
								if ( $quantity <= 0 ) {
									$qty = 0;
								} else {
									$qty = $quantity * ( WC()->cart->cart_contents[ $key ]['woobt_qty'] ?: 1 );
								}

								WC()->cart->set_quantity( $key, $qty, false );
							}
						}
					}
				}

				function woobt_add_cart_item_data( $cart_item_data, $product_id ) {
					if ( isset( $_POST['woobt_ids'] ) && ( $this->woobt_get_ids( $product_id ) || ( get_option( '_woobt_default', 'none' ) !== 'none' ) ) ) {
						// make sure that is bought together product
						$ids = $this->woobt_clean_ids( $_POST['woobt_ids'] );

						if ( ! empty( $ids ) ) {
							$cart_item_data['woobt_ids'] = $ids;
						}

						unset( $_POST['woobt_ids'] );
					}

					return $cart_item_data;
				}

				function woobt_check_in_cart( $product_id ) {
					foreach ( WC()->cart->get_cart() as $cart_item ) {
						if ( $cart_item['product_id'] === $product_id ) {
							return true;
						}
					}

					return false;
				}

				function woobt_individually_found_in_cart( $found_in_cart, $product_id ) {
					if ( $this->woobt_check_in_cart( $product_id ) ) {
						return true;
					}

					return $found_in_cart;
				}

				function woobt_add_to_cart_validation( $passed, $product_id ) {
					if ( ( get_post_meta( $product_id, 'woobt_separately', true ) !== 'on' ) && $this->woobt_get_ids( $product_id ) ) {
						if ( isset( $_POST['woobt_ids'] ) && ! empty( $_POST['woobt_ids'] ) ) {
							if ( $items = $this->woobt_get_items( $_POST['woobt_ids'], $product_id ) ) {
								foreach ( $items as $item ) {
									$item_product = wc_get_product( $item['id'] );

									if ( ! $item_product ) {
										wc_add_notice( esc_html__( 'One of the associated products is unavailable.', 'woobt' ), 'error' );
										wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woobt' ), 'error' );

										return false;
									}

									if ( $item_product->is_type( 'variable' ) ) {
										wc_add_notice( sprintf( /* translators: %s is the product name */ esc_html__( '"%s" is un-purchasable.', 'woobt' ), esc_html( $item_product->get_name() ) ), 'error' );
										wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woobt' ), 'error' );

										return false;
									}

									if ( $item_product->is_sold_individually() && $this->woobt_check_in_cart( $item['id'] ) ) {
										wc_add_notice( sprintf( /* translators: %s is the product name */ esc_html__( 'You cannot add another "%s" to the cart.', 'woobt' ), esc_html( $item_product->get_name() ) ), 'error' );
										wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woobt' ), 'error' );

										return false;
									}

									if ( get_post_meta( $product_id, 'woobt_custom_qty', true ) === 'on' ) {
										if ( ( $limit_min = get_post_meta( $product_id, 'woobt_limit_each_min', true ) ) && ( $item['qty'] < (float) $limit_min ) ) {
											wc_add_notice( sprintf( /* translators: %s is the product name */ esc_html__( '"%s" does not reach the minimum quantity.', 'woobt' ), esc_html( $item_product->get_name() ) ), 'error' );
											wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woobt' ), 'error' );

											return false;
										}

										if ( ( $limit_max = get_post_meta( $product_id, 'woobt_limit_each_max', true ) ) && ( $item['qty'] > (float) $limit_max ) ) {
											wc_add_notice( sprintf( /* translators: %s is the product name */ esc_html__( '"%s" passes the maximum quantity.', 'woobt' ), esc_html( $item_product->get_name() ) ), 'error' );
											wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woobt' ), 'error' );

											return false;
										}
									}
								}
							}
						}
					}

					return $passed;
				}

				function woobt_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
					if ( isset( $cart_item_data['woobt_ids'] ) && ! empty( $cart_item_data['woobt_ids'] ) ) {
						if ( $items = $this->woobt_get_items( $cart_item_data['woobt_ids'], $product_id ) ) {
							$pricing = get_option( '_woobt_pricing', 'sale_price' );

							// add child products
							foreach ( $items as $item ) {
								$item_id           = $item['id'];
								$item_price        = apply_filters( 'woobt_item_price', $item['price'], $item_id, $product_id );
								$item_qty          = $item['qty'];
								$item_variation_id = 0;
								$item_variation    = array();
								$item_product      = wc_get_product( $item_id );

								if ( $item_product instanceof WC_Product_Variation ) {
									// ensure we don't add a variation to the cart directly by variation ID
									$item_variation_id = $item_id;
									$item_id           = $item_product->get_parent_id();
									$item_variation    = $item_product->get_variation_attributes();
								}

								if ( $item_product && $item_product->is_in_stock() && $item_product->is_purchasable() ) {
									// calc new price
									if ( $pricing === 'sale_price' ) {
										// from sale price
										$item_new_price = $this->woobt_new_price( $item_product->get_price(), $item_price );
									} else {
										// from regular price
										$item_new_price = $this->woobt_new_price( $item_product->get_regular_price(), $item_price );
									}

									// add to cart
									if ( get_post_meta( $product_id, 'woobt_separately', true ) !== 'on' ) {
										$item_key = WC()->cart->add_to_cart( $item_id, $item_qty, $item_variation_id, $item_variation, array(
											'woobt_parent_id'  => $product_id,
											'woobt_parent_key' => $cart_item_key,
											'woobt_qty'        => $item_qty / $quantity,
											'woobt_price'      => $item_new_price
										) );

										if ( $item_key ) {
											WC()->cart->cart_contents[ $cart_item_key ]['woobt_keys'][] = $item_key;
										}
									} else {
										WC()->cart->add_to_cart( $item_id, $item_qty, $item_variation_id, $item_variation );
									}
								}
							}
						}
					}
				}

				function woobt_get_cart_contents( $cart_contents ) {
					foreach ( $cart_contents as $cart_item_key => $cart_item ) {
						if ( isset( $cart_item['woobt_parent_id'], $cart_item['woobt_price'] ) ) {
							$cart_item['data']->set_price( $cart_item['woobt_price'] );
						}

						if ( ! empty( $cart_item['woobt_ids'] ) && ( $discount = get_post_meta( $cart_item['product_id'], 'woobt_discount', true ) ) && ( get_post_meta( $cart_item['product_id'], 'woobt_separately', true ) !== 'on' ) ) {
							if ( $cart_item['variation_id'] > 0 ) {
								$item_product = wc_get_product( $cart_item['variation_id'] );
							} else {
								$item_product = wc_get_product( $cart_item['product_id'] );
							}

							$ori_price = $item_product->get_price();

							// check if has linked products
							$has_linked = false;

							if ( isset( $cart_item['woobt_keys'] ) ) {
								foreach ( $cart_item['woobt_keys'] as $key ) {
									if ( isset( $cart_contents[ $key ] ) ) {
										$has_linked = true;
										break;
									}
								}
							}

							if ( $has_linked ) {
								$discount_price = $ori_price * ( 100 - (float) $discount ) / 100;
								$cart_item['data']->set_price( $discount_price );
							} else {
								$cart_item['data']->set_price( $ori_price );
							}
						}
					}

					return $cart_contents;
				}

				function woobt_get_cart_item_from_session( $cart_item, $item_session_values ) {
					if ( isset( $item_session_values['woobt_ids'] ) && ! empty( $item_session_values['woobt_ids'] ) ) {
						$cart_item['woobt_ids'] = $item_session_values['woobt_ids'];
					}

					if ( isset( $item_session_values['woobt_parent_id'] ) ) {
						$cart_item['woobt_parent_id']  = $item_session_values['woobt_parent_id'];
						$cart_item['woobt_parent_key'] = $item_session_values['woobt_parent_key'];
						$cart_item['woobt_price']      = $item_session_values['woobt_price'];
						$cart_item['woobt_qty']        = $item_session_values['woobt_qty'];
					}

					return $cart_item;
				}

				function woobt_add_order_item_meta( $item, $cart_item_key, $values ) {
					// add _ to hide
					if ( isset( $values['woobt_parent_id'] ) ) {
						$item->update_meta_data( '_woobt_parent_id', $values['woobt_parent_id'] );
					}

					if ( isset( $values['woobt_ids'] ) ) {
						$item->update_meta_data( '_woobt_ids', $values['woobt_ids'] );
					}
				}

				function woobt_hidden_order_item_meta( $hidden ) {
					return array_merge( $hidden, array(
						'_woobt_parent_id',
						'_woobt_ids',
						'woobt_parent_id',
						'woobt_ids'
					) );
				}

				function woobt_before_order_item_meta( $item_id ) {
					if ( $parent_id = wc_get_order_item_meta( $item_id, '_woobt_parent_id', true ) ) {
						$associated_text = get_option( '_woobt_associated_text', '' );

						if ( empty( $associated_text ) ) {
							$associated_text = /* translators: %s is the main product name */
								esc_html__( '(bought together %s)', 'woobt' );
						}

						echo sprintf( $associated_text, get_the_title( $parent_id ) );
					}
				}

				function woobt_get_search_results() {
					$keyword        = esc_html( $_POST['woobt_keyword'] );
					$id             = absint( $_POST['woobt_id'] );
					$ids            = $this->woobt_clean_ids( $_POST['woobt_ids'] );
					$exclude_ids    = array( $id );
					$added_products = explode( ',', $ids );

					if ( ( get_option( '_woobt_search_id', 'no' ) === 'yes' ) && is_numeric( $keyword ) ) {
						// search by id
						$query_args = array(
							'p'         => absint( $keyword ),
							'post_type' => 'product'
						);
					} else {
						$query_args = array(
							'is_woobt'       => true,
							'post_type'      => 'product',
							'post_status'    => 'publish',
							's'              => $keyword,
							'posts_per_page' => get_option( '_woobt_search_limit', 10 ),
						);

						if ( get_option( '_woobt_search_same', 'no' ) !== 'yes' ) {
							if ( is_array( $added_products ) && count( $added_products ) > 0 ) {
								foreach ( $added_products as $added_product ) {
									$added_product_data = explode( '/', $added_product );
									$exclude_ids[]      = absint( $added_product_data[0] ?: 0 );
								}
							}

							$query_args['post__not_in'] = $exclude_ids;
						}
					}

					$query = new WP_Query( $query_args );

					if ( $query->have_posts() ) {
						echo '<ul>';

						while ( $query->have_posts() ) {
							$query->the_post();
							$product = wc_get_product( get_the_ID() );

							if ( ! $product ) {
								continue;
							}

							$this->woobt_product_data_li( $product, '100%', 1, true );

							if ( $product->is_type( 'variable' ) ) {
								// show all children
								$children = $product->get_children();

								if ( is_array( $children ) && count( $children ) > 0 ) {
									foreach ( $children as $child ) {
										$product_child = wc_get_product( $child );

										if ( $product_child ) {
											$this->woobt_product_data_li( $product_child, '100%', 1, true );
										}
									}
								}
							}
						}

						echo '</ul>';
						wp_reset_postdata();
					} else {
						echo '<ul><span>' . sprintf( /* translators: %s is the keyword */ esc_html__( 'No results found for "%s"', 'woobt' ), $keyword ) . '</span></ul>';
					}

					die();
				}

				function woobt_product_data_li( $product, $price = '100%', $qty = 1, $search = false ) {
					$product_id    = $product->get_id();
					$product_class = ! $product->is_in_stock() ? 'out-of-stock' : '';

					if ( ! in_array( $product->get_type(), self::$types, true ) ) {
						$product_class .= ' disabled';
					}

					if ( class_exists( 'WPCleverWoopq' ) && ( get_option( '_woopq_decimal', 'no' ) === 'yes' ) ) {
						$step = '0.000001';
					} else {
						$step = '1';
						$qty  = (int) $qty;
					}

					if ( $search ) {
						$remove_btn = '<span class="remove hint--left" aria-label="' . esc_html__( 'Add', 'woobt' ) . '">+</span>';
					} else {
						$remove_btn = '<span class="remove hint--left" aria-label="' . esc_html__( 'Remove', 'woobt' ) . '">×</span>';
					}

					echo '<li class="' . esc_attr( trim( $product_class ) ) . '" data-id="' . $product->get_id() . '"><span class="move"></span><span class="price hint--right" aria-label="' . esc_html__( 'Set a new price using a number (eg. "49") or percentage (eg. "90%" of original price)', 'woobt' ) . '"><input type="text" value="' . $price . '"/></span><span class="qty hint--right" aria-label="' . esc_html__( 'Default quantity', 'woobt' ) . '"><input type="number" value="' . esc_attr( $qty ) . '" step="' . esc_attr( $step ) . '"/></span> <span class="data">' . strip_tags( $product->get_name() ) . ' (' . $product->get_price_html() . ')</span> <span class="type"><a href="' . get_edit_post_link( $product_id ) . '" target="_blank">' . $product->get_type() . '<br/>#' . $product->get_id() . '</a></span> ' . $remove_btn . '</li>';
				}

				function woobt_product_data_tabs( $tabs ) {
					$tabs['woobt'] = array(
						'label'  => esc_html__( 'Bought Together', 'woobt' ),
						'target' => 'woobt_settings',
					);

					return $tabs;
				}

				function woobt_product_data_panels() {
					global $post;
					$post_id = $post->ID;
					?>
                    <div id='woobt_settings' class='panel woocommerce_options_panel woobt_table'>
                        <table>
                            <tr>
                                <th><?php esc_html_e( 'Search', 'woobt' ); ?> (<a
                                            href="<?php echo admin_url( 'admin.php?page=wpclever-woobt&tab=settings#search' ); ?>"
                                            target="_blank"><?php esc_html_e( 'settings', 'woobt' ); ?></a>)
                                </th>
                                <td>
                                    <div class="w100">
								<span class="loading"
                                      id="woobt_loading"
                                      style="display:none;"><?php esc_html_e( 'searching...', 'woobt' ); ?></span>
                                        <input type="search" id="woobt_keyword"
                                               placeholder="<?php esc_attr_e( 'Type any keyword to search', 'woobt' ); ?>"/>
                                        <div id="woobt_results" class="woobt_results" style="display:none;"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Selected', 'woobt' ); ?></th>
                                <td>
                                    <div class="w100">
                                        <input type="hidden" id="woobt_id" name="woobt_id"
                                               value="<?php echo esc_attr( $post_id ); ?>"/>
                                        <input type="hidden" id="woobt_ids" name="woobt_ids"
                                               value="<?php echo $this->woobt_get_ids( $post_id, 'edit' ); ?>"
                                               readonly/>
                                        <div id="woobt_selected" class="woobt_selected">
                                            <ul>
												<?php
												echo '<li class="woobt_default">' . sprintf( esc_html__( '* If don\'t choose any products, it can shows the default products %s.', 'woobt' ), '<a
                                                    href="' . admin_url( 'admin.php?page=wpclever-woobt&tab=settings#search' ) . '" target="_blank">' . esc_html__( 'here', 'woobt' ) . '</a>' ) . '</li>';

												if ( $ids = $this->woobt_get_ids( $post_id, 'edit' ) ) {
													if ( $items = $this->woobt_get_items( $ids, $post_id ) ) {
														foreach ( $items as $item ) {
															$item_id      = $item['id'];
															$item_price   = $item['price'];
															$item_qty     = $item['qty'];
															$item_product = wc_get_product( $item_id );

															if ( ! $item_product ) {
																continue;
															}

															$this->woobt_product_data_li( $item_product, $item_price, $item_qty, false );
														}
													}
												}
												?>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Add separately', 'woobt' ); ?></th>
                                <td>
                                    <input id="woobt_separately" name="woobt_separately"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_separately', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <span class="woocommerce-help-tip"
                                          data-tip="<?php esc_attr_e( 'If enabled, the associated products will be added as separate items and stay unaffected from the main product, their prices will change back to the original.', 'woobt' ); ?>"></span>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Discount', 'woobt' ); ?></th>
                                <td>
                                    <input id="woobt_discount" name="woobt_discount"
                                           type="number" min="0" max="100" step="0.0001" style="width: 50px"
                                           value="<?php echo get_post_meta( $post_id, 'woobt_discount', true ); ?>"/>%
                                    <span
                                            class="woocommerce-help-tip"
                                            data-tip="Discount for the main product when buying at least one product in this list."></span>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Checked all', 'woobt' ); ?></th>
                                <td>
                                    <input id="woobt_checked_all" name="woobt_checked_all"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_checked_all', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woobt_checked_all"><?php esc_html_e( 'Checked all by default.', 'woobt' ); ?></label>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Custom quantity', 'woobt' ); ?></th>
                                <td>
                                    <input id="woobt_custom_qty" name="woobt_custom_qty"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_custom_qty', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woobt_custom_qty"><?php esc_html_e( 'Allow the customer can change the quantity of each product.', 'woobt' ); ?></label>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space woobt_tr_hide_if_custom_qty">
                                <th><?php esc_html_e( 'Sync quantity', 'woobt' ); ?></th>
                                <td>
                                    <input id="woobt_sync_qty" name="woobt_sync_qty"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_sync_qty', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woobt_sync_qty"><?php esc_html_e( 'Sync the quantity of the main product with associated products.', 'woobt' ); ?></label>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space woobt_tr_show_if_custom_qty">
                                <th><?php esc_html_e( 'Limit each item', 'woobt' ); ?></th>
                                <td>
                                    <input id="woobt_limit_each_min_default" name="woobt_limit_each_min_default"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_limit_each_min_default', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woobt_limit_each_min_default"><?php esc_html_e( 'Use default quantity as min', 'woobt' ); ?></label>
                                    <u>or</u> Min <input name="woobt_limit_each_min" type="number"
                                                         min="0"
                                                         value="<?php echo( get_post_meta( $post_id, 'woobt_limit_each_min', true ) ?: '' ); ?>"
                                                         style="width: 60px; float: none"/> Max <input
                                            name="woobt_limit_each_max"
                                            type="number" min="1"
                                            value="<?php echo( get_post_meta( $post_id, 'woobt_limit_each_max', true ) ?: '' ); ?>"
                                            style="width: 60px; float: none"/>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Above text', 'woobt' ); ?></th>
                                <td>
                                    <div class="w100">
                                        <textarea
                                                name="woobt_before_text" rows="1"
                                                style="width: 100%"><?php echo stripslashes( get_post_meta( $post_id, 'woobt_before_text', true ) ); ?></textarea>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Under text', 'woobt' ); ?></th>
                                <td>
                                    <div class="w100">
                                        <textarea
                                                name="woobt_after_text" rows="1"
                                                style="width: 100%"><?php echo stripslashes( get_post_meta( $post_id, 'woobt_after_text', true ) ); ?></textarea>
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
					<?php
				}

				function woobt_save_option_field( $post_id ) {
					if ( isset( $_POST['woobt_ids'] ) ) {
						$ids = $this->woobt_clean_ids( sanitize_text_field( $_POST['woobt_ids'] ) );
						update_post_meta( $post_id, 'woobt_ids', $ids );
					}

					if ( ! empty( $_POST['woobt_discount'] ) ) {
						update_post_meta( $post_id, 'woobt_discount', $_POST['woobt_discount'] );
					} else {
						delete_post_meta( $post_id, 'woobt_discount' );
					}

					if ( ! empty( $_POST['woobt_before_text'] ) ) {
						update_post_meta( $post_id, 'woobt_before_text', addslashes( $_POST['woobt_before_text'] ) );
					} else {
						delete_post_meta( $post_id, 'woobt_before_text' );
					}

					if ( ! empty( $_POST['woobt_after_text'] ) ) {
						update_post_meta( $post_id, 'woobt_after_text', addslashes( $_POST['woobt_after_text'] ) );
					} else {
						delete_post_meta( $post_id, 'woobt_after_text' );
					}

					if ( isset( $_POST['woobt_checked_all'] ) ) {
						update_post_meta( $post_id, 'woobt_checked_all', 'on' );
					} else {
						update_post_meta( $post_id, 'woobt_checked_all', 'off' );
					}

					if ( isset( $_POST['woobt_separately'] ) ) {
						update_post_meta( $post_id, 'woobt_separately', 'on' );
					} else {
						update_post_meta( $post_id, 'woobt_separately', 'off' );
					}

					if ( isset( $_POST['woobt_custom_qty'] ) ) {
						update_post_meta( $post_id, 'woobt_custom_qty', 'on' );
					} else {
						update_post_meta( $post_id, 'woobt_custom_qty', 'off' );
					}

					if ( isset( $_POST['woobt_sync_qty'] ) ) {
						update_post_meta( $post_id, 'woobt_sync_qty', 'on' );
					} else {
						update_post_meta( $post_id, 'woobt_sync_qty', 'off' );
					}

					if ( isset( $_POST['woobt_limit_each_min_default'] ) ) {
						update_post_meta( $post_id, 'woobt_limit_each_min_default', 'on' );
					} else {
						update_post_meta( $post_id, 'woobt_limit_each_min_default', 'off' );
					}

					if ( isset( $_POST['woobt_limit_each_min'] ) ) {
						update_post_meta( $post_id, 'woobt_limit_each_min', sanitize_text_field( $_POST['woobt_limit_each_min'] ) );
					}

					if ( isset( $_POST['woobt_limit_each_max'] ) ) {
						update_post_meta( $post_id, 'woobt_limit_each_max', sanitize_text_field( $_POST['woobt_limit_each_max'] ) );
					}
				}

				function woobt_add_to_cart_form() {
					global $product;

					if ( ! $product->is_type( 'grouped' ) ) {
						wp_enqueue_script( 'wc-add-to-cart-variation' );
						$this->woobt_show_items();
					}
				}

				function woobt_add_to_cart_button() {
					global $product;

					if ( ! $product->is_type( 'grouped' ) ) {
						echo '<input name="woobt_ids" class="woobt_ids woobt-ids" data-id="' . esc_attr( $product->get_id() ) . '" type="hidden"/>';
					}
				}

				function woobt_has_variables( $items ) {
					foreach ( $items as $item ) {
						if ( is_array( $item ) && isset( $item['id'] ) ) {
							$item_id = $item['id'];
						} else {
							$item_id = absint( $item );
						}

						$item_product = wc_get_product( $item_id );

						if ( ! $item_product ) {
							continue;
						}

						if ( $item_product->is_type( 'variable' ) ) {
							return true;
						}
					}

					return false;
				}

				function woobt_shortcode() {
					ob_start();
					$this->woobt_show_items();
					$items = ob_get_clean();

					return $items;
				}

				function woobt_show_items() {
					global $product;
					$product_id  = $product->get_id();
					$items       = array();
					$pricing     = get_option( '_woobt_pricing', 'sale_price' );
					$custom_qty  = get_post_meta( $product_id, 'woobt_custom_qty', true ) === 'on';
					$sync_qty    = get_post_meta( $product_id, 'woobt_sync_qty', true ) === 'on';
					$checked_all = get_post_meta( $product_id, 'woobt_checked_all', true ) === 'on';
					$separately  = get_post_meta( $product_id, 'woobt_separately', true ) === 'on';
					$count       = 1;

					if ( $ids = $this->woobt_get_ids( $product_id ) ) {
						$items = $this->woobt_get_items( $ids, $product_id );
					}

					if ( ! $items && ( get_option( '_woobt_default', 'none' ) === 'upsells' ) ) {
						$items = $product->get_upsell_ids();
					}

					if ( ! $items && ( get_option( '_woobt_default', 'none' ) === 'related' ) ) {
						$items = wc_get_related_products( $product_id );
					}

					if ( ! $items && ( get_option( '_woobt_default', 'none' ) === 'related_upsells' ) ) {
						$items_upsells = $product->get_upsell_ids();
						$items_related = wc_get_related_products( $product_id );
						$items         = array_merge( $items_upsells, $items_related );
					}

					if ( $items && ! empty( $items ) ) {
						echo '<div class="woobt-wrap woobt-wrap-' . esc_attr( $product_id ) . ' ' . ( get_option( '_woobt_responsive', 'yes' ) === 'yes' ? 'woobt-wrap-responsive' : '' ) . '" data-id="' . esc_attr( $product_id ) . '">';

						do_action( 'woobt_wrap_before', $product );

						if ( $before_text = apply_filters( 'woobt_before_text', get_post_meta( $product_id, 'woobt_before_text', true ), $product_id ) ) {
							echo '<div class="woobt_before_text woobt-before-text woobt-text">' . do_shortcode( stripslashes( $before_text ) ) . '</div>';
						}
						?>
                        <div class="woobt_products woobt-products"
                             data-show-price="<?php echo esc_attr( get_option( '_woobt_show_price', 'yes' ) ); ?>"
                             data-optional="<?php echo esc_attr( $custom_qty ? 'on' : 'off' ); ?>"
                             data-sync-qty="<?php echo esc_attr( $sync_qty ? 'on' : 'off' ); ?>"
                             data-variables="<?php echo esc_attr( $this->woobt_has_variables( $items ) ? 'yes' : 'no' ); ?>"
                             data-product-id="<?php echo esc_attr( $product->get_type() === 'variable' ? '0' : $product_id ); ?>"
                             data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
                             data-product-price="<?php echo esc_attr( $product->get_type() === 'variable' ? '0' : wc_get_price_to_display( $product ) ); ?>"
                             data-product-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
                             data-product-sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                             data-product-o-sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                             data-product-price-html="<?php echo esc_attr( htmlentities( $product->get_price_html() ) ); ?>"
                             data-pricing="<?php echo esc_attr( $pricing ); ?>"
                             data-discount="<?php echo esc_attr( ! $separately && get_post_meta( $product_id, 'woobt_discount', true ) ? get_post_meta( $product_id, 'woobt_discount', true ) : '0' ); ?>">
							<?php
							// this item
							if ( get_option( '_woobt_show_this_item', 'yes' ) !== 'no' ) {
								?>
                                <div class="woobt-product woobt-product-this"
                                     data-id="<?php echo esc_attr( $product_id ); ?>"
                                     data-new-price="<?php echo esc_attr( ! $separately && ( $discount = get_post_meta( $product_id, 'woobt_discount', true ) ) ? $product->get_price() * ( 100 - (float) $discount ) / 100 : '100%' ); ?>"
                                     data-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
                                     data-price="<?php echo esc_attr( wc_get_price_to_display( $product ) ); ?>"
                                     data-regular-price="<?php echo esc_attr( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ); ?>"
                                     data-qty="1"
                                     data-qty-ori="1">
									<?php do_action( 'woobt_product_before', $product ); ?>

                                    <div class="woobt-choose">
                                        <input class="woobt-checkbox" type="checkbox" checked disabled/>
                                        <span class="checkmark"></span>
                                    </div>

									<?php if ( get_option( '_woobt_show_thumb', 'yes' ) !== 'no' ) { ?>
                                        <div class="woobt-thumb">
											<?php echo $product->get_image(); ?>
                                        </div>
									<?php } ?>

                                    <div class="woobt-title">
										<?php echo esc_html__( 'This item:', 'woobt' ) . ' <span>' . $product->get_name() . '</span>'; ?>
                                    </div>

									<?php if ( $custom_qty ) { ?>
                                        <div class="woobt-quantity">
											<?php
											if ( get_option( '_woobt_plus_minus', 'no' ) === 'yes' ) {
												echo '<div class="woobt-quantity-input">';
												echo '<div class="woobt-quantity-input-minus">-</div>';
											}

											woocommerce_quantity_input( array(
												'input_name' => 'woobt-quantity',
												'classes'    => array(
													'input-text',
													'woobt-qty',
													'woobt-this-qty',
													'qty',
													'text'
												)
											), $product );

											if ( get_option( '_woobt_plus_minus', 'no' ) === 'yes' ) {
												echo '<div class="woobt-quantity-input-plus">+</div>';
												echo '</div><!-- /woobt-quantity-input -->';
											}
											?>
                                        </div>
									<?php }

									if ( get_option( '_woobt_show_price', 'yes' ) !== 'no' ) { ?>
                                        <div class="woobt-price">
                                            <div class="woobt-price-new">
												<?php
												if ( ! $separately && ( $discount = get_post_meta( $product_id, 'woobt_discount', true ) ) ) {
													$sale_price = $product->get_price() * ( 100 - (float) $discount ) / 100;
													echo wc_format_sale_price( $product->get_price(), $sale_price ) . $product->get_price_suffix( $sale_price );
												} else {
													echo $product->get_price_html();
												}
												?>
                                            </div>
                                            <div class="woobt-price-ori">
												<?php echo $product->get_price_html(); ?>
                                            </div>
                                        </div>
									<?php }

									do_action( 'woobt_product_after', $product );
									?>
                                </div>
								<?php
							}

							// other items
							foreach ( $items as $item ) {
								if ( is_array( $item ) ) {
									$item_id    = $item['id'];
									$item_price = $item['price'];
									$item_qty   = $item['qty'];
								} else {
									// make it works with upsells & related
									$item_id    = absint( $item );
									$item_price = '100%';
									$item_qty   = 1;
								}

								$item_product = wc_get_product( $item_id );

								if ( ! $item_product || ! in_array( $item_product->get_type(), self::$types, true ) || ( ( get_option( '_woobt_exclude_unpurchasable', 'no' ) === 'yes' ) && ( ! $item_product->is_purchasable() || ! $item_product->is_in_stock() ) ) ) {
									continue;
								}

								$item_qty_min = 1;
								$item_qty_max = 1000;

								if ( $custom_qty ) {
									if ( get_post_meta( $product_id, 'woobt_limit_each_min_default', true ) === 'on' ) {
										$item_qty_min = $item_qty;
									} else {
										$item_qty_min = absint( get_post_meta( $product_id, 'woobt_limit_each_min', true ) ?: 0 );
									}

									$item_qty_max = absint( get_post_meta( $product_id, 'woobt_limit_each_max', true ) ?: 1000 );

									if ( $item_qty < $item_qty_min ) {
										$item_qty = $item_qty_min;
									}

									if ( $item_qty > $item_qty_max ) {
										$item_qty = $item_qty_max;
									}
								}

								$checked_individual = apply_filters( 'woobt_checked_individual', false, $item_id, $product_id );
								$item_price         = apply_filters( 'woobt_item_price', ! $separately ? $item_price : '100%', $item_id, $product_id );
								?>
                                <div class="woobt-product woobt-product-together"
                                     data-id="<?php echo esc_attr( $item_product->is_type( 'variable' ) || ! $item_product->is_in_stock() ? 0 : $item_id ); ?>"
                                     data-name="<?php echo esc_attr( $item_product->get_name() ); ?>"
                                     data-new-price="<?php echo esc_attr( $item_price ); ?>"
                                     data-price-suffix="<?php echo esc_attr( htmlentities( $item_product->get_price_suffix() ) ); ?>"
                                     data-price="<?php echo esc_attr( ( $pricing === 'sale_price' ) ? wc_get_price_to_display( $item_product ) : wc_get_price_to_display( $item_product, array( 'price' => $item_product->get_regular_price() ) ) ); ?>"
                                     data-regular-price="<?php echo esc_attr( wc_get_price_to_display( $item_product, array( 'price' => $item_product->get_regular_price() ) ) ); ?>"
                                     data-qty="<?php echo esc_attr( $item_qty ); ?>"
                                     data-qty-ori="<?php echo esc_attr( $item_qty ); ?>">

									<?php do_action( 'woobt_product_before', $item_product, $count ); ?>

                                    <div class="woobt-choose">
                                        <input class="woobt-checkbox" type="checkbox"
                                               value="<?php echo esc_attr( $item_id ); ?>" <?php echo( ! $item_product->is_in_stock() ? 'disabled' : '' ); ?> <?php echo( $item_product->is_in_stock() && ( $checked_all || $checked_individual ) ? 'checked' : '' ); ?>/>
                                        <span class="checkmark"></span>
                                    </div>

									<?php if ( get_option( '_woobt_show_thumb', 'yes' ) !== 'no' ) { ?>
                                        <div class="woobt-thumb">
                                            <div class="woobt-thumb-ori">
												<?php echo $item_product->get_image(); ?>
                                            </div>
                                            <div class="woobt-thumb-new"></div>
                                        </div>
									<?php } ?>

                                    <div class="woobt-title">
										<?php if ( ! $custom_qty ) {
											$item_product_qty = '<span class="woobt-qty-num"><span class="woobt-qty">' . $item_qty . '</span> × </span>';
										} else {
											$item_product_qty = '';
										}

										echo apply_filters( 'woobt_product_qty', $item_product_qty, $item_qty, $item_product );

										if ( $item_product->is_in_stock() ) {
											$item_product_name = $item_product->get_name();
										} else {
											$item_product_name = '<s>' . $item_product->get_name() . '</s>';
										}

										if ( get_option( '_woobt_link', 'yes' ) !== 'no' ) {
											$item_product_name = '<a ' . ( get_option( '_woobt_link', 'yes' ) === 'yes_popup' ? 'class="woosq-btn" data-id="' . $item_id . '"' : '' ) . ' href="' . $item_product->get_permalink() . '" ' . ( get_option( '_woobt_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>' . $item_product_name . '</a>';
										}

										echo apply_filters( 'woobt_product_name', $item_product_name, $item_product );

										if ( get_option( '_woobt_show_description', 'no' ) === 'yes' ) {
											echo '<div class="woobt-description">' . $item_product->get_short_description() . '</div>';
										}

										echo '<div class="woobt-availability">' . wc_get_stock_html( $item_product ) . '</div>';
										?>
                                    </div><!-- /woobt-title -->

									<?php if ( $custom_qty ) {
										echo '<div class="woobt-quantity">';

										if ( get_option( '_woobt_plus_minus', 'no' ) === 'yes' ) {
											echo '<div class="woobt-quantity-input">';
											echo '<div class="woobt-quantity-input-minus">-</div>';
										}

										woocommerce_quantity_input( array(
											'input_name'  => 'woobt-quantity',
											'classes'     => array( 'input-text', 'woobt-qty', 'qty', 'text' ),
											'input_value' => $item_qty,
											'min_value'   => $item_qty_min,
											'max_value'   => $item_qty_max
										), $item_product );

										if ( get_option( '_woobt_plus_minus', 'no' ) === 'yes' ) {
											echo '<div class="woobt-quantity-input-plus">+</div>';
											echo '</div><!-- /woobt-quantity-input -->';
										}

										echo '</div><!-- /woobt-quantity -->';
									}

									if ( get_option( '_woobt_show_price', 'yes' ) !== 'no' ) { ?>
                                        <div class="woobt-price">
                                            <div class="woobt-price-new"></div>
                                            <div class="woobt-price-ori">
												<?php
												if ( ! $separately && ( $item_price !== '100%' ) ) {
													$item_ori_price = ( $pricing === 'sale_price' ) ? $item_product->get_price() : $item_product->get_regular_price();
													$item_new_price = $this->woobt_new_price( wc_get_price_to_display( $item_product, array( 'price' => $item_ori_price ) ), $item_price );

													if ( $item_new_price < $item_ori_price ) {
														$item_product_price = wc_format_sale_price( wc_get_price_to_display( $item_product, array( 'price' => $item_ori_price ) ), $item_new_price );
													} else {
														$item_product_price = wc_price( $item_new_price );
													}

													$item_product_price .= $item_product->get_price_suffix();
												} else {
													$item_product_price = $item_product->get_price_html();
												}

												echo apply_filters( 'woobt_product_price', $item_product_price, $item_product );
												?>
                                            </div>
                                        </div><!-- /woobt-price -->
									<?php }

									do_action( 'woobt_product_after', $item_product, $count );
									?>
                                </div>
								<?php
								$count ++;
							} ?>
                        </div>
						<?php
						echo '<div class="woobt_total woobt-total woobt-text"></div>';
						echo '<div class="woobt_alert woobt-alert woobt-text" style="display: none"></div>';

						if ( $after_text = apply_filters( 'woobt_after_text', get_post_meta( $product_id, 'woobt_after_text', true ), $product_id ) ) {
							echo '<div class="woobt_after_text woobt-after-text woobt-text">' . do_shortcode( stripslashes( $after_text ) ) . '</div>';
						}

						do_action( 'woobt_wrap_after', $product );

						echo '</div><!-- /woobt-wrap -->';
					}
				}

				function woobt_get_ids( $product_id, $context = 'display' ) {
					$ids = get_post_meta( $product_id, 'woobt_ids', true );

					return apply_filters( 'woobt_get_ids', $ids, $product_id, $context );
				}

				function woobt_clean_ids( $ids ) {
					$ids = preg_replace( '/[^.%,\/0-9]/', '', $ids );

					return $ids;
				}

				function woobt_get_items( $ids, $product_id = 0 ) {
					$items = array();
					$ids   = $this->woobt_clean_ids( $ids );

					if ( ! empty( $ids ) ) {
						$_items = explode( ',', $ids );

						if ( is_array( $_items ) && count( $_items ) > 0 ) {
							foreach ( $_items as $_item ) {
								$_item_data    = explode( '/', $_item );
								$_item_id      = absint( isset( $_item_data[0] ) ? $_item_data[0] : 0 );
								$_item_product = wc_get_product( $_item_id );

								if ( ! $_item_product || ( ( get_option( '_woobt_exclude_unpurchasable', 'no' ) === 'yes' ) && ( ! $_item_product->is_purchasable() || ! $_item_product->is_in_stock() ) ) ) {
									continue;
								}

								$items[] = array(
									'id'    => $_item_id,
									'price' => isset( $_item_data[1] ) ? $this->woobt_format_price( $_item_data[1] ) : '100%',
									'qty'   => (float) ( isset( $_item_data[2] ) ? $_item_data[2] : 1 )
								);
							}
						}
					}

					$items = apply_filters( 'woobt_get_items', $items, $product_id );

					if ( count( $items ) > 0 ) {
						return $items;
					}

					return false;
				}

				function woobt_search_sku( $query ) {
					if ( $query->is_search && isset( $query->query['is_woobt'] ) ) {
						global $wpdb;
						$sku = $query->query['s'];
						$ids = $wpdb->get_col( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value = %s;", $sku ) );

						if ( ! $ids ) {
							return;
						}

						unset( $query->query['s'], $query->query_vars['s'] );
						$query->query['post__in'] = array();

						foreach ( $ids as $id ) {
							$post = get_post( $id );

							if ( $post->post_type === 'product_variation' ) {
								$query->query['post__in'][]      = $post->post_parent;
								$query->query_vars['post__in'][] = $post->post_parent;
							} else {
								$query->query_vars['post__in'][] = $post->ID;
							}
						}
					}
				}

				function woobt_search_exact( $query ) {
					if ( $query->is_search && isset( $query->query['is_woobt'] ) ) {
						$query->set( 'exact', true );
					}
				}

				function woobt_search_sentence( $query ) {
					if ( $query->is_search && isset( $query->query['is_woobt'] ) ) {
						$query->set( 'sentence', true );
					}
				}

				function woobt_format_price( $price ) {
					// format price to percent or number
					$price = preg_replace( '/[^.%0-9]/', '', $price );

					return $price;
				}

				function woobt_new_price( $old_price, $new_price ) {
					if ( strpos( $new_price, '%' ) !== false ) {
						$calc_price = ( (float) $new_price * $old_price ) / 100;
					} else {
						$calc_price = $new_price;
					}

					return $calc_price;
				}
			}

			new WPCleverWoobt();
		}
	}
} else {
	add_action( 'admin_notices', 'woobt_notice_premium' );
}

if ( ! function_exists( 'woobt_notice_wc' ) ) {
	function woobt_notice_wc() {
		?>
        <div class="error">
            <p><strong>WPC Frequently Bought Together</strong> requires WooCommerce version 3.0 or greater.</p>
        </div>
		<?php
	}
}

if ( ! function_exists( 'woobt_notice_premium' ) ) {
	function woobt_notice_premium() {
		?>
        <div class="error">
            <p>Seems you're using both free and premium version of <strong>WPC Frequently Bought Together</strong>.
                Please deactivate the free version when using the premium version.</p>
        </div>
		<?php
	}
}