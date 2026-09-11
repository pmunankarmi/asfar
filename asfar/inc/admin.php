<?php
/** Keep administration focused on the site's PHP-defined content fields. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Hide ACF's field-definition screens; retain all registered editing fields and options pages.
add_filter( 'acf/settings/show_admin', '__return_false' );

// Close discussion regardless of legacy post settings, including REST and XML-RPC submissions.
add_filter( 'comments_open', '__return_false', 100 );
add_filter( 'pings_open', '__return_false', 100 );
add_filter( 'comments_array', '__return_empty_array', 100 );
add_filter( 'pre_option_default_comment_status', function () { return 'closed'; } );
add_filter( 'pre_option_default_ping_status', function () { return 'closed'; } );
add_filter( 'feed_links_show_comments_feed', '__return_false' );

add_action( 'init', function () {
	foreach ( get_post_types() as $post_type ) {
		remove_post_type_support( $post_type, 'comments' );
		remove_post_type_support( $post_type, 'trackbacks' );
	}
}, 100 );

add_action( 'admin_menu', function () {
	remove_menu_page( 'edit-comments.php' );
}, 100 );

add_action( 'admin_bar_menu', function ( $admin_bar ) {
	$admin_bar->remove_node( 'comments' );
}, 100 );

add_action( 'admin_init', function () {
	global $pagenow;
	if ( in_array( $pagenow, array( 'edit-comments.php', 'comment.php' ), true ) ) {
		wp_safe_redirect( admin_url() );
		exit;
	}
} );

add_action( 'template_redirect', function () {
	if ( is_comment_feed() ) {
		wp_safe_redirect( home_url( '/' ) );
		exit;
	}
} );
