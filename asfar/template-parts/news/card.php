<?php
$home = ! empty( $args['home'] );
$url = get_field( 'external_url' ) ?: get_permalink();
$date = get_field( 'display_date' ) ?: get_the_date();
?>
<?php if ( $home ) : ?>
	<a class="mt-dk-newscard" href="<?php echo esc_url( $url ); ?>">
		<p class="mt-dk-newscard__date"><?php echo esc_html( $date ); ?></p>
		<span class="mt-dk-newscard__ph">
			<?php the_post_thumbnail( 'large', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
			<span class="mt-dk-newscard__grad" aria-hidden="true"></span>
			<h3 class="mt-dk-newscard__title"><?php echo esc_html( get_the_title() ); ?></h3>
		</span>
	</a>
<?php else : ?>
	<article class="mt-news__card mt-reveal">
		<figure class="mt-news__thumb">
			<?php the_post_thumbnail( 'large', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
		</figure>
		<div class="mt-news__body">
			<span class="mt-news__pill"><?php echo esc_html( $date ); ?></span>
			<h3 class="mt-news__title"><?php echo esc_html( get_the_title() ); ?></h3>
		</div>
		<a class="mt-news__link" href="<?php echo esc_url( $url ); ?>">
			<span><?php echo esc_html( get_the_title() ); ?></span>
		</a>
	</article>
<?php endif; ?>
