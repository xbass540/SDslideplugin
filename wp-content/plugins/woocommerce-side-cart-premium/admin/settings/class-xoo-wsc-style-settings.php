<?php

/**
 * Admin Part of Plugin, dashboard and options.
 *
 * @package    WooCommerce Side Cart
 */
class xoo_wsc_Style_Settings extends xoo_wsc_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0 
	 * @access   private
	 * @var      string    $xoo_wsc    The ID of this plugin.
	 */
	private $xoo_wsc;

	/**
	 * The ID of General Settings.
	 *
	 * @since    1.0.0 
	 * @access   private
	 * @var      string    $group    The ID of General Settings.
	 */
	private $group;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @var      string    $xoo_wsc     The name of this plugin.
	 * @var      string    $version    The version of this plugin.
	 */
	public function __construct( $xoo_wsc ) {

		$this->xoo_wsc = $xoo_wsc;
		$this->group = $xoo_wsc.'-sy';
	}

	/**
	 * Creates our settings sections with fields etc. 
	 *
	 * @since    1.0.0
	 */
	public function settings_api_init(){
		
		// register_setting( $option_group, $option_name, $settings_sanitize_callback );
		register_setting(
			$this->group . '-options',
			$this->group . '-options',
			array( $this, 'settings_sanitize' )
		);

		// add_settings_section( $id, $title, $callback, $menu_slug );
		add_settings_section(
			$this->group . '-sch-options', // section
			'',
			array( $this, 'sch_options_section' ),
			$this->group // Side Cart - Head Section
		);

		add_settings_section(
			$this->group . '-scb-options', // section
			'',
			array( $this, 'scb_options_section' ),
			$this->group // Side Cart - Body Section
		);

		add_settings_section(
			$this->group . '-scf-options', // section
			'',
			array( $this, 'scf_options_section' ),
			$this->group // Side Cart - Footer Section
		);

		add_settings_section(
			$this->group . '-bk-options', // section
			'',
			array( $this, 'bk_options_section' ),
			$this->group // Cart Basket Section
		);

		add_settings_section(
			$this->group . '-sp-options', // section
			'',
			array( $this, 'sp_options_section' ),
			$this->group // Cart Basket Section
		);

		/*
		 =============================================
		 ============= Side Cart - Head ==============
		 =============================================
		*/

		// add_settings_field( $id, $title, $callback, $menu_slug, $section, $args );
		add_settings_field(
			'sch-bgc',
			 __( 'Background Color', 'side-cart-woocommerce' ),
			array( $this, 'sch_bgc' ),
			$this->group,
			$this->group . '-sch-options' // Background Color 
		);

		add_settings_field(
			'sch-fc',
			 __( 'Font Color', 'side-cart-woocommerce' ),
			array( $this, 'sch_fc' ),
			$this->group,
			$this->group . '-sch-options' // Font Color
		);

		add_settings_field(
			'sch-fs',
			 __( 'Font Size', 'side-cart-woocommerce' ),
			array( $this, 'sch_fs' ),
			$this->group,
			$this->group . '-sch-options' // Font Size
		);

		add_settings_field(
			'sch-bc',
			 __( 'Border Color', 'side-cart-woocommerce' ),
			array( $this, 'sch_bc' ),
			$this->group,
			$this->group . '-sch-options' // Border Color
		);

		add_settings_field(
			'sch-bs',
			 __( 'Border Size', 'side-cart-woocommerce' ),
			array( $this, 'sch_bs' ),
			$this->group,
			$this->group . '-sch-options' // Border Size
		);

		add_settings_field(
			'sch-ps',
			 __( 'Padding', 'side-cart-woocommerce' ),
			array( $this, 'sch_ps' ),
			$this->group,
			$this->group . '-sch-options' // Padding
		);

		add_settings_field(
			'sch-cis',
			 __( 'Close Cart Icon Size', 'side-cart-woocommerce' ),
			array( $this, 'sch_cis' ),
			$this->group,
			$this->group . '-sch-options' // Close Cart Icon Size
		);


		/*
		 =============================================
		 ============= Side Cart - Body ==============
		 =============================================
		*/

		 add_settings_field(
			'scb-open',
			 __( 'Open From', 'side-cart-woocommerce' ),
			array( $this, 'scb_open' ),
			$this->group,
			$this->group . '-scb-options' // Container Open
		);


		add_settings_field(
			'scb-ch',
			 __( 'Container Height', 'side-cart-woocommerce' ),
			array( $this, 'scb_ch' ),
			$this->group,
			$this->group . '-scb-options' // Container Height
		);

		 add_settings_field(
			'scb-cw',
			 __( 'Container Width', 'side-cart-woocommerce' ),
			array( $this, 'scb_cw' ),
			$this->group,
			$this->group . '-scb-options' // Container Width 
		);

		add_settings_field(
			'scb-bgc',
			 __( 'Background Color', 'side-cart-woocommerce' ),
			array( $this, 'scb_bgc' ),
			$this->group,
			$this->group . '-scb-options' // Background Color 
		);

		add_settings_field(
			'scb-fc',
			 __( 'Font Color', 'side-cart-woocommerce' ),
			array( $this, 'scb_fc' ),
			$this->group,
			$this->group . '-scb-options' // Font Color
		);

		add_settings_field(
			'scb-fs',
			 __( 'Font Size', 'side-cart-woocommerce' ),
			array( $this, 'scb_fs' ),
			$this->group,
			$this->group . '-scb-options' // Font Size
		);

		add_settings_field(
			'scb-imgw',
			 __( 'Product Image Width', 'side-cart-woocommerce' ),
			array( $this, 'scb_imgw' ),
			$this->group,
			$this->group . '-scb-options' // Image Width
		);

		add_settings_field(
			'scb-rfc',
			 __( 'Remove Text Color', 'side-cart-woocommerce' ),
			array( $this, 'scb_rfc' ),
			$this->group,
			$this->group . '-scb-options' // Remove Text Color
		);

		add_settings_field(
			'scb-ptfc',
			 __( 'Product Title Color', 'side-cart-woocommerce' ),
			array( $this, 'scb_ptfc' ),
			$this->group,
			$this->group . '-scb-options' // Product Title color
		);

		add_settings_field(
			'scb-ptfs',
			 __( 'Product Title Font Size', 'side-cart-woocommerce' ),
			array( $this, 'scb_ptfs' ),
			$this->group,
			$this->group . '-scb-options' // Product Title Font Size
		);

		add_settings_field(
			'scb-prbc',
			 __( 'Product Row Border Color', 'side-cart-woocommerce' ),
			array( $this, 'scb_prbc' ),
			$this->group,
			$this->group . '-scb-options' // Product Row Border Color
		);

		add_settings_field(
			'scb-prbs',
			 __( 'Product Row Border Size', 'side-cart-woocommerce' ),
			array( $this, 'scb_prbs' ),
			$this->group,
			$this->group . '-scb-options' // Product Row Border Size
		);


		add_settings_field(
			'scb-empimg',
			 __( 'Empty Cart Image', 'side-cart-woocommerce' ),
			array( $this, 'scb_empimg' ),
			$this->group,
			$this->group . '-scb-options' // Empty Cart image
		);

		/*
		 =============================================
		 ============ Side Cart - Footer =============
		 =============================================
		*/


		add_settings_field(
			'scf-bgc',
			 __( 'Background Color', 'side-cart-woocommerce' ),
			array( $this, 'scf_bgc' ),
			$this->group,
			$this->group . '-scf-options' // Background Color 
		);

		add_settings_field(
			'scf-bm',
			 __( 'Buttons Margin', 'side-cart-woocommerce' ),
			array( $this, 'scf_bm' ),
			$this->group,
			$this->group . '-scf-options' // Button Margin 
		);


		add_settings_field(
			'scf-btn-ts',
			 __( 'Button Theme Styling', 'side-cart-woocommerce' ),
			array( $this, 'scf_btn_ts' ),
			$this->group,
			$this->group . '-scf-options' // Button Theme Styling
		);


		add_settings_field(
			'scf-btn-bgc',
			 __( 'Button Bg Color', 'side-cart-woocommerce' ),
			array( $this, 'scf_btn_bgc' ),
			$this->group,
			$this->group . '-scf-options' // Button Background Color 
		);

		add_settings_field(
			'scf-btn-tc',
			 __( 'Button Text Color', 'side-cart-woocommerce' ),
			array( $this, 'scf_btn_tc' ),
			$this->group,
			$this->group . '-scf-options' // Button Text Color 
		);

		add_settings_field(
			'scf-btn-pd',
			 __( 'Button Padding', 'side-cart-woocommerce' ),
			array( $this, 'scf_btn_pd' ),
			$this->group,
			$this->group . '-scf-options' // Button Padding 
		);


		/*
		 =============================================
		 =============== Cart Basket =================
		 =============================================
		*/

		add_settings_field(
			'bk-pos',
			 __( 'Basket Position', 'side-cart-woocommerce' ),
			array( $this, 'bk_pos' ),
			$this->group,
			$this->group . '-bk-options' // Basket Position
		);


		add_settings_field(
			'bk-cubi',
			 __( 'Custom Basket Icon', 'side-cart-woocommerce' ),
			array( $this, 'bk_cubi' ),
			$this->group,
			$this->group . '-bk-options' // Custom Basket Icon
		);


		add_settings_field(
			'bk-bit',
			 __( 'Basket Icon', 'side-cart-woocommerce' ),
			array( $this, 'bk_bit' ),
			$this->group,
			$this->group . '-bk-options' // Basket Icon
		);

		add_settings_field(
			'bk-bbgc',
			 __( 'Basket Background Color', 'side-cart-woocommerce' ),
			array( $this, 'bk_bbgc' ),
			$this->group,
			$this->group . '-bk-options' // Basket Bg Color
		);

		add_settings_field(
			'bk-bfc',
			 __( 'Basket Icon Color', 'side-cart-woocommerce' ),
			array( $this, 'bk_bfc' ),
			$this->group,
			$this->group . '-bk-options' // Basket Icon Color
		);

		add_settings_field(
			'bk-bfs',
			 __( 'Basket Icon Size', 'side-cart-woocommerce' ),
			array( $this, 'bk_bfs' ),
			$this->group,
			$this->group . '-bk-options' // Basket Font Size
		);

		add_settings_field(
			'bk-cbgc',
			 __( 'Count Background Color', 'side-cart-woocommerce' ),
			array( $this, 'bk_cbgc' ),
			$this->group,
			$this->group . '-bk-options' // Count background Color
		);

		add_settings_field(
			'bk-cfc',
			 __( 'Count Text Color', 'side-cart-woocommerce' ),
			array( $this, 'bk_cfc' ),
			$this->group,
			$this->group . '-bk-options' // Count Text Color
		);


		/*
		 =============================================
		 =============== Suggested Products =================
		 =============================================
		*/


		add_settings_field(
			'sp-pos',
			 __( 'Position', 'side-cart-woocommerce' ),
			array( $this, 'sp_pos' ),
			$this->group,
			$this->group . '-sp-options' // Count Text Color
		);

		add_settings_field(
			'sp-bgc',
			 __( 'Background Color', 'side-cart-woocommerce' ),
			array( $this, 'sp_bgc' ),
			$this->group,
			$this->group . '-sp-options' // Count Text Color
		);

		add_settings_field(
			'sp-imgw',
			 __( 'Image Width', 'side-cart-woocommerce' ),
			array( $this, 'sp_imgw' ),
			$this->group,
			$this->group . '-sp-options' // Count Text Color
		);

	}

	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @return 		mixed 						The settings section
	 */
	public function sch_options_section() {
		$this->get_section_markup('Side Cart - Head');

	}


	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @return 		mixed 						The settings section
	 */
	public function scb_options_section() {
		$this->get_section_markup('Side Cart - Body');

	} 


	/**
	 * Creates a settings section
	 *
	 * @since 		1.0.0
	 * @return 		mixed 						The settings section
	 */
	public function scf_options_section() {
		$this->get_section_markup('Side Cart - Footer');

	} 


	/**
	 * Creates a basket section
	 *
	 * @since 		1.0.0
	 * @return 		mixed 						The settings section
	 */
	public function bk_options_section() {
		$this->get_section_markup('Cart Basket');
	} 



	/**
	 * Creates a Suggested product Section
	 *
	 * @since 		1.0.0
	 * @return 		mixed 						The settings section
	 */
	public function sp_options_section() {
		$this->get_section_markup('Suggested Products');
	} 


	/*
	 =============================================
	 ========= Side Cart - Head Section ==========
	 =============================================
	*/


	 /**
	 * Head- Background Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sch_bgc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sch-bgc']) ? $options['sch-bgc'] : '#ffffff';
		$id 		= $this->group.'-options[sch-bgc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/**
	 * Head- font Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sch_fc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sch-fc']) ? $options['sch-fc'] : '#000000';
		$id 		= $this->group.'-options[sch-fc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}

	/**
	 * Head- font Size
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sch_fs() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sch-fs']) ? $options['sch-fs'] : 20;
		$id 		= $this->group.'-options[sch-fs]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 25)</span>
		<?php
	}



	/**
	 * Head- Border Size
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sch_bs() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sch-bs']) ? $options['sch-bs'] : 1;
		$id 		= $this->group.'-options[sch-bs]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 1)</span>
		<?php
	}

	/**
	 * Head- Border Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sch_bc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sch-bc']) ? $options['sch-bc'] : '#eeeeee';
		$id 		= $this->group.'-options[sch-bc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}

	/**
	 * Padding
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sch_ps() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sch-ps']) ? $options['sch-ps'] : '10px 20px';
		$id 		= $this->group.'-options[sch-ps]';
		?>
		<input type="text" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">top-bottom right-left (Default: 10px 20px)</span>
		<?php
	}

	/**
	 * Head- Close Cart Icon Size
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sch_cis() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sch-cis']) ? $options['sch-cis'] : 20;
		$id 		= $this->group.'-options[sch-cis]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 20)</span>
		<?php
	}


	/*
	 =============================================
	 ========= Side Cart - Body Section ==========
	 =============================================
	*/

	 /**
	 * Container Height
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_ch() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= !empty( $options['scb-ch']) ? $options['scb-ch'] : 'full_screen';
		$id 		= $this->group.'-options[scb-ch]';
		?>
		<select name="<?php echo $id; ?>">
			<option value="full_screen" <?php selected($option,'full_screen'); ?>>Full Screen</option>
			<option value="auto_adjust" <?php selected($option,'auto_adjust'); ?>>Auto Adjust</option>
		</select>
		<?php
	}


	/**
	 * Container open
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_open() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= !empty( $options['scb-open']) ? $options['scb-open'] : 'right';
		$id 		= $this->group.'-options[scb-open]';
		?>
		<select name="<?php echo $id; ?>">
			<option value="right" <?php selected($option,'right'); ?>>Right</option>
			<option value="left" <?php selected($option,'left'); ?>>Left</option>
		</select>
		<?php
	}


	 /**
	 * Container Width
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_cw() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= !empty( $options['scb-cw']) ? $options['scb-cw'] : 300;
		$id 		= $this->group.'-options[scb-cw]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 300)</span>
		<?php
	}


	 /**
	 * Body- Background Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_bgc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-bgc']) ? $options['scb-bgc'] : '#ffffff';
		$id 		= $this->group.'-options[scb-bgc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/**
	 * Body- font Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_fc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-fc']) ? $options['scb-fc'] : '#000000';
		$id 		= $this->group.'-options[scb-fc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}

	/**
	 * Body- font Size
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_fs() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-fs']) ? $options['scb-fs'] : 14;
		$id 		= $this->group.'-options[scb-fs]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 25)</span>
		<?php
	}


	/**
	 * Body- Product Images Width 
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_imgw() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-imgw']) ? $options['scb-imgw'] : 35;
		$id 		= $this->group.'-options[scb-imgw]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Width in percentage. (Default: 35)</span>
		<?php
	}

	/**
	 * Body- Remove Text Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_rfc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-rfc']) ? $options['scb-rfc'] : '#000000';
		$id 		= $this->group.'-options[scb-rfc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}

	/**
	 * Body- Product Title Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_ptfc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-ptfc']) ? $options['scb-ptfc'] : '#000000';
		$id 		= $this->group.'-options[scb-ptfc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}

	/**
	 * Body- Product Title Size
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_ptfs() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-ptfs']) ? $options['scb-ptfs'] : 16;
		$id 		= $this->group.'-options[scb-ptfs]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/**
	 * Body- Product Row Border Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_prbc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-prbc']) ? $options['scb-prbc'] : '#eeeeee';
		$id 		= $this->group.'-options[scb-prbc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/**
	 * Body- Product Row Border Size
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function scb_prbs() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-prbs']) ? $options['scb-prbs'] : 1;
		$id 		= $this->group.'-options[scb-prbs]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 1)</span>
		<?php
	}



	/**
	 * Body- Empty cart Image
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */

	public function scb_empimg() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scb-empimg']) ? $options['scb-empimg'] : '';
		$id 		= $this->group.'-options[scb-empimg]';
		$this->upload_image_markup($id,$option);
		?>
		<span class="description">Leave empty , for plain text.</span>
		<?php
	}

	/*
	 =============================================
	 ========= Side Cart - Footer Section ==========
	 =============================================
	*/

	/**
	 * Footer- Background Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	*/
	public function scf_bgc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scf-bgc']) ? $options['scf-bgc'] : '#ffffff';
		$id 		= $this->group.'-options[scf-bgc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/**
	 * Footer- Buttons Margin
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	*/
	public function scf_bm() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scf-bm']) ? $options['scf-bm'] : 4;
		$id 		= $this->group.'-options[scf-bm]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 4)</span>
		<?php
	}


	/**
	 * Button Theme Styling
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	*/

	public function scf_btn_ts() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scf-btn-ts']) ? $options['scf-btn-ts'] : 'false';
		$id 		= $this->group.'-options[scf-btn-ts]';
		?>
		<input type="hidden" name="<?php echo $id; ?>" value="false">
		<input type="checkbox" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="1" <?php checked($option, 1); ?> />
		<label for="<?php echo $id; ?>">Use Default Theme Styling.</label><?php
	}


	/**
	 * Button Background Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	*/
	public function scf_btn_bgc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scf-btn-bgc']) ? $options['scf-btn-bgc'] : '#777';
		$id 		= $this->group.'-options[scf-btn-bgc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/**
	 * Button text Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	*/
	public function scf_btn_tc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scf-btn-tc']) ? $options['scf-btn-tc'] : '#fff';
		$id 		= $this->group.'-options[scf-btn-tc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}



	/**
	 * Button Padding
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	*/
	public function scf_btn_pd() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['scf-btn-pd']) ? $options['scf-btn-pd'] : '5';
		$id 		= $this->group.'-options[scf-btn-pd]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 5)</span>
		<?php
	}


	/*
	 =============================================
	 ============= Cart Basket - Section =========
	 =============================================
	*/

	/**
	 * Basket Position
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_pos() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-pos']) ? $options['bk-pos'] : 'bottom_fixed';
		$id 		= $this->group.'-options[bk-pos]';
		?>
		<select name="<?php echo $id; ?>">
			<option value="top" <?php selected($option,'top'); ?>>Top</option>
			<option value="top_fixed" <?php selected($option,'top_fixed'); ?>>Top Fixed</option>
			<option value="bottom_fixed" <?php selected($option,'bottom_fixed'); ?>>Bottom Fixed</option>
		<?php
	}



	/**
	 * Custom basket icon
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_cubi() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-cubi']) ? $options['bk-cubi'] : '';
		$id 		= $this->group.'-options[bk-cubi]';
		$this->upload_image_markup($id,$option);
	}



	/**
	 * Basket icon type
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_bit() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-bit']) ? $options['bk-bit'] : 'xoo-wsc-icon-basket1';
		$id 		= $this->group.'-options[bk-bit]';
		?>
		<select class="xoo-wsc-bk-icon" name="<?php echo $id; ?>">
			<option value="xoo-wsc-icon-basket1" <?php selected($option,'xoo-wsc-icon-basket1'); ?>>&#xe904; Basket Icon 1</option>
			<option value="xoo-wsc-icon-basket2" <?php selected($option,'xoo-wsc-icon-basket2'); ?>>&#xe905; Basket Icon 2</option>
			<option value="xoo-wsc-icon-basket3" <?php selected($option,'xoo-wsc-icon-basket3'); ?>>&#xe906; Basket Icon 3</option>
			<option value="xoo-wsc-icon-basket4" <?php selected($option,'xoo-wsc-icon-basket4'); ?>>&#xe902; Basket Icon 4</option>
			<option value="xoo-wsc-icon-basket5" <?php selected($option,'xoo-wsc-icon-basket5'); ?>>&#xe901; Basket Icon 5</option>
			<option value="xoo-wsc-icon-basket6" <?php selected($option,'xoo-wsc-icon-basket6'); ?>>&#xe903; Basket Icon 6</option>
		</select>
		<?php
	}

	/**
	 * Basket Background Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_bbgc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-bbgc']) ? $options['bk-bbgc'] : '#ffffff';
		$id 		= $this->group.'-options[bk-bbgc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/**
	 * Basket Icon Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_bfc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-bfc']) ? $options['bk-bfc'] : '#000000';
		$id 		= $this->group.'-options[bk-bfc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}

	/**
	 * Basket Icon Size
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_bfs() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-bfs']) ? $options['bk-bfs'] : 35;
		$id 		= $this->group.'-options[bk-bfs]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 35)</span>
		<?php
	}

	/**
	 * Count BG Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_cbgc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-cbgc']) ? $options['bk-cbgc'] : '#cc0086';
		$id 		= $this->group.'-options[bk-cbgc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/**
	 * Count Text Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function bk_cfc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['bk-cfc']) ? $options['bk-cfc'] : '#ffffff';
		$id 		= $this->group.'-options[bk-cfc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/*
	 =============================================
	 ============= Suggested Product - Section =========
	 =============================================
	*/


	/**
	 * Position
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sp_pos() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sp-pos']) ? $options['sp-pos'] : '';
		$id 		= $this->group.'-options[sp-pos]';
		?>
		<select name="<?php echo $id; ?>">
			<option value="above_totals" <?php selected($option,'above_totals'); ?>> Above Totals</option>
			<option value="at_bottom" <?php selected($option,'at_bottom'); ?>>At bottom</option>
		</select>
		<?php
	}


	/**
	 * BG Color
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sp_bgc() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sp-bgc']) ? $options['sp-bgc'] : '#fff';
		$id 		= $this->group.'-options[sp-bgc]';
		?>
		<input type="text" class="color-field" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<?php
	}


	/**
	 * Image size
	 *
	 * @since 		1.0.0
	 * @return 		mixed 			The settings field
	 */
	public function sp_imgw() {

		$options 	= get_option( $this->group . '-options' );
		$option 	= isset( $options['sp-imgw']) ? $options['sp-imgw'] : 75;
		$id 		= $this->group.'-options[sp-imgw]';
		?>
		<input type="number" id="<?php echo $id; ?>" name="<?php echo $id; ?>" value="<?php echo $option; ?>" />
		<span class="description">Size in px (Default: 75)</span>
		<?php
	}

}