<?php $section = asfar_section( 'impact' ); ?>
<section class="dk-impact" id="impact">
	<div class="dk-wrap">
		<h2 class="dk-label dk-rise"><?php echo esc_html( $section['heading'] ?? '' ); ?></h2>
		<div class="dk-impact__tiles dk-rise">
			<?php foreach ( $section['statistics'] ?? array() as $index => $statistic ) : ?>
				<div class="dk-impact__tile <?php echo 0 === $index ? 'dk-impact__tile--hi' : ''; ?>">
					<?php if ( 0 === $index ) : ?>
						<span class="dk-impact__frond" aria-hidden="true"></span>
					<?php endif; ?>
					<p class="dk-impact__num"><?php echo esc_html( $statistic['value'] ); ?></p>
					<p class="dk-impact__lbl"><?php echo asfar_lines( $statistic['label'] ); ?></p>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</section>
