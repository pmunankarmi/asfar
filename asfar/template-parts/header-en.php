
<div class="grain" aria-hidden="true"></div>
<div class="loader" id="loader" aria-hidden="true">
<div class="loader__box">
<img class="loader__logo" src="<?php echo esc_url( asfar_logo() ); ?>" alt="<?php echo esc_attr( asfar_value('c_273', 'global_en') ); ?>">
<span class="loader__bar"><i></i></span>
</div>
</div>
<div class="progress" id="progress" aria-hidden="true"></div>

<header class="nav" id="nav" data-theme="light">
<div class="wrap nav__in">
<a class="nav__logo" href="<?php echo esc_url( asfar_link('c_274', 'global_en') ); ?>" aria-label="<?php echo esc_attr( asfar_value('c_275', 'global_en') ); ?>">
<img class="nav__logo-img nav__logo-img--light" src="<?php echo esc_url( asfar_logo() ); ?>" alt="<?php echo esc_attr( asfar_value('c_276', 'global_en') ); ?>">
<img class="nav__logo-img nav__logo-img--dark" src="<?php echo esc_url( asfar_logo( true ) ); ?>" alt="<?php echo esc_attr( asfar_value('c_278', 'global_en') ); ?>">
</a>
<nav class="nav__links" aria-label="<?php echo esc_attr( asfar_value('c_279', 'global_en') ); ?><?php if ( has_nav_menu( 'primary' ) ) { asfar_navigation( 'primary' ); } else { ?>">
<a href="<?php echo esc_url( asfar_link('c_280', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_281', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_282', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_283', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_284', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_285', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_286', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_287', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_288', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_289', 'global_en') ); ?></a>
<?php } ?></nav>
<div class="nav__side">
<div class="nav__lang"><?php asfar_languages(); ?></div>
<button class="nav__burger" id="burger" type="button" aria-label="<?php echo esc_attr( asfar_value('c_290', 'global_en') ); ?>" aria-expanded="false" aria-controls="menu" data-label-open="<?php echo esc_attr( asfar_value('c_291', 'global_en') ); ?>" data-label-close="<?php echo esc_attr( asfar_value('c_292', 'global_en') ); ?>"><span></span><span></span><span></span></button>
</div>
</div>
</header>

<div class="menu__scrim" id="menuScrim" aria-hidden="true"></div>
<div class="menu" id="menu" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( asfar_value('c_293', 'global_en') ); ?>" aria-hidden="true">
<div class="menu__grid">
<nav class="menu__links" aria-label="<?php echo esc_attr( asfar_value('c_294', 'global_en') ); ?><?php if ( has_nav_menu( 'drawer' ) ) { asfar_navigation( 'drawer' ); } else { ?>">
<a href="<?php echo esc_url( asfar_link('c_295', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_296', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_297', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_298', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_299', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_300', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_301', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_302', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_303', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_304', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_305', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_306', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_307', 'global_en') ); ?>"><?php echo esc_html( asfar_value('c_308', 'global_en') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_309', 'global_en') ); ?>" data-partner=""><?php echo esc_html( asfar_value('c_310', 'global_en') ); ?></a>
<?php } ?></nav> </div>
</div>
