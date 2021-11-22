<?php
/**
 * WooCommerce Stock Manager in app offers
 *
 * @version      1.2.0
 *
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for handling in app offer in WooCommerce Stock Manager
 */
class SA_In_App_Offers {

	/**
	 * Variable to hold instance of this class
	 *
	 * @var $instance
	 */
	private static $instance = null;

	/**
	 * The plugin file
	 *
	 * @var string $plugin_file
	 */
	public $plugin_file = '';

	/**
	 * The plugin url
	 *
	 * @var string $plugin_file
	 */
	public $plugin_url = '';

	/**
	 * The prefix
	 *
	 * @var string $prefix
	 */
	public $prefix = '';

	/**
	 * The option name
	 *
	 * @var string $option_name
	 */
	public $option_name = '';

	/**
	 * The campaign
	 *
	 * @var string $campaign
	 */
	public $campaign = '';

	/**
	 * The start
	 *
	 * @var string $start
	 */
	public $start = '';

	/**
	 * The end
	 *
	 * @var string $end
	 */
	public $end = '';

	/**
	 * Is plugin page
	 *
	 * @var bool $end
	 */
	public $is_plugin_page = false;

	/**
	 * Constructor
	 *
	 * @param array $args Configuration.
	 */
	public function __construct( $args ) {

		$this->plugin_file    = ( ! empty( $args['file'] ) ) ? $args['file'] : '';
		$this->prefix         = ( ! empty( $args['prefix'] ) ) ? $args['prefix'] : '';
		$this->option_name    = ( ! empty( $args['option_name'] ) ) ? $args['option_name'] : '';
		$this->campaign       = ( ! empty( $args['campaign'] ) ) ? $args['campaign'] : '';
		$this->start          = ( ! empty( $args['start'] ) ) ? $args['start'] : '';
		$this->end            = ( ! empty( $args['end'] ) ) ? $args['end'] : '';
		$this->is_plugin_page = ( ! empty( $args['is_plugin_page'] ) ) ? $args['is_plugin_page'] : false;

		add_action( 'admin_notices', array( $this, 'in_app_offer' ) );

	}

	/**
	 * Get single instance of this class
	 *
	 * @param array $args Configuration.
	 * @return Singleton object of this class
	 */
	public static function get_instance( $args ) {
		// Check if instance is already exists.
		if ( is_null( self::$instance ) ) {
			self::$instance = new self( $args );
		}

		return self::$instance;
	}

	/**
	 * Whether to show or not
	 *
	 * @return boolean
	 */
	public function is_show() {

		$timezone_format = _x( 'Y-m-d', 'timezone date format' );
		$current_date    = strtotime( date_i18n( $timezone_format ) );
		$start           = strtotime( $this->start );
		$end             = strtotime( $this->end );
		if ( ( $current_date >= $start ) && ( $current_date <= $end ) ) {
			$option_value  = get_option( $this->option_name, '' );
			$get_post_type = isset( $_GET['post_type'] ) ? sanitize_text_field( wp_unslash( $_GET['post_type'] ) ) : ''; // phpcs:ignore

			if ( ( 'product' === $get_post_type || $this->is_plugin_page ) && '' === $option_value ) {
				return true;
			}
		}

		return false;

	}

	/**
	 * The offer content
	 */
	public function in_app_offer() {
		if ( $this->is_show() ) {
			?>
			<div class="sa_offer_container"><?php $this->show_offer_content(); ?></div>
			<?php
		}
	}

	/**
	 * The offer content
	 */
	public function show_offer_content() {
		$sa_offer_2020 = get_option( $this->option_name, '' );
		if ( 'no' === $sa_offer_2020 ) {
			return;
		}

		if ( ! wp_script_is( 'jquery' ) ) {
			wp_enqueue_script( 'jquery' );
		}

		if ( 'wsm' === $this->prefix ) {
			?>
			<style type="text/css">
				.sa_offer {
					margin: 1em auto;
					text-align: center;
					font-size: 1.2em;
					line-height: 1em;
					padding: 1em;
				}
				.sa_offer_content img {
					width: 50%;
				}
				.sa_dismiss {
					font-size: 0.5em;
					display: inline-block;
					width: 100%;
					text-align: center;
					letter-spacing: 2px;
				}
			</style>
			<div class="sa_offer">
				<div class="sa_offer_content">
					<a href="https://www.storeapps.org/woocommerce-plugins/?utm_source=in_app&utm_medium=<?php echo esc_attr( $this->prefix ); ?>_banner&utm_campaign=<?php echo esc_attr( $this->campaign ); ?>" target="_blank">
						<img src="<?php echo esc_url( plugins_url( 'sa-includes/images/bfcm-2020.jpg', $this->plugin_file ) ); ?>" />
					</a>
					<div class="sa_dismiss"> <!-- Do not change this class -->
						<a href="?wsm_dismiss_admin_notice=1&option_name=sa_offer_bfcm_2020" style="color: black; text-decoration: none;"><?php echo esc_html__( 'Hide this' ); ?></a>
					</div>
				</div>
			</div>
			<?php
		}
		?>
		<script type="text/javascript">
			jQuery(function(){
				jQuery('div.sa_offer').not(':eq(0)').hide();	// To hide offer div if present multiple times.
			});
		</script>
		<?php
	}

}
