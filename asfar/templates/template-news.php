<?php
/** Template Name: ASFAR News */
if ( ! function_exists( 'get_field' ) ) {
	get_template_part( 'template-parts/content-fallback' );
	return;
}
get_header();
?>
<main id="top">
	<section class="mt-news mt-newspage mt-section">
		<div class="mt-wrap">
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="mt-teampage__head">
				<h1 class="mt-statement mt-reveal"><?php the_title(); ?></h1>
				<p class="mt-news__lede mt-teampage__lede mt-reveal"><?php echo asfar_lines( get_field( 'introduction' ) ); ?></p>
				</div>
				<?php $news = asfar_news_query(); ?>
				<div class="mt-newspage__grid">
					<?php while ( $news->have_posts() ) : $news->the_post(); ?>
						<?php get_template_part( 'template-parts/news/card' ); ?>
					<?php endwhile; ?>
				</div>
				<?php
				echo wp_kses_post( (string) paginate_links( array( 'total' => $news->max_num_pages, 'current' => max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) ) ) ) );
				wp_reset_postdata();
				?>
			<?php endwhile; ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
