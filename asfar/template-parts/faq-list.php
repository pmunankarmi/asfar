<?php foreach ( $args['items'] as $item ) : ?>
	<div class="dk-faq__item">
		<button class="dk-faq__btn" type="button" aria-expanded="false">
			<span><?php echo esc_html( $item['question'] ); ?></span>
			<span class="dk-faq__sign" aria-hidden="true"></span>
		</button>
		<div class="dk-faq__awrap">
			<p class="dk-faq__a"><?php echo asfar_lines( $item['answer'] ); ?></p>
		</div>
	</div>
<?php endforeach; ?>
