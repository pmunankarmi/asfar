<?php
/** Shared header for every language. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<div class="mt-grain" aria-hidden="true"></div>
	<div class="mt-loader" id="loader" aria-hidden="true">
		<div class="mt-loader__box">
			<img class="mt-loader__logo" src="<?php echo esc_url( asfar_logo() ); ?>" alt="<?php echo esc_attr( asfar_option( 'site_name' ) ); ?>">
			<span class="mt-loader__bar"><i></i></span>
		</div>
	</div>
	<div class="mt-progress" id="progress" aria-hidden="true"></div>
	<header class="mt-nav" id="nav" data-theme="light">
		<div class="mt-wrap mt-nav__in">
			<a class="mt-nav__logo" href="<?php echo esc_url( asfar_home_url() ); ?>" aria-label="<?php echo esc_attr( asfar_option( 'home_label' ) ); ?>">
				<img class="mt-nav__logo-img mt-nav__logo-img--light" src="<?php echo esc_url( asfar_logo() ); ?>" alt="<?php echo esc_attr( asfar_option( 'site_name' ) ); ?>">
				<img class="mt-nav__logo-img mt-nav__logo-img--dark" src="<?php echo esc_url( asfar_logo( true ) ); ?>" alt="<?php echo esc_attr( asfar_option( 'site_name' ) ); ?>">
			</a>
			<nav class="mt-nav__links" aria-label="<?php echo esc_attr( asfar_option( 'primary_label' ) ); ?>">
				<?php asfar_menu( 'primary' ); ?>
			</nav>
			<div class="mt-nav__side">
				<div class="mt-nav__lang">
					<?php asfar_languages(); ?>
				</div>
				<button class="mt-nav__burger" id="burger" type="button" aria-expanded="false" aria-controls="menu"
					aria-label="<?php echo esc_attr( asfar_option( 'open_menu' ) ); ?>"
					data-label-open="<?php echo esc_attr( asfar_option( 'open_menu' ) ); ?>"
					data-label-close="<?php echo esc_attr( asfar_option( 'close_menu' ) ); ?>">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</header>
	<div class="mt-menu__scrim" id="menuScrim" aria-hidden="true"></div>
	<div class="mt-menu" id="menu" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?php echo esc_attr( asfar_option( 'menu_label' ) ); ?>">
		<img class="mt-menu__pattern" src="<?php echo esc_url( asfar_original_image_url( 'img/asfar-motif-a.svg' ) ); ?>" alt="" aria-hidden="true">
		<div class="mt-menu__grid">
			<nav class="mt-menu__links" aria-label="<?php echo esc_attr( asfar_option( 'menu_label' ) ); ?>">
				<?php asfar_menu( 'drawer' ); ?>
			</nav>
		</div>
	</div>
