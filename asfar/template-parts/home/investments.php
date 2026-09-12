<?php while ( have_rows( 'investments_section' ) ) : the_row(); ?>
<span id="mt-sectors"></span>
<section class="mt-dk-sectors" id="mt-dkSectors">
	<div class="mt-dk-wrap mt-dk-sectors__grid">
		<div class="mt-dk-rise">
			<h2 class="mt-dk-label"><?php echo asfar_lines( get_sub_field( 'heading' ) ); ?></h2>
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
		<?php get_template_part( 'template-parts/arrows', null, array( 'class' => 'mt-dk-arrows--sectors' ) ); ?>
		<?php get_template_part( 'template-parts/wave', null, array( 'color' => '#F2ECE3' ) ); ?>
	</div>
</section>

<?php endwhile; ?>
