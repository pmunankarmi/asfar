<?php
/** Drag rows in native WordPress lists; store the same order used by the theme. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Insert a moved item beside another sibling, retaining items on other pages. */
function asfar_move_list_item( $scope, $moved_id, $target_id, $placement ) {
	if ( ! current_user_can( 'manage_options' ) || ! isset( asfar_order_scopes()[ $scope ] ) ) {
		return new WP_Error( 'permission', 'You cannot reorder this content.' );
	}
	if ( ! in_array( $placement, array( 'before', 'after' ), true ) || $moved_id === $target_id ) {
		return new WP_Error( 'invalid', 'Choose a different item to reorder.' );
	}
	list( $kind, $name ) = explode( ':', $scope, 2 );
	$is_post = 'post' === $kind;
	$moved = $is_post ? get_post( $moved_id ) : get_term( $moved_id, $name );
	$target = $is_post ? get_post( $target_id ) : get_term( $target_id, $name );
	foreach ( array( $moved, $target ) as $item ) {
		if ( ! $item || is_wp_error( $item ) || ( $is_post && $item->post_type !== $name ) ) {
			return new WP_Error( 'invalid', 'The item no longer belongs to this list. Reload and try again.' );
		}
	}
	$parent = $is_post ? $moved->post_parent : $moved->parent;
	if ( (int) $parent !== (int) ( $is_post ? $target->post_parent : $target->parent ) ) {
		return new WP_Error( 'parent', 'Drag within the same parent group. Use Content Order to see sibling groups.' );
	}
	$language_function = $is_post ? 'pll_get_post_language' : 'pll_get_term_language';
	$language = function_exists( $language_function ) ? $language_function( $moved_id ) : '';
	$target_language = function_exists( $language_function ) ? $language_function( $target_id ) : '';
	if ( $language !== $target_language ) {
		return new WP_Error( 'language', 'Drag within the same language. Select a language in the list filter first.' );
	}

	$groups = array();
	foreach ( asfar_order_items( $scope, $language ?: '' ) as $item ) {
		$item_parent = $is_post ? $item->post_parent : $item->parent;
		$groups[ $item_parent ][] = (int) ( $is_post ? $item->ID : $item->term_id );
	}
	$siblings = $groups[ $parent ] ?? array();
	if ( ! in_array( $moved_id, $siblings, true ) || ! in_array( $target_id, $siblings, true ) ) {
		return new WP_Error( 'stale', 'The list has changed. Reload before reordering.' );
	}
	$siblings = array_values( array_diff( $siblings, array( $moved_id ) ) );
	$position = array_search( $target_id, $siblings, true ) + ( 'after' === $placement ? 1 : 0 );
	array_splice( $siblings, $position, 0, array( $moved_id ) );
	$groups[ $parent ] = $siblings;
	return asfar_save_content_order( $scope, $language ?: '', $groups );
}

function asfar_list_order_columns( $columns ) {
	if ( ! current_user_can( 'manage_options' ) ) {
		return $columns;
	}
	$result = array();
	foreach ( $columns as $key => $label ) {
		$result[ $key ] = $label;
		if ( 'cb' === $key ) {
			$result['mt_order'] = 'Order';
		}
	}
	return $result;
}

function asfar_list_order_handle( $id, $order ) {
	return '<span class="mt-list-order-handle dashicons dashicons-menu" data-id="' . esc_attr( $id ) . '" title="Drag to reorder" aria-hidden="true"></span> <span class="mt-list-order-number">' . esc_html( $order ) . '</span>';
}

add_action( 'init', function () {
	foreach ( asfar_order_scopes() as $scope => $label ) {
		list( $kind, $name ) = explode( ':', $scope, 2 );
		if ( 'post' === $kind ) {
			add_post_type_support( $name, 'page-attributes' );
		}
	}
}, 20 );

add_action( 'admin_init', function () {
	foreach ( asfar_order_scopes() as $scope => $label ) {
		list( $kind, $name ) = explode( ':', $scope, 2 );
		if ( 'post' === $kind ) {
			add_filter( "manage_{$name}_posts_columns", 'asfar_list_order_columns' );
			add_action( "manage_{$name}_posts_custom_column", function ( $column, $id ) {
				if ( 'mt_order' === $column ) {
					echo asfar_list_order_handle( $id, get_post_field( 'menu_order', $id ) );
				}
			}, 10, 2 );
		} else {
			add_filter( "manage_edit-{$name}_columns", 'asfar_list_order_columns' );
			add_filter( "manage_{$name}_custom_column", function ( $content, $column, $id ) {
				return 'mt_order' === $column ? asfar_list_order_handle( $id, (int) get_term_meta( $id, 'display_order', true ) ) : $content;
			}, 10, 3 );
		}
	}
} );

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( ! in_array( $hook, array( 'edit.php', 'edit-tags.php' ), true ) || ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	$scope = 'edit-tags.php' === $hook ? 'term:' . $screen->taxonomy : 'post:' . $screen->post_type;
	if ( ! isset( asfar_order_scopes()[ $scope ] ) || 'trash' === ( $_GET['post_status'] ?? '' ) ) {
		return;
	}
	wp_enqueue_style( 'asfar-list-order', get_template_directory_uri() . '/assets/css/admin/list-order.css', array(), ASFAR_VERSION );
	wp_enqueue_script( 'asfar-list-order', get_template_directory_uri() . '/assets/js/list-order.js', array( 'jquery-ui-sortable' ), ASFAR_VERSION, true );
	wp_localize_script( 'asfar-list-order', 'asfarListOrder', array(
		'url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'asfar_list_order' ),
		'scope' => $scope,
	) );
} );

add_action( 'wp_ajax_asfar_list_order', function () {
	check_ajax_referer( 'asfar_list_order', 'nonce' );
	$result = asfar_move_list_item(
		sanitize_text_field( wp_unslash( $_POST['scope'] ?? '' ) ),
		absint( $_POST['moved'] ?? 0 ),
		absint( $_POST['target'] ?? 0 ),
		sanitize_key( $_POST['placement'] ?? '' )
	);
	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_message(), 400 );
	}
	wp_send_json_success( 'Order saved.' );
} );
