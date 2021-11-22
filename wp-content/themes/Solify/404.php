<?php
/**
 * The main template file
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 *
 * @link https://solomondesigns.co.uk
 *
 * @package WordPress
 * @subpackage Solomondesigns WP Theme
 * @since SDWPT 1
 */
get_header();
?>
<div class="container">
<div class="content-area">
<section class="top-header">
    <div class="row ">
      <div class=" col-12 ">



<h2>Page not found</h2>
<h3>Use the search box to find what you are looking for</h3>

<p><?php get_search_form(); ?></p>
      </div>

    </div>
</section>
</div>
</div>
<?php
get_footer();

?>
