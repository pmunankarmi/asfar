<?php
/** Run only against the isolated, marked WordPress fixture installation. */
define( 'WP_ADMIN', true );
$_SERVER['HTTP_HOST'] = 'localhost';
$_SERVER['REQUEST_URI'] = '/wp-admin/';
require rtrim( $argv[1], '/' ) . '/wp-load.php';
if ( ! defined( 'ASFAR_SLUG_TEST_SITE' ) || ! ASFAR_SLUG_TEST_SITE ) {
	throw new RuntimeException( 'A marked test installation is required.' );
}
require dirname( __DIR__ ) . '/asfar/inc/content.php';
require dirname( __DIR__ ) . '/asfar/inc/content-types.php';
require dirname( __DIR__ ) . '/asfar/inc/team-types.php';
asfar_register_content_types();
$terms = array();
$members = array();
foreach ( array( 30, 10, 20 ) as $order ) {
	$result = wp_insert_term( 'Test type ' . $order . '-' . time(), 'team_department' );
	$id = $result['term_id'];
	update_term_meta( $id, 'display_order', $order );
	$terms[$order] = $id;
	$member = wp_insert_post( array( 'post_type' => 'asfar_team', 'post_status' => 'publish', 'post_title' => 'Test member' ) );
	wp_set_object_terms( $member, array( $id ), 'team_department' );
	$members[] = $member;
}
function check_team_type( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	echo 'PASS: ' . $message . "\n";
}
try {
	$first_page = get_terms( array( 'taxonomy' => 'team_department', 'include' => array_values( $terms ), 'hide_empty' => false, 'number' => 2 ) );
	check_team_type( array( $terms[10], $terms[20] ) === wp_list_pluck( $first_page, 'term_id' ), 'Admin menu order is applied before pagination' );
	update_term_meta( $terms[10], 'show_on_homepage', 0 );
	$visible = asfar_team_departments();
	check_team_type( array( $terms[20], $terms[30] ) === wp_list_pluck( $visible, 'term_id' ), 'Hidden type is excluded; legacy terms stay visible in menu order' );
	check_team_type( array( 0, 1 ) === array_keys( $visible ), 'First visible tab keeps index zero' );
} finally {
	foreach ( $members as $id ) { wp_delete_post( $id, true ); }
	foreach ( $terms as $id ) { wp_delete_term( $id, 'team_department' ); }
}
