<?php
get_header();
?>
<div class="container">
<div class="content-area">
<section class="top-header">
    <div class="row ">
      <div class=" col-12 col-md-12 ">
<?php

if ( have_posts() ):
	while ( have_posts() ): the_post();

?>
<p>	<?php the_content(); ?></p>

<?php
endwhile;
else:
?>

<p>There is nothng here</p>

<?php endif; ?>
      </div>

    </div>
</section>
</div>
</div>
<?php
get_footer();

?>
