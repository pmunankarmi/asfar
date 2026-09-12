<?php
$animation = get_field( 'banner_animation' ) ?: array();
$marks = array();
while ( have_rows( 'banner_animation' ) ) {
	the_row();
	while ( have_rows( 'stops' ) ) {
		the_row();
		$button = get_sub_field( 'button' ) ?: array();
		$marks[] = array( 'name' => get_sub_field( 'name' ), 'cta' => $button['title'] ?? '', 'href' => asfar_resolve_url( $button['url'] ?? '' ) );
	}
}
if ( ! have_rows( 'banner_slides' ) ) { return; }
?>
<section class="mt-hero" id="heroSlider" data-morphmarks="<?php echo esc_attr( wp_json_encode( $marks ) ); ?>">
	<div class="mt-hero__pin">
		<div class="mt-dk-film">
			<div class="mt-hero__scenes" aria-hidden="true">
				<?php while ( have_rows( 'banner_slides' ) ) : the_row(); ?>
					<div class="mt-hero__scene <?php echo 1 === get_row_index() ? 'mt-is-active' : ''; ?>">
						<?php echo wp_get_attachment_image( get_sub_field( 'image' ), 'full', false, array( 'class' => 'mt-hero__img', 'alt' => '', 'loading' => 'eager' ) ); ?>
					</div>
				<?php endwhile; ?>
			</div>
			<?php if ( ! empty( $animation['enabled'] ) && $marks ) : ?>
				<canvas class="mt-hero__morph-canvas" id="heroMorphCanvas" data-frames="273" aria-hidden="true"></canvas>
			<?php endif; ?>
			<div class="mt-hero__scrim" aria-hidden="true"></div>
		</div>
		<div class="mt-wrap mt-hero__in">
			<div class="mt-hero__slides">
				<?php while ( have_rows( 'banner_slides' ) ) : the_row(); ?>
					<div class="mt-hero__slide <?php echo 1 === get_row_index() ? 'mt-is-active' : ''; ?>">
						<p class="mt-hero__kicker">
							<?php echo esc_html( get_sub_field( 'eyebrow' ) ); ?>
							<span class="mt-hero__kicker-rule"></span><span class="mt-hero__kicker-dia" aria-hidden="true"></span>
						</p>
						<<?php echo 1 === get_row_index() ? 'h1' : 'h2'; ?> class="mt-hero__title">
							<?php echo asfar_lines( get_sub_field( 'heading' ) ); ?>
							<?php if ( get_sub_field( 'highlight' ) ) : ?>
								<br><b><?php echo asfar_lines( get_sub_field( 'highlight' ) ); ?></b>
							<?php endif; ?>
						</<?php echo 1 === get_row_index() ? 'h1' : 'h2'; ?>>

					</div>
				<?php endwhile; ?>
			</div>
			<div class="mt-hero__cta mt-hero__cta--fixed">
				<?php while ( have_rows( 'banner_slides' ) ) : the_row(); ?>
				<?php if ( 1 === get_row_index() ) : ?>
				<?php if ( get_sub_field( 'video' ) && get_sub_field( 'video_label' ) ) : ?>
					<button type="button" class="mt-btn mt-btn--gold" data-video="<?php echo esc_url( asfar_attachment_url( get_sub_field( 'video' ) ) ); ?>"><?php echo esc_html( get_sub_field( 'video_label' ) ); ?></button>
				<?php endif; ?>
				<?php asfar_button( get_sub_field( 'button' ), 'mt-btn mt-btn--line-light' ); ?>
				<?php endif; ?>
				<?php endwhile; ?>
			</div>
			<?php if ( ! empty( $animation['enabled'] ) && $marks ) : ?>
				<div class="mt-hero__morphcap">
					<p class="mt-hero__morph-lead"><?php echo asfar_lines( $animation['heading'] ); ?></p>
					<p class="mt-hero__morph-over"><?php echo esc_html( $animation['subtitle'] ); ?></p>
					<h2 class="mt-hero__title" id="heroMorphName"><?php echo esc_html( $marks[0]['name'] ); ?></h2>
				</div>
			<?php endif; ?>
		</div>
		<div class="mt-dk-dashes" id="dkHeroDashes" aria-label="<?php echo esc_attr( asfar_option( 'banner_label' ) ); ?>">
			<?php if ( ! empty( $animation['enabled'] ) && $marks ) : ?>
				<?php while ( have_rows( 'banner_animation' ) ) : the_row(); ?>
					<?php while ( have_rows( 'stops' ) ) : the_row(); ?>
						<button class="mt-hero__dot <?php echo 1 === get_row_index() ? 'mt-is-active' : ''; ?>" type="button" data-i="<?php echo esc_attr( get_row_index() - 1 ); ?>" aria-label="<?php echo esc_attr( get_sub_field( 'name' ) ); ?>" aria-pressed="<?php echo 1 === get_row_index() ? 'true' : 'false'; ?>"></button>
					<?php endwhile; ?>
				<?php endwhile; ?>
			<?php else : ?>
				<?php while ( have_rows( 'banner_slides' ) ) : the_row(); ?>
					<button class="mt-hero__dot <?php echo 1 === get_row_index() ? 'mt-is-active' : ''; ?>" type="button" data-i="<?php echo esc_attr( get_row_index() - 1 ); ?>" aria-label="<?php echo esc_attr( get_sub_field( 'heading' ) ); ?>" aria-pressed="<?php echo 1 === get_row_index() ? 'true' : 'false'; ?>"></button>
				<?php endwhile; ?>
			<?php endif; ?>
		</div>
		<?php get_template_part( 'template-parts/wave' ); ?>
	</div>
</section>
