<?php while ( have_rows( 'vision_section' ) ) : the_row(); ?>
<section class="mt-dk-vision-band">
	<div class="mt-dk-wrap">
		<div class="mt-dk-vision__row" id="mt-philosophy">
			<div class="mt-dk-rise">
				<h2 class="mt-dk-label"><?php echo asfar_lines( get_sub_field( 'heading' ) ); ?></h2>
			</div>
			<div class="mt-dk-vision mt-dk-rise">
				<?php while ( have_rows( 'cards' ) ) : the_row(); if ( 1 === get_row_index() ) : ?>
					<div class="mt-dk-vision__tan">
						<span class="mt-dk-vision__motif" aria-hidden="true"></span>
						<h3><?php echo esc_html( get_sub_field( 'heading' ) ); ?></h3>
						<p><?php echo asfar_lines( get_sub_field( 'description' ) ); ?></p>
					</div>
				<?php endif; endwhile; ?>
				<div class="mt-dk-vision__col">
					<?php while ( have_rows( 'cards' ) ) : the_row(); if ( 1 === get_row_index() ) { continue; } ?>
						<div class="mt-dk-card mt-dk-vision__card">
							<span class="mt-dk-vision__dia" aria-hidden="true"></span>
							<h3><?php echo esc_html( get_sub_field( 'heading' ) ); ?></h3>
							<p><?php echo asfar_lines( get_sub_field( 'description' ) ); ?></p>
						</div>
					<?php endwhile; ?>
				</div>
			</div>
		</div>
	</div>
</section>
<?php get_template_part( 'template-parts/wave', null, array( 'color' => '#154751', 'background' => '#F2ECE3' ) ); ?>

<?php endwhile; ?>
