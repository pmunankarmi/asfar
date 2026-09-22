<?php
$button = $args['button'];
?>
<a class="<?php echo esc_attr( $args['class'] ); ?>" href="<?php echo esc_url( asfar_resolve_url( $button['url'] ) ); ?>"<?php if ( '_blank' === ( $button['target'] ?? '' ) ) : ?> target="_blank" rel="noopener"<?php endif; ?>>
	<?php echo esc_html( $button['title'] ); ?>
</a>
