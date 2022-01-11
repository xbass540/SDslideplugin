<?php

//import data

//disable proteus copyright
add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );

function ocdi_import_files() {
    return array(
        array(
            'import_file_name'           => 'Solify Demo Import',
            'categories'                 => array( 'Category 1', 'Category 2' ),
            'import_file_url'            => 'https://eshop.solomondesigns.co.uk/ocdi/demo-content.xml',
            'import_widget_file_url'     => 'https://eshop.solomondesigns.co.uk/ocdi/widgets.wie',
            'import_customizer_file_url' => 'https://eshop.solomondesigns.co.uk/ocdi/customizer.dat',
            'import_preview_image_url'   => 'https://eshop.solomondesigns.co.uk/ocdi/preview_solify.jpg',
            'import_notice'              => __( 'After you import this demo, you will have to setup the slider separately.', 'your-textdomain' ),
            'preview_url'                => 'https://eshop.solomondesigns.co.uk/',
        )
    );
}
add_filter( 'pt-ocdi/import_files', 'ocdi_import_files' );

//define default pages
function ocdi_after_import_setup() {
    // Assign menus to their locations.
    $main_menu = get_term_by( 'name', 'Main menu', 'nav_menu' );

    set_theme_mod( 'nav_menu_locations', array(
            'my_main_menu' => $main_menu->term_id, // replace 'main-menu' here with the menu location identifier from register_nav_menu() function
        )
    );

    // Assign front page and posts page (blog page).
    $front_page_id = get_page_by_title( 'Home' );
    $blog_page_id  = get_page_by_title( 'Blog' );

    update_option( 'show_on_front', 'page' );
    update_option( 'page_on_front', $front_page_id->ID );
    update_option( 'page_for_posts', $blog_page_id->ID );

}
add_action( 'pt-ocdi/after_import', 'ocdi_after_import_setup' );
//import data finish


//require TGM plugin
require_once get_template_directory() . '/inc/class-tgm-plugin-activation.php';
require_once get_template_directory() . '/inc/required-plugins.php';

