<?php
/** Standard news article. */
if ( ! function_exists( 'get_field' ) ) {
	get_template_part( 'template-parts/content-fallback' );
	return;
}
get_header();
?>
<main id="mt-top">
	<?php while ( have_posts() ) : the_post(); ?>
		<article class="mt-article">
			<div class="mt-wrap mt-article__grid">
				<div class="mt-article__body">
					<header class="mt-article__head">
						<span class="mt-article__pill"><?php echo esc_html( get_field( 'category_label' ) ); ?></span>
						<h1 class="mt-article__title"><?php the_title(); ?></h1>
						<p class="mt-article__date"><?php echo esc_html( get_field( 'display_date' ) ?: get_the_date() ); ?></p>
					</header>
					<figure class="mt-article__figure"><?php the_post_thumbnail( 'large' ); ?></figure>
					<?php while ( have_rows( 'paragraphs' ) ) : the_row(); ?>
						<p class="<?php echo 1 === get_row_index() ? 'mt-article__lede' : ''; ?>"><?php echo asfar_lines( get_sub_field( 'text' ) ); ?></p>
					<?php endwhile; ?>
					<?php the_content(); ?>
					<?php if ( get_field( 'external_url' ) ) : ?>
						<a href="<?php echo esc_url( get_field( 'external_url' ) ); ?>"><?php the_title(); ?></a>
					<?php endif; ?>
				</div>
				<aside class="mt-article__aside" aria-label="<?php echo esc_attr( asfar_option( 'archive_title' ) ); ?>">
					<div class="mt-article__more">
						<?php $related = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 4, 'post__not_in' => array( get_the_ID() ), 'lang' => asfar_language() ) ); ?>
						<?php while ( $related->have_posts() ) : $related->the_post(); ?>
							<a class="mt-article__morecard" href="<?php echo esc_url( get_field( 'external_url' ) ?: get_permalink() ); ?>">
								<?php the_post_thumbnail( 'medium' ); ?>
								<span><?php the_title(); ?></span>
							</a>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
					<a class="mt-article__back" href="<?php echo esc_url( asfar_resolve_url( 'news.html' ) ); ?>"><?php echo esc_html( asfar_option( 'archive_title' ) ); ?></a>
				</aside>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
