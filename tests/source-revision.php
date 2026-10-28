<?php
/** Run only against an isolated imported WordPress fixture. */
$_SERVER['HTTP_HOST'] = '127.0.0.1:8877';
$_SERVER['REQUEST_URI'] = '/wp-admin/';
define( 'WP_ADMIN', true );
require rtrim( $argv[1], '/' ) . '/wp-load.php';
wp_set_current_user( 1 );
function check_source( $ok, $label ) {
	if ( ! $ok ) { throw new RuntimeException( $label ); }
	echo 'PASS: ' . $label . "\n";
}
asfar_update_source_revision();
$pages = get_option( 'asfar_page_map' );
check_source( 'ar' === pll_default_language() && (int) get_option( 'page_on_front' ) === (int) $pages['index-ar'], 'Arabic is the default homepage' );
check_source( ! get_option( 'page_for_posts' ), 'News pages use their native page templates' );
check_source( (bool) get_field( 'banner_animation', $pages['index'] )['enabled'], 'Latest destination animation enabled' );
$home = $pages['index'];
$about = get_field( 'about_section', $home );
$edited = $about;
$edited['card_heading'] = 'Later editorial change';
update_field( 'field_asfar_home_about_about_section', $edited, $home );
asfar_update_source_revision();
check_source( get_field( 'about_section', $home )['card_heading'] === 'Later editorial change', 'Repeat visits preserve subsequent editor changes' );
update_field( 'field_asfar_home_about_about_section', $about, $home );
$backup = get_option( 'asfar_source_revision_backup' );
foreach ( $backup['menu_urls'] as $id => $url ) {
	$parts = explode( '#', $url, 2 );
	if ( isset( $backup['urls'][$parts[0]] ) ) {
		$expected = get_permalink( $backup['urls'][$parts[0]] ) . ( isset( $parts[1] ) ? '#' . $parts[1] : '' );
		check_source( get_post_meta( $id, '_menu_item_url', true ) === $expected, 'Imported menu retains its language destination after default switch' );
	}
}
