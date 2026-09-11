<?php
$home = ! empty( $args['home'] );
$url = asfar_value( 'external_url' ) ?: get_permalink();
$date = asfar_value( 'display_date' ) ?: get_the_date();
?>
<?php if ( $home ) : ?>
	<a class="dk-newscard" href="<?php echo esc_url( $url ); ?>">
		<p class="dk-newscard__date"><?php echo esc_html( $date ); ?></p>
		<span class="dk-newscard__ph">
			<?php the_post_thumbnail( 'large', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
			<span class="dk-newscard__grad" aria-hidden="true"></span>
			<h3 class="dk-newscard__title"><?php echo esc_html( get_the_title() ); ?></h3>
		</span>
	</a>
<?php else : ?>
	<article class="news__card reveal">
		<figure class="news__thumb">
			<?php the_post_thumbnail( 'large', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
		</figure>
		<div class="news__body">
			<span class="news__pill"><?php echo esc_html( $date ); ?></span>
			<h3 class="news__title"><?php echo esc_html( get_the_title() ); ?></h3>
		</div>
		<a class="news__link" href="<?php echo esc_url( $url ); ?>">
			<span><?php echo esc_html( get_the_title() ); ?></span>
		</a>
	</article>
<?php endif; ?>
