<?php
/** ASFAR theme bootstrap. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
define( 'ASFAR_VERSION', '1.1.9' );
foreach ( array( 'admin', 'anchors', 'content', 'translations', 'content-types', 'fields', 'media', 'setup', 'migration', 'repair', 'source-update', 'template-upgrade', 'forms', 'updates' ) as $asfar_module ) {
 require_once get_template_directory() . '/inc/' . $asfar_module . '.php';
}
add_action( 'after_setup_theme', function () {
 add_theme_support( 'title-tag' );
 add_theme_support( 'post-thumbnails' );
 add_theme_support( 'custom-logo' );
 add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ) );
 register_nav_menus( array( 'primary' => 'Primary navigation', 'footer' => 'Footer navigation', 'drawer' => 'Expanded navigation' ) );
} );
// Use the classic editor for pages, posts and custom post types.
add_filter( 'use_block_editor_for_post_type', '__return_false', 100 );
add_filter( 'use_block_editor_for_post', '__return_false', 100 );
add_filter( 'use_widgets_block_editor', '__return_false' );

add_action( 'wp_enqueue_scripts', function () {
 $uri = get_template_directory_uri();
 wp_enqueue_style( 'asfar-app', $uri . '/assets/css/app.css', array(), ASFAR_VERSION );
 wp_enqueue_style( 'asfar-deck', $uri . '/assets/css/deck.css', array( 'asfar-app' ), ASFAR_VERSION );
 if ( ! is_page_template( 'templates/template-home.php' ) ) { wp_enqueue_style( 'asfar-article', $uri . '/assets/css/deck-article.css', array( 'asfar-deck' ), ASFAR_VERSION ); }
 if ( 'ar' === asfar_language() ) { wp_enqueue_style( 'asfar-rtl', $uri . '/assets/css/app-rtl.css', array( 'asfar-deck' ), ASFAR_VERSION ); }
 wp_enqueue_style( 'asfar', get_stylesheet_uri(), array( 'asfar-deck' ), ASFAR_VERSION );
 asfar_enqueue_image_styles();
 wp_enqueue_script( 'asfar-lenis', $uri . '/assets/js/vendor/lenis.min.js', array(), ASFAR_VERSION, true );
 wp_enqueue_script( 'asfar-app', $uri . '/assets/js/app.js', array( 'asfar-lenis' ), ASFAR_VERSION, true );
 wp_localize_script( 'asfar-app', 'asfarSettings', array( 'assets' => asfar_uploads_media_url() . '/', 'close' => asfar_option( 'close' ), 'empty' => asfar_option( 'bio_empty' ), 'label' => asfar_option( 'bio_label' ), 'endpoint' => admin_url( 'admin-ajax.php' ), 'error' => asfar_option( 'form_error' ), 'invalid' => asfar_option( 'form_invalid' ) ) );
 wp_enqueue_script( 'asfar-deck', $uri . '/assets/js/deck.js', array( 'asfar-app' ), ASFAR_VERSION, true );
 wp_enqueue_script( 'asfar-wordpress', $uri . '/assets/js/wordpress.js', array( 'asfar-deck' ), ASFAR_VERSION, true );
} );
add_action( 'admin_notices', function () {
 if ( ! current_user_can( 'manage_options' ) ) { return; }
 if ( ! function_exists( 'acf_add_options_page' ) ) { echo '<div class="notice notice-error"><p>ASFAR requires ACF Pro for content editing. Install and activate your licensed copy; no premium plugin is bundled.</p></div>'; }
 if ( ! function_exists( 'pll_current_language' ) ) { echo '<div class="notice notice-warning"><p>Activate Polylang and add English (en) and Arabic (ar) to enable bilingual navigation.</p></div>'; }
 if ( ! get_option( 'asfar_seed_complete' ) ) { echo '<div class="notice notice-info"><p>Use Appearance → ASFAR Setup to import the supplied content.</p></div>'; }
} );

add_filter( 'body_class', function ( $classes ) {
 if ( ! is_page_template( 'templates/template-home.php' ) ) { $classes[] = 'mt-page--inner'; }
 return $classes;
} );
