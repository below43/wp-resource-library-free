<?php get_header(); ?>

<main id="main" class="site-main" role="main">
	<header class="page-header">
		<h1 class="page-title"><?php single_cat_title(); ?></h1>
	</header>
	<?php
	while ( have_posts() ) : the_post();

		if (get_post_type() == 'resource') {
			echo do_shortcode('[display_resource_list]');
		} else {
			get_template_part( 'template-parts/content', get_post_type() );
		}

		// If comments are open or we have at least one comment, load up the comment template.
		if ( comments_open() || get_comments_number() ) :
			comments_template();
		endif;

	endwhile; // End of the loop.
	?>
</main><!-- #main -->

<?php get_footer(); ?>