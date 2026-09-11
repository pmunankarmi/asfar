<?php
/** Standard news article. */
get_header();
?>
<main id="top">
	<?php while ( have_posts() ) : the_post(); ?>
		<article class="article">
			<div class="wrap article__grid">
				<div class="article__body">
					<header class="article__head">
						<span class="article__pill"><?php echo esc_html( asfar_value( 'category_label' ) ); ?></span>
						<h1 class="article__title"><?php the_title(); ?></h1>
						<p class="article__date"><?php echo esc_html( asfar_value( 'display_date' ) ?: get_the_date() ); ?></p>
					</header>
					<figure class="article__figure"><?php the_post_thumbnail( 'large' ); ?></figure>
					<?php foreach ( asfar_rows( 'paragraphs' ) as $index => $paragraph ) : ?>
						<p class="<?php echo 0 === $index ? 'article__lede' : ''; ?>"><?php echo asfar_lines( $paragraph['text'] ); ?></p>
					<?php endforeach; ?>
					<?php the_content(); ?>
					<?php if ( asfar_value( 'external_url' ) ) : ?>
						<a href="<?php echo esc_url( asfar_value( 'external_url' ) ); ?>"><?php the_title(); ?></a>
					<?php endif; ?>
				</div>
				<aside class="article__aside" aria-label="<?php echo esc_attr( asfar_option( 'archive_title' ) ); ?>">
					<div class="article__more">
						<?php $related = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 4, 'post__not_in' => array( get_the_ID() ), 'lang' => asfar_language() ) ); ?>
						<?php while ( $related->have_posts() ) : $related->the_post(); ?>
							<a class="article__morecard" href="<?php echo esc_url( asfar_value( 'external_url' ) ?: get_permalink() ); ?>">
								<?php the_post_thumbnail( 'medium' ); ?>
								<span><?php the_title(); ?></span>
							</a>
						<?php endwhile; wp_reset_postdata(); ?>
					</div>
					<a class="article__back" href="<?php echo esc_url( asfar_resolve_url( 'news.html' ) ); ?>"><?php echo esc_html( asfar_option( 'archive_title' ) ); ?></a>
				</aside>
			</div>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
