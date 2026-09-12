<?php
/** Template Name: ASFAR FAQ */
if ( ! function_exists( 'get_field' ) ) {
	get_template_part( 'template-parts/content-fallback' );
	return;
}
get_header();
?>
<main id="mt-top" class="mt-asfar-content">
	<section class="mt-dk-faq">
		<div class="mt-dk-wrap">
			<?php while ( have_posts() ) : the_post(); ?>
				<h1 class="mt-dk-h2"><?php the_title(); ?></h1>
				<p><?php echo asfar_lines( get_field( 'introduction' ) ); ?></p>
				<div class="mt-dk-faq__list" id="mt-dkFaq">
				<?php get_template_part( 'template-parts/faq-list', null, array( 'page_id' => get_the_ID() ) ); ?>
				</div>
			<?php endwhile; ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
