<?php
/** Isolated CLI bootstrap with both third-party plugins disabled. */
$_SERVER['HTTP_HOST'] = '127.0.0.1:8877'; $_SERVER['REQUEST_URI'] = '/';
define( 'WPMU_PLUGIN_DIR', '/private/tmp/asfar-no-mu-plugins' );
$GLOBALS['wp_filter']['option_active_plugins'][10][] = array( 'function' => function () { return array(); }, 'accepted_args' => 1 );
require rtrim( $argv[1], '/' ) . '/wp-load.php';
if ( function_exists( 'get_field' ) || function_exists( 'pll_current_language' ) ) { throw new RuntimeException( 'Plugins were not disabled.' ); }
$map = get_option( 'asfar_page_map' );
query_posts( array( 'page_id' => $map['index'] ) );
ob_start(); load_template( get_template_directory() . '/templates/template-home.php' ); $html = ob_get_clean();
if ( ! str_contains( $html, '</html>' ) ) { throw new RuntimeException( 'Rendering incomplete.' ); }
echo "PASS: Page renders without ACF or Polylang; no PHP fatal error.\n";
