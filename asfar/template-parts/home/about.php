<?php while ( have_rows( 'about_section' ) ) : the_row(); ?>
<section class="mt-dk-about" id="mt-about">
	<div class="mt-dk-wrap">
		<div class="mt-dk-about__row">
			<div class="mt-dk-rise">
				<h2 class="mt-dk-label"><?php echo asfar_lines( get_sub_field( 'heading' ) ); ?></h2>
				<p class="mt-dk-sub mt-asfar-about-subtitle"><?php echo asfar_lines( get_sub_field( 'subtitle' ) ); ?></p>
			</div>
			<div class="mt-dk-card mt-dk-about__card mt-dk-rise">
				<span class="mt-dk-about__frond" aria-hidden="true"></span>
				<h3><?php echo asfar_lines( get_sub_field( 'card_heading' ) ); ?></h3>
				<?php while ( have_rows( 'paragraphs' ) ) : the_row(); ?>
					<p><?php echo asfar_lines( get_sub_field( 'text' ) ); ?></p>
				<?php endwhile; ?>
			</div>
			<span class="mt-dk-about__pif" role="img" aria-label="<?php echo esc_attr( get_sub_field( 'ownership' ) ); ?>"><?php echo esc_html( get_sub_field( 'ownership' ) ); ?></span>
		</div>
	</div>
</section>
<?php get_template_part( 'template-parts/wave', null, array( 'color' => '#F2ECE3', 'background' => '#FCF7EE' ) ); ?>

<?php endwhile; ?>
