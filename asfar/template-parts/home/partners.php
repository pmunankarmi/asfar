<?php $section = asfar_section( 'partners' ); ?>
<section class="dk-partners" id="partners">
	<div class="dk-wrap dk-partners__grid">
		<h2 class="dk-label dk-rise"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
		<div class="dk-partners__card dk-rise">
			<?php foreach ( asfar_rows( 'partner_logos', 'asfar_partners' ) as $partner ) : ?>
				<?php if ( $partner['website'] ) : ?>
					<a href="<?php echo esc_url( $partner['website'] ); ?>">
				<?php endif; ?>
				<?php echo wp_get_attachment_image( $partner['logo'], 'full', false, array( 'alt' => asfar_translate( $partner['name'] ), 'loading' => 'lazy' ) ); ?>
				<?php if ( $partner['website'] ) : ?>
					</a>
				<?php endif; ?>
			<?php endforeach; ?>
		</div>
	</div>
</section>
