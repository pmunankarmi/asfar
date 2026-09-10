
<div class="grain" aria-hidden="true"></div>
<div class="loader" id="loader" aria-hidden="true">
<div class="loader__box">
<img class="loader__logo" src="<?php echo esc_url( asfar_logo() ); ?>" alt="<?php echo esc_attr( asfar_value('c_1', 'global_ar') ); ?>">
<span class="loader__bar"><i></i></span>
</div>
</div>
<div class="progress" id="progress" aria-hidden="true"></div>

<header class="nav" id="nav" data-theme="light">
<div class="wrap nav__in">
<a class="nav__logo" href="<?php echo esc_url( asfar_link('c_2', 'global_ar') ); ?>" aria-label="<?php echo esc_attr( asfar_value('c_3', 'global_ar') ); ?>">
<img class="nav__logo-img nav__logo-img--light" src="<?php echo esc_url( asfar_logo() ); ?>" alt="<?php echo esc_attr( asfar_value('c_4', 'global_ar') ); ?>">
<img class="nav__logo-img nav__logo-img--dark" src="<?php echo esc_url( asfar_logo( true ) ); ?>" alt="<?php echo esc_attr( asfar_value('c_6', 'global_ar') ); ?>">
</a>
<nav class="nav__links" aria-label="<?php echo esc_attr( asfar_value('c_7', 'global_ar') ); ?><?php if ( has_nav_menu( 'primary' ) ) { asfar_navigation( 'primary' ); } else { ?>">
<a href="<?php echo esc_url( asfar_link('c_8', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_9', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_10', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_11', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_12', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_13', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_14', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_15', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_16', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_17', 'global_ar') ); ?></a>
<?php } ?></nav>
<div class="nav__side">
<div class="nav__lang"><?php asfar_languages(); ?></div>
<button class="nav__burger" id="burger" type="button" aria-label="<?php echo esc_attr( asfar_value('c_18', 'global_ar') ); ?>" aria-expanded="false" aria-controls="menu" data-label-open="<?php echo esc_attr( asfar_value('c_19', 'global_ar') ); ?>" data-label-close="<?php echo esc_attr( asfar_value('c_20', 'global_ar') ); ?>"><span></span><span></span><span></span></button>
</div>
</div>
</header>

<div class="menu__scrim" id="menuScrim" aria-hidden="true"></div>
<div class="menu" id="menu" role="dialog" aria-modal="true" aria-label="<?php echo esc_attr( asfar_value('c_21', 'global_ar') ); ?>" aria-hidden="true">
<div class="menu__grid">
<nav class="menu__links" aria-label="<?php echo esc_attr( asfar_value('c_22', 'global_ar') ); ?><?php if ( has_nav_menu( 'drawer' ) ) { asfar_navigation( 'drawer' ); } else { ?>">
<a href="<?php echo esc_url( asfar_link('c_23', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_24', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_25', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_26', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_27', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_28', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_29', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_30', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_31', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_32', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_33', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_34', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_35', 'global_ar') ); ?>"><?php echo esc_html( asfar_value('c_36', 'global_ar') ); ?></a>
<a href="<?php echo esc_url( asfar_link('c_37', 'global_ar') ); ?>" data-partner=""><?php echo esc_html( asfar_value('c_38', 'global_ar') ); ?></a>
<?php } ?></nav> </div>
</div>
