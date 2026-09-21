<?php
/** Run only in the isolated WordPress test site. */
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
require dirname( __DIR__ ) . '/asfar/inc/content-order.php';
require dirname( __DIR__ ) . '/asfar/inc/list-order.php';
asfar_register_content_types();
wp_set_current_user( 1 );
function verify_order( $condition, $message ) {
	if ( ! $condition ) { throw new RuntimeException( $message ); }
	echo 'PASS: ' . $message . "\n";
}
$created = array();
$old_scopes = get_option( 'asfar_ordered_scopes', array() );
try {
	foreach ( array( 'post', 'asfar_team', 'asfar_project', 'page' ) as $type ) {
		$ids = array();
		foreach ( array( 'First', 'Second' ) as $title ) {
			$id = wp_insert_post( array( 'post_type' => $type, 'post_status' => 'publish', 'post_title' => $title ) );
			pll_set_post_language( $id, 'en' );
			$ids[] = $id;
			$created[] = $id;
		}
		$items = asfar_order_items( 'post:' . $type, 'en' );
		$groups = array();
		foreach ( $items as $item ) { $groups[$item->post_parent][] = $item->ID; }
		$groups[0] = array_reverse( $groups[0] );
		verify_order( true === asfar_save_content_order( 'post:' . $type, 'en', $groups ), $type . ' order saves' );
		verify_order( $groups[0] === array_values( array_map( function ( $item ) { return $item->ID; }, array_filter( asfar_order_items( 'post:' . $type, 'en' ), function ( $item ) { return 0 === (int) $item->post_parent; } ) ) ), $type . ' order persists' );
		$missing = $groups;
		array_pop( $missing[0] );
		verify_order( is_wp_error( asfar_save_content_order( 'post:' . $type, 'en', $missing ) ), 'Incomplete list rejected' );
		$wrong_parent = $groups;
		$wrong_parent[999999] = array( array_pop( $wrong_parent[0] ) );
		verify_order( is_wp_error( asfar_save_content_order( 'post:' . $type, 'en', $wrong_parent ) ), 'Parent changes rejected' );
		if ( 'post' === $type ) {
			foreach ( $ids as $id ) { update_post_meta( $id, 'show_on_homepage', '1' ); }
			PLL()->curlang = PLL()->model->get_language( 'en' );
			$home_ids = wp_list_pluck( asfar_news_query( true )->posts, 'ID' );
			$expected_home = array_values( array_intersect( $groups[0], $ids ) );
			verify_order( $expected_home === array_values( array_intersect( $home_ids, $ids ) ), 'Homepage follows saved post order' );
		}
		// Moving two visible rows must retain every unseen sibling.
		$before = $groups[0];
		$moved_id = $before[0];
		$target_id = $before[count( $before ) - 1];
		verify_order( true === asfar_move_list_item( 'post:' . $type, $moved_id, $target_id, 'after' ), $type . ' native row move saves' );
		$after = wp_list_pluck( asfar_order_items( 'post:' . $type, 'en' ), 'ID' );
		verify_order( array_merge( array_slice( $before, 1 ), array( $moved_id ) ) === $after, 'Unseen rows retain their relative order' );
		verify_order( is_wp_error( asfar_move_list_item( 'post:' . $type, $moved_id, $target_id, 'invalid' ) ), 'Invalid move rejected' );
		if ( 'page' === $type ) {
			wp_update_post( array( 'ID' => $moved_id, 'post_parent' => $target_id ) );
			verify_order( is_wp_error( asfar_move_list_item( 'post:page', $moved_id, $target_id, 'before' ) ), 'Native move cannot change parent groups' );
			wp_update_post( array( 'ID' => $moved_id, 'post_parent' => 0 ) );
		}
		pll_set_post_language( $moved_id, 'ar' );
		verify_order( is_wp_error( asfar_move_list_item( 'post:' . $type, $moved_id, $target_id, 'before' ) ), 'Native move cannot cross languages' );
		pll_set_post_language( $moved_id, 'en' );
		$bad = $groups;
		$bad[0][] = $groups[0][0];
		verify_order( is_wp_error( asfar_save_content_order( 'post:' . $type, 'en', $bad ) ), 'Duplicate IDs rejected' );
		verify_order( is_wp_error( asfar_save_content_order( 'post:' . $type, 'ar', $groups ) ), 'Wrong language rejected' );
	}
	$term_ids = array();
	foreach ( array( 'One', 'Two' ) as $title ) {
		$result = wp_insert_term( $title . time(), 'team_department' );
		$term_ids[] = $result['term_id'];
		pll_set_term_language( $result['term_id'], 'en' );
	}
	$desired = array_reverse( $term_ids );
	verify_order( true === asfar_save_content_order( 'term:team_department', 'en', array( 0 => $desired ) ), 'Taxonomy order saves' );
	verify_order( $desired === wp_list_pluck( asfar_order_items( 'term:team_department', 'en' ), 'term_id' ), 'Taxonomy order matches admin and homepage comparator' );
	verify_order( true === asfar_move_list_item( 'term:team_department', $desired[0], $desired[1], 'after' ), 'Native taxonomy move saves' );
	verify_order( $term_ids === wp_list_pluck( asfar_order_items( 'term:team_department', 'en' ), 'term_id' ), 'Native taxonomy move persists' );
	foreach ( $term_ids as $id ) { wp_delete_term( $id, 'team_department' ); }
	wp_set_current_user( 0 );
	verify_order( is_wp_error( asfar_move_list_item( 'post:post', 1, 2, 'after' ) ), 'Unauthorized native move rejected' );
	verify_order( is_wp_error( asfar_save_content_order( 'post:post', 'en', array() ) ), 'Unauthorized save rejected' );
} finally {
	wp_set_current_user( 1 );
	foreach ( $created as $id ) { wp_delete_post( $id, true ); }
	update_option( 'asfar_ordered_scopes', $old_scopes );
}
