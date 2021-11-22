<?php get_header(); ?>

<div class="container">
<div class="content-area">
  <section class="top-header">
      <div class="row justify-content-center align-items-center business-contact-details">
        <div class="business-email text-center col-12 col-md-3 "><?php
if ( have_posts() ) {
	while ( have_posts() ) {
		the_post(); ?>

<p>	post here</p>

<?php	} // end while
} // end if
?></div>

      </div>
  </section>


</div>
</div>
<?php get_footer(); ?>
