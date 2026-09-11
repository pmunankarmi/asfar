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
verify( (int) wp_count_posts( 'asfar_team' )->publish === 38, '38 translated team members migrated' );
verify( (int) wp_count_posts( 'asfar_project' )->publish === 8, '8 translated projects migrated' );
verify( pll_is_translated_post_type( 'asfar_team' ) && pll_is_translated_post_type( 'asfar_project' ) && pll_is_translated_taxonomy( 'team_department' ), 'Polylang translates native content types and departments' );
$content = get_option( 'asfar_content_map' );
verify( pll_get_post( $content['project_0_en'], 'ar' ) === $content['project_0_ar'], 'Project translation pairs linked' );
verify( pll_get_post( $content['team_0_en'], 'ar' ) === $content['team_0_ar'], 'Team translation pairs linked' );
verify( count( asfar_home_field_groups() ) === 10, 'Ten homepage field groups matching template sections' );
verify( get_post_meta( $content['faq_en'], '_wp_page_template', true ) === 'template-faq.php', 'FAQ managed on a native page' );
verify( asfar_option( 'contact_button', 'ar' ) === 'تواصل معنا', 'Shared footer label translated through Polylang' );
$old = get_field( 'about_section', $map['index'] );
try {
 update_field( 'field_asfar_home_about_about_section', array_merge( $old, array( 'heading' => '' ) ), $map['index'] );
 delete_option( 'asfar_content_schema_version' );
 asfar_migrate();
 verify( get_field( 'about_section', $map['index'] )['heading'] === '', 'Migration rerun preserves deliberately cleared named field' );
 verify( (int) wp_count_posts( 'asfar_team' )->publish === 38 && (int) wp_count_posts( 'asfar_project' )->publish === 8, 'Migration rerun creates no duplicate content' );
} finally { update_field( 'field_asfar_home_about_about_section', $old, $map['index'] ); }
// Exercise legacy numbered-field edits without modifying the existing source pages.
$fixture_post = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => 'Migration test fixture' ) );
try {
 update_post_meta( $fixture_post, 'c_350', 'Edited old heading' );
 update_post_meta( $fixture_post, 'c_351', '' );
 $migrated = asfar_legacy_value( array( 'join' => array( 'c_350', 'c_351' ) ), $fixture_post, array( 'c_350' => 'Original', 'c_351' => 'Original second line' ) );
 verify( $migrated === "Edited old heading\n", 'Legacy edits and empty fragments survive migration' );
} finally { wp_delete_post( $fixture_post, true ); }
// ACF Pro's actual legacy options storage is a count and nested option values.
update_option( 'asfar_test_rows', 1 );
update_option( 'asfar_test_rows_0_title', 'Edited title' );
update_option( 'asfar_test_rows_0_stats', 1 );
update_option( 'asfar_test_rows_0_stats_0_value', '17' );
try {
 $rows = asfar_legacy_option( 'asfar_test', 'rows', array( array( 'title' => '', 'stats' => array( array( 'value' => '' ) ) ) ) );
 verify( $rows === array( array( 'title' => 'Edited title', 'stats' => array( array( 'value' => '17' ) ) ) ), 'Legacy nested repeater counts reconstructed correctly' );
 update_option( 'asfar_test_rows', 0 );
 verify( asfar_legacy_option( 'asfar_test', 'rows', array( array( 'title' => 'Default' ) ) ) === array(), 'Cleared legacy repeater stays empty' );
} finally {
 foreach ( array( 'rows', 'rows_0_title', 'rows_0_stats', 'rows_0_stats_0_value' ) as $key ) { delete_option( 'asfar_test_' . $key ); }
}
$faq = get_field( 'faq_items', $content['faq_en'] );
try {
 update_field( 'field_asfar_faq_faq_items', array( array( 'question' => 'Edited FAQ question', 'answer' => 'Edited FAQ answer' ) ), $content['faq_en'] );
 verify( asfar_faq_items( $content['faq_en'] )[0]['answer'] === 'Edited FAQ answer', 'Homepage reads edits directly from FAQ source page' );
} finally { update_field( 'field_asfar_faq_faq_items', $faq, $content['faq_en'] ); }
verify( asfar_project_rows( array() ) === array(), 'Clearing homepage project selection hides project rows' );
$logo = get_theme_mod( 'custom_logo' );
$dark_logo = asfar_value( 'dark_logo', 'asfar_shared' );
remove_theme_mod( 'custom_logo' );
verify( 0 === absint( asfar_value( 'site_logo', 'asfar_shared' ) ), 'Customizer logo removal syncs to ACF' );
set_theme_mod( 'custom_logo', $logo );
verify( absint( asfar_value( 'site_logo', 'asfar_shared' ) ) === absint( $logo ), 'Customizer logo update syncs to ACF' );
update_field( 'field_asfar_settings_site_logo', 0, 'asfar_shared' );
do_action( 'acf/save_post', 'asfar_shared' );
verify( ! get_theme_mod( 'custom_logo' ), 'ACF logo removal syncs to Customizer' );
verify( asfar_logo( true ) === '', 'Removing main logo also hides dark variant' );
update_field( 'field_asfar_settings_site_logo', $logo, 'asfar_shared' ); do_action( 'acf/save_post', 'asfar_shared' );
verify( absint( get_theme_mod( 'custom_logo' ) ) === absint( $logo ), 'ACF logo update syncs to Customizer' );
update_field( 'field_asfar_settings_dark_logo', $dark_logo, 'asfar_shared' );
foreach ( array( '=1+1', '+cmd', '-cmd', '@SUM(A1)', " \t=1", "\tfoo" ) as $value ) { verify( str_starts_with( asfar_csv_cell( $value ), "'" ), 'CSV neutralizes ' . json_encode( $value ) ); }
verify( asfar_csv_cell( 'مرحبا بالعالم' ) === 'مرحبا بالعالم', 'Arabic CSV text unchanged' );
$groups = acf_get_local_field_groups();
$seen = array();
$walk = function ( $fields ) use ( &$walk, &$seen ) { foreach ( $fields as $field ) { verify( ! isset( $seen[ $field['key'] ] ), 'Unique ACF key ' . $field['key'] ); $seen[ $field['key'] ] = 1; if ( isset( $field['sub_fields'] ) ) { $walk( $field['sub_fields'] ); } } };
// Keys are checked with one summarized assertion to keep test output readable.
$keys = array(); foreach ( $groups as $g ) { foreach ( acf_get_fields( $g ) ?: array() as $f ) { $keys[] = $f['key']; } }
verify( count( $keys ) === count( array_unique( $keys ) ), 'Registered ACF field keys unique' );
delete_transient( 'asfar_github_release' );
$fake = function ( $pre, $args, $url ) { if ( str_contains( $url, 'api.github.com/repos/pmunankarmi/asfar/releases/latest' ) ) { return array( 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( array( 'tag_name' => 'v1.2.0', 'assets' => array( array( 'name' => 'asfar.zip', 'browser_download_url' => 'https://github.com/pmunankarmi/asfar/releases/download/v1.2.0/asfar.zip' ) ) ) ) ); } return $pre; };
add_filter( 'pre_http_request', $fake, 10, 3 );
$transient = apply_filters( 'pre_set_site_transient_update_themes', (object) array( 'checked' => array( 'asfar' => '1.0.0' ) ) );
verify( ( $transient->response['asfar']['new_version'] ?? '' ) === '1.2.0', 'Native dashboard update offered for newer GitHub release' );
remove_filter( 'pre_http_request', $fake, 10 );
verify( asfar_release()['version'] === '1.2.0', 'GitHub release check cached' );
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
