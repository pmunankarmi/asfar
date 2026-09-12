<?php
/** Individual team member. */
if ( ! function_exists( 'get_field' ) ) {
	get_template_part( 'template-parts/content-fallback' );
	return;
}
get_header();
?>
<main id="top" class="mt-asfar-content mt-wrap">
	<?php while ( have_posts() ) : the_post(); ?>
		<article>
			<h1><?php the_title(); ?></h1>
			<p><?php echo esc_html( get_field( 'job_title' ) ); ?></p>
			<?php the_post_thumbnail( 'large' ); ?>
			<p><?php echo asfar_lines( get_field( 'biography' ) ); ?></p>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
