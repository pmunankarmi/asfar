<?php
$section = asfar_section( 'news' );
$news = asfar_news_query( true );
?>
<section class="dk-news" id="dkNews">
	<span id="news"></span>
	<div class="dk-wrap">
		<h2 class="dk-label dk-label--terra dk-rise"><?php echo esc_html( $section['heading'] ?? '' ); ?></h2>
		<div class="dk-news__rail dk-rise">
			<div class="dk-news__strip">
				<?php while ( $news->have_posts() ) : ?>
					<?php $news->the_post(); ?>
					<?php get_template_part( 'template-parts/news/card', null, array( 'home' => true ) ); ?>
				<?php endwhile; ?>
				<?php wp_reset_postdata(); ?>
			</div>
		</div>
		<div class="dk-news__foot">
			<?php get_template_part( 'template-parts/arrows' ); ?>
			<?php asfar_button( $section['button'] ?? array() ); ?>
		</div>
	</div>
	<?php get_template_part( 'template-parts/wave' ); ?>
</section>
