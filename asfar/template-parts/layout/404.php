<?php
/** Missing page. */
if ( ! function_exists( 'get_field' ) ) {
	get_template_part( 'template-parts/content-fallback' );
	return;
}
get_header();
?>
<main id="top" class="mt-asfar-content mt-wrap">
	<h1><?php echo esc_html( asfar_option( 'not_found' ) ); ?></h1>
	<a href="<?php echo esc_url( asfar_home_url() ); ?>"><?php echo esc_html( asfar_option( 'back_home' ) ); ?></a>
</main>
<?php get_footer(); ?>
