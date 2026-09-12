<?php while ( have_rows( 'faq_items', $args['page_id'] ?? get_the_ID() ) ) : the_row(); ?>
	<div class="mt-dk-faq__item">
		<button class="mt-dk-faq__btn" type="button" aria-expanded="false">
			<span><?php echo esc_html( get_sub_field( 'question' ) ); ?></span>
			<span class="mt-dk-faq__sign" aria-hidden="true"></span>
		</button>
		<div class="mt-dk-faq__awrap">
			<p class="mt-dk-faq__a"><?php echo asfar_lines( get_sub_field( 'answer' ) ); ?></p>
		</div>
	</div>
<?php endwhile; ?>
