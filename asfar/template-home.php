<?php
/**
 * Template Name: ASFAR Homepage
 *
 * Each frontend section has its own template part and ACF field group.
 */
get_header();
while ( have_posts() ) :
	the_post();
	?>
	<main id="top">
		<?php get_template_part( 'template-parts/home/banner' ); ?>
		<?php get_template_part( 'template-parts/home/about' ); ?>
		<?php get_template_part( 'template-parts/home/vision' ); ?>
		<?php get_template_part( 'template-parts/home/impact' ); ?>
		<?php get_template_part( 'template-parts/home/projects' ); ?>
		<?php get_template_part( 'template-parts/home/investments' ); ?>
		<?php get_template_part( 'template-parts/home/news' ); ?>
		<?php get_template_part( 'template-parts/home/team' ); ?>
		<?php get_template_part( 'template-parts/home/partners' ); ?>
		<?php get_template_part( 'template-parts/home/faq' ); ?>
	</main>
	<?php
endwhile;
get_footer();
