<?php
/**
 * Integration checks against an isolated WordPress + Polylang installation.
 * Run: php tests/shared-slugs.php /path/to/wordpress admin
 * Then: php tests/shared-slugs.php /path/to/wordpress frontend
 * The test site's wp-config.php must define ASFAR_SLUG_TEST_SITE as true.
 */
$mode = $argv[2] ?? 'frontend';
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
if ( 'admin' === $mode ) {
	define( 'WP_ADMIN', true );
}
require rtrim( $argv[1], '/' ) . '/wp-load.php';
if ( ! defined( 'ASFAR_SLUG_TEST_SITE' ) || ! ASFAR_SLUG_TEST_SITE ) {
	throw new RuntimeException( 'Refusing to create fixtures outside a marked test site.' );
}
require dirname( __DIR__ ) . '/asfar/inc/polylang-slug.php';
require dirname( __DIR__ ) . '/asfar/inc/slug-migration.php';

function check_shared_slug( $condition, $message ) {
	if ( ! $condition ) {
		throw new RuntimeException( 'FAIL: ' . $message );
	}
	echo 'PASS: ' . $message . "\n";
}

if ( 'admin' === $mode ) {
	wp_set_current_user( 1 );
	$slug = 'test-supplier-' . time();
	$fixtures = array();

	foreach ( array( 'page', 'post' ) as $type ) {
		$pair = array();
		foreach ( array( 'en', 'ar' ) as $language ) {
			$id = wp_insert_post( array(
				'post_type' => $type,
				'post_status' => 'publish',
				'post_title' => 'Test supplier ' . $language,
				'post_content' => 'Preserve this content.',
				'post_name' => $slug . '-' . $type . ( 'ar' === $language ? '-ar' : '' ),
			) );
			pll_set_post_language( $id, $language );
			$pair[$language] = $id;
		}
		pll_save_post_translations( $pair );
		$fixtures[$type] = $pair;
	}

	$arabic_id = $fixtures['page']['ar'];
	$old_url = get_permalink( $arabic_id );
	$menu = wp_create_nav_menu( 'Slug test ' . time() );
	$menu_item = wp_update_nav_menu_item( $menu, 0, array(
		'menu-item-title' => 'Supplier section',
		'menu-item-type' => 'custom',
		'menu-item-url' => $old_url . '#details',
		'menu-item-status' => 'publish',
	) );

	check_shared_slug( array() === asfar_slug_apply(), 'Migration completes without errors' );
	foreach ( $fixtures as $type => $pair ) {
		check_shared_slug( get_post_field( 'post_name', $pair['en'] ) === get_post_field( 'post_name', $pair['ar'] ), $type . ' translations share their slug' );
		check_shared_slug( get_post_field( 'post_content', $pair['ar'] ) === 'Preserve this content.', $type . ' content is preserved' );
		check_shared_slug( pll_get_post( $pair['en'], 'ar' ) === $pair['ar'], $type . ' translation link is preserved' );
	}

	$shared_slug = get_post_field( 'post_name', $arabic_id );
	$duplicate = wp_insert_post( array( 'post_type' => 'page', 'post_title' => 'Duplicate Arabic', 'post_status' => 'publish', 'post_name' => 'duplicate-' . time() ) );
	pll_set_post_language( $duplicate, 'ar' );
	check_shared_slug( wp_unique_post_slug( $shared_slug, $duplicate, 'publish', 'page', 0 ) !== $shared_slug, 'Same-language duplicates remain blocked' );
	check_shared_slug( wp_unique_post_slug( 'feed', $duplicate, 'publish', 'page', 0 ) !== 'feed', 'Reserved feed slug remains blocked' );
	check_shared_slug( get_post_meta( $menu_item, '_menu_item_url', true ) === get_permalink( $arabic_id ) . '#details', 'Custom menu link keeps its anchor' );
	check_shared_slug( array() === asfar_slug_apply(), 'Migration can be retried safely' );
	$fixtures['old_url'] = $old_url;
	update_option( 'asfar_slug_test_fixtures', $fixtures );
	wp_delete_post( $duplicate, true );
	exit;
}

$fixtures = get_option( 'asfar_slug_test_fixtures' );
foreach ( array( 'en', 'ar', 'en', 'ar' ) as $language ) {
	PLL()->curlang = PLL()->model->get_language( $language );
	foreach ( array( 'page', 'post' ) as $type ) {
		$id = $fixtures[$type][$language];
		$slug = get_post_field( 'post_name', $id );
		$args = array( 'lang' => $language, 'post_type' => $type );
		$args[ 'page' === $type ? 'pagename' : 'name' ] = $slug;
		$query = new WP_Query( $args );
		check_shared_slug( array( $id ) === wp_list_pluck( $query->posts, 'ID' ), $language . ' ' . $type . ' resolves correctly with a warm cache' );
	}
}
$old_path = wp_parse_url( $fixtures['old_url'], PHP_URL_PATH );
check_shared_slug( asfar_old_slug_destination( $old_path . '?source=test' ) === get_permalink( $fixtures['page']['ar'] ) . '?source=test', 'Old page URL redirects and keeps query parameters' );
$new_path = wp_parse_url( get_permalink( $fixtures['page']['ar'] ), PHP_URL_PATH );
check_shared_slug( '' === asfar_old_slug_destination( $new_path ), 'Current page URL does not redirect to itself' );
