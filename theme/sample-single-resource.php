<?php get_header(); ?>

<main id="main" class="site-main" role="main">
	<?php
	while ( have_posts() ) : the_post();

		// Check if the post is being displayed on a singular page
		if (is_singular()) {
			echo do_shortcode('[display_resource]');
		} else {
			echo do_shortcode('[display_resource_list]');
		}

		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile; // End of the loop.
	?>
</main><!-- #main -->

<?php get_footer(); ?>