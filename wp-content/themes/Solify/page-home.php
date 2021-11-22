<?php get_header(); ?>
<div class="content-area">

<main>
<div class="container">

    <section class="row justify-content-center home-slideshow">
      <?php echo do_shortcode('[smartslider3 slider=2]'); ?>
    </section>

    <div class="row justify-content-center ">
      <div class="col-12 home-top-header">

        <p>	<?php the_content(); ?></p>

      </div>
    </div>

    <section class="product-categories-home">

     <div class="row justify-content-center">
      <div class="col-12 col-md-12 category-names"><p><?php $id = 24;if( $term = get_term_by( 'id', $id, 'product_cat' ) ){ echo $term->name;} ?></p></div>
     <div class="col-12 col-md-12"><?php echo do_shortcode('[products limit="8" columns="4" category="swimwear"]'); ?></div>

     <!-- 2nd category products -->
     <div class="col-12 col-md-12 category-names"><p><?php $id = 21;if( $term = get_term_by( 'id', $id, 'product_cat' ) ){ echo $term->name;} ?></p></div>
     <div class="col-12 col-md-12"><?php echo do_shortcode('[products limit="4" columns="4" category="maternity-swimwear"]'); ?></div>

     <!-- 3rd category products -->
     <div class="col-12 col-md-12 category-names"><p><?php $id = 22;if( $term = get_term_by( 'id', $id, 'product_cat' ) ){ echo $term->name;} ?></p></div>
     <div class="col-12 col-md-12"><?php echo do_shortcode('[products limit="4" columns="4" category="mens-swimwear"]'); ?></div>

     <!-- 4th category products -->
     <div class="col-12 col-md-12 category-names"><p><?php $id = 19;if( $term = get_term_by( 'id', $id, 'product_cat' ) ){ echo $term->name;} ?></p></div>
     <div class="col-12 col-md-12"><?php echo do_shortcode('[products limit="4" columns="4" category="girls-swimwear"]'); ?></div>

    </div>

    </section>

    <section>
      <div class="row justify-content-center quality-specs">
        <div class="col-4 col-md-4 "><div class="box-home text-center"><i class="fa fa-shield" aria-hidden="true"></i> <p>EXTRA QUALITY</p> </div></div>
        <div class="col-4 col-md-4 "><div class="box-home text-center"><i class="fa fa-money" aria-hidden="true"></i> <p>SECURE PAYMENT</p></div></div>
        <div class="col-4 col-md-4 "><div class="box-home text-center"><i class="fa fa-file-text-o" aria-hidden="true"></i><p>2 YEAR WARRANTY</p></div></div>
      </div>
    </section>


  </div>
</main>

</div>

<?php get_footer(); ?>
