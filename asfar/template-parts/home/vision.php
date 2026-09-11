<?php
$section = asfar_section( 'vision' );
$cards = $section['cards'] ?? array();
?>
<section class="dk-vision-band">
	<div class="dk-wrap">
		<div class="dk-vision__row" id="philosophy">
			<div class="dk-rise">
				<h2 class="dk-label"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
			</div>
			<div class="dk-vision dk-rise">
				<?php if ( $cards ) : ?>
					<div class="dk-vision__tan">
						<span class="dk-vision__motif" aria-hidden="true"></span>
						<h3><?php echo esc_html( $cards[0]['heading'] ); ?></h3>
						<p><?php echo asfar_lines( $cards[0]['description'] ); ?></p>
					</div>
				<?php endif; ?>
				<div class="dk-vision__col">
					<?php foreach ( array_slice( $cards, 1 ) as $card ) : ?>
						<div class="dk-card dk-vision__card">
							<span class="dk-vision__dia" aria-hidden="true"></span>
							<h3><?php echo esc_html( $card['heading'] ); ?></h3>
							<p><?php echo asfar_lines( $card['description'] ); ?></p>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php get_template_part( 'template-parts/wave', null, array( 'color' => '#154751', 'background' => '#F2ECE3' ) ); ?>