//load all scripts here
function load_scripts(){
    wp_enqueue_script('bootstrap-js', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/js/bootstrap.min.js', array('jquery') , '4.4.1', false);//true sends the file before </body>

  wp_enqueue_style('bootstrap-css', 'https://stackpath.bootstrapcdn.com/bootstrap/4.4.1/css/bootstrap.min.css' , array(), '4.4.1', 'all');

    wp_enqueue_script('font-awesome-js', 'https://use.fontawesome.com/64b8575d02.js', array() , '4.7.0', true);//true sends the file before </body>



wp_enqueue_style('template', get_template_directory_uri().'/css/template.css', array(), '1.0', 'all', true);


}
add_action('wp_enqueue_scripts', 'load_scripts');

//eshop sidebars- This function creates the widget areas and enables widget option in WP backend
add_action('widgets_init','eshop_sidebars');
function eshop_sidebars(){
  register_sidebar(
    array(
      'name' => 'Product Page Sidebar',
      'id' => 'sidebar-1',
      'description' => 'This is the product page sidebar',
      'before_widget' => '<div class="widget-wrapper">',
      'after_widget' => '</div>',
      'before_title' => '<h2 class="widget-title">',
      'after_title' => '</h2>'

    )
  );
  //eshop sidebar
  register_sidebar(
    array(
      'name' => 'Eshop Page Sidebar',
      'id' => 'sidebar-2',
      'description' => 'This is the eshop page sidebar',
      'before_widget' => '<div class="widget-wrapper">',
      'after_widget' => '</div>',
      'before_title' => '<h2 class="widget-title">',
      'after_title' => '</h2>'

    )
  );
  //footer sidebars
  register_sidebar(
    array(
      'name' => 'Footer  Sidebar',
      'id' => 'sidebar-3',
      'description' => 'This is the footer sidebar',
      'before_widget' => '<div class="widget-wrapper">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="widget-title">',
      'after_title' => '</h4>'

    )
  );
}


//top footer sidebars
add_action('widgets_init','eshop_sidebars_top_footer');
function eshop_sidebars_top_footer(){
    //top footer sidebars
  register_sidebar(
    array(
      'name' => 'Top Footer Sidebar',
      'id' => 'sidebar-4',
      'description' => 'This is the top footer sidebar',
      'before_widget' => '<div class="widget-wrapper-top-footer">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="widget-title">',
      'after_title' => '</h4>'

    )
  );
}

//copyright sidebars
add_action('widgets_init','eshop_sidebars_copyright');
function eshop_sidebars_copyright(){
    //top footer sidebars
  register_sidebar(
    array(
      'name' => 'Copyright Sidebar',
      'id' => 'sidebar-5',
      'description' => 'This is the Copyright sidebar',
      'before_widget' => '<div class="widget-wrapper-copyright">',
      'after_widget' => '</div>',
      'before_title' => '<h4 class="widget-title">',
      'after_title' => '</h4>'

    )
  );
}



//configuration function
function solomon_config(){

  //register menu and positions
  register_nav_menus(
    array(
      'my_main_menu' => 'Main Menu',
      'second_menu' => 'Second Menu'
    )
  );

//add theme support
//array of parameters

function solomondesigns_custom_logo_setup() {
//define parameters array
 $defaults = array(
 'height'      => 60,
 'width'       => 68,
 'flex-height' => true,
 'flex-width'  => true,
 'header-text' => array( 'site-title', 'site-description' ),
 );
 //defines theme support custom logo hook custom-logo  taking the $defaults parameters array
 add_theme_support( 'custom-logo', $defaults );

//add post thumbnails
add_theme_support( 'post-thumbnails' );

}

//when the setup is ready run the function solomondesigns_custom_logo_setup
add_action( 'after_setup_theme', 'solomondesigns_custom_logo_setup' );


//finish theme support

}

//execute config function
add_action('after_setup_theme', 'solomon_config',0); //this action is executed whenever the adter_setup_them hook is available


//add woocmmerce theme support
function mytheme_add_woocommerce_support() {
	add_theme_support( 'woocommerce', array(
	        'product_grid'          => array(
            'default_rows'    => 3,
            'min_rows'        => 2,
            'max_rows'        => 8,
            'default_columns' => 4,
            'min_columns'     => 2,
            'max_columns'     => 5,
        ),
	) );
}
add_action( 'after_setup_theme', 'mytheme_add_woocommerce_support' );

add_theme_support( 'wc-product-gallery-zoom' );
add_theme_support( 'wc-product-gallery-lightbox' );
add_theme_support( 'wc-product-gallery-slider' );
add_theme_support( 'title-tag' );

//product page hooks
//add sale flash
add_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
//remove short description
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
//remove breadcrumbs
remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20, 0 );

//asterisc info below add to cart button
add_action('woocommerce_after_add_to_cart_form','insert_asterisc_info',3);

function insert_asterisc_info(){
  echo '<p class="asterisc-info">*Additional discounts and promotions are not applicable to this product.</p>';
}
//insert product details title
add_action('woocommerce_after_add_to_cart_form','description_title', 7);
function description_title(){
  echo '<h3 class="product_description_title">DETAILS AND QUALITY INFO</h3>';
}
//add short description here
add_action('woocommerce_after_add_to_cart_form','woocommerce_template_single_excerpt', 10);

//REMOVE META
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
//insert related products title
add_action('woocommerce_after_add_to_cart_form','frequently_products_title',12);
function frequently_products_title(){
  echo '<h3 class="frequently_description_title">FREQUENTLY BOUGHT TOGETHER</h3>';
}

//remove product summary
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs' );

//remove related products
remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );
//add trelated products to the end of page
add_action( 'woocommerce_after_single_product', 'woocommerce_output_related_products',5);

//CHECKOUT PAGE hooks
//remove get_checkout_fields
add_filter( 'woocommerce_checkout_fields' , 'custom_override_checkout_fields' );

