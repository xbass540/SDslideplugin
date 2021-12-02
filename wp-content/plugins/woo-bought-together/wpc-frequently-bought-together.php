<?php
/*
Plugin Name: WPC Frequently Bought Together for WooCommerce
Plugin URI: https://wpclever.net/
Description: Increase your sales with personalized product recommendations.
Version: 4.0.0
Author: WPClever
Author URI: https://wpclever.net
Text Domain: woo-bought-together
Domain Path: /languages/
Requires at least: 4.0
Tested up to: 5.8
WC requires at least: 3.0
WC tested up to: 5.9
*/

defined( 'ABSPATH' ) || exit;

! defined( 'WOOBT_VERSION' ) && define( 'WOOBT_VERSION', '4.0.0' );
! defined( 'WOOBT_URI' ) && define( 'WOOBT_URI', plugin_dir_url( __FILE__ ) );
! defined( 'WOOBT_REVIEWS' ) && define( 'WOOBT_REVIEWS', 'https://wordpress.org/support/plugin/woo-bought-together/reviews/?filter=5' );
! defined( 'WOOBT_CHANGELOG' ) && define( 'WOOBT_CHANGELOG', 'https://wordpress.org/plugins/woo-bought-together/#developers' );
! defined( 'WOOBT_DISCUSSION' ) && define( 'WOOBT_DISCUSSION', 'https://wordpress.org/support/plugin/woo-bought-together' );
! defined( 'WPC_URI' ) && define( 'WPC_URI', WOOBT_URI );

include 'includes/wpc-dashboard.php';
include 'includes/wpc-menu.php';
include 'includes/wpc-kit.php';
include 'includes/wpc-notice.php';

