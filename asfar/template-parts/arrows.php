<div class="dk-arrows <?php echo esc_attr( $args['class'] ?? '' ); ?>">
	<button class="dk-arrow" type="button" data-dir="-1" aria-label="<?php echo esc_attr( asfar_option( 'previous_label' ) ); ?>">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
			<path d="M15 4 7 12l8 8" />
		</svg>
	</button>
	<button class="dk-arrow" type="button" data-dir="1" aria-label="<?php echo esc_attr( asfar_option( 'next_label' ) ); ?>">
		<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
			<path d="M9 4l8 8-8 8" />
		</svg>
	</button>
</div>