// Our hooked in function - $fields is passed via the filter!
function custom_override_checkout_fields( $fields ) {
  unset($fields['order']['order_comments']);
  unset($fields['billing']['billing_address_2']);
  unset($fields['billing']['billing_state']);
  unset($fields['shipping']['shipping_address_2']);
  unset($fields['shipping']['shipping_state']);
  //remove labels
  $fields['billing']['billing_first_name']['label'] = '';
  $fields['billing']['billing_last_name']['label'] = '';
  $fields['billing']['billing_email']['label'] = '';
  $fields['billing']['billing_company']['label'] = '';
  $fields['billing']['billing_address_1']['label'] = '';
  $fields['billing']['billing_country']['label'] = '';
  $fields['billing']['billing_postcode']['label'] = '';
  $fields['billing']['billing_phone']['label'] = '';
  $fields['billing']['billing_city']['label'] = '';
//shipping
  $fields['shipping']['shipping_first_name']['label'] = '';
  $fields['shipping']['shipping_last_name']['label'] = '';
  $fields['shipping']['shipping_email']['label'] = '';
  $fields['shipping']['shipping_company']['label'] = '';
  $fields['shipping']['shipping_address_1']['label'] = '';
  $fields['shipping']['shipping_country']['label'] = '';
  $fields['shipping']['shipping_postcode']['label'] = '';
  $fields['shipping']['shipping_phone']['label'] = '';
  $fields['shipping']['shipping_city']['label'] = '';
  //placeholders
  $fields['billing']['billing_first_name']['placeholder'] = 'First Name';
  $fields['billing']['billing_last_name']['placeholder'] = 'Last Name';
  $fields['billing']['billing_email']['placeholder'] = 'E-mail';
  $fields['billing']['billing_company']['placeholder'] = 'Company (Optional)';
  $fields['billing']['billing_address_1']['placeholder'] = 'Address';
  $fields['billing']['billing_country']['placeholder'] = 'Country';
  $fields['billing']['billing_postcode']['placeholder'] = 'Post Code';
  $fields['billing']['billing_phone']['placeholder'] = 'Phone';
  $fields['billing']['billing_city']['placeholder'] = 'City';
  //shipping
  $fields['shipping']['shipping_first_name']['placeholder'] = 'First Name';
  $fields['shipping']['shipping_last_name']['placeholder'] = 'Last Name';
  $fields['shipping']['shipping_email']['placeholder'] = 'E-mail';
  $fields['shipping']['shipping_company']['placeholder'] = 'Company (Optional)';
  $fields['shipping']['shipping_address_1']['placeholder'] = 'Address';
  $fields['shipping']['shipping_country']['placeholder'] = 'Country';
  $fields['shipping']['shipping_postcode']['placeholder'] = 'Post Code';
  $fields['shipping']['shipping_phone']['placeholder'] = 'Phone';
  $fields['shipping']['shipping_city']['placeholder'] = 'City';
  return $fields;
}

//remove labels

//move coupon from top
remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );
//add coupon code before payment
add_action( 'woocommerce_review_order_before_submit', 'woocommerce_checkout_coupon_form', 10 );

//move email field on the top
/**
 * @snippet       Move / ReOrder Fields @ Checkout Page, WooCommerce version 3.0+
 * @how-to        Get CustomizeWoo.com FREE
 * @sourcecode    https://businessbloomer.com/?p=19571
 * @author        Rodolfo Melogli
 * @compatible    Woo 3.5.3
 * @donate $9     https://businessbloomer.com/bloomer-armada/
 */

add_filter( 'woocommerce_billing_fields', 'move_checkout_email_field', 10, 1 );

function move_checkout_email_field( $address_fields ) {
  // default priorities:
  // 'first_name' - 10
  // 'last_name' - 20
  // 'company' - 30
  // 'country' - 40
  // 'address_1' - 50
  // 'address_2' - 60
  // 'city' - 70
  // 'state' - 80
  // 'postcode' - 90
    $address_fields['billing_email']['priority'] = 5;
    return $address_fields;
}

