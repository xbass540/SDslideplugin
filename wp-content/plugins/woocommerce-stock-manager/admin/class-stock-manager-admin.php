<?php
/**
 * @author    StoreApps
 * @package   woocommerce-stock-manager/admin
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Stock_Manager_Admin {

	/**
	 * Instance of this class.
	 *
	 * @since    1.0.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Slug of the plugin screen.
	 *
	 * @since    1.0.0
	 *
	 * @var      string
	 */
	protected $plugin_screen_hook_suffix = null;

	/**
	 * Initialize the plugin by loading admin scripts & styles and adding a
	 * settings page and menu.
	 *
	 * @since     1.0.0
	 */
	private function __construct() {

		$plugin = Stock_Manager::get_instance();
		$this->plugin_slug = $plugin->get_plugin_slug();

		// Load admin style sheet and JavaScript.
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_styles' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_scripts' ) );

		// Add the options page and menu item.
		add_action( 'admin_menu', array( $this, 'add_plugin_admin_menu' ) );

		add_action( 'admin_init', array( $this, 'output_buffer' ) );
	
		include_once( 'includes/wcm-class-stock.php' );
	
		add_action( 'admin_notices', array( $this, 'includes' ) );

		add_action( 'admin_init', array( $this, 'generate_csv_file' ) );

		add_action( 'admin_init', array( $this, 'wsm_dismiss_admin_notice' ) );

		// To update footer text on WSM screens.
		add_filter( 'admin_footer_text', array( $this, 'wsm_footer_text' ), 99999 );
		add_filter( 'update_footer', array( $this, 'wsm_update_footer_text' ), 99999 );
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     1.0.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}
  
	/**
	 * Include required core files used in admin.
	 * 
	 * @since     1.0.0      
	 */
	public function includes() {
		$is_wsm_admin = $this->is_wsm_admin_page();
		if ( $is_wsm_admin ) {
			$this->may_be_show_sa_in_app_offer();
			$this->wsm_add_subscribe_notice();
			
		}
	}

	/**
	 * Function to check if WSM admin page.
	 */
	public function is_wsm_admin_page() {
		if( isset( $_GET ) && isset( $_GET['page'] ) ) {
			if ( 'stock-manager' === $_GET['page'] || 'stock-manager-import-export' === $_GET['page'] || 'stock-manager-log' === $_GET['page'] || 'stock-manager-setting' === $_GET['page'] || 'stock-manager-storeapps-plugins' === $_GET['page'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Get stock class
	 * @return WCM_Stock
	 * 
	 * @since     1.0.0      
	 */
	public function stock() {
		return WCM_Stock::get_instance();	
	}

	/**
	 * Register and enqueue admin-specific CSS.
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_styles() {
		if( isset( $_GET['page'] ) && ( $_GET['page'] == 'stock-manager' || $_GET['page'] == 'stock-manager-import-export' || $_GET['page'] == 'stock-manager-log' ) ){
			wp_enqueue_style( $this->plugin_slug .'-admin-styles', plugins_url( 'assets/css/admin.css', __FILE__ ), array(), Stock_Manager::VERSION );

			$old_styles = get_option( 'woocommerce_stock_old_styles' );
			if( !empty( $old_styles ) && $old_styles == 'ok' ){
				wp_enqueue_style( $this->plugin_slug .'-old-styles', plugins_url( 'assets/css/old.css', __FILE__ ), array(), Stock_Manager::VERSION );              
			}
		}
	}

	/**
	 * Register and enqueue admin-specific JavaScript.
	 * @since     1.0.0
	 *
	 * @return    null    Return early if no settings page is registered.
	 */
	public function enqueue_admin_scripts() {
		if( isset( $_GET['page'] ) && ( $_GET['page'] == 'stock-manager' || $_GET['page'] == 'stock-manager-import-export' ) ) {
			$low_stock_threshold = get_option( 'woocommerce_notify_low_stock_amount', 5 );
			$low_stock_threshold = ( ! empty( $low_stock_threshold ) ) ? $low_stock_threshold : 5;

			$params = array(
				'ajax_nonce' => wp_create_nonce( 'wsm_update' ),
			);
			wp_localize_script( $this->plugin_slug . '-admin-script', 'ajax_object', $params );
			wp_enqueue_script( $this->plugin_slug . '-admin-script', plugins_url( 'assets/js/admin.js', __FILE__ ), array( 'jquery' ), Stock_Manager::VERSION );

			wp_enqueue_style( $this->plugin_slug .'-admin-script-react', plugins_url( 'assets/build/index.css', __FILE__ ), array(), Stock_Manager::VERSION );
			wp_enqueue_script( $this->plugin_slug . '-admin-script-react', plugins_url( 'assets/build/index.js', __FILE__ ), array( 'wp-polyfill', 'wp-i18n', 'wp-url' ), Stock_Manager::VERSION );
			wp_localize_script( $this->plugin_slug . '-admin-script-react', 'WooCommerceStockManagerPreloadedState', array(
				'app'=> [
					'textDomain' => $this->plugin_slug,
					'root' => esc_url_raw(rest_url()),
					'adminUrl' => admin_url(),
					'nonce' => wp_create_nonce('wp_rest'),
					'perPage' => apply_filters('woocommerce_stock_manager_per_page', 50),
					'lowStockThreshold' => $low_stock_threshold,
				],
				'product-categories' => array_reduce(get_terms(['taxonomy' => 'product_cat', 'hide_empty' => false]), function($carry, $item) {
					$carry[$item->term_id] = $item->name;
					return $carry;
				}, []),
				'product-types' => wc_get_product_types(),
				'stock-status-options' => wc_get_product_stock_status_options(),
				'shipping-classes' => array_merge(array('' => __('No shipping class', 'woocommerce-stock-manager')), array_reduce(get_terms(['taxonomy' => 'product_shipping_class', 'hide_empty' => false]), function($carry, $item) {
					$carry[$item->slug] = $item->name;
					return $carry;
				}, [])),
				'tax-classes' => wc_get_product_tax_class_options(),
				'tax-statuses' => [
					'taxable' => __('Taxable', 'woocommerce-stock-manager'),
					'shipping' => __('Shipping only', 'woocommerce-stock-manager'),
					'none' => _x('None', 'Tax status', 'woocommerce-stock-manager'),
				],
				'backorders-options' => [
					'no' => __('No','woocommerce-stock-manager'),
					'notify' => __('Notify','woocommerce-stock-manager'),
					'yes' => __('Yes','woocommerce-stock-manager'),
				],
			));

			wp_set_script_translations( $this->plugin_slug . '-admin-script-react', 'stock-manager', STOCKDIR . 'languages' );
		}

		// Klawoo subscribe.
		$wsm_dismiss_admin_notice = get_option( 'wsm_dismiss_subscribe_admin_notice', false );
		if ( empty( $wsm_dismiss_admin_notice ) ) {
			$is_wsm_admin = $this->is_wsm_admin_page();
			if ( $is_wsm_admin ) {
				$params = array(
					'ajax_nonce' => wp_create_nonce( 'wsm_update' ),
				);
				wp_localize_script( $this->plugin_slug . '-admin-script-w', 'ajax_object', $params );
				wp_enqueue_script( $this->plugin_slug . '-admin-script-w', plugins_url( 'assets/js/subscribe.js', __FILE__ ), array( 'jquery' ), Stock_Manager::VERSION );
				
			}
		}
	}

	public function get_free_menu_position($start, $increment = 0.0001) {
		foreach ($GLOBALS['menu'] as $key => $menu) {
			$menus_positions[] = $key;
		}
	
		if (!in_array($start, $menus_positions)) return $start;
	
		/* the position is already reserved find the closet one */
		while (in_array($start, $menus_positions)) {
			$start += $increment;
		}
		return $start;
	}

	/**
	 * Register the administration menu for this plugin into the WordPress Dashboard menu.
	 *
	 * @since    1.0.0
	 */
	public function add_plugin_admin_menu() {

		$value = 'manage_woocommerce';

		$manage = apply_filters( 'stock_manager_manage', $value );

		$position = (string) $this->get_free_menu_position(56.00001);

		$hook = add_menu_page(
			__( 'WooCommerce Stock Manager', $this->plugin_slug ),
			__( 'Stock Manager', $this->plugin_slug ),
			$manage,
			'stock-manager',
			array( $this, 'display_plugin_admin_page' ),
			'dashicons-book-alt',
			$position
		);

		// Show screen option for React App
		add_action('load-' . $hook, function() {
			add_filter('screen_options_show_screen', function () {
				return true;
			});
		});
		
		add_submenu_page(
			'stock-manager',
			__( 'Import/Export', $this->plugin_slug ),
			__( 'Import/Export', $this->plugin_slug ),
			$manage,
			'stock-manager-import-export',
			array( $this, 'display_import_export_page' )
		);
		add_submenu_page(
			'stock-manager',
			__( 'Stock log', $this->plugin_slug ),
			__( 'Stock log', $this->plugin_slug ),
			$manage,
			'stock-manager-log',
			array( $this, 'display_log_page' )
		);
		add_submenu_page(
			'stock-manager',
			__( 'Setting', $this->plugin_slug ),
			__( 'Setting', $this->plugin_slug ),
			$manage,
			'stock-manager-setting',
			array( $this, 'display_setting_page' )
		);
		add_submenu_page(
			'stock-manager',
			__( 'StoreApps Plugins', $this->plugin_slug ),
			__( 'StoreApps Plugins', $this->plugin_slug ),
			$manage,
			'stock-manager-storeapps-plugins',
			array( $this, 'display_sa_marketplace_page' )
		);

	}

	/**
	 * Render the settings page for this plugin.
	 *
	 * @since    1.0.0
	 */
	public function display_plugin_admin_page() {
		include_once( 'views/admin.php' );
	}
	/**
	 * Render the impoer export page for this plugin.
	 *
	 * @since    1.0.0
	 */
	
	public function display_import_export_page() {
		include_once( 'views/import-export.php' );
	}
	
	/**
	 * Render the setting page for this plugin.
	 *
	 * @since    1.2.2
	 */
	public function display_setting_page() {
		include_once( 'views/setting.php' );
	}

	/**
	 * Render the StoreApps Marketplace page.
	 *
	 * @since    2.2.0
	 */
	public function display_sa_marketplace_page() {
		include_once( 'views/class-storeapps-marketplace.php' );
		WSM_StoreApps_Marketplace::init();
	}

	/**
	 * Render the setting page for this plugin.
	 *
	 * @since    2.0.0
	 */
	public function display_log_page() {
		if( !empty( $_GET['history'] ) ){
			include_once( 'views/log-history.php' );
		}else{
			include_once( 'views/log.php' );
		}
	}

	/**
	 * Add settings action link to the plugins page.
	 *
	 * @since    1.0.0
	 */
	public function add_action_links( $links ) {
		return array_merge(
			array(
				'settings' => '<a href="' . admin_url( 'admin.php?page=stock-manager' ) . '">' . __( 'Settings', $this->plugin_slug ) . '</a>'
			),
			$links
		);
	}

	/**
	 * Create csv array and download cvs file
	 *
	 * @since 1.0.0  
	 */        
	public function stock_convert_to_csv($input_array, $output_file_name, $delimiter){
	
		$temp_memory = fopen('php://memory', 'w');
	
		foreach ($input_array as $line) {
			fputcsv($temp_memory, $line, $delimiter);
		}
		fseek($temp_memory, 0);
	
		header('Content-Type: application/csv');
		header('Content-Disposition: attachement; filename="' . $output_file_name . '";');
	
		fpassthru($temp_memory);
		exit();
	}

	/**
	 * Generate csv data
	 *
	 * @since 1.0.0  
	 */        
	public function generate_csv_file() {

		if(isset($_GET['action']) && $_GET['action'] == 'export') {
			$stock = $this->stock();

			$array_to_csv = array();
			//First line
			$array_to_csv[] = array('id','sku','Product name','Manage stock','Stock status','Backorders','Stock','Type','Parent ID'); 
		   
			$products = $stock->get_products_for_export(); 
		  
		  foreach( $products as $item ){ 
			$product_meta = get_post_meta($item->ID);
			$item_product = wc_get_product($item->ID);
			$product_type = $item_product->get_type();
			$id = $item->ID;
			if(!empty($product_meta['_sku'][0])){          $sku = $product_meta['_sku'][0]; }else{ $sku = ''; }
			$product_name = $item->post_title;
			if(!empty($product_meta['_manage_stock'][0])){ $manage_stock = $product_meta['_manage_stock'][0]; }else{ $manage_stock = ''; }
			if(!empty($product_meta['_stock_status'][0])){ $stock_status = $product_meta['_stock_status'][0]; }else{ $stock_status = ''; }
			if(!empty($product_meta['_backorders'][0])){   $backorders = $product_meta['_backorders'][0]; }else{ $backorders = ''; }
			if(!empty($product_meta['_stock'][0])){        $stock = $product_meta['_stock'][0]; }else{ $stock = '0'; }
			if($product_type == 'variable'){               $product_type = 'variable'; }else{ $product_type = 'simple'; }
			
			$array_to_csv[] = array($id,$sku,$product_name,$manage_stock,$stock_status,$backorders,$stock,$product_type,'');
		   
			if($product_type == 'variable'){
					$args = array(
					   'post_parent' => $item->ID,
					   'post_type'   => 'product_variation', 
					   'numberposts' => -1,
					   'post_status' => 'publish' 
					); 
					$variations_array = get_children( $args );
					foreach($variations_array as $vars){
				 
						$var_meta = get_post_meta($vars->ID);
						$item_product = wc_get_product($vars->ID);
		   
						$id = $vars->ID;
						if(!empty($var_meta['_sku'][0])){          $sku = $var_meta['_sku'][0]; }else{ $sku = ''; }
						$product_name = '';
						foreach($item_product->variation_data as $k => $v){ 
							$tag = get_term_by('slug', $v, str_replace('attribute_','',$k));
							if($tag == false ){
								$product_name .= $v.' ';
							}else{
								if(is_array($tag)){
									$product_name .= $tag['name'].' ';
								}else{
									$product_name .= $tag->name.' ';
								}
							}
						} 
						
						if(!empty($var_meta['_manage_stock'][0])){ $manage_stock = $var_meta['_manage_stock'][0]; }else{ $manage_stock = ''; }
						if(!empty($var_meta['_stock_status'][0])){ $stock_status = $var_meta['_stock_status'][0]; }else{ $stock_status = ''; }
						if(!empty($var_meta['_backorders'][0])){   $backorders = $var_meta['_backorders'][0]; }else{ $backorders = ''; }
						if(!empty($var_meta['_stock'][0])){        $stock = $var_meta['_stock'][0]; }else{ $stock = '0'; }
						$product_type = 'product-variant';
						$parent_id = $item->ID; 
						
						$array_to_csv[] = array($id,$sku,$product_name,$manage_stock,$stock_status,$backorders,$stock,$product_type,$parent_id);                    
						
					}
				}
			}
			$this->stock_convert_to_csv($array_to_csv, 'stock-manager-export.csv', ',');	
		}
	}

	/**
	 * Headers allready sent fix
	 *
	 */        
	public function output_buffer() {
		ob_start();
	}

	/**
	 * Function to show SA in app offers in WSM if any.
	 * Added @since: 2.5.2.
	 */
	public function may_be_show_sa_in_app_offer() {
		if ( ! class_exists( 'SA_In_App_Offers' ) ) {
			include_once STOCKDIR . '/sa-includes/class-sa-in-app-offers.php';

			$args = array(
				'file'           => WSM_PLUGIN_FILE,
				'prefix'         => 'wsm',
				'option_name'    => 'sa_offer_bfcm_2020_wsm',
				'campaign'       => 'sa_bfcm_2020',
				'start'          => '2020-11-24 06:00:00',
				'end'            => '2020-12-03 06:00:00',
				'is_plugin_page' => true,
			);

			SA_In_App_Offers::get_instance( $args );
		}
	}

	/**
	 * Function to dismiss admin notice.
	 */
	public function wsm_dismiss_admin_notice() {

		if ( isset( $_GET['wsm_dismiss_admin_notice'] ) && '1' == $_GET['wsm_dismiss_admin_notice'] && isset( $_GET['option_name'] ) ) {
			$option_name = sanitize_text_field( wp_unslash( $_GET['option_name'] ) );
			update_option( $option_name . '_wsm', 'no', 'no' );
			$referer = wp_get_referer();
			wp_safe_redirect( $referer );
			exit();
		}

	}

	/**
	 * Function to show notice in the admin.
	 */
	public function wsm_add_subscribe_notice() {
		$wsm_dismiss_admin_notice = get_option( 'wsm_dismiss_subscribe_admin_notice', false );

		if ( empty( $wsm_dismiss_admin_notice ) ) {
			?>
			<style type="text/css" class="wsm-subscribe">
				#wsm_promo_msg {
					display: block !important;
					background-color: #f2f6fc;
					border-left-color: #5850ec;
				}
				#wsm_promo_msg table {
					width: 100%;
					padding-bottom: 0.25em;
				}
				#wsm_dashicon {
					padding: 0.5em;
					width: 3%;
				}
				#wsm_promo_msg_content {
					padding: 0.5em;
				}
				#wsm_promo_msg .dashicons.dashicons-awards {
					font-size: 5em;
					color: #b08d57;
					margin-left: -0.2em;
					margin-bottom: 0.65em;
				}
				.wsm_headline {
					padding: 0.5em 0;
					font-size: 1.4em;
				}
				form.wsm_klawoo_subscribe {
					padding: 0.5em 0;
					margin-block-end: 0 !important;
					font-size: 1.1em;
				}
				form.wsm_klawoo_subscribe #email {
					width: 14em;
					height: 1.75em;
				}
				form.wsm_klawoo_subscribe #wsm_gdpr_agree {
					margin-left: 0.5em;
					vertical-align: sub;
				}
				form.wsm_klawoo_subscribe .wsm_gdpr_label {
					margin-right: 0.5em;
				}
				form.wsm_klawoo_subscribe #wsm_submit {
					font-size: 1.3em;
					line-height: 0em;
					margin-top: 0;
					font-weight: bold;
					background: #5850ec;
					border-color: #5850ec;
				}
				.wsm_success {
					font-size: 1.5em;
					font-weight: bold;
				}
			</style>
			<div id="wsm_promo_msg" class="updated fade">
				<table>
					<tbody> 
						<tr>
							<td id="wsm_dashicon"> 
								<span class="dashicons dashicons-awards"></span>
							</td> 
							<td id="wsm_promo_msg_content">
								<div class="wsm_headline">Get latest hacks & tips to better manage your store using WooCommerce Stock Manager!</div>
								<form name="wsm_klawoo_subscribe" class="wsm_klawoo_subscribe" action="#" method="POST" accept-charset="utf-8">									
									<input type="email" class="regular-text ltr" name="email" id="email" placeholder="Your email address" required="required" />
									<input type="checkbox" name="wsm_gdpr_agree" id="wsm_gdpr_agree" value="1" required="required" />
									<label for="wsm_gdpr_agree" class="wsm_gdpr_label">I have read and agreed to your <a href="https://www.storeapps.org/privacy-policy/?utm_source=wsm&utm_medium=in_app_subscribe&utm_campaign=in_app_subscribe" target="_blank">Privacy Policy</a>.</label>
									<input type="hidden" name="list" value="3pFQTnTsH763gAKTuvOGhPzA"/>
									<input type="submit" name="submit" id="wsm_submit" class="button button-primary" value="Subscribe" />
								</form>
							</td>
							</tr>
					</tbody> 
				</table> 
			</div>
			<?php
		}
	}

	/**
	 * Function to ask to review the plugin in footer
	 *
	 * @param  string $wsm_rating_text Text in footer (left).
	 * @return string $wsm_rating_text
	 */
	public function wsm_footer_text( $wsm_rating_text ) {

		$is_wsm_admin = $this->is_wsm_admin_page();
		if ( $is_wsm_admin ) {
			/* translators: %1$s & %2$s: Opening & closing strong tag. %3$s: link to WooCommerce Stock Manager on WordPress.org */
			$wsm_rating_text = sprintf( __( 'If you are liking %1$sWooCommerce Stock Manager%2$s, please rate us %3$s. A huge thanks from StoreApps in advance!', 'woocommerce-stock-manager' ), '<strong>', '</strong>', '<a target="_blank" href="' . esc_url( 'https://wordpress.org/support/plugin/woocommerce-stock-manager/reviews/?filter=5' ) . '" style="color: #5850EC;">5-star</a>' );
		}

		return $wsm_rating_text;

	}

	/**
	 * Function to show installed version of the plugin
	 *
	 * @param  string $wsm_text Text in footer (right).
	 * @return string $wsm_text
	 */
	public function wsm_update_footer_text( $wsm_text ) {

		$is_wsm_admin = $this->is_wsm_admin_page();
		if ( $is_wsm_admin ) {
			$wsm_text = 'Installed Version: '. Stock_Manager::VERSION . '!';
			?>
			<style type="text/css">
				#wpfooter {
					position: unset;
				}
				#wpfooter #footer-upgrade {
					color: #5850EC;
				}
			</style>
			<?php
		}

		return $wsm_text;

	}

}//End class
