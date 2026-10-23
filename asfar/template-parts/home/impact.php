<?php while ( have_rows( 'impact_section' ) ) : the_row(); ?>
<section class="mt-dk-impact" id="impact">
	<div class="mt-dk-wrap">
		<h2 class="mt-dk-label mt-dk-rise"><?php echo esc_html( get_sub_field( 'heading' ) ); ?></h2>
		<div class="mt-dk-impact__tiles mt-dk-rise">
			<?php while ( have_rows( 'statistics' ) ) : the_row(); ?>
				<div class="mt-dk-impact__tile <?php echo 1 === get_row_index() ? 'mt-dk-impact__tile--hi' : ''; ?>">
					<?php if ( 1 === get_row_index() ) : ?>
						<span class="mt-dk-impact__frond" aria-hidden="true"></span>
					<?php endif; ?>
					<p class="mt-dk-impact__num"><?php echo asfar_statistic_value( get_sub_field( 'value' ) ); ?></p>
					<p class="mt-dk-impact__lbl"><?php echo asfar_lines( get_sub_field( 'label' ) ); ?></p>
				</div>
			<?php endwhile; ?>
		</div>
	</div>
</section>

<?php endwhile; ?>