if ( ! function_exists( 'woobt_init' ) ) {
	add_action( 'plugins_loaded', 'woobt_init', 11 );

	function woobt_init() {
		// Load textdomain
		load_plugin_textdomain( 'woo-bought-together', false, basename( __DIR__ ) . '/languages/' );

		if ( ! function_exists( 'WC' ) || ! version_compare( WC()->version, '3.0', '>=' ) ) {
			add_action( 'admin_notices', 'woobt_notice_wc' );

			return;
		}

		if ( ! class_exists( 'WPCleverWoobt' ) && class_exists( 'WC_Product' ) ) {
			class WPCleverWoobt {
				protected static $localization = array();
				protected static $types = array( 'simple', 'woosb', 'bundle', 'subscription' );

				function __construct() {
					// Init
					add_action( 'init', array( $this, 'woobt_init' ) );

					// Menu
					add_action( 'admin_menu', array( $this, 'woobt_admin_menu' ) );

					// Enqueue frontend scripts
					add_action( 'wp_enqueue_scripts', array( $this, 'woobt_wp_enqueue_scripts' ) );

					// Enqueue backend scripts
					add_action( 'admin_enqueue_scripts', array( $this, 'woobt_admin_enqueue_scripts' ) );

					// Backend AJAX search
					add_action( 'wp_ajax_woobt_update_search_settings', array(
						$this,
						'woobt_update_search_settings'
					) );
					add_action( 'wp_ajax_woobt_get_search_results', array( $this, 'woobt_get_search_results' ) );

					// Shortcode
					add_shortcode( 'woobt', array( $this, 'woobt_shortcode' ) );
					add_shortcode( 'woobt_items', array( $this, 'woobt_shortcode' ) );

					// Product data tabs
					add_filter( 'woocommerce_product_data_tabs', array( $this, 'woobt_product_data_tabs' ), 10, 1 );

					// Product data panels
					add_action( 'woocommerce_product_data_panels', array( $this, 'woobt_product_data_panels' ) );
					add_action( 'woocommerce_process_product_meta', array( $this, 'woobt_save_option_field' ) );

					// Product price
					add_filter( 'woocommerce_product_price_class', array( $this, 'woobt_product_price_class' ) );

					// Add to cart button & form
					$woobt_position = apply_filters( 'woobt_position', get_option( '_woobt_position', apply_filters( 'woobt_default_position', 'before' ) ) );

					if ( ( get_option( '_woobt_atc_button', 'main' ) === 'main' ) && ( $woobt_position !== 'none' ) ) {
						add_action( 'woocommerce_before_add_to_cart_button', array(
							$this,
							'woobt_add_to_cart_button'
						) );
					}

					switch ( $woobt_position ) {
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
						case 'below_summary':
							add_action( 'woocommerce_after_single_product_summary', array(
								$this,
								'woobt_add_to_cart_form'
							), 9 );
							break;
						default:
							add_action( 'woobt_position_' . $woobt_position, array(
								$this,
								'woobt_add_to_cart_form'
							) );
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
					add_action( 'woocommerce_add_to_cart', array( $this, 'woobt_add_to_cart' ), 10, 3 );
					add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woobt_add_cart_item_data' ), 10, 2 );
					add_filter( 'woocommerce_get_cart_item_from_session', array(
						$this,
						'woobt_get_cart_item_from_session'
					), 10, 2 );

					// Add all to cart
					add_action( 'wp_ajax_woobt_add_all_to_cart', array( $this, 'woobt_add_all_to_cart' ) );
					add_action( 'wp_ajax_nopriv_woobt_add_all_to_cart', array( $this, 'woobt_add_all_to_cart' ) );

					// Cart contents
					add_filter( 'woocommerce_get_cart_contents', array( $this, 'woobt_get_cart_contents' ), 10, 1 );

					// Cart item
					add_filter( 'woocommerce_cart_item_name', array( $this, 'woobt_cart_item_name' ), 10, 2 );
					add_filter( 'woocommerce_cart_item_price', array( $this, 'woobt_cart_item_price' ), 10, 2 );

					add_filter( 'woocommerce_cart_item_quantity', array( $this, 'woobt_cart_item_quantity' ), 10, 3 );

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
					), 10, 2 );

					// Order again
					add_filter( 'woocommerce_order_again_cart_item_data', array(
						$this,
						'woobt_order_again_item_data'
					), 10, 2 );
					add_action( 'woocommerce_cart_loaded_from_session', array(
						$this,
						'woobt_cart_loaded_from_session'
					) );

					// Undo remove
					add_action( 'woocommerce_cart_item_restored', array( $this, 'woobt_cart_item_restored' ), 10, 2 );

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

					// WPML
					if ( function_exists( 'wpml_loaded' ) ) {
						add_filter( 'woobt_item_id', array( $this, 'woobt_wpml_item_id' ), 99 );
					}
				}

				function woobt_init() {
					self::$localization = (array) get_option( 'woobt_localization' );
					self::$types        = apply_filters( 'woobt_product_types', self::$types );
				}

				function woobt_admin_menu() {
					add_submenu_page( 'wpclever', esc_html__( 'WPC Frequently Bought Together', 'woo-bought-together' ), esc_html__( 'Bought Together', 'woo-bought-together' ), 'manage_options', 'wpclever-woobt', array(
						&$this,
						'woobt_admin_menu_content'
					) );
				}

				function woobt_admin_menu_content() {
					add_thickbox();
					$active_tab = isset( $_GET['tab'] ) ? $_GET['tab'] : 'settings';
					?>
                    <div class="wpclever_settings_page wrap">
                        <h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Frequently Bought Together', 'woo-bought-together' ) . ' ' . WOOBT_VERSION; ?></h1>
                        <div class="wpclever_settings_page_desc about-text">
                            <p>
								<?php printf( /* translators: %s is the stars */ esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'woo-bought-together' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
                                <br/>
                                <a href="<?php echo esc_url( WOOBT_REVIEWS ); ?>"
                                   target="_blank"><?php esc_html_e( 'Reviews', 'woo-bought-together' ); ?></a> | <a
                                        href="<?php echo esc_url( WOOBT_CHANGELOG ); ?>"
                                        target="_blank"><?php esc_html_e( 'Changelog', 'woo-bought-together' ); ?></a>
                                | <a href="<?php echo esc_url( WOOBT_DISCUSSION ); ?>"
                                     target="_blank"><?php esc_html_e( 'Discussion', 'woo-bought-together' ); ?></a>
                            </p>
                        </div>
                        <div class="wpclever_settings_page_nav">
                            <h2 class="nav-tab-wrapper">
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woobt&tab=how' ); ?>"
                                   class="<?php echo $active_tab === 'how' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'How to use?', 'woo-bought-together' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woobt&tab=settings' ); ?>"
                                   class="<?php echo $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'Settings', 'woo-bought-together' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woobt&tab=localization' ); ?>"
                                   class="<?php echo $active_tab === 'localization' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
									<?php esc_html_e( 'Localization', 'woo-bought-together' ); ?>
                                </a>
                                <a href="<?php echo admin_url( 'admin.php?page=wpclever-woobt&tab=premium' ); ?>"
                                   class="<?php echo $active_tab === 'premium' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>"
                                   style="color: #c9356e">
									<?php esc_html_e( 'Premium Version', 'woo-bought-together' ); ?>
                                </a>
                                <a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-kit' ) ); ?>"
                                   class="nav-tab">
									<?php esc_html_e( 'Essential Kit', 'woo-bought-together' ); ?>
                                </a>
                            </h2>
                        </div>
                        <div class="wpclever_settings_page_content">
							<?php if ( $active_tab === 'how' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>
										<?php esc_html_e( 'When adding/editing the product you can choose Bought Together tab then add some products with the new price.', 'woo-bought-together' ); ?>
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
												<?php esc_html_e( 'General', 'woo-bought-together' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Pricing method', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_pricing">
                                                    <option value="sale_price" <?php echo esc_attr( get_option( '_woobt_pricing', 'sale_price' ) === 'sale_price' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'from Sale price', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="regular_price" <?php echo esc_attr( get_option( '_woobt_pricing', 'sale_price' ) === 'regular_price' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'from Regular price ', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Calculate prices from the sale price (default) or regular price of products.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Default products', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_default">
                                                    <option value="upsells" <?php echo esc_attr( get_option( '_woobt_default', 'none' ) === 'upsells' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Upsells', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="related" <?php echo esc_attr( get_option( '_woobt_default', 'none' ) === 'related' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Related', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="related_upsells" <?php echo esc_attr( get_option( '_woobt_default', 'none' ) === 'related_upsells' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Related & Upsells', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="none" <?php echo esc_attr( get_option( '_woobt_default', 'none' ) === 'none' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'None', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Default products when don\'t specified any products.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Position', 'woo-bought-together' ); ?></th>
                                            <td>
												<?php
												$woobt_position  = apply_filters( 'woobt_position', get_option( '_woobt_position', apply_filters( 'woobt_default_position', 'before' ) ) );
												$woobt_positions = apply_filters( 'woobt_positions', array(
													'before'        => esc_html__( 'Above add to cart button', 'woo-bought-together' ),
													'after'         => esc_html__( 'Under add to cart button', 'woo-bought-together' ),
													'below_title'   => esc_html__( 'Under the title', 'woo-bought-together' ),
													'below_price'   => esc_html__( 'Under the price', 'woo-bought-together' ),
													'below_excerpt' => esc_html__( 'Under the excerpt', 'woo-bought-together' ),
													'below_meta'    => esc_html__( 'Under the meta', 'woo-bought-together' ),
													'below_summary' => esc_html__( 'Under summary', 'woo-bought-together' ),
													'none'          => esc_html__( 'None (hide it)', 'woo-bought-together' ),
												) );

												if ( is_array( $woobt_positions ) && ( count( $woobt_positions ) > 0 ) ) {
													echo '<select name="_woobt_position">';

													foreach ( $woobt_positions as $k => $p ) {
														echo '<option value="' . esc_attr( $k ) . '" ' . ( $k === $woobt_position ? 'selected' : '' ) . '>' . esc_html( $p ) . '</option>';
													}

													echo '</select>';
												}
												?>
                                                <span class="description"><?php esc_html_e( 'Choose the position to show the products list. You also can use the shortcode [woobt] to show the list where you want.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Layout', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_layout">
                                                    <option value="default" <?php echo esc_attr( get_option( '_woobt_layout', 'default' ) === 'default' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Default', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="separate" <?php echo esc_attr( get_option( '_woobt_layout', 'default' ) === 'separate' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Separate images', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Add to cart button', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_atc_button">
                                                    <option value="main" <?php echo esc_attr( get_option( '_woobt_atc_button', 'main' ) === 'main' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Main product\'s button', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="separate" <?php echo esc_attr( get_option( '_woobt_atc_button', 'main' ) === 'separate' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Separate button', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show "this item"', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_show_this_item">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_show_this_item', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_show_this_item', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Exclude unpurchasable', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_exclude_unpurchasable">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_exclude_unpurchasable', 'no' ) === 'yes' ? 'selected' : '' ); ?>><?php esc_html_e( 'Yes', 'woo-bought-together' ); ?></option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_exclude_unpurchasable', 'no' ) === 'no' ? 'selected' : '' ); ?>><?php esc_html_e( 'No', 'woo-bought-together' ); ?></option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Exclude unpurchasable products from the list.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show thumbnail', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_show_thumb">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_show_thumb', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_show_thumb', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show price', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_show_price">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_show_price', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Price', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="total" <?php echo esc_attr( get_option( '_woobt_show_price', 'yes' ) === 'total' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Total', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_show_price', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show short description', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_show_description">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_show_description', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_show_description', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Show plus/minus button', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_plus_minus">
                                                    <option value="yes" <?php echo( get_option( '_woobt_plus_minus', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo( get_option( '_woobt_plus_minus', 'no' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Show the plus/minus button for the quantity input.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Variations selector', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_variations_selector">
                                                    <option value="default" <?php echo esc_attr( get_option( '_woobt_variations_selector', 'default' ) === 'default' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Default', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="wpc_radio" <?php echo esc_attr( get_option( '_woobt_variations_selector', 'default' ) === 'wpc_radio' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Use WPC Variations Radio Buttons', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">If you choose "Use WPC Variations Radio Buttons", please install <a
                                                            href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=wpc-variations-radio-buttons&TB_iframe=true&width=800&height=550' ) ); ?>"
                                                            class="thickbox"
                                                            title="Install WPC Variations Radio Buttons">WPC Variations Radio Buttons</a> to make it work.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Link to individual product', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_link">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_link', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open in the same tab', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="yes_blank" <?php echo esc_attr( get_option( '_woobt_link', 'yes' ) === 'yes_blank' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open in the new tab', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="yes_popup" <?php echo esc_attr( get_option( '_woobt_link', 'yes' ) === 'yes_popup' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, open quick view popup', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_link', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description">If you choose "Open quick view popup", please install <a
                                                            href="<?php echo esc_url( admin_url( 'plugin-install.php?tab=plugin-information&plugin=woo-smart-quick-view&TB_iframe=true&width=800&height=550' ) ); ?>"
                                                            class="thickbox" title="Install WPC Smart Quick View">WPC Smart Quick View</a> to make it work.</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Change image', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_change_image">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_change_image', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_change_image', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Change the main product image when choosing the variation of variable products.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Change price', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_change_price">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_change_price', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="yes_custom" <?php echo esc_attr( get_option( '_woobt_change_price', 'yes' ) === 'yes_custom' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes, custom selector', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_change_price', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <input type="text" name="_woobt_change_price_custom"
                                                       value="<?php echo get_option( '_woobt_change_price_custom', '.summary > .price' ); ?>"
                                                       placeholder=".summary > .price"/>
                                                <span class="description"><?php esc_html_e( 'Change the main product price when choosing the variation or quantity of products. It uses JavaScript to change product price so it is very dependent on theme’s HTML. If it cannot find and update the product price, please contact us and we can help you find the right selector or adjust the JS file.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Counter', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_counter">
                                                    <option value="individual" <?php echo esc_attr( get_option( '_woobt_counter', 'individual' ) === 'individual' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Count the individual products', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="qty" <?php echo esc_attr( get_option( '_woobt_counter', 'individual' ) === 'qty' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Count the product quantities', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="hide" <?php echo esc_attr( get_option( '_woobt_counter', 'individual' ) === 'hide' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Hide', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Counter on the add to cart button.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Responsive', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_responsive">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_responsive', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_responsive', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Change the layout for small screen devices.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Cart & Checkout', 'woo-bought-together' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Change quantity', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <select name="_woobt_cart_quantity">
                                                    <option value="yes" <?php echo esc_attr( get_option( '_woobt_cart_quantity', 'yes' ) === 'yes' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                                    </option>
                                                    <option value="no" <?php echo esc_attr( get_option( '_woobt_cart_quantity', 'yes' ) === 'no' ? 'selected' : '' ); ?>>
														<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                                    </option>
                                                </select>
                                                <span class="description"><?php esc_html_e( 'Buyer can change the quantity of associated products or not?', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Search', 'woo-bought-together' ); ?>
                                            </th>
                                        </tr>
										<?php self::woobt_search_settings(); ?>
                                        <tr class="submit">
                                            <th colspan="2">
                                                <input type="submit" name="submit" class="button button-primary"
                                                       value="<?php esc_html_e( 'Update Options', 'woo-bought-together' ); ?>"/>
                                                <input type="hidden" name="action" value="update"/>
                                                <input type="hidden" name="page_options"
                                                       value="_woobt_pricing,_woobt_default,_woobt_layout,_woobt_position,_woobt_atc_button,_woobt_exclude_unpurchasable,_woobt_show_thumb,_woobt_show_price,_woobt_show_description,_woobt_plus_minus,_woobt_variations_selector,_woobt_link,_woobt_show_this_item,_woobt_change_image,_woobt_change_price,_woobt_change_price_custom,_woobt_counter,_woobt_responsive,_woobt_cart_quantity,_woobt_search_limit,_woobt_search_sku,_woobt_search_id,_woobt_search_exact,_woobt_search_sentence,_woobt_search_same,_woobt_search_types"/>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab === 'localization' ) { ?>
                                <form method="post" action="options.php">
									<?php wp_nonce_field( 'update-options' ) ?>
                                    <table class="form-table">
                                        <tr class="heading">
                                            <th scope="row"><?php esc_html_e( 'General', 'woo-bought-together' ); ?></th>
                                            <td>
												<?php esc_html_e( 'Leave blank to use the default text and its equivalent translation in multiple languages.', 'woo-bought-together' ); ?>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'This item', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[this_item]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'this_item' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'This item:', 'woo-bought-together' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Choose', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[choose]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'choose' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Choose', 'woo-bought-together' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Clear', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[clear]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'clear' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Clear', 'woo-bought-together' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Additional price', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[additional]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'additional' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Additional price:', 'woo-bought-together' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Total price', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[total]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'total' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Total:', 'woo-bought-together' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Associated', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[associated]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'associated' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( '(bought together %s)', 'woo-bought-together' ); ?>"/>
                                                <span class="description"><?php esc_html_e( 'The text behind associated products. Use "%s" for the main product name.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Add to cart', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[add_to_cart]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'add_to_cart' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Add to cart', 'woo-bought-together' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Add all to cart', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[add_all_to_cart]"
                                                       class="regular-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'add_all_to_cart' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Add all to cart', 'woo-bought-together' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Default above text', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[above_text]"
                                                       class="large-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'above_text' ) ); ?>"/>
                                                <span class="description"><?php esc_html_e( 'The default text above products list. You can overwrite it for each product in product settings.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Default under text', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[under_text]"
                                                       class="large-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'under_text' ) ); ?>"/>
                                                <span class="description"><?php esc_html_e( 'The default text under products list. You can overwrite it for each product in product settings.', 'woo-bought-together' ); ?></span>
                                            </td>
                                        </tr>
                                        <tr class="heading">
                                            <th colspan="2">
												<?php esc_html_e( 'Alert', 'woo-bought-together' ); ?>
                                            </th>
                                        </tr>
                                        <tr>
                                            <th><?php esc_html_e( 'Require selection', 'woo-bought-together' ); ?></th>
                                            <td>
                                                <input type="text" name="woobt_localization[alert_selection]"
                                                       class="large-text"
                                                       value="<?php echo esc_attr( self::woobt_localization( 'alert_selection' ) ); ?>"
                                                       placeholder="<?php esc_attr_e( 'Please select a purchasable variation for [name] before adding this product to the cart.', 'woo-bought-together' ); ?>"/>
                                            </td>
                                        </tr>
                                        <tr class="submit">
                                            <th colspan="2">
                                                <input type="submit" name="submit" class="button button-primary"
                                                       value="<?php esc_attr_e( 'Update Options', 'woo-bought-together' ); ?>"/>
                                                <input type="hidden" name="action" value="update"/>
                                                <input type="hidden" name="page_options" value="woobt_localization"/>
                                            </th>
                                        </tr>
                                    </table>
                                </form>
							<?php } elseif ( $active_tab == 'premium' ) { ?>
                                <div class="wpclever_settings_page_content_text">
                                    <p>Get the Premium Version just $29! <a
                                                href="https://wpclever.net/downloads/frequently-bought-together?utm_source=pro&utm_medium=woobt&utm_campaign=wporg"
                                                target="_blank">https://wpclever.net/downloads/frequently-bought-together</a>
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

				function woobt_search_settings() {
					?>
                    <tr>
                        <th><?php esc_html_e( 'Search limit', 'woo-bought-together' ); ?></th>
                        <td>
                            <input name="_woobt_search_limit" type="number" min="1"
                                   max="500"
                                   value="<?php echo get_option( '_woobt_search_limit', 10 ); ?>"/>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Search by SKU', 'woo-bought-together' ); ?></th>
                        <td>
                            <select name="_woobt_search_sku">
                                <option value="yes" <?php echo esc_attr( get_option( '_woobt_search_sku', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                </option>
                                <option value="no" <?php echo esc_attr( get_option( '_woobt_search_sku', 'no' ) === 'no' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                </option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Search by ID', 'woo-bought-together' ); ?></th>
                        <td>
                            <select name="_woobt_search_id">
                                <option value="yes" <?php echo esc_attr( get_option( '_woobt_search_id', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                </option>
                                <option value="no" <?php echo esc_attr( get_option( '_woobt_search_id', 'no' ) === 'no' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                </option>
                            </select>
                            <span class="description"><?php esc_html_e( 'Search by ID when entering the numeric only.', 'woo-bought-together' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Search exact', 'woo-bought-together' ); ?></th>
                        <td>
                            <select name="_woobt_search_exact">
                                <option value="yes" <?php echo esc_attr( get_option( '_woobt_search_exact', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                </option>
                                <option value="no" <?php echo esc_attr( get_option( '_woobt_search_exact', 'no' ) === 'no' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                </option>
                            </select>
                            <span class="description"><?php esc_html_e( 'Match whole product title or content?', 'woo-bought-together' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Search sentence', 'woo-bought-together' ); ?></th>
                        <td>
                            <select name="_woobt_search_sentence">
                                <option value="yes" <?php echo esc_attr( get_option( '_woobt_search_sentence', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                </option>
                                <option value="no" <?php echo esc_attr( get_option( '_woobt_search_sentence', 'no' ) === 'no' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                </option>
                            </select>
                            <span class="description"><?php esc_html_e( 'Do a phrase search?', 'woo-bought-together' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Accept same products', 'woo-bought-together' ); ?></th>
                        <td>
                            <select name="_woobt_search_same">
                                <option value="yes" <?php echo esc_attr( get_option( '_woobt_search_same', 'no' ) === 'yes' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'Yes', 'woo-bought-together' ); ?>
                                </option>
                                <option value="no" <?php echo esc_attr( get_option( '_woobt_search_same', 'no' ) === 'no' ? 'selected' : '' ); ?>>
									<?php esc_html_e( 'No', 'woo-bought-together' ); ?>
                                </option>
                            </select>
                            <span class="description"><?php esc_html_e( 'If yes, a product can be added many times.', 'woo-bought-together' ); ?></span>
                        </td>
                    </tr>
                    <tr>
                        <th><?php esc_html_e( 'Product types', 'woo-bought-together' ); ?></th>
                        <td>
							<?php
							$search_types  = get_option( '_woobt_search_types', array( 'all' ) );
							$product_types = wc_get_product_types();
							$product_types = array_merge( array( 'all' => esc_html__( 'All', 'woo-bought-together' ) ), $product_types );

							$key_pos = array_search( 'variable', array_keys( $product_types ) );

							if ( $key_pos !== false ) {
								$key_pos ++;
								$second_array  = array_splice( $product_types, $key_pos );
								$product_types = array_merge( $product_types, array( 'variation' => esc_html__( ' → Variation', 'woo-bought-together' ) ), $second_array );
							}

							echo '<select name="_woobt_search_types[]" multiple style="width: 200px; height: 150px;">';

							foreach ( $product_types as $key => $name ) {
								echo '<option value="' . esc_attr( $key ) . '" ' . ( in_array( $key, $search_types, true ) ? 'selected' : '' ) . '>' . esc_html( $name ) . '</option>';
							}

							echo '</select>';
							?>
                        </td>
                    </tr>
					<?php
				}

				function woobt_wp_enqueue_scripts() {
					wp_enqueue_style( 'woobt-frontend', WOOBT_URI . 'assets/css/frontend.css', array(), WOOBT_VERSION );
					wp_enqueue_script( 'woobt-frontend', WOOBT_URI . 'assets/js/frontend.js', array( 'jquery' ), WOOBT_VERSION, true );

					wp_localize_script( 'woobt-frontend', 'woobt_vars', array(
							'ajax_url'                 => admin_url( 'admin-ajax.php' ),
							'add_to_cart_button'       => get_option( '_woobt_atc_button', 'main' ),
							'position'                 => apply_filters( 'woobt_position', get_option( '_woobt_position', apply_filters( 'woobt_default_position', 'before' ) ) ),
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
							'additional_price_text'    => self::woobt_localization( 'additional', esc_html__( 'Additional price:', 'woo-bought-together' ) ),
							'total_price_text'         => self::woobt_localization( 'total', esc_html__( 'Total:', 'woo-bought-together' ) ),
							'add_to_cart'              => get_option( '_woobt_atc_button', 'main' ) === 'main' ? self::woobt_localization( 'add_to_cart', esc_html__( 'Add to cart', 'woo-bought-together' ) ) : self::woobt_localization( 'add_all_to_cart', esc_html__( 'Add all to cart', 'woo-bought-together' ) ),
							'alert_selection'          => self::woobt_localization( 'alert_selection', esc_html__( 'Please select a purchasable variation for [name] before adding this product to the cart.', 'woo-bought-together' ) ),
						)
					);
				}

				function woobt_admin_enqueue_scripts() {
					wp_enqueue_style( 'hint', WOOBT_URI . 'assets/css/hint.css' );
					wp_enqueue_style( 'woobt-backend', WOOBT_URI . 'assets/css/backend.css', array(), WOOBT_VERSION );
					wp_enqueue_script( 'woobt-backend', WOOBT_URI . 'assets/js/backend.js', array(
						'jquery',
						'jquery-ui-dialog',
						'jquery-ui-sortable'
					), WOOBT_VERSION, true );
				}

				function woobt_action_links( $links, $file ) {
					static $plugin;

					if ( ! isset( $plugin ) ) {
						$plugin = plugin_basename( __FILE__ );
					}

					if ( $plugin === $file ) {
						$settings         = '<a href="' . admin_url( 'admin.php?page=wpclever-woobt&tab=settings' ) . '">' . esc_html__( 'Settings', 'woo-bought-together' ) . '</a>';
						$links['premium'] = '<a href="' . admin_url( 'admin.php?page=wpclever-woobt&tab=premium' ) . '">' . esc_html__( 'Premium Version', 'woo-bought-together' ) . '</a>';
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
							'support' => '<a href="' . esc_url( WOOBT_DISCUSSION ) . '" target="_blank">' . esc_html__( 'Community support', 'woo-bought-together' ) . '</a>',
						);

						return array_merge( $links, $row_meta );
					}

					return (array) $links;
				}

				function woobt_display_post_states( $states, $post ) {
					if ( 'product' == get_post_type( $post->ID ) ) {
						if ( $ids = self::woobt_get_ids( $post->ID, 'edit' ) ) {
							$items = self::woobt_get_items( $ids, $post->ID, 'edit' );

							if ( ! empty( $items ) ) {
								$count    = count( $items );
								$states[] = apply_filters( 'woobt_post_states', '<span class="woobt-state">' . sprintf( /* translators: %s is the count */ esc_html__( 'Associate (%s)', 'woo-bought-together' ), $count ) . '</span>', $count, $post->ID );
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
						$associated_text = self::woobt_localization( 'associated', esc_html__( '(bought together %s)', 'woo-bought-together' ) );
						$parent_id       = apply_filters( 'woobt_item_id', $item['woobt_parent_id'] );

						if ( strpos( $item_name, '</a>' ) !== false ) {
							$name = sprintf( $associated_text, '<a href="' . get_permalink( $parent_id ) . '">' . get_the_title( $parent_id ) . '</a>' );
						} else {
							$name = sprintf( $associated_text, get_the_title( $parent_id ) );
						}

						$item_name .= ' <span class="woobt-item-name">' . apply_filters( 'woobt_item_name', $name, $item ) . '</span>';
					}

					return $item_name;
				}

				function woobt_cart_item_price( $price, $cart_item ) {
					if ( isset( $cart_item['woobt_parent_id'], $cart_item['woobt_price'], $cart_item['woobt_price_item'] ) && ( $cart_item['woobt_price_item'] !== '100%' ) && ! empty( $cart_item['woobt_price_item'] ) ) {
						return wc_price( wc_get_price_to_display( $cart_item['data'], array( 'price' => $cart_item['woobt_price'] ) ) );
					}

					return $price;
				}

				function woobt_cart_item_quantity( $quantity, $cart_item_key, $cart_item ) {
					// add qty as text - not input
					if ( isset( $cart_item['woobt_parent_id'] ) ) {
						if ( ( get_option( '_woobt_cart_quantity', 'yes' ) === 'no' ) || ( isset( $cart_item['woobt_sync_qty'] ) && $cart_item['woobt_sync_qty'] ) ) {
							return $cart_item['quantity'];
						}
					}

					return $quantity;
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
					if ( self::woobt_check_in_cart( $product_id ) ) {
						return true;
					}

					return $found_in_cart;
				}

				function woobt_add_to_cart_validation( $passed, $product_id ) {
					if ( ( get_post_meta( $product_id, 'woobt_separately', true ) !== 'on' ) && self::woobt_get_ids( $product_id ) ) {
						if ( isset( $_REQUEST['woobt_ids'] ) || isset( $_REQUEST['data']['woobt_ids'] ) ) {
							if ( isset( $_REQUEST['woobt_ids'] ) ) {
								$items = self::woobt_get_items( $_REQUEST['woobt_ids'], $product_id );
							} elseif ( isset( $_REQUEST['data']['woobt_ids'] ) ) {
								$items = self::woobt_get_items( $_REQUEST['data']['woobt_ids'], $product_id );
							}

							if ( ! empty( $items ) ) {
								foreach ( $items as $item ) {
									$item_product = wc_get_product( $item['id'] );

									if ( ! $item_product ) {
										wc_add_notice( esc_html__( 'One of the associated products is unavailable.', 'woo-bought-together' ), 'error' );
										wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woo-bought-together' ), 'error' );

										return false;
									}

									if ( $item_product->is_type( 'variable' ) ) {
										wc_add_notice( sprintf( /* translators: %s is the product name */ esc_html__( '"%s" is un-purchasable.', 'woo-bought-together' ), esc_html( $item_product->get_name() ) ), 'error' );
										wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woo-bought-together' ), 'error' );

										return false;
									}

									if ( $item_product->is_sold_individually() && self::woobt_check_in_cart( $item['id'] ) ) {
										wc_add_notice( sprintf( /* translators: %s is the product name */ esc_html__( 'You cannot add another "%s" to the cart.', 'woo-bought-together' ), esc_html( $item_product->get_name() ) ), 'error' );
										wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woo-bought-together' ), 'error' );

										return false;
									}

									if ( apply_filters( 'woobt_custom_qty', get_post_meta( $product_id, 'woobt_custom_qty', true ) === 'on', $product_id ) ) {
										if ( ( $limit_min = get_post_meta( $product_id, 'woobt_limit_each_min', true ) ) && ( $item['qty'] < (float) $limit_min ) ) {
											wc_add_notice( sprintf( /* translators: %s is the product name */ esc_html__( '"%s" does not reach the minimum quantity.', 'woo-bought-together' ), esc_html( $item_product->get_name() ) ), 'error' );
											wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woo-bought-together' ), 'error' );

											return false;
										}

										if ( ( $limit_max = get_post_meta( $product_id, 'woobt_limit_each_max', true ) ) && ( $item['qty'] > (float) $limit_max ) ) {
											wc_add_notice( sprintf( /* translators: %s is the product name */ esc_html__( '"%s" passes the maximum quantity.', 'woo-bought-together' ), esc_html( $item_product->get_name() ) ), 'error' );
											wc_add_notice( esc_html__( 'You cannot add this product to the cart.', 'woo-bought-together' ), 'error' );

											return false;
										}
									}
								}
							}
						}
					}

					return $passed;
				}

				function woobt_add_cart_item_data( $cart_item_data, $product_id ) {
					if ( ( isset( $_REQUEST['woobt_ids'] ) || isset( $_REQUEST['data']['woobt_ids'] ) ) && ( get_post_meta( $product_id, 'woobt_separately', true ) !== 'on' ) && ( self::woobt_get_ids( $product_id ) || ( get_option( '_woobt_default', 'none' ) !== 'none' ) ) ) {
						// make sure that is bought together product
						if ( isset( $_REQUEST['woobt_ids'] ) ) {
							$ids = self::woobt_clean_ids( $_REQUEST['woobt_ids'] );
						} elseif ( isset( $_REQUEST['data']['woobt_ids'] ) ) {
							$ids = self::woobt_clean_ids( $_REQUEST['data']['woobt_ids'] );
						}

						if ( ! empty( $ids ) ) {
							$cart_item_data['woobt_ids'] = $ids;
						}
					}

					return $cart_item_data;
				}

				function woobt_add_to_cart( $cart_item_key, $product_id, $quantity ) {
					if ( ( isset( $_REQUEST['woobt_ids'] ) || isset( $_REQUEST['data']['woobt_ids'] ) ) && ( self::woobt_get_ids( $product_id ) || ( get_option( '_woobt_default', 'none' ) !== 'none' ) ) ) {
						if ( isset( $_REQUEST['woobt_ids'] ) ) {
							$ids = self::woobt_clean_ids( $_REQUEST['woobt_ids'] );
							unset( $_REQUEST['woobt_ids'] );
						} elseif ( isset( $_REQUEST['data']['woobt_ids'] ) ) {
							$ids = self::woobt_clean_ids( $_REQUEST['data']['woobt_ids'] );
							unset( $_REQUEST['data']['woobt_ids'] );
						}

						if ( $items = self::woobt_get_items( $ids, $product_id ) ) {
							$custom_qty = apply_filters( 'woobt_custom_qty', get_post_meta( $product_id, 'woobt_custom_qty', true ) === 'on', $product_id );
							$sync_qty   = ! $custom_qty && apply_filters( 'woobt_sync_qty', get_post_meta( $product_id, 'woobt_sync_qty', true ) === 'on' );

							// add sync_qty for the main product
							if ( get_post_meta( $product_id, 'woobt_separately', true ) !== 'on' ) {
								WC()->cart->cart_contents[ $cart_item_key ]['woobt_ids']      = $ids;
								WC()->cart->cart_contents[ $cart_item_key ]['woobt_key']      = $cart_item_key;
								WC()->cart->cart_contents[ $cart_item_key ]['woobt_sync_qty'] = $sync_qty;
							}

							// add child products
							self::woobt_add_to_cart_items( $items, $cart_item_key, $product_id, $quantity );
						}
					}
				}

				function woobt_add_to_cart_items( $items, $cart_item_key, $product_id, $quantity ) {
					$pricing    = get_option( '_woobt_pricing', 'sale_price' );
					$custom_qty = apply_filters( 'woobt_custom_qty', get_post_meta( $product_id, 'woobt_custom_qty', true ) === 'on', $product_id );
					$sync_qty   = ! $custom_qty && apply_filters( 'woobt_sync_qty', get_post_meta( $product_id, 'woobt_sync_qty', true ) === 'on' );

					// add child products
					foreach ( $items as $item ) {
						$item_id           = $item['id'];
						$item_price        = apply_filters( 'woobt_item_price', $item['price'], $item_id, $product_id );
						$item_qty          = $item['qty'];
						$item_variation    = $item['attrs'];
						$item_variation_id = 0;
						$item_product      = wc_get_product( $item_id );

						if ( $item_product instanceof WC_Product_Variation ) {
							// ensure we don't add a variation to the cart directly by variation ID
							$item_variation_id = $item_id;
							$item_id           = $item_product->get_parent_id();

							if ( empty( $item_variation ) ) {
								$item_variation = $item_product->get_variation_attributes();
							}
						}

						if ( $item_product && $item_product->is_in_stock() && $item_product->is_purchasable() && ( 'trash' !== $item_product->get_status() ) ) {
							if ( get_post_meta( $product_id, 'woobt_separately', true ) !== 'on' ) {
								// calc new price
								if ( $pricing === 'sale_price' ) {
									// from sale price
									$item_new_price = self::woobt_new_price( $item_product->get_price(), $item_price );
								} else {
									// from regular price
									$item_new_price = self::woobt_new_price( $item_product->get_regular_price(), $item_price );
								}

								// add to cart
								$item_key = WC()->cart->add_to_cart( $item_id, $item_qty, $item_variation_id, $item_variation, array(
									'woobt_parent_id'  => $product_id,
									'woobt_parent_key' => $cart_item_key,
									'woobt_qty'        => $item_qty,
									'woobt_sync_qty'   => $sync_qty,
									'woobt_price_item' => $item_price,
									'woobt_price'      => $item_new_price
								) );

								if ( $item_key ) {
									WC()->cart->cart_contents[ $item_key ]['woobt_key']         = $item_key;
									WC()->cart->cart_contents[ $cart_item_key ]['woobt_keys'][] = $item_key;
								}
							} else {
								if ( $sync_qty ) {
									WC()->cart->add_to_cart( $item_id, $item_qty * $quantity, $item_variation_id, $item_variation );
								} else {
									WC()->cart->add_to_cart( $item_id, $item_qty, $item_variation_id, $item_variation );
								}
							}
						}
					}
				}

				function woobt_add_all_to_cart() {
					ob_start();

					if ( ! isset( $_POST['product_id'] ) ) {
						return;
					}

					$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
					$product           = wc_get_product( $product_id );
					$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
					$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
					$product_status    = get_post_status( $product_id );
					$variation_id      = $_POST['variation_id'];
					$variation         = $_POST['variation'];

					if ( $product && 'variation' === $product->get_type() ) {
						$variation_id = $product_id;
						$product_id   = $product->get_parent_id();

						if ( empty( $variation ) ) {
							$variation = $product->get_variation_attributes();
						}
					}

					if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) && 'publish' === $product_status ) {
						do_action( 'woocommerce_ajax_added_to_cart', $product_id );

						if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
							wc_add_to_cart_message( array( $product_id => $quantity ), true );
						}

						WC_AJAX::get_refreshed_fragments();
					} else {
						$data = array(
							'error'       => true,
							'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
						);

						wp_send_json( $data );
					}

					die();
				}

				function woobt_get_cart_contents( $cart_contents ) {
					foreach ( $cart_contents as $cart_item_key => $cart_item ) {
						if ( isset( $cart_item['woobt_parent_id'], $cart_item['woobt_price'], $cart_item['woobt_price_item'] ) && ( $cart_item['woobt_price_item'] !== '100%' ) && ! empty( $cart_item['woobt_price_item'] ) ) {
							$cart_item['data']->set_price( $cart_item['woobt_price'] );
						}

						// sync quantity
						if ( ! empty( $cart_item['woobt_parent_key'] ) && ! empty( $cart_item['woobt_qty'] ) && ! empty( $cart_item['woobt_sync_qty'] ) ) {
							$parent_key = $cart_item['woobt_parent_key'];

							if ( isset( $cart_contents[ $parent_key ] ) ) {
								$cart_contents[ $cart_item_key ]['quantity'] = $cart_item['woobt_qty'] * $cart_contents[ $parent_key ]['quantity'];
							} elseif ( ( $parent_new_key = array_search( $parent_key, array_combine( array_keys( $cart_contents ), array_column( $cart_contents, 'woobt_key' ) ) ) ) ) {
								$cart_contents[ $cart_item_key ]['quantity'] = $cart_item['woobt_qty'] * $cart_contents[ $parent_new_key ]['quantity'];
							}
						}

						if ( ! empty( $cart_item['woobt_ids'] ) && ( $discount = get_post_meta( $cart_item['product_id'], 'woobt_discount', true ) ) && ( get_post_meta( $cart_item['product_id'], 'woobt_separately', true ) !== 'on' ) ) {
							if ( $cart_item['variation_id'] > 0 ) {
								$item_product = wc_get_product( $cart_item['variation_id'] );
							} else {
								$item_product = wc_get_product( $cart_item['product_id'] );
							}

							$ori_price = $item_product->get_price();

							// has associated products
							$has_associated = false;

							if ( isset( $cart_item['woobt_keys'] ) ) {
								foreach ( $cart_item['woobt_keys'] as $key ) {
									if ( isset( $cart_contents[ $key ] ) ) {
										$has_associated = true;
										break;
									}
								}
							}

							if ( $has_associated && ! empty( $discount ) ) {
								$discount_price = $ori_price * ( 100 - (float) $discount ) / 100;
								$cart_item['data']->set_price( $discount_price );
							}
						}
					}

					return $cart_contents;
				}

				function woobt_get_cart_item_from_session( $cart_item, $item_session_values ) {
					if ( isset( $item_session_values['woobt_ids'] ) && ! empty( $item_session_values['woobt_ids'] ) ) {
						$cart_item['woobt_ids']      = $item_session_values['woobt_ids'];
						$cart_item['woobt_sync_qty'] = $item_session_values['woobt_sync_qty'];
					}

					if ( isset( $item_session_values['woobt_parent_id'] ) ) {
						$cart_item['woobt_parent_id']  = $item_session_values['woobt_parent_id'];
						$cart_item['woobt_parent_key'] = $item_session_values['woobt_parent_key'];
						$cart_item['woobt_price']      = $item_session_values['woobt_price'];
						$cart_item['woobt_price_item'] = $item_session_values['woobt_price_item'];
						$cart_item['woobt_qty']        = $item_session_values['woobt_qty'];
						$cart_item['woobt_sync_qty']   = $item_session_values['woobt_sync_qty'];
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

				function woobt_before_order_item_meta( $item_id, $item ) {
					if ( $parent_id = $item->get_meta( '_woobt_parent_id' ) ) {
						echo sprintf( self::woobt_localization( 'associated', esc_html__( '(bought together %s)', 'woo-bought-together' ) ), get_the_title( $parent_id ) );
					}
				}

				function woobt_order_again_item_data( $data, $item ) {
					if ( $woobt_ids = $item->get_meta( '_woobt_ids' ) ) {
						$data['woobt_order_again'] = 'yes';
						$data['woobt_ids']         = $woobt_ids;
					}

					if ( $woobt_parent_id = $item->get_meta( '_woobt_parent_id' ) ) {
						$data['woobt_order_again'] = 'yes';
						$data['woobt_parent_id']   = $woobt_parent_id;
					}

					return $data;
				}

				function woobt_cart_loaded_from_session( $cart ) {
					foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
						// remove associated products first
						if ( isset( $cart_item['woobt_order_again'], $cart_item['woobt_parent_id'] ) ) {
							$cart->remove_cart_item( $cart_item_key );
						}
					}

					foreach ( $cart->cart_contents as $cart_item_key => $cart_item ) {
						// add associated products again
						if ( isset( $cart_item['woobt_order_again'], $cart_item['woobt_ids'] ) ) {
							unset( $cart->cart_contents[ $cart_item_key ]['woobt_order_again'] );

							$product_id = $cart_item['product_id'];
							$custom_qty = apply_filters( 'woobt_custom_qty', get_post_meta( $product_id, 'woobt_custom_qty', true ) === 'on', $product_id );
							$sync_qty   = ! $custom_qty && apply_filters( 'woobt_sync_qty', get_post_meta( $product_id, 'woobt_sync_qty', true ) === 'on' );

							$cart->cart_contents[ $cart_item_key ]['woobt_key']      = $cart_item_key;
							$cart->cart_contents[ $cart_item_key ]['woobt_sync_qty'] = $sync_qty;

							if ( $items = self::woobt_get_items( $cart_item['woobt_ids'], $cart_item['product_id'] ) ) {
								self::woobt_add_to_cart_items( $items, $cart_item_key, $cart_item['product_id'], $cart_item['quantity'] );
							}
						}
					}
				}

				function woobt_cart_item_restored( $cart_item_key, $cart ) {
					if ( isset( $cart->cart_contents[ $cart_item_key ]['woobt_ids'] ) ) {
						// remove old keys
						unset( $cart->cart_contents[ $cart_item_key ]['woobt_keys'] );

						$ids        = $cart->cart_contents[ $cart_item_key ]['woobt_ids'];
						$product_id = $cart->cart_contents[ $cart_item_key ]['product_id'];
						$quantity   = $cart->cart_contents[ $cart_item_key ]['quantity'];

						if ( get_post_meta( $product_id, 'woobt_separately', true ) !== 'on' ) {
							if ( $items = self::woobt_get_items( $ids, $product_id ) ) {
								self::woobt_add_to_cart_items( $items, $cart_item_key, $product_id, $quantity );
							}
						}
					}
				}

				function woobt_update_search_settings() {
					update_option( '_woobt_search_limit', (int) sanitize_text_field( $_POST['limit'] ) );
					update_option( '_woobt_search_sku', sanitize_text_field( $_POST['sku'] ) );
					update_option( '_woobt_search_id', sanitize_text_field( $_POST['id'] ) );
					update_option( '_woobt_search_exact', sanitize_text_field( $_POST['exact'] ) );
					update_option( '_woobt_search_sentence', sanitize_text_field( $_POST['sentence'] ) );
					update_option( '_woobt_search_same', sanitize_text_field( $_POST['same'] ) );
					update_option( '_woobt_search_types', (array) $_POST['types'] );

					die();
				}

				function woobt_get_search_results() {
					$types          = get_option( '_woobt_search_types', array( 'all' ) );
					$keyword        = esc_html( $_POST['woobt_keyword'] );
					$id             = absint( $_POST['woobt_id'] );
					$ids            = self::woobt_clean_ids( $_POST['woobt_ids'] );
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
							'posts_per_page' => get_option( '_woobt_search_limit', 10 )
						);

						if ( ! empty( $types ) && ! in_array( 'all', $types, true ) ) {
							$product_types = $types;

							if ( in_array( 'variation', $types, true ) ) {
								$product_types[] = 'variable';
							}

							$query_args['tax_query'] = array(
								array(
									'taxonomy' => 'product_type',
									'field'    => 'slug',
									'terms'    => $product_types,
								),
							);
						}

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

							if ( ! $product->is_type( 'variable' ) || in_array( 'variable', $types, true ) || in_array( 'all', $types, true ) ) {
								self::woobt_product_data_li( $product, '100%', 1, true );
							}

							if ( $product->is_type( 'variable' ) && ( empty( $types ) || in_array( 'all', $types, true ) || in_array( 'variation', $types, true ) ) ) {
								// show all children
								$children = $product->get_children();

								if ( is_array( $children ) && count( $children ) > 0 ) {
									foreach ( $children as $child ) {
										$product_child = wc_get_product( $child );

										if ( $product_child ) {
											self::woobt_product_data_li( $product_child, '100%', 1, true );
										}
									}
								}
							}
						}

						echo '</ul>';
						wp_reset_postdata();
					} else {
						echo '<ul><span>' . sprintf( /* translators: %s is the keyword */ esc_html__( 'No results found for "%s"', 'woo-bought-together' ), esc_html( $keyword ) ) . '</span></ul>';
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
						$remove_btn = '<span class="remove hint--left" aria-label="' . esc_html__( 'Add', 'woo-bought-together' ) . '">+</span>';
					} else {
						$remove_btn = '<span class="remove hint--left" aria-label="' . esc_html__( 'Remove', 'woo-bought-together' ) . '">×</span>';
					}

					echo '<li class="' . esc_attr( trim( $product_class ) ) . '" data-id="' . $product->get_id() . '"><span class="move"></span><span class="price hint--right" aria-label="' . esc_html__( 'Set a new price using a number (eg. "49") or percentage (eg. "90%" of original price)', 'woo-bought-together' ) . '"><input type="text" value="' . $price . '"/></span><span class="qty hint--right" aria-label="' . esc_html__( 'Default quantity', 'woo-bought-together' ) . '"><input type="number" value="' . esc_attr( $qty ) . '" step="' . esc_attr( $step ) . '"/></span> <span class="data">' . ( $product->get_status() === 'private' ? '<span class="info">private</span> ' : '' ) . '<span class="name">' . strip_tags( $product->get_name() ) . '</span> <span class="info">' . $product->get_price_html() . '</span></span> <span class="type"><a href="' . get_edit_post_link( $product_id ) . '" target="_blank">' . $product->get_type() . '<br/>#' . $product->get_id() . '</a></span> ' . $remove_btn . '</li>';
				}

				function woobt_product_data_tabs( $tabs ) {
					$tabs['woobt'] = array(
						'label'  => esc_html__( 'Bought Together', 'woo-bought-together' ),
						'target' => 'woobt_settings',
					);

					return $tabs;
				}

				function woobt_product_data_panels() {
					global $post;
					$post_id = $post->ID;
					?>
                    <div id='woobt_settings' class='panel woocommerce_options_panel woobt_table'>
                        <div id="woobt_search_settings" style="display: none"
                             data-title="<?php esc_html_e( 'Search settings', 'woo-bought-together' ); ?>">
                            <table>
								<?php self::woobt_search_settings(); ?>
                                <tr>
                                    <th></th>
                                    <td>
                                        <button id="woobt_search_settings_update" class="button button-primary">
											<?php esc_html_e( 'Update Options', 'woo-bought-together' ); ?>
                                        </button>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <table>
                            <tr>
                                <th><?php esc_html_e( 'Search', 'woo-bought-together' ); ?> (<a
                                            href="<?php echo admin_url( 'admin.php?page=wpclever-woobt&tab=settings#search' ); ?>"
                                            id="woobt_search_settings_btn"><?php esc_html_e( 'settings', 'woo-bought-together' ); ?></a>)
                                </th>
                                <td>
                                    <div class="w100">
								<span class="loading"
                                      id="woobt_loading"
                                      style="display: none"><?php esc_html_e( 'searching...', 'woo-bought-together' ); ?></span>
                                        <input type="search" id="woobt_keyword"
                                               placeholder="<?php esc_attr_e( 'Type any keyword to search', 'woo-bought-together' ); ?>"/>
                                        <div id="woobt_results" class="woobt_results" style="display: none"></div>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Selected', 'woo-bought-together' ); ?></th>
                                <td>
                                    <div class="w100">
                                        <input type="hidden" id="woobt_id" name="woobt_id"
                                               value="<?php echo esc_attr( $post_id ); ?>"/>
                                        <input type="hidden" id="woobt_ids" name="woobt_ids"
                                               value="<?php echo self::woobt_get_ids( $post_id, 'edit' ); ?>"
                                               readonly/>
                                        <div id="woobt_selected" class="woobt_selected">
                                            <ul>
												<?php
												echo '<li class="woobt_default">' . sprintf( esc_html__( '* If don\'t choose any products, it can shows the default products %s.', 'woo-bought-together' ), '<a
                                                    href="' . admin_url( 'admin.php?page=wpclever-woobt&tab=settings#search' ) . '" target="_blank">' . esc_html__( 'here', 'woo-bought-together' ) . '</a>' ) . '</li>';

												if ( $ids = self::woobt_get_ids( $post_id, 'edit' ) ) {
													if ( $items = self::woobt_get_items( $ids, $post_id, 'edit' ) ) {
														foreach ( $items as $item ) {
															$item_id      = $item['id'];
															$item_price   = $item['price'];
															$item_qty     = $item['qty'];
															$item_product = wc_get_product( $item_id );

															if ( ! $item_product ) {
																continue;
															}

															self::woobt_product_data_li( $item_product, $item_price, $item_qty, false );
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
                                <th><?php esc_html_e( 'Add separately', 'woo-bought-together' ); ?></th>
                                <td>
                                    <input id="woobt_separately" name="woobt_separately"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_separately', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <span class="woocommerce-help-tip"
                                          data-tip="<?php esc_attr_e( 'If enabled, the associated products will be added as separate items and stay unaffected from the main product, their prices will change back to the original.', 'woo-bought-together' ); ?>"></span>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Selecting method', 'woo-bought-together' ); ?></th>
                                <td>
                                    <select name="woobt_selection">
                                        <option value="multiple" <?php echo( get_post_meta( $post_id, 'woobt_selection', true ) === 'multiple' ? 'selected' : '' ); ?>><?php esc_html_e( 'Multiple selection (default)', 'woo-bought-together' ); ?></option>
                                        <option value="single" <?php echo( get_post_meta( $post_id, 'woobt_selection', true ) === 'single' ? 'selected' : '' ); ?>><?php esc_html_e( 'Single selection (choose 1 only)', 'woo-bought-together' ); ?></option>
                                    </select>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Discount', 'woo-bought-together' ); ?></th>
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
                                <th><?php esc_html_e( 'Checked all', 'woo-bought-together' ); ?></th>
                                <td>
                                    <input id="woobt_checked_all" name="woobt_checked_all"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_checked_all', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woobt_checked_all"><?php esc_html_e( 'Checked all by default.', 'woo-bought-together' ); ?></label>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Custom quantity', 'woo-bought-together' ); ?></th>
                                <td>
                                    <input id="woobt_custom_qty" name="woobt_custom_qty"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_custom_qty', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woobt_custom_qty"><?php esc_html_e( 'Allow the customer can change the quantity of each product.', 'woo-bought-together' ); ?></label>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space woobt_tr_hide_if_custom_qty">
                                <th><?php esc_html_e( 'Sync quantity', 'woo-bought-together' ); ?></th>
                                <td>
                                    <input id="woobt_sync_qty" name="woobt_sync_qty"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_sync_qty', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woobt_sync_qty"><?php esc_html_e( 'Sync the quantity of the main product with associated products.', 'woo-bought-together' ); ?></label>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space woobt_tr_show_if_custom_qty">
                                <th><?php esc_html_e( 'Limit each item', 'woo-bought-together' ); ?></th>
                                <td>
                                    <input id="woobt_limit_each_min_default" name="woobt_limit_each_min_default"
                                           type="checkbox" <?php echo( get_post_meta( $post_id, 'woobt_limit_each_min_default', true ) === 'on' ? 'checked' : '' ); ?>/>
                                    <label for="woobt_limit_each_min_default"><?php esc_html_e( 'Use default quantity as min', 'woo-bought-together' ); ?></label>
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
                                <th><?php esc_html_e( 'Above text', 'woo-bought-together' ); ?></th>
                                <td>
                                    <div class="w100">
                                        <textarea
                                                name="woobt_before_text" rows="1"
                                                style="width: 100%"><?php echo stripslashes( get_post_meta( $post_id, 'woobt_before_text', true ) ); ?></textarea>
                                    </div>
                                </td>
                            </tr>
                            <tr class="woobt_tr_space">
                                <th><?php esc_html_e( 'Under text', 'woo-bought-together' ); ?></th>
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
						$ids = self::woobt_clean_ids( sanitize_text_field( $_POST['woobt_ids'] ) );
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

					if ( isset( $_POST['woobt_selection'] ) ) {
						update_post_meta( $post_id, 'woobt_selection', sanitize_text_field( $_POST['woobt_selection'] ) );
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

				function woobt_product_price_class( $class ) {
					global $product;

					return $class . ' woobt-price-' . $product->get_id();
				}

				function woobt_add_to_cart_form( $custom_position = false ) {
					global $product;

					if ( ! $product || $product->is_type( 'grouped' ) ) {
						return;
					}

					self::woobt_show_items( null, $custom_position );
				}

				function woobt_add_to_cart_button() {
					global $product;

					if ( ! $product->is_type( 'grouped' ) ) {
						echo '<input name="woobt_ids" class="woobt-ids woobt-ids-' . esc_attr( $product->get_id() ) . '" data-id="' . esc_attr( $product->get_id() ) . '" type="hidden"/>';
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

				function woobt_shortcode( $atts ) {
					$atts = shortcode_atts( array( 'id' => null, 'custom_position' => true ), $atts );

					ob_start();
					self::woobt_show_items( $atts['id'], $atts['custom_position'] );
					$items = ob_get_clean();

					return $items;
				}

				function woobt_show_items( $product_id = null, $is_custom_position = false ) {
					if ( ! $product_id ) {
						global $product;

						if ( $product ) {
							$product_id = $product->get_id();
						}
					} else {
						$product = wc_get_product( $product_id );
					}

					if ( ! $product_id || ! $product ) {
						return;
					}

					wp_enqueue_script( 'wc-add-to-cart-variation' );

					$items       = array();
					$pricing     = get_option( '_woobt_pricing', 'sale_price' );
					$custom_qty  = apply_filters( 'woobt_custom_qty', get_post_meta( $product_id, 'woobt_custom_qty', true ) === 'on', $product_id );
					$sync_qty    = apply_filters( 'woobt_sync_qty', get_post_meta( $product_id, 'woobt_sync_qty', true ) === 'on', $product_id );
					$checked_all = apply_filters( 'woobt_checked_all', get_post_meta( $product_id, 'woobt_checked_all', true ) === 'on', $product_id );
					$separately  = apply_filters( 'woobt_separately', get_post_meta( $product_id, 'woobt_separately', true ) === 'on', $product_id );
					$selection   = apply_filters( 'woobt_selection', get_post_meta( $product_id, 'woobt_selection', true ) ?: 'multiple', $product_id );
					$order       = 1;

					if ( $ids = self::woobt_get_ids( $product_id ) ) {
						$items = self::woobt_get_items( $ids, $product_id );
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

					// filter items before showing
					$items = apply_filters( 'woobt_show_items', $items );

					$layout             = get_option( '_woobt_layout', 'default' );
					$is_separate_layout = $layout === 'separate';
					$is_separate_atc    = get_option( '_woobt_atc_button', 'main' ) === 'separate';

					$wrap_class = 'woobt-wrap woobt-layout-' . esc_attr( $layout ) . ' woobt-wrap-' . esc_attr( $product_id ) . ' ' . ( get_option( '_woobt_responsive', 'yes' ) === 'yes' ? 'woobt-wrap-responsive' : '' );

					if ( $is_custom_position ) {
						$wrap_class .= ' woobt-wrap-custom-position';
					}

					if ( $is_separate_atc ) {
						$wrap_class .= ' woobt-wrap-separate-atc';
					}

					if ( $items && ! empty( $items ) ) {
						foreach ( $items as $key => $item ) {
							if ( is_array( $item ) ) {
								$_item['id']      = $item['id'];
								$_item['price']   = $item['price'];
								$_item['qty']     = $item['qty'];
								$_item['product'] = wc_get_product( $_item['id'] );
							} else {
								// make it works with upsells & related
								$_item['id']      = absint( $item );
								$_item['price']   = '100%';
								$_item['qty']     = 1;
								$_item['product'] = wc_get_product( $_item['id'] );
							}

							if ( ! $_item['product'] || ! in_array( $_item['product']->get_type(), self::$types, true ) || ( ( get_option( '_woobt_exclude_unpurchasable', 'no' ) === 'yes' ) && ( ! $_item['product']->is_purchasable() || ! $_item['product']->is_in_stock() ) ) ) {
								unset( $items[ $key ] );
								continue;
							}

							$items[ $key ] = $_item;
						}

						echo '<div class="' . esc_attr( $wrap_class ) . '" data-id="' . esc_attr( $product_id ) . '" data-selection="' . esc_attr( $selection ) . '">';

						do_action( 'woobt_wrap_before', $product );

						if ( $before_text = apply_filters( 'woobt_before_text', get_post_meta( $product_id, 'woobt_before_text', true ) ?: self::woobt_localization( 'above_text' ), $product_id ) ) {
							echo '<div class="woobt-before-text woobt-text">' . do_shortcode( stripslashes( $before_text ) ) . '</div>';
						}

						if ( $is_separate_layout ) {
							?>
                            <div class="woobt-images">
								<?php
								echo ' <div class="woobt-image woobt-image-this woobt-image-' . esc_attr( $product_id ) . '">' . $product->get_image() . '</div>';

								foreach ( $items as $item ) {
									$item_product = $item['product'];

									echo ' <div class="woobt-image woobt-image-' . esc_attr( $item['id'] ) . '"><span>+</span>';

									if ( get_option( '_woobt_link', 'yes' ) !== 'no' ) {
										echo '<a ' . ( get_option( '_woobt_link', 'yes' ) === 'yes_popup' ? 'class="woosq-link" data-id="' . esc_attr( $item['id'] ) . '" data-context="woobt"' : '' ) . ' href="' . $item_product->get_permalink() . '" ' . ( get_option( '_woobt_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>' . $item_product->get_image() . '</a>';
									} else {
										echo $item_product->get_image();
									}

									echo '</div>';
								}
								?>
                            </div>
							<?php
						}
						?>
                        <div class="woobt-products woobt-products-<?php echo esc_attr( $product_id ); ?>"
                             data-show-price="<?php echo esc_attr( get_option( '_woobt_show_price', 'yes' ) ); ?>"
                             data-optional="<?php echo esc_attr( $custom_qty ? 'on' : 'off' ); ?>"
                             data-sync-qty="<?php echo esc_attr( $sync_qty ? 'on' : 'off' ); ?>"
                             data-variables="<?php echo esc_attr( self::woobt_has_variables( $items ) ? 'yes' : 'no' ); ?>"
                             data-product-id="<?php echo esc_attr( $product->is_type( 'variable' ) ? '0' : $product_id ); ?>"
                             data-product-type="<?php echo esc_attr( $product->get_type() ); ?>"
                             data-product-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
                             data-product-sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                             data-product-o-sku="<?php echo esc_attr( $product->get_sku() ); ?>"
                             data-product-price-html="<?php echo esc_attr( htmlentities( $product->get_price_html() ) ); ?>"
                             data-pricing="<?php echo esc_attr( $pricing ); ?>"
                             data-discount="<?php echo esc_attr( ! $separately && get_post_meta( $product_id, 'woobt_discount', true ) ? get_post_meta( $product_id, 'woobt_discount', true ) : '0' ); ?>">
							<?php
							// this item
							if ( $is_custom_position || $is_separate_atc || get_option( '_woobt_show_this_item', 'yes' ) !== 'no' ) {
								?>
                                <div class="woobt-product woobt-product-this"
                                     data-id="<?php echo esc_attr( $product->is_type( 'variable' ) || ! $product->is_in_stock() ? 0 : $product_id ); ?>"
                                     data-pid="<?php echo esc_attr( $product_id ); ?>"
                                     data-name="<?php echo esc_attr( $product->get_name() ); ?>"
                                     data-new-price="<?php echo esc_attr( ! $separately && ( $discount = get_post_meta( $product_id, 'woobt_discount', true ) ) ? ( 100 - (float) $discount ) . '%' : '100%' ); ?>"
                                     data-price-suffix="<?php echo esc_attr( htmlentities( $product->get_price_suffix() ) ); ?>"
                                     data-price="<?php echo esc_attr( wc_get_price_to_display( $product ) ); ?>"
                                     data-regular-price="<?php echo esc_attr( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ); ?>"
                                     data-qty="1"
                                     data-qty-ori="1">
									<?php do_action( 'woobt_product_before', $product ); ?>

                                    <div class="woobt-choose">
                                        <input class="woobt-checkbox woobt-checkbox-this" type="checkbox" checked
                                               disabled/>
                                        <span class="checkmark"></span>
                                    </div>

									<?php if ( get_option( '_woobt_show_thumb', 'yes' ) !== 'no' ) { ?>
                                        <div class="woobt-thumb">
											<?php echo $product->get_image(); ?>
                                        </div>
									<?php } ?>

                                    <div class="woobt-title">
                                        <span class="woobt-title-inner">
											<?php echo '<span>' . self::woobt_localization( 'this_item', esc_html__( 'This item:', 'woo-bought-together' ) ) . '</span> <span>' . $product->get_name() . '</span>'; ?>
                                        </span>

										<?php if ( $is_separate_layout && ( get_option( '_woobt_show_price', 'yes' ) !== 'no' ) ) { ?>
                                            <span class="woobt-price">
                                                <span class="woobt-price-new">
                                                    <?php
                                                    if ( ! $separately && ( $discount = get_post_meta( $product_id, 'woobt_discount', true ) ) ) {
	                                                    $sale_price = $product->get_price() * ( 100 - (float) $discount ) / 100;
	                                                    echo wc_format_sale_price( $product->get_price(), $sale_price ) . $product->get_price_suffix( $sale_price );
                                                    } else {
	                                                    echo $product->get_price_html();
                                                    }
                                                    ?>
                                                </span>
                                                <span class="woobt-price-ori">
                                                    <?php echo $product->get_price_html(); ?>
                                                </span>
                                            </span>
										<?php } ?>

										<?php
										if ( $product->is_type( 'variable' ) ) {
											if ( ( get_option( '_woobt_variations_selector', 'default' ) === 'wpc_radio' ) && class_exists( 'WPClever_Woovr' ) ) {
												echo '<div class="wpc_variations_form">';
												// use class name wpc_variations_form to prevent found_variation in woovr
												WPClever_Woovr::woovr_variations_form( $product );
												echo '</div>';
											} else {
												$attributes           = $product->get_variation_attributes();
												$available_variations = $product->get_available_variations();

												if ( is_array( $attributes ) && ( count( $attributes ) > 0 ) ) {
													echo '<div class="variations_form" data-product_id="' . absint( $product_id ) . '" data-product_variations="' . htmlspecialchars( wp_json_encode( $available_variations ) ) . '">';
													echo '<div class="variations">';

													foreach ( $attributes as $attribute_name => $options ) { ?>
                                                        <div class="variation">
                                                            <div class="label">
																<?php echo wc_attribute_label( $attribute_name ); ?>
                                                            </div>
                                                            <div class="select">
																<?php
																$selected = isset( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ? wc_clean( stripslashes( urldecode( $_REQUEST[ 'attribute_' . sanitize_title( $attribute_name ) ] ) ) ) : $product->get_variation_default_attribute( $attribute_name );
																wc_dropdown_variation_attribute_options( array(
																	'options'          => $options,
																	'attribute'        => $attribute_name,
																	'product'          => $product,
																	'selected'         => $selected,
																	'show_option_none' => self::woobt_localization( 'choose', esc_html__( 'Choose', 'woo-bought-together' ) ) . ' ' . wc_attribute_label( $attribute_name )
																) );
																?>
                                                            </div>
                                                        </div>
													<?php }

													echo '<div class="reset">' . apply_filters( 'woocommerce_reset_variations_link', '<a class="reset_variations" href="#">' . self::woobt_localization( 'clear', esc_html__( 'Clear', 'woo-bought-together' ) ) . '</a>' ) . '</div>';
													echo '</div>';
													echo '</div>';

													if ( get_option( '_woobt_show_description', 'no' ) === 'yes' ) {
														echo '<div class="woobt-variation-description"></div>';
													}
												}
											}
										}

										echo '<div class="woobt-availability">' . wc_get_stock_html( $product ) . '</div>';
										?>
                                    </div>

									<?php if ( $custom_qty ) { ?>
                                        <div class="woobt-quantity">
											<?php
											if ( get_option( '_woobt_plus_minus', 'no' ) === 'yes' ) {
												echo '<div class="woobt-quantity-input">';
												echo '<div class="woobt-quantity-input-minus">-</div>';
											}

											woocommerce_quantity_input( array(
												'input_name' => 'woobt_qty_0',
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
												echo '</div>';
											}
											?>
                                        </div>
									<?php }

									if ( ! $is_separate_layout && ( get_option( '_woobt_show_price', 'yes' ) !== 'no' ) ) { ?>
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
							} else {
								?>
                                <div class="woobt-product woobt-product-this woobt-hide-this"
                                     data-id="<?php echo esc_attr( $product->is_type( 'variable' ) || ! $product->is_in_stock() ? 0 : $product_id ); ?>"
                                     data-pid="<?php echo esc_attr( $product_id ); ?>"
                                     data-name="<?php echo esc_attr( $product->get_name() ); ?>"
                                     data-price="<?php echo esc_attr( wc_get_price_to_display( $product ) ); ?>"
                                     data-regular-price="<?php echo esc_attr( wc_get_price_to_display( $product, array( 'price' => $product->get_regular_price() ) ) ); ?>"
                                     data-qty="1" style="display:none;">
                                    <div class="woobt-choose">
                                        <input class="woobt-checkbox woobt-checkbox-this" type="checkbox" checked
                                               disabled/>
                                        <span class="checkmark"></span>
                                    </div>
                                </div>
								<?php
							}

							// other items
							foreach ( $items as $item ) {
								$item_id      = $item['id'];
								$item_price   = $item['price'];
								$item_qty     = $item['qty'];
								$item_product = $item['product'];
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
                                     data-pid="<?php echo esc_attr( $item_id ); ?>"
                                     data-name="<?php echo esc_attr( $item_product->get_name() ); ?>"
                                     data-new-price="<?php echo esc_attr( $item_price ); ?>"
                                     data-price-suffix="<?php echo esc_attr( htmlentities( $item_product->get_price_suffix() ) ); ?>"
                                     data-price="<?php echo esc_attr( ( $pricing === 'sale_price' ) ? wc_get_price_to_display( $item_product ) : wc_get_price_to_display( $item_product, array( 'price' => $item_product->get_regular_price() ) ) ); ?>"
                                     data-regular-price="<?php echo esc_attr( wc_get_price_to_display( $item_product, array( 'price' => $item_product->get_regular_price() ) ) ); ?>"
                                     data-qty="<?php echo esc_attr( $item_qty ); ?>"
                                     data-qty-ori="<?php echo esc_attr( $item_qty ); ?>">

									<?php do_action( 'woobt_product_before', $item_product, $order ); ?>

                                    <div class="woobt-choose">
                                        <input class="woobt-checkbox" type="checkbox"
                                               value="<?php echo esc_attr( $item_id ); ?>" <?php echo( ! $item_product->is_in_stock() ? 'disabled' : '' ); ?> <?php echo( $item_product->is_in_stock() && ( $checked_all || $checked_individual ) ? 'checked' : '' ); ?>/>
                                        <span class="checkmark"></span>
                                    </div>

									<?php if ( get_option( '_woobt_show_thumb', 'yes' ) !== 'no' ) {
										echo '<div class="woobt-thumb">';

										if ( get_option( '_woobt_link', 'yes' ) !== 'no' ) {
											echo '<a ' . ( get_option( '_woobt_link', 'yes' ) === 'yes_popup' ? 'class="woosq-link" data-id="' . $item_id . '" data-context="woobt"' : '' ) . ' href="' . $item_product->get_permalink() . '" ' . ( get_option( '_woobt_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>';
										}
										?>
                                        <div class="woobt-thumb-ori">
											<?php echo $item_product->get_image(); ?>
                                        </div>
                                        <div class="woobt-thumb-new"></div>
										<?php
										if ( get_option( '_woobt_link', 'yes' ) !== 'no' ) {
											echo '</a>';
										}

										echo '</div>';
									} ?>

                                    <div class="woobt-title">
                                        <span class="woobt-title-inner">
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
	                                            $item_product_name = '<a ' . ( get_option( '_woobt_link', 'yes' ) === 'yes_popup' ? 'class="woosq-link" data-id="' . $item_id . '" data-context="woobt"' : '' ) . ' href="' . $item_product->get_permalink() . '" ' . ( get_option( '_woobt_link', 'yes' ) === 'yes_blank' ? 'target="_blank"' : '' ) . '>' . $item_product_name . '</a>';
                                            } else {
	                                            $item_product_name = '<span>' . $item_product_name . '</span>';
                                            }

                                            echo apply_filters( 'woobt_product_name', $item_product_name, $item_product );
                                            ?>
                                        </span>

										<?php
										if ( $is_separate_layout && ( get_option( '_woobt_show_price', 'yes' ) !== 'no' ) ) {
											?>
                                            <span class="woobt-price">
                                                <span class="woobt-price-new"></span>
                                                <span class="woobt-price-ori">
													<?php
													if ( ! $separately && ( $item_price !== '100%' ) ) {
														if ( $item_product->is_type( 'variable' ) ) {
															$item_ori_price_min = ( $pricing === 'sale_price' ) ? $item_product->get_variation_price( 'min', true ) : $item_product->get_variation_regular_price( 'min', true );
															$item_ori_price_max = ( $pricing === 'sale_price' ) ? $item_product->get_variation_price( 'max', true ) : $item_product->get_variation_regular_price( 'max', true );
															$item_new_price_min = self::woobt_new_price( $item_ori_price_min, $item_price );
															$item_new_price_max = self::woobt_new_price( $item_ori_price_max, $item_price );

															if ( $item_new_price_min < $item_new_price_max ) {
																$item_product_price = wc_format_price_range( $item_new_price_min, $item_new_price_max );
															} else {
																$item_product_price = wc_format_sale_price( $item_ori_price_min, $item_new_price_min );
															}
														} else {
															$item_ori_price = ( $pricing === 'sale_price' ) ? wc_get_price_to_display( $item_product, array( 'price' => $item_product->get_price() ) ) : wc_get_price_to_display( $item_product, array( 'price' => $item_product->get_regular_price() ) );
															$item_new_price = self::woobt_new_price( $item_ori_price, $item_price );

															if ( $item_new_price < $item_ori_price ) {
																$item_product_price = wc_format_sale_price( $item_ori_price, $item_new_price );
															} else {
																$item_product_price = wc_price( $item_new_price );
															}
														}

														$item_product_price .= $item_product->get_price_suffix();
													} else {
														$item_product_price = $item_product->get_price_html();
													}

													echo apply_filters( 'woobt_product_price', $item_product_price, $item_product, $item );
													?>
                                                </span>
                                            </span>
											<?php
										}

										if ( get_option( '_woobt_show_description', 'no' ) === 'yes' ) {
											echo '<div class="woobt-description">' . $item_product->get_short_description() . '</div>';
										}

										echo '<div class="woobt-availability">' . wc_get_stock_html( $item_product ) . '</div>';
										?>
                                    </div>

									<?php if ( $custom_qty ) {
										echo '<div class="woobt-quantity">';

										if ( get_option( '_woobt_plus_minus', 'no' ) === 'yes' ) {
											echo '<div class="woobt-quantity-input">';
											echo '<div class="woobt-quantity-input-minus">-</div>';
										}

										woocommerce_quantity_input( array(
											'classes'     => array( 'input-text', 'woobt-qty', 'qty', 'text' ),
											'input_value' => $item_qty,
											'min_value'   => $item_qty_min,
											'max_value'   => $item_qty_max,
											'input_name'  => 'woobt_qty_' . $order,
											'woobt_qty'   => array(
												'input_value' => $item_qty,
												'min_value'   => $item_qty_min,
												'max_value'   => $item_qty_max
											)
											// compatible with WPC Product Quantity
										), $item_product );

										if ( get_option( '_woobt_plus_minus', 'no' ) === 'yes' ) {
											echo '<div class="woobt-quantity-input-plus">+</div>';
											echo '</div>';
										}

										echo '</div>';
									}

									if ( ! $is_separate_layout && ( get_option( '_woobt_show_price', 'yes' ) !== 'no' ) ) { ?>
                                        <div class="woobt-price">
                                            <div class="woobt-price-new"></div>
                                            <div class="woobt-price-ori">
												<?php
												if ( ! $separately && ( $item_price !== '100%' ) ) {
													if ( $item_product->is_type( 'variable' ) ) {
														$item_ori_price_min = ( $pricing === 'sale_price' ) ? $item_product->get_variation_price( 'min', true ) : $item_product->get_variation_regular_price( 'min', true );
														$item_ori_price_max = ( $pricing === 'sale_price' ) ? $item_product->get_variation_price( 'max', true ) : $item_product->get_variation_regular_price( 'max', true );
														$item_new_price_min = self::woobt_new_price( $item_ori_price_min, $item_price );
														$item_new_price_max = self::woobt_new_price( $item_ori_price_max, $item_price );

														if ( $item_new_price_min < $item_new_price_max ) {
															$item_product_price = wc_format_price_range( $item_new_price_min, $item_new_price_max );
														} else {
															$item_product_price = wc_format_sale_price( $item_ori_price_min, $item_new_price_min );
														}
													} else {
														$item_ori_price = ( $pricing === 'sale_price' ) ? wc_get_price_to_display( $item_product, array( 'price' => $item_product->get_price() ) ) : wc_get_price_to_display( $item_product, array( 'price' => $item_product->get_regular_price() ) );
														$item_new_price = self::woobt_new_price( $item_ori_price, $item_price );

														if ( $item_new_price < $item_ori_price ) {
															$item_product_price = wc_format_sale_price( $item_ori_price, $item_new_price );
														} else {
															$item_product_price = wc_price( $item_new_price );
														}
													}

													$item_product_price .= $item_product->get_price_suffix();
												} else {
													$item_product_price = $item_product->get_price_html();
												}

												echo apply_filters( 'woobt_product_price', $item_product_price, $item_product, $item );
												?>
                                            </div>
                                        </div>
									<?php }

									do_action( 'woobt_product_after', $item_product, $order );
									?>
                                </div>
								<?php
								$order ++;
							} ?>
                        </div>
						<?php
						echo '<div class="woobt-additional woobt-text"></div>';
						echo '<div class="woobt-total woobt-text"></div>';
						echo '<div class="woobt-alert woobt-text"></div>';

						if ( $after_text = apply_filters( 'woobt_after_text', get_post_meta( $product_id, 'woobt_after_text', true ) ?: self::woobt_localization( 'under_text' ), $product_id ) ) {
							echo '<div class="woobt-after-text woobt-text">' . do_shortcode( stripslashes( $after_text ) ) . '</div>';
						}

						if ( $is_custom_position || $is_separate_atc ) {
							echo '<div class="woobt-actions">';
							echo '<div class="woobt-form">';
							echo '<input type="hidden" name="woobt_ids" class="woobt-ids woobt-ids-' . esc_attr( $product->get_id() ) . '" data-id="' . esc_attr( $product->get_id() ) . '"/>';
							echo '<input type="hidden" name="quantity" value="1"/>';
							echo '<input type="hidden" name="product_id" value="' . esc_attr( $product_id ) . '">';
							echo '<input type="hidden" name="variation_id" class="variation_id" value="0">';
							echo '<button type="submit" class="single_add_to_cart_button button alt">' . self::woobt_localization( 'add_all_to_cart', esc_html__( 'Add all to cart', 'woo-bought-together' ) ) . '</button>';
							echo '</div>';
							echo '</div>';
						}

						do_action( 'woobt_wrap_after', $product );

						echo '</div>';
					}
				}

				function woobt_get_ids( $product_id, $context = 'display' ) {
					$ids = get_post_meta( $product_id, 'woobt_ids', true );

					return apply_filters( 'woobt_get_ids', $ids, $product_id, $context );
				}

				function woobt_get_items( $ids, $product_id = 0, $context = 'view' ) {
					$items = array();
					$ids   = self::woobt_clean_ids( $ids );

					if ( ! empty( $ids ) ) {
						$_items = explode( ',', $ids );

						if ( is_array( $_items ) && count( $_items ) > 0 ) {
							foreach ( $_items as $_item ) {
								$_item_data    = explode( '/', $_item );
								$_item_id      = apply_filters( 'woobt_item_id', absint( $_item_data[0] ?: 0 ) );
								$_item_product = wc_get_product( $_item_id );

								if ( ! $_item_product ) {
									continue;
								}

								if ( ( $context === 'view' ) && ( ( get_option( '_woobt_exclude_unpurchasable', 'no' ) === 'yes' ) && ( ! $_item_product->is_purchasable() || ! $_item_product->is_in_stock() ) ) ) {
									continue;
								}

								$items[] = array(
									'id'    => $_item_id,
									'price' => isset( $_item_data[1] ) ? self::woobt_format_price( $_item_data[1] ) : '100%',
									'qty'   => (float) ( isset( $_item_data[2] ) ? $_item_data[2] : 1 ),
									'attrs' => isset( $_item_data[3] ) ? (array) json_decode( rawurldecode( $_item_data[3] ) ) : array()
								);
							}
						}
					}

					$items = apply_filters( 'woobt_get_items', $items, $ids, $product_id, $context );

					if ( $items && is_array( $items ) && count( $items ) > 0 ) {
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

				public static function woobt_clean_ids( $ids ) {
					//$ids = preg_replace( '/[^.%,\/0-9]/', '', $ids );

					return apply_filters( 'woobt_clean_ids', $ids );
				}

				public static function woobt_format_price( $price ) {
					// format price to percent or number
					$price = preg_replace( '/[^.%0-9]/', '', $price );

					return apply_filters( 'woobt_format_price', $price );
				}

				public static function woobt_new_price( $old_price, $new_price ) {
					if ( strpos( $new_price, '%' ) !== false ) {
						$calc_price = ( (float) $new_price * $old_price ) / 100;
					} else {
						$calc_price = $new_price;
					}

					return apply_filters( 'woobt_new_price', $calc_price, $old_price );
				}

				function woobt_wpml_item_id( $id ) {
					return apply_filters( 'wpml_object_id', $id, 'product', true );
				}

				function woobt_localization( $key = '', $default = '' ) {
					$str = '';

					if ( ! empty( $key ) && ! empty( self::$localization[ $key ] ) ) {
						$str = self::$localization[ $key ];
					} elseif ( ! empty( $default ) ) {
						$str = $default;
					}

					return apply_filters( 'woobt_localization_' . $key, $str );
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