<?php $section = asfar_section( 'investments' ); ?>
<span id="sectors"></span>
<section class="dk-sectors" id="dkSectors">
	<div class="dk-wrap dk-sectors__grid">
		<div class="dk-rise">
			<h2 class="dk-label"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
			<p class="dk-sub dk-sectors__intro"><?php echo asfar_lines( $section['description'] ?? '' ); ?></p>
		</div>
		<div class="dk-sectors__rail dk-rise">
			<div class="dk-sectors__track">
				<?php foreach ( $section['sectors'] ?? array() as $sector ) : ?>
					<div class="dk-sector">
						<?php echo wp_get_attachment_image( $sector['image'], 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
						<span class="dk-sector__pol" aria-hidden="true"></span>
						<span class="dk-sector__name"><?php echo esc_html( $sector['label'] ); ?></span>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<?php get_template_part( 'template-parts/arrows', null, array( 'class' => 'dk-arrows--sectors' ) ); ?>
		<?php get_template_part( 'template-parts/wave', null, array( 'color' => '#F2ECE3' ) ); ?>
	</div>
</section>