//shipping section
add_filter( 'woocommerce_shipping_fields', 'move_shipping_email_field', 10, 1 );

function move_shipping_email_field( $shipping_address_fields ) {
  // default priorities:
  // 'first_name' - 10
  // 'last_name' - 20
  // 'company' - 30
  // 'country' - 40
  // 'address_1' - 50
  // 'address_2' - 60
  // 'city' - 70
  // 'state' - 80
  // 'postcode' - 90
    $shipping_address_fields['shipping_email']['priority'] = 5;
    return $shipping_address_fields;
}

//Max Mega Menu setup
function megamenu_add_theme_default_1587798877($themes) {
    $themes["default_1587798877"] = array(
        'title' => 'Default',
        'container_background_from' => 'rgba(255, 255, 255, 0)',
        'container_background_to' => 'rgba(255, 255, 255, 0)',
        'container_padding_left' => '10px',
        'container_padding_right' => '10px',
        'container_padding_top' => '10px',
        'container_padding_bottom' => '10px',
        'menu_item_align' => 'center',
        'menu_item_background_hover_from' => 'rgb(34, 34, 34)',
        'menu_item_background_hover_to' => 'rgb(34, 34, 34)',
        'menu_item_spacing' => '30px',
        'menu_item_link_font' => 'Verdana, Geneva, sans-serif',
        'menu_item_link_font_size' => '12px',
        'menu_item_link_color' => 'rgb(255, 255, 255)',
        'menu_item_link_color_hover' => 'rgb(241, 241, 241)',
        'menu_item_link_padding_left' => '0px',
        'menu_item_link_padding_right' => '0px',
        'menu_item_link_padding_top' => '0',
        'menu_item_link_padding_bottom' => '0',
        'panel_font_size' => '14px',
        'panel_font_color' => '#666',
        'panel_font_family' => 'inherit',
        'panel_second_level_font_color' => '#555',
        'panel_second_level_font_color_hover' => '#555',
        'panel_second_level_text_transform' => 'uppercase',
        'panel_second_level_font' => 'inherit',
        'panel_second_level_font_size' => '16px',
        'panel_second_level_font_weight' => 'bold',
        'panel_second_level_font_weight_hover' => 'bold',
        'panel_second_level_text_decoration' => 'none',
        'panel_second_level_text_decoration_hover' => 'none',
        'panel_third_level_font_color' => '#666',
        'panel_third_level_font_color_hover' => '#666',
        'panel_third_level_font' => 'inherit',
        'panel_third_level_font_size' => '14px',
        'flyout_link_size' => '14px',
        'flyout_link_color' => '#666',
        'flyout_link_color_hover' => '#666',
        'flyout_link_family' => 'inherit',
        'responsive_breakpoint' => '1024px',
        'toggle_background_from' => '#222',
        'toggle_background_to' => '#222',
        'mobile_background_from' => '#222',
        'mobile_background_to' => '#222',
        'mobile_menu_item_link_font_size' => '14px',
        'mobile_menu_item_link_color' => '#ffffff',
        'mobile_menu_item_link_text_align' => 'left',
        'mobile_menu_item_link_color_hover' => '#ffffff',
        'mobile_menu_item_background_hover_from' => '#333',
        'mobile_menu_item_background_hover_to' => '#333',
        'custom_css' => '/** Push menu onto new line **/
#{$wrap} {
    clear: both;
}',
    );
    return $themes;
}
add_filter("megamenu_themes", "megamenu_add_theme_default_1587798877");


//add some hook 

function add_some_html (){
  ?>
  <div class="test-cont">Add some text here</div>
  <?
}

add_action('dynamic_sidebar_before','add_some_html',5);
/////end hook 

function modify_content($content){
  $content = $content . 'All right reserved';
  return $content;
}

add_filter('the_content', 'modify_content');

 ?>
