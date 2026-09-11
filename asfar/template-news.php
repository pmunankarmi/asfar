<?php
/** Template Name: ASFAR News */
get_header();
?>
<main id="top">
	<section class="news section">
		<div class="wrap">
			<?php while ( have_posts() ) : the_post(); ?>
				<h1 class="statement reveal"><?php the_title(); ?></h1>
				<p class="news__lede"><?php echo asfar_lines( asfar_value( 'introduction' ) ); ?></p>
				<?php $news = asfar_news_query(); ?>
				<div class="news__grid">
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
