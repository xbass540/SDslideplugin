<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo('charset'); ?>">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta http-equiv="X-UA-Compatible" content="ie=edge">

  <?php wp_head(); ?>
  
</head>
<body <?php body_class();?>>

  <header>
    <section class="top-header">
        <div class="row justify-content-center align-items-center business-contact-details">
       <div class="text-center col-12 col-xl-2"> <?php the_custom_logo(); ?> </div>
          <div class="business-tel text-center col-12 col-xl-8"><?php wp_nav_menu( array( 'theme_location' => 'my_main_menu' ) ); ?></div>
          <div class="text-center col-12 col-xl-2 social-top"><a href="/my-account"><i class="fa fa-user account-icon" aria-hidden="true"></i></a> <a href="/cart"> <i class="fa fa-cart-arrow-down cart-icon" aria-hidden="true"></i></a></div>
        </div>
    </section>

    <section class="row justify-content-center align-items-center nav-header"> <?php dynamic_sidebar('smartslider_area_1'); ?></div> </section>

  </header>
