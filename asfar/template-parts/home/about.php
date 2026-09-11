<?php $section = asfar_section( 'about' ); ?>
<section class="dk-about" id="about">
	<div class="dk-wrap">
		<div class="dk-about__row">
			<div class="dk-rise">
				<h2 class="dk-label"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
				<p class="dk-sub asfar-about-subtitle"><?php echo asfar_lines( $section['subtitle'] ?? '' ); ?></p>
			</div>
			<div class="dk-card dk-about__card dk-rise">
				<span class="dk-about__frond" aria-hidden="true"></span>
				<h3><?php echo asfar_lines( $section['card_heading'] ?? '' ); ?></h3>
				<?php foreach ( $section['paragraphs'] ?? array() as $paragraph ) : ?>
					<p><?php echo asfar_lines( $paragraph['text'] ); ?></p>
				<?php endforeach; ?>
			</div>
			<span class="dk-about__pif"><?php echo esc_html( $section['ownership'] ?? '' ); ?></span>
		</div>
	</div>
</section>
<?php get_template_part( 'template-parts/wave', null, array( 'color' => '#F2ECE3', 'background' => '#FCF7EE' ) ); ?>
