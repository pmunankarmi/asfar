<?php
$color = $args['color'] ?? '#FCF7EE';
$background = $args['background'] ?? 'transparent';
?>
<div class="mt-dk-wave mt-dk-wave--tight" aria-hidden="true" style="background: <?php echo esc_attr( $background ); ?>">
	<svg viewBox="0 0 1280 44" preserveAspectRatio="none">
		<path d="M1280 21.22C1280 21.22 1030.16 3.24 946.52 0.67C816.65 -3.74 556.54 14.98 426.67 19.39C319.91 23.06 0 32.6 0 32.6L0 44L1280 44Z" fill="<?php echo esc_attr( $color ); ?>" />
	</svg>
</div>
