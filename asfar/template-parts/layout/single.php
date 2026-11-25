<?php
/** Standard news article. */
get_header();
?>
<main id="top">
	<?php while ( have_posts() ) : the_post(); ?>
		<article class="mt-article">
			<div class="mt-wrap mt-article__grid">
				<div class="mt-article__body">
					<header class="mt-article__head">
						<a class="mt-article__back mt-article__back--top" href="<?php echo esc_url( asfar_home_url() . '#news' ); ?>"><?php echo esc_html( asfar_amv4_label( 'Back to Media Center' ) ); ?></a>
						<?php if ( function_exists( 'get_field' ) && get_field( 'category_label' ) ) : ?>
							<span class="mt-article__pill"><?php echo esc_html( get_field( 'category_label' ) ); ?></span>
						<?php endif; ?>
						<h1 class="mt-article__title"><?php the_title(); ?></h1>
						<p class="mt-article__date"><?php echo esc_html( function_exists( 'get_field' ) ? ( get_field( 'display_date' ) ?: get_the_date() ) : get_the_date() ); ?></p>
					</header>
					<figure class="mt-article__figure"><?php the_post_thumbnail( 'large' ); ?></figure>
					<?php
					$article_body = function_exists( 'get_field' ) ? get_field( 'body_content' ) : '';
					if ( $article_body ) {
						echo wp_kses_post( $article_body );
					} elseif ( function_exists( 'have_rows' ) && have_rows( 'paragraphs' ) ) {
						while ( have_rows( 'paragraphs' ) ) {
							the_row();
							$paragraph = get_sub_field( 'text' );
							if ( $paragraph ) {
								echo '<p>' . wp_kses_post( $paragraph ) . '</p>';
							}
						}
					} else {
						the_content();
					}
					?>
					<?php if ( function_exists( 'get_field' ) && get_field( 'external_url' ) ) : ?>
						<a href="<?php echo esc_url( get_field( 'external_url' ) ); ?>"><?php the_title(); ?></a>
					<?php endif; ?>
				</div>
				<aside class="mt-article__aside" aria-label="<?php echo esc_attr( asfar_option( 'archive_title' ) ); ?>">
					<div class="mt-article__more">
						<?php $related = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 4, 'post__not_in' => array( get_the_ID() ), 'lang' => asfar_language() ) ); ?>
						<?php while ( $related->have_posts() ) : $related->the_post(); ?>
							<a class="mt-article__morecard" href="<?php echo esc_url( function_exists( 'get_field' ) && get_field( 'external_url' ) ? get_field( 'external_url' ) : get_permalink() ); ?>">
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
