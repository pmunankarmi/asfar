<?php
$slides = asfar_rows( 'banner_slides' );
$animation = asfar_rows( 'banner_animation' );
$marks = array();
foreach ( $animation['stops'] ?? array() as $stop ) {
	$marks[] = array( 'name' => $stop['name'], 'cta' => $stop['button']['title'] ?? '', 'href' => asfar_resolve_url( $stop['button']['url'] ?? '' ) );
}
if ( ! $slides ) {
	return;
}
?>
<section class="hero" id="heroSlider" data-morphmarks="<?php echo esc_attr( wp_json_encode( $marks ) ); ?>">
	<div class="hero__pin">
		<div class="dk-film">
			<div class="hero__scenes" aria-hidden="true">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<?php $slide = wp_parse_args( $slide, array( 'image' => 0, 'eyebrow' => '', 'heading' => '', 'highlight' => '', 'video' => 0, 'video_label' => '', 'button' => array() ) ); ?>
					<div class="hero__scene <?php echo 0 === $index ? 'is-active' : ''; ?>">
						<?php echo wp_get_attachment_image( $slide['image'], 'full', false, array( 'class' => 'hero__img', 'alt' => '', 'loading' => 'eager' ) ); ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php if ( ! empty( $animation['enabled'] ) && $marks ) : ?>
				<canvas class="hero__morph-canvas" id="heroMorphCanvas" data-frames="273" aria-hidden="true"></canvas>
			<?php endif; ?>
			<div class="hero__scrim" aria-hidden="true"></div>
		</div>
		<div class="wrap hero__in">
			<div class="hero__slides">
				<?php foreach ( $slides as $index => $slide ) : ?>
					<?php $slide = wp_parse_args( $slide, array( 'image' => 0, 'eyebrow' => '', 'heading' => '', 'highlight' => '', 'video' => 0, 'video_label' => '', 'button' => array() ) ); ?>
					<div class="hero__slide <?php echo 0 === $index ? 'is-active' : ''; ?>">
						<p class="hero__kicker">
							<?php echo esc_html( $slide['eyebrow'] ); ?>
							<span class="hero__kicker-rule"></span><span class="hero__kicker-dia" aria-hidden="true"></span>
						</p>
						<<?php echo 0 === $index ? 'h1' : 'h2'; ?> class="hero__title">
							<?php echo asfar_lines( $slide['heading'] ); ?>
							<?php if ( $slide['highlight'] ) : ?>
								<br><b><?php echo asfar_lines( $slide['highlight'] ); ?></b>
							<?php endif; ?>
						</<?php echo 0 === $index ? 'h1' : 'h2'; ?>>

					</div>
				<?php endforeach; ?>
			</div>
			<div class="hero__cta hero__cta--fixed">
				<?php $buttons = $slides[0]; ?>
				<?php if ( ! empty( $buttons['video'] ) && ! empty( $buttons['video_label'] ) ) : ?>
					<button type="button" class="btn btn--gold" data-video="<?php echo esc_url( asfar_attachment_url( $buttons['video'] ) ); ?>"><?php echo esc_html( $buttons['video_label'] ); ?></button>
				<?php endif; ?>
				<?php asfar_button( $buttons['button'] ?? array(), 'btn btn--line-light' ); ?>
			</div>
			<?php if ( ! empty( $animation['enabled'] ) && $marks ) : ?>
				<div class="hero__morphcap">
					<p class="hero__morph-lead"><?php echo asfar_lines( $animation['heading'] ); ?></p>
					<p class="hero__morph-over"><?php echo esc_html( $animation['subtitle'] ); ?></p>
					<h2 class="hero__title" id="heroMorphName"><?php echo esc_html( $marks[0]['name'] ); ?></h2>
				</div>
			<?php endif; ?>
		</div>
		<div class="dk-dashes" id="dkHeroDashes" aria-label="<?php echo esc_attr( asfar_option( 'banner_label' ) ); ?>">
			<?php $navigation = ! empty( $animation['enabled'] ) && $marks ? $marks : $slides; ?>
			<?php foreach ( $navigation as $index => $item ) : ?>
				<button class="hero__dot <?php echo 0 === $index ? 'is-active' : ''; ?>" type="button" data-i="<?php echo esc_attr( $index ); ?>" aria-label="<?php echo esc_attr( $item['name'] ?? $item['heading'] ?? '' ); ?>" aria-pressed="<?php echo 0 === $index ? 'true' : 'false'; ?>"></button>
			<?php endforeach; ?>
		</div>
		<?php get_template_part( 'template-parts/wave' ); ?>
	</div>
</section>
