<?php
/** Homepage selection shared by linked news translations. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function asfar_homepage_post_ids( $post_id ) {
	$translations = function_exists( 'pll_get_post_translations' ) ? pll_get_post_translations( $post_id ) : array();
	$ids = array_unique( array_merge( array( (int) $post_id ), array_map( 'intval', array_values( $translations ) ) ) );
	return array_values( array_filter( $ids, function ( $id ) { return 'post' === get_post_type( $id ); } ) );
}

/** Keep the stored value identical, whether edited in ACF or the Posts list. */
function asfar_sync_homepage_post( $post_id, $enabled ) {
	static $syncing = false;
	if ( $syncing || 'post' !== get_post_type( $post_id ) ) {
		return;
	}
	$syncing = true;
	foreach ( asfar_homepage_post_ids( $post_id ) as $id ) {
		update_post_meta( $id, 'show_on_homepage', $enabled ? '1' : '0' );
	}
	$syncing = false;
}

function asfar_homepage_meta_changed( $meta_id, $post_id, $key, $value ) {
	if ( 'show_on_homepage' === $key ) {
		asfar_sync_homepage_post( $post_id, '1' === (string) $value );
	}
}
add_action( 'added_post_meta', 'asfar_homepage_meta_changed', 10, 4 );
add_action( 'updated_post_meta', 'asfar_homepage_meta_changed', 10, 4 );
// A newly linked translation inherits the selection already made for its story.
add_action( 'pll_save_post', function ( $post_id ) {
	foreach ( asfar_homepage_post_ids( $post_id ) as $id ) {
		if ( '1' === get_post_meta( $id, 'show_on_homepage', true ) ) {
			asfar_sync_homepage_post( $post_id, true );
			break;
		}
	}
} );

// On upgrade, retain existing selections and include their linked translations.
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) || ! function_exists( 'pll_get_post_translations' ) || get_option( 'asfar_homepage_translation_sync' ) ) {
		return;
	}
	$ids = get_posts( array(
		'post_type' => 'post', 'post_status' => 'any', 'posts_per_page' => -1,
		'fields' => 'ids', 'meta_key' => 'show_on_homepage', 'meta_value' => '1',
		'suppress_filters' => true, 'lang' => '',
	) );
	foreach ( $ids as $id ) {
		asfar_sync_homepage_post( $id, true );
	}
	update_option( 'asfar_homepage_translation_sync', 1, false );
} );

add_filter( 'manage_post_posts_columns', function ( $columns ) {
	$columns['mt_homepage'] = 'Show on homepage';
	return $columns;
} );
add_action( 'manage_post_posts_custom_column', function ( $column, $post_id ) {
	if ( 'mt_homepage' !== $column ) {
		return;
	}
	$can_edit = true;
	foreach ( asfar_homepage_post_ids( $post_id ) as $id ) {
		$can_edit = $can_edit && current_user_can( 'edit_post', $id );
	}
	printf(
		'<input type="checkbox" class="mt-homepage-toggle" data-post-id="%d" aria-label="%s" %s %s><span class="mt-homepage-status" role="status"></span>',
		(int) $post_id,
		esc_attr( 'Show on homepage: ' . get_the_title( $post_id ) ),
		checked( '1', get_post_meta( $post_id, 'show_on_homepage', true ), false ),
		disabled( false, $can_edit, false )
	);
}, 10, 2 );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'edit.php' !== $hook || 'post' !== get_current_screen()->post_type ) {
		return;
	}
	wp_enqueue_script( 'asfar-homepage-posts', get_template_directory_uri() . '/assets/js/homepage-posts.js', array( 'jquery' ), ASFAR_VERSION, true );
	wp_localize_script( 'asfar-homepage-posts', 'asfarHomepagePosts', array(
		'url' => admin_url( 'admin-ajax.php' ), 'nonce' => wp_create_nonce( 'asfar_homepage_post' ),
	) );
} );

add_action( 'wp_ajax_asfar_homepage_post', function () {
	check_ajax_referer( 'asfar_homepage_post', 'nonce' );
	$post_id = absint( $_POST['post_id'] ?? 0 );
	$ids = asfar_homepage_post_ids( $post_id );
	if ( ! $ids ) {
		wp_send_json_error( 'Post not found.', 404 );
	}
	foreach ( $ids as $id ) {
		if ( ! current_user_can( 'edit_post', $id ) ) {
			wp_send_json_error( 'You cannot edit this post or its translation.', 403 );
		}
	}
	$enabled = '1' === ( $_POST['enabled'] ?? '' );
	asfar_sync_homepage_post( $post_id, $enabled );
	wp_send_json_success( array( 'ids' => $ids, 'enabled' => $enabled ) );
} );
