<?php
/** Individual project details. */
if ( ! function_exists( 'get_field' ) ) {
	get_template_part( 'template-parts/content-fallback' );
	return;
}
get_header();
?>
<main id="top" class="mt-asfar-content mt-wrap">
	<?php while ( have_posts() ) : the_post(); ?>
		<article>
			<p><?php echo esc_html( get_field( 'company' ) ); ?></p>
			<h1><?php the_title(); ?></h1>
			<figure class="mt-article__figure"><?php the_post_thumbnail( 'large' ); ?></figure>
			<h2><?php echo esc_html( get_field( 'headline' ) ); ?></h2>
			<p><?php echo asfar_lines( get_field( 'summary' ) ); ?></p>
			<dl class="mt-asfar-project-statistics">
				<?php while ( have_rows( 'statistics' ) ) : the_row(); ?>
					<div>
						<dt><?php echo esc_html( get_sub_field( 'label' ) ); ?></dt>
						<dd><?php echo asfar_statistic_value( get_sub_field( 'value' ) ); ?></dd>
					</div>
				<?php endwhile; ?>
			</dl>
			<figure class="mt-article__figure"><?php echo wp_get_attachment_image( get_field( 'detail_image' ), 'large' ); ?></figure>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
