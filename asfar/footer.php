<?php
/** One footer; Polylang translates its labels and messages. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
	<footer class="dk-footer" id="contact">
		<div class="dk-footer__wave" aria-hidden="true">
			<svg viewBox="0 0 1280 22.86" preserveAspectRatio="none">
				<path d="M0 10.45L291.43 1.65C330.69 0.32 377.61 0 428.88 0.34C582.69 1.40 775.65 8.40 918.55 11.65L1280 22.86L0 22.86Z" fill="#154751" />
			</svg>
		</div>
		<div class="dk-wrap">
			<div class="dk-footer__in">
				<?php echo wp_get_attachment_image( asfar_option( 'footer_logo' ), 'full', false, array( 'class' => 'dk-footer__logo', 'alt' => asfar_option( 'site_name' ) ) ); ?>
				<a class="dk-footer__item" href="<?php echo esc_url( 'mailto:' . asfar_option( 'contact_email' ) ); ?>">
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
						<rect x="2" y="4" width="20" height="16" rx="2" />
						<path d="m22 7-10 6L2 7" />
					</svg>
					<span dir="ltr"><?php echo esc_html( asfar_option( 'contact_email' ) ); ?></span>
				</a>
				<div class="dk-footer__cta footer__enquire" data-enquire>
					<button type="button" class="dk-pill enquire__btn" id="enquireBtn" aria-expanded="false" aria-controls="contactForm">
						<?php echo esc_html( asfar_option( 'contact_button' ) ); ?>
					</button>
					<?php get_template_part( 'template-parts/contact-form' ); ?>
				</div>
			</div>
			<div class="dk-footer__legal">
				<?php echo wp_get_attachment_image( asfar_option( 'ownership_logo' ), 'full', false, array( 'alt' => asfar_option( 'ownership_label' ), 'loading' => 'lazy' ) ); ?>
				<span><?php echo esc_html( asfar_option( 'copyright' ) ); ?></span>
				<a class="dk-footer__top" href="#top" id="dkTop">
					<?php echo esc_html( asfar_option( 'back_top' ) ); ?>
					<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
						<path d="M12 19V5M5 12l7-7 7 7" />
					</svg>
				</a>
			</div>
			<div class="asfar-social">
				<?php asfar_menu( 'footer' ); ?>
				<?php foreach ( asfar_rows( 'social_links', 'asfar_shared' ) as $social ) : ?>
					<a href="<?php echo esc_url( $social['url'] ); ?>"><?php echo esc_html( asfar_translate( $social['label'] ) ); ?></a>
				<?php endforeach; ?>
			</div>
		</div>
	</footer>
	<?php wp_footer(); ?>
</body>
</html>
