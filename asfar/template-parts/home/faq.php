<?php
$section = get_field( 'faq_section' ) ?: array();
$faq_page = asfar_translated_post( $section['page'] ?? 0 );
?>
<section class="mt-dk-faq" id="mt-faq">
	<div class="mt-dk-wrap mt-dk-faq__grid">
		<h2 class="mt-dk-label mt-dk-rise"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
		<div class="mt-dk-faq__list mt-dk-rise" id="mt-dkFaq">
			<?php if ( $faq_page ) { get_template_part( 'template-parts/faq-list', null, array( 'page_id' => $faq_page ) ); } ?>
		</div>
	</div>
</section>
