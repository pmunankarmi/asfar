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
	<div class="grain" aria-hidden="true"></div>
	<div class="loader" id="loader" aria-hidden="true">
		<div class="loader__box">
			<img class="loader__logo" src="<?php echo esc_url( asfar_logo() ); ?>" alt="<?php echo esc_attr( asfar_option( 'site_name' ) ); ?>">
			<span class="loader__bar"><i></i></span>
		</div>
	</div>
	<div class="progress" id="progress" aria-hidden="true"></div>
	<header class="nav" id="nav" data-theme="light">
		<div class="wrap nav__in">
			<a class="nav__logo" href="<?php echo esc_url( asfar_home_url() ); ?>" aria-label="<?php echo esc_attr( asfar_option( 'home_label' ) ); ?>">
				<img class="nav__logo-img nav__logo-img--light" src="<?php echo esc_url( asfar_logo() ); ?>" alt="<?php echo esc_attr( asfar_option( 'site_name' ) ); ?>">
				<img class="nav__logo-img nav__logo-img--dark" src="<?php echo esc_url( asfar_logo( true ) ); ?>" alt="<?php echo esc_attr( asfar_option( 'site_name' ) ); ?>">
			</a>
			<nav class="nav__links" aria-label="<?php echo esc_attr( asfar_option( 'primary_label' ) ); ?>">
				<?php asfar_menu( 'primary' ); ?>
			</nav>
			<div class="nav__side">
				<div class="nav__lang">
					<?php asfar_languages(); ?>
				</div>
				<button class="nav__burger" id="burger" type="button" aria-expanded="false" aria-controls="menu"
					aria-label="<?php echo esc_attr( asfar_option( 'open_menu' ) ); ?>"
					data-label-open="<?php echo esc_attr( asfar_option( 'open_menu' ) ); ?>"
					data-label-close="<?php echo esc_attr( asfar_option( 'close_menu' ) ); ?>">
					<span></span><span></span><span></span>
				</button>
			</div>
		</div>
	</header>
	<div class="menu__scrim" id="menuScrim" aria-hidden="true"></div>
	<div class="menu" id="menu" role="dialog" aria-modal="true" aria-hidden="true" aria-label="<?php echo esc_attr( asfar_option( 'menu_label' ) ); ?>">
		<div class="menu__grid">
			<nav class="menu__links" aria-label="<?php echo esc_attr( asfar_option( 'menu_label' ) ); ?>">
				<?php asfar_menu( 'drawer' ); ?>
			</nav>
		</div>
	</div>
