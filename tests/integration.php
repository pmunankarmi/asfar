<?php
/** Run: php tests/integration.php /path/to/wordpress */
$_SERVER['HTTP_HOST'] = '127.0.0.1:8877'; $_SERVER['REQUEST_URI'] = '/wp-admin/';
define( 'WP_ADMIN', true );
require rtrim( $argv[1], '/' ) . '/wp-load.php';
wp_set_current_user( 1 );
function verify( $condition, $label ) { if ( ! $condition ) { throw new RuntimeException( 'FAIL: ' . $label ); } echo 'PASS: ' . $label . "\n"; }
$map = get_option( 'asfar_page_map' );
verify( count( $map ) === 44, '44 imported pages/posts' );
verify( get_post_meta( $map['index'], '_wp_page_template', true ) === 'template-home.php', 'Selectable homepage template' );
verify( pll_get_post( $map['index'], 'ar' ) === $map['index-ar'], 'Polylang homepage translation pair' );
verify( count( asfar_option( 'team', 'en' ) ) === 19, 'Structured team imported' );
$seed = json_decode( file_get_contents( get_template_directory() . '/inc/seed.json' ), true );
$field = array_key_first( $seed['pages']['index']['values'] );
$old = get_post_meta( $map['index'], $field, true );
update_field( 'field_asfar_' . $field, '', $map['index'] );
asfar_seed();
verify( '' === get_post_meta( $map['index'], $field, true ), 'Repeat import preserves deliberately cleared field' );
update_field( 'field_asfar_' . $field, $old, $map['index'] );
$logo = get_theme_mod( 'custom_logo' );
remove_theme_mod( 'custom_logo' );
verify( 0 === absint( asfar_value( 'site_logo', 'asfar_shared' ) ), 'Customizer logo removal syncs to ACF' );
set_theme_mod( 'custom_logo', $logo );
verify( absint( asfar_value( 'site_logo', 'asfar_shared' ) ) === absint( $logo ), 'Customizer logo update syncs to ACF' );
update_field( 'field_asfar_site_logo', 0, 'asfar_shared' );
do_action( 'acf/save_post', 'asfar_shared' );
verify( ! get_theme_mod( 'custom_logo' ), 'ACF logo removal syncs to Customizer' );
verify( asfar_logo( true ) === '', 'Removing main logo also hides dark variant' );
update_field( 'field_asfar_site_logo', $logo, 'asfar_shared' ); do_action( 'acf/save_post', 'asfar_shared' );
verify( absint( get_theme_mod( 'custom_logo' ) ) === absint( $logo ), 'ACF logo update syncs to Customizer' );
foreach ( array( '=1+1', '+cmd', '-cmd', '@SUM(A1)', " \t=1", "\tfoo" ) as $value ) { verify( str_starts_with( asfar_csv_cell( $value ), "'" ), 'CSV neutralizes ' . json_encode( $value ) ); }
verify( asfar_csv_cell( 'مرحبا بالعالم' ) === 'مرحبا بالعالم', 'Arabic CSV text unchanged' );
$groups = acf_get_local_field_groups();
$seen = array();
$walk = function ( $fields ) use ( &$walk, &$seen ) { foreach ( $fields as $field ) { verify( ! isset( $seen[ $field['key'] ] ), 'Unique ACF key ' . $field['key'] ); $seen[ $field['key'] ] = 1; if ( isset( $field['sub_fields'] ) ) { $walk( $field['sub_fields'] ); } } };
// Keys are checked with one summarized assertion to keep test output readable.
$keys = array(); foreach ( $groups as $g ) { foreach ( acf_get_fields( $g ) ?: array() as $f ) { $keys[] = $f['key']; } }
verify( count( $keys ) === count( array_unique( $keys ) ), 'Registered ACF field keys unique' );
delete_transient( 'asfar_github_release' );
$fake = function ( $pre, $args, $url ) { if ( str_contains( $url, 'api.github.com/repos/pmunankarmi/asfar/releases/latest' ) ) { return array( 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( array( 'tag_name' => 'v1.1.0', 'assets' => array( array( 'name' => 'asfar.zip', 'browser_download_url' => 'https://github.com/pmunankarmi/asfar/releases/download/v1.1.0/asfar.zip' ) ) ) ) ); } return $pre; };
add_filter( 'pre_http_request', $fake, 10, 3 );
$transient = apply_filters( 'pre_set_site_transient_update_themes', (object) array( 'checked' => array( 'asfar' => '1.0.0' ) ) );
verify( ( $transient->response['asfar']['new_version'] ?? '' ) === '1.1.0', 'Native dashboard update offered for newer GitHub release' );
remove_filter( 'pre_http_request', $fake, 10 );
verify( asfar_release()['version'] === '1.1.0', 'GitHub release check cached' );
delete_transient( 'asfar_github_release' );
$fail = function () { return new WP_Error( 'test', 'Offline' ); };add_filter( 'pre_http_request', $fail );
verify( asfar_release() === array(), 'GitHub API failure degrades gracefully' );remove_filter( 'pre_http_request', $fail );delete_transient( 'asfar_github_release' );
verify( get_option( 'asfar_schema_version' ) === '1', 'Versioned submissions schema created' );
echo "Integration checks complete. ACF Pro UI is not exercised by the local API fixture.\n";

require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-base.php';
require_once ABSPATH . 'wp-admin/includes/class-wp-filesystem-direct.php';
$GLOBALS['wp_filesystem'] = new WP_Filesystem_Direct( null );
$fixture = sys_get_temp_dir() . '/asfar-package-' . wp_generate_uuid4();
wp_mkdir_p( $fixture . '/github-source-name' );
file_put_contents( $fixture . '/github-source-name/style.css', "/*\nTheme Name: ASFAR\nVersion: 1.1.0\n*/" );
file_put_contents( $fixture . '/github-source-name/functions.php', '<?php' );
$result = apply_filters( 'upgrader_source_selection', $fixture . '/github-source-name/', $fixture . '/', new stdClass(), array( 'type' => 'theme', 'theme' => 'asfar' ) );
verify( ! is_wp_error( $result ) && basename( rtrim( $result, '/' ) ) === 'asfar' && is_file( $result . 'style.css' ), 'Updater preserves installed theme slug' );
$GLOBALS['wp_filesystem']->delete( $fixture, true );
