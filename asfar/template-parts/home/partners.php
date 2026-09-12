<?php $section = get_field( 'partners_section' ) ?: array(); ?>
<section class="mt-dk-partners" id="mt-partners">
	<div class="mt-dk-wrap mt-dk-partners__grid">
		<h2 class="mt-dk-label mt-dk-rise"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
		<div class="mt-dk-partners__card mt-dk-rise">
			<?php while ( have_rows( 'partner_logos', 'asfar_partners' ) ) : the_row(); ?>
				<?php if ( get_sub_field( 'website' ) ) : ?>
					<a href="<?php echo esc_url( get_sub_field( 'website' ) ); ?>">
				<?php endif; ?>
				<?php echo wp_get_attachment_image( get_sub_field( 'logo' ), 'full', false, array( 'alt' => asfar_translate( get_sub_field( 'name' ) ), 'loading' => 'lazy' ) ); ?>
				<?php if ( get_sub_field( 'website' ) ) : ?>
					</a>
				<?php endif; ?>
			<?php endwhile; ?>
		</div>
	</div>
</section>
