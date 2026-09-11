<?php
$section = asfar_section( 'faq' );
$items = asfar_faq_items( $section['page'] ?? 0 );
?>
<section class="dk-faq" id="faq">
	<div class="dk-wrap dk-faq__grid">
		<h2 class="dk-label dk-rise"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
		<div class="dk-faq__list dk-rise" id="dkFaq">
			<?php get_template_part( 'template-parts/faq-list', null, array( 'items' => $items ) ); ?>
		</div>
	</div>
</section>
