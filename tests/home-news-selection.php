<?php
/** Run against an isolated WordPress installation marked ASFAR_SLUG_TEST_SITE. */
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/';
require rtrim( $argv[1], '/' ) . '/wp-load.php';
if ( ! defined( 'ASFAR_SLUG_TEST_SITE' ) || ! ASFAR_SLUG_TEST_SITE ) {
	throw new RuntimeException( 'A marked test site is required.' );
}
require dirname( __DIR__ ) . '/asfar/inc/content.php';
$posts = array();
foreach ( array( 'selected', 'disabled', 'unset', 'draft', 'arabic' ) as $case ) {
	$id = wp_insert_post( array(
		'post_title' => 'Homepage selection ' . $case,
		'post_type' => 'post',
		'post_status' => 'draft' === $case ? 'draft' : 'publish',
	) );
	pll_set_post_language( $id, 'arabic' === $case ? 'ar' : 'en' );
	if ( 'unset' !== $case ) {
		update_post_meta( $id, 'show_on_homepage', 'disabled' === $case ? '0' : '1' );
	}
	$posts[$case] = $id;
}
function check_home_news( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	echo 'PASS: ' . $message . "\n";
}
try {
	PLL()->curlang = PLL()->model->get_language( 'en' );
	$home_ids = wp_list_pluck( asfar_news_query( true )->posts, 'ID' );
	check_home_news( in_array( $posts['selected'], $home_ids, true ), 'Selected published post appears' );
	foreach ( array( 'disabled', 'unset', 'draft', 'arabic' ) as $case ) {
		check_home_news( ! in_array( $posts[$case], $home_ids, true ), $case . ' post is excluded from English homepage' );
	}
	$archive_ids = wp_list_pluck( asfar_news_query()->posts, 'ID' );
	check_home_news( in_array( $posts['disabled'], $archive_ids, true ) && in_array( $posts['unset'], $archive_ids, true ), 'Full news archive is unaffected' );
	update_post_meta( $posts['selected'], 'show_on_homepage', '0' );
	check_home_news( ! in_array( $posts['selected'], wp_list_pluck( asfar_news_query( true )->posts, 'ID' ), true ), 'Turning off the toggle removes the post' );
	PLL()->curlang = PLL()->model->get_language( 'ar' );
	check_home_news( in_array( $posts['arabic'], wp_list_pluck( asfar_news_query( true )->posts, 'ID' ), true ), 'Arabic homepage uses its own selected posts' );
} finally {
	foreach ( $posts as $id ) { wp_delete_post( $id, true ); }
}
