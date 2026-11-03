<?php foreach ( $args['languages'] as $language ) : ?>
	<?php if ( ! $language['current_lang'] ) : ?>
		<a class="mt-nav__lang-opt" href="<?php echo esc_url( ! empty( $language['no_translation'] ) ? pll_home_url( $language['slug'] ) : $language['url'] ); ?>" lang="<?php echo esc_attr( $language['slug'] ); ?>" hreflang="<?php echo esc_attr( $language['slug'] ); ?>">
			<?php echo esc_html( $language['name'] ); ?>
		</a>
	<?php endif; ?>
<?php endforeach; ?>
