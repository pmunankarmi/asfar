<?php
/** Run only against the isolated WordPress/Polylang test installation. */
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require rtrim( $argv[1], '/' ) . '/wp-load.php';
if ( ! defined( 'ASFAR_SLUG_TEST_SITE' ) || ! ASFAR_SLUG_TEST_SITE ) {
	throw new RuntimeException( 'An isolated test site is required.' );
}
require dirname( __DIR__ ) . '/asfar/inc/homepage-posts.php';
$ids = array();
try {
	foreach ( array( 'en', 'ar' ) as $language ) {
		$ids[$language] = wp_insert_post( array( 'post_title' => 'Homepage sync fixture', 'post_status' => 'publish' ) );
		pll_set_post_language( $ids[$language], $language );
	}
	pll_save_post_translations( $ids );
	foreach ( array( array( 'en', '1' ), array( 'ar', '0' ), array( 'ar', '1' ), array( 'en', '0' ) ) as $change ) {
		update_post_meta( $ids[$change[0]], 'show_on_homepage', $change[1] );
		foreach ( $ids as $id ) {
			if ( $change[1] !== get_post_meta( $id, 'show_on_homepage', true ) ) {
				throw new RuntimeException( 'Translation selection did not synchronize.' );
			}
		}
		echo 'PASS: ' . $change[0] . ' selection ' . $change[1] . " syncs both languages\n";
	}
} finally {
	foreach ( $ids as $id ) { wp_delete_post( $id, true ); }
}
