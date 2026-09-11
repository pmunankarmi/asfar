<?php
$section = asfar_section( 'portfolio' );
$projects = asfar_project_rows( $section['projects'] ?? array() );
?>
<span id="projects"></span>
<section class="dk-map" id="dkMap">
	<div class="dk-map__bgs" aria-hidden="true">
		<?php foreach ( $projects as $index => $project ) : ?>
			<div class="dk-map__bg <?php echo 0 === $index ? 'is-active' : ''; ?>" style="background-image: url('<?php echo esc_url( asfar_attachment_url( $project['bg'] ) ); ?>')"></div>
		<?php endforeach; ?>
	</div>
	<div class="dk-map__in">
		<h2 class="dk-map__title dk-rise"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
		<div class="dk-map__stage dk-rise" aria-hidden="true">
			<?php asfar_render_map_artwork(); ?>
			<div class="dk-map__pin"></div>
			<div class="dk-map__pinlab"></div>
		</div>
		<aside class="dk-map__panel dk-rise" aria-live="polite">
			<?php foreach ( $projects as $index => $project ) : ?>
				<div class="asfar-portfolio-pane" <?php echo 0 === $index ? '' : 'hidden'; ?>
					data-hot="<?php echo esc_attr( $project['hot'] ); ?>"
					data-x="<?php echo esc_attr( $project['mark_x'] ); ?>"
					data-y="<?php echo esc_attr( $project['mark_y'] ); ?>"
					data-label="<?php echo esc_attr( $project['label'] ); ?>">
					<h3 class="dk-map__region dk-map__swap"><a href="<?php echo esc_url( get_permalink( $project['id'] ) ); ?>"><?php echo esc_html( $project['name'] ); ?></a></h3>
					<div class="dk-map__rows">
						<?php foreach ( $project['stats'] as $statistic ) : ?>
							<div class="dk-map__row dk-map__swap">
								<p class="dk-map__val"><?php echo esc_html( $statistic['value'] ); ?></p>
								<p class="dk-map__lab"><?php echo esc_html( $statistic['label'] ); ?></p>
							</div>
						<?php endforeach; ?>
					</div>
				</div>
			<?php endforeach; ?>
		</aside>
	</div>
	<div class="dk-map__dashes" role="tablist" aria-label="<?php echo esc_attr( $section['heading'] ?? '' ); ?>">
		<?php foreach ( $projects as $index => $project ) : ?>
			<button type="button" role="tab" class="<?php echo 0 === $index ? 'is-active' : ''; ?>" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>" aria-label="<?php echo esc_attr( $project['name'] ); ?>"></button>
		<?php endforeach; ?>
	</div>
</section>
