<?php
/**
 * Template Name: General Template
 * Template Post Type: post, page
 *
 * @package WordPress
 * @subpackage Twenty_Twenty
 * @since Twenty Twenty 1.0
 */
?>


<?php get_header(); ?>

<div class="container">
<main id="site-content" role="main">

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

</main><!-- #site-content -->
</div>
<?php get_footer(); ?>
