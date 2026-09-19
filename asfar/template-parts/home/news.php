<?php
$section = get_field( 'news_section' ) ?: array();
$news = asfar_news_query( true );
?>
<section class="mt-dk-news" id="dkNews">
	<span id="news"></span>
	<div class="mt-dk-wrap">
		<h2 class="mt-dk-label mt-dk-label--terra mt-dk-rise"><?php echo esc_html( $section['heading'] ?? '' ); ?></h2>
		<div class="mt-dk-news__rail mt-dk-rise">
			<div class="mt-dk-news__strip">
				<?php while ( $news->have_posts() ) : ?>
					<?php $news->the_post(); ?>
					<?php get_template_part( 'template-parts/news/card', null, array( 'home' => true ) ); ?>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			</div>
		</div>
		<div class="mt-dk-news__foot">
			<?php get_template_part( 'template-parts/arrows' ); ?>
			<?php asfar_button( $section['button'] ?? array() ); ?>
		</div>
	</div>
	<?php get_template_part( 'template-parts/wave' ); ?>
</section>
