<?php while ( have_rows( 'investments_section' ) ) : the_row(); ?>
<span id="sectors"></span>
<section class="mt-dk-sectors" id="dkSectors">
	<div class="mt-dk-wrap mt-dk-sectors__grid">
		<div class="mt-dk-rise">
			<h2 class="mt-dk-label"><?php
				$heading_lines = preg_split( '/\R/u', (string) get_sub_field( 'heading' ) );
				echo esc_html( array_shift( $heading_lines ) );
				if ( $heading_lines ) {
					echo '<br><span class="mt-dk-sectors__ln2">' . asfar_lines( implode( "\n", $heading_lines ) ) . '</span>';
				}
				?></h2>
			<p class="mt-dk-sub mt-dk-sectors__intro"><?php echo asfar_lines( get_sub_field( 'description' ) ); ?></p>
		</div>
		<div class="mt-dk-sectors__rail mt-dk-rise">
			<div class="mt-dk-sectors__track">
				<?php while ( have_rows( 'sectors' ) ) : the_row(); ?>
					<div class="mt-dk-sector">
						<?php echo wp_get_attachment_image( get_sub_field( 'image' ), 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
						<span class="mt-dk-sector__pol" aria-hidden="true"></span>
						<span class="mt-dk-sector__name"><?php echo esc_html( get_sub_field( 'label' ) ); ?></span>
					</div>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
	<?php get_template_part( 'template-parts/arrows', null, array( 'class' => 'mt-dk-arrows--sectors' ) ); ?>
		<?php get_template_part( 'template-parts/wave', null, array( 'color' => '#F2ECE3' ) ); ?>
</section>

<?php endwhile; ?>
