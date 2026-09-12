<?php
$section = get_field( 'portfolio_section' ) ?: array();
$projects = asfar_project_rows( $section['projects'] ?? array() );
?>
<span id="projects"></span>
<section class="mt-dk-map" id="dkMap">
	<div class="mt-dk-map__bgs" aria-hidden="true">
		<?php foreach ( $projects as $index => $project ) : ?>
			<div class="mt-dk-map__bg <?php echo 0 === $index ? 'mt-is-active' : ''; ?>" style="background-image: url('<?php echo esc_url( asfar_attachment_url( $project['bg'] ) ); ?>')"></div>
		<?php endforeach; ?>
	</div>
	<div class="mt-dk-map__in">
		<h2 class="mt-dk-map__title mt-dk-rise"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
		<div class="mt-dk-map__stage mt-dk-rise">
			<?php asfar_render_map_artwork(); ?>
			<div class="mt-dk-map__pin"></div>
			<div class="mt-dk-map__pinlab"></div>
		</div>
		<aside class="mt-dk-map__panel mt-dk-rise" aria-live="polite">
			<?php foreach ( $projects as $index => $project ) : ?>
				<div class="mt-asfar-portfolio-pane" <?php echo 0 === $index ? '' : 'hidden'; ?>
					data-hot="<?php echo esc_attr( $project['hot'] ); ?>"
					data-x="<?php echo esc_attr( $project['mark_x'] ); ?>"
					data-y="<?php echo esc_attr( $project['mark_y'] ); ?>"
					data-label="<?php echo esc_attr( $project['label'] ); ?>">
					<h3 class="mt-dk-map__region mt-dk-map__swap"><a href="<?php echo esc_url( get_permalink( $project['id'] ) ); ?>"><?php echo esc_html( $project['name'] ); ?></a></h3>
					<div class="mt-dk-map__rows">
						<?php while ( have_rows( 'statistics', $project['id'] ) ) : the_row(); ?>
							<div class="mt-dk-map__row mt-dk-map__swap">
								<p class="mt-dk-map__val"><?php echo esc_html( get_sub_field( 'value' ) ); ?></p>
								<p class="mt-dk-map__lab"><?php echo esc_html( get_sub_field( 'label' ) ); ?></p>
							</div>
						<?php endwhile; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</aside>
	</div>
	<div class="mt-dk-map__dashes" role="tablist" aria-label="<?php echo esc_attr( $section['heading'] ?? '' ); ?>">
		<?php foreach ( $projects as $index => $project ) : ?>
			<button type="button" role="tab" class="<?php echo 0 === $index ? 'mt-is-active' : ''; ?>" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $project['name'] ); ?>"></button>
		<?php endforeach; ?>
	</div>
</section>
