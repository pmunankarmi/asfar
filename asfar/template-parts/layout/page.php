<?php
/** Standard page template. */
if ( ! function_exists( 'get_field' ) ) {
	get_template_part( 'template-parts/content-fallback' );
	return;
}
get_header();
?>
<main id="top" class="mt-asfar-content mt-wrap">
	<?php while ( have_posts() ) : the_post(); ?>
		<h1><?php the_title(); ?></h1>
		<p><?php echo asfar_lines( get_field( 'introduction' ) ); ?></p>
		<?php while ( have_rows( 'paragraphs' ) ) : the_row(); ?>
			<p><?php echo asfar_lines( get_sub_field( 'text' ) ); ?></p>
		<?php endwhile; ?>
		<?php the_content(); ?>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
