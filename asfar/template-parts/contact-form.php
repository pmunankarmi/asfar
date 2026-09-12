<?php
/** Shared contact form. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<form class="mt-footer__form" id="mt-contactForm" method="post" novalidate aria-labelledby="mt-enquireBtn" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
	<?php asfar_form_hidden(); ?>
	<div class="mt-footer__form-grid">
		<label class="mt-ffield">
			<span><?php echo esc_html( asfar_option( 'name_label' ) ); ?></span>
			<input type="text" name="name" autocomplete="name" required placeholder="<?php echo esc_attr( asfar_option( 'name_placeholder' ) ); ?>">
		</label>
		<label class="mt-ffield">
			<span><?php echo esc_html( asfar_option( 'email_label' ) ); ?></span>
			<input type="email" name="email" autocomplete="email" required placeholder="<?php echo esc_attr( asfar_option( 'email_placeholder' ) ); ?>">
		</label>
		<label class="mt-ffield mt-ffield--wide">
			<span><?php echo esc_html( asfar_option( 'message_label' ) ); ?></span>
			<textarea name="message" rows="2" required placeholder="<?php echo esc_attr( asfar_option( 'message_placeholder' ) ); ?>"></textarea>
		</label>
		<button type="submit" class="mt-btn mt-btn--solid mt-footer__form-btn">
			<?php echo esc_html( asfar_option( 'send_label' ) ); ?>
		</button>
	</div>
	<p class="mt-footer__form-note" role="status" aria-live="polite"></p>
</form>
