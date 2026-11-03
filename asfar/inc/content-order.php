<?php
/** Drag-and-drop ordering for content and taxonomy terms, without a plugin. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Only expose public content types that have an editing screen. */
function asfar_order_scopes() {
	$scopes = array();
	foreach ( get_post_types( array( 'public' => true, 'show_ui' => true ), 'objects' ) as $type ) {
		if ( 'attachment' !== $type->name ) {
			$scopes[ 'post:' . $type->name ] = $type->labels->name;
		}
	}
	foreach ( get_taxonomies( array( 'public' => true, 'show_ui' => true ), 'objects' ) as $taxonomy ) {
		if ( 'post_format' !== $taxonomy->name ) {
			$scopes[ 'term:' . $taxonomy->name ] = $taxonomy->labels->name . ' (taxonomy)';
		}
	}
	return $scopes;
}

/** Return every item in a language so ordering is not limited by pagination. */
function asfar_order_items( $scope, $language ) {
	list( $kind, $name ) = explode( ':', $scope, 2 );
	if ( 'post' === $kind ) {
		return get_posts( array(
			'post_type'        => $name,
			'post_status'      => array( 'publish', 'private', 'draft', 'pending', 'future' ),
			'posts_per_page'   => -1,
			'lang'             => $language,
			'suppress_filters' => false,
			'orderby'          => array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'ASC' ),
		) );
	}
	$terms = get_terms( array( 'taxonomy' => $name, 'hide_empty' => false, 'lang' => $language ) );
	return is_wp_error( $terms ) ? array() : asfar_sort_team_types( $terms );
}

/** Validate the complete submitted list before changing any stored order. */
function asfar_save_content_order( $scope, $language, $groups ) {
	if ( ! current_user_can( 'manage_options' ) || ! isset( asfar_order_scopes()[ $scope ] ) ) {
		return new WP_Error( 'permission', 'You cannot reorder this content.' );
	}
	if ( ! is_array( $groups ) ) {
		return new WP_Error( 'invalid', 'Invalid order. Reload the page and try again.' );
	}
	$items = asfar_order_items( $scope, $language );
	$is_post = str_starts_with( $scope, 'post:' );
	$expected = array();
	foreach ( $items as $item ) {
		$id = $is_post ? $item->ID : $item->term_id;
		$parent = $is_post ? $item->post_parent : $item->parent;
		$expected[$id] = (int) $parent;
	}
	$submitted = array();
	foreach ( $groups as $parent => $ids ) {
		if ( ! is_array( $ids ) ) {
			return new WP_Error( 'invalid', 'Invalid ordering group.' );
		}
		foreach ( $ids as $id ) {
			$id = absint( $id );
			if ( ! isset( $expected[$id] ) || isset( $submitted[$id] ) || $expected[$id] !== (int) $parent ) {
				return new WP_Error( 'stale', 'Content changed or a parent group does not match. Reload before saving.' );
			}
			if ( ! current_user_can( $is_post ? 'edit_post' : 'edit_term', $id ) ) {
				return new WP_Error( 'permission', 'You cannot edit one of these items.' );
			}
			$submitted[$id] = true;
		}
	}
	if ( count( $submitted ) !== count( $expected ) ) {
		return new WP_Error( 'stale', 'The list has changed. Reload before saving.' );
	}

	foreach ( $groups as $ids ) {
		foreach ( array_values( $ids ) as $position => $id ) {
			if ( $is_post ) {
				$result = wp_update_post( array( 'ID' => absint( $id ), 'menu_order' => $position ), true );
			} else {
				$result = update_term_meta( absint( $id ), 'display_order', $position );
			}
			if ( is_wp_error( $result ) ) {
				return $result;
			}
		}
	}
	$ordered_scopes = get_option( 'asfar_ordered_scopes', array() );
	$ordered_scopes[$scope] = true;
	update_option( 'asfar_ordered_scopes', $ordered_scopes, false );
	return true;
}

add_action( 'admin_menu', function () {
	add_management_page( 'Content Order', 'Content Order', 'manage_options', 'asfar-content-order', 'asfar_render_content_order' );
} );

function asfar_render_content_order() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$scopes = asfar_order_scopes();
	$scope = sanitize_text_field( wp_unslash( $_GET['scope'] ?? 'post:post' ) );
	if ( ! isset( $scopes[$scope] ) ) {
		$scope = 'post:post';
	}
	$languages = function_exists( 'pll_languages_list' ) ? pll_languages_list() : array();
	$language = sanitize_key( $_GET['language'] ?? ( function_exists( 'pll_default_language' ) ? pll_default_language() : '' ) );
	if ( $languages && ! in_array( $language, $languages, true ) ) {
		$language = $languages[0];
	}
	$is_post = str_starts_with( $scope, 'post:' );
	$groups = array();
	foreach ( asfar_order_items( $scope, $language ) as $item ) {
		$parent = $is_post ? $item->post_parent : $item->parent;
		$groups[$parent][] = $item;
	}
	?>
	<div class="wrap">
		<h1>Content Order</h1>
		<p>Drag items within their parent group, or use the up/down buttons. Click Save Order when finished.</p>
		<form method="get">
			<input type="hidden" name="page" value="asfar-content-order">
			<label for="content-order-scope">Content</label>
			<select id="content-order-scope" name="scope">
				<?php foreach ( $scopes as $value => $label ) : ?>
					<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $scope, $value ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<?php if ( $languages ) : ?>
				<label for="content-order-language">Language</label>
				<select id="content-order-language" name="language">
					<?php foreach ( $languages as $value ) : ?>
						<option value="<?php echo esc_attr( $value ); ?>" <?php selected( $language, $value ); ?>><?php echo esc_html( $value ); ?></option>
					<?php endforeach; ?>
				</select>
			<?php endif; ?>
			<button class="button">Load items</button>
		</form>
		<div class="mt-content-order" data-scope="<?php echo esc_attr( $scope ); ?>" data-language="<?php echo esc_attr( $language ); ?>">
			<?php foreach ( $groups as $parent => $items ) : ?>
				<h2><?php echo $parent ? esc_html( 'Children of ' . ( $is_post ? get_the_title( $parent ) : get_term( $parent )->name ) ) : 'Top-level items'; ?></h2>
				<ul class="mt-order-list" data-parent="<?php echo esc_attr( $parent ); ?>">
					<?php foreach ( $items as $item ) : ?>
						<?php $id = $is_post ? $item->ID : $item->term_id; ?>
						<li class="mt-order-item" data-id="<?php echo esc_attr( $id ); ?>">
							<span class="mt-order-handle dashicons dashicons-menu" aria-hidden="true"></span>
							<span class="mt-order-title"><?php echo esc_html( ( $is_post ? $item->post_title : $item->name ) ?: '(No title)' ); ?></span>
							<button type="button" class="button mt-order-up" aria-label="Move up">↑</button>
							<button type="button" class="button mt-order-down" aria-label="Move down">↓</button>
						</li>
					<?php endforeach; ?>
				</ul>
			<?php endforeach; ?>
			<?php if ( $groups ) : ?>
				<button type="button" class="button button-primary" id="save-content-order">Save Order</button>
			<?php else : ?>
				<p>No items found for this selection.</p>
			<?php endif; ?>
			<p id="content-order-status" role="status" aria-live="polite"></p>
		</div>
	</div>
	<?php
}

add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( 'tools_page_asfar-content-order' !== $hook ) {
		return;
	}
	$uri = get_template_directory_uri();
	wp_enqueue_style( 'asfar-content-order', $uri . '/assets/css/admin/content-order.css', array(), ASFAR_VERSION );
	wp_enqueue_script( 'asfar-content-order', $uri . '/assets/js/content-order.js', array( 'jquery-ui-sortable' ), ASFAR_VERSION, true );
	wp_localize_script( 'asfar-content-order', 'asfarContentOrder', array(
		'url' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce( 'asfar_content_order' ),
	) );
} );

add_action( 'wp_ajax_asfar_content_order', function () {
	check_ajax_referer( 'asfar_content_order', 'nonce' );
	$scope = sanitize_text_field( wp_unslash( $_POST['scope'] ?? '' ) );
	$language = sanitize_key( $_POST['language'] ?? '' );
	$groups = json_decode( wp_unslash( $_POST['groups'] ?? '' ), true );
	$result = asfar_save_content_order( $scope, $language, $groups );
	if ( is_wp_error( $result ) ) {
		wp_send_json_error( $result->get_error_message(), 400 );
	}
	wp_send_json_success( 'Order saved.' );
} );

// Offer an Order link alongside each native list's existing view filters.
add_action( 'admin_init', function () {
	foreach ( asfar_order_scopes() as $scope => $label ) {
		list( $kind, $name ) = explode( ':', $scope, 2 );
		$screen = 'edit-' . $name;
		add_filter( 'views_' . $screen, function ( $views ) use ( $scope ) {
			if ( current_user_can( 'manage_options' ) ) {
				$url = add_query_arg( array( 'page' => 'asfar-content-order', 'scope' => $scope ), admin_url( 'tools.php' ) );
				$views['asfar_order'] = '<a href="' . esc_url( $url ) . '">Drag &amp; Drop Order</a>';
			}
			return $views;
		} );
	}
} );

// Respect manual post ordering in native admin lists after it has been saved.
add_action( 'pre_get_posts', function ( $query ) {
	if ( ! is_admin() || ! $query->is_main_query() || isset( $_GET['orderby'] ) ) {
		return;
	}
	$type = $query->get( 'post_type' ) ?: 'post';
	$scopes = get_option( 'asfar_ordered_scopes', array() );
	if ( is_string( $type ) && ! empty( $scopes[ 'post:' . $type ] ) ) {
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC', 'ID' => 'ASC' ) );
	}
} );

add_filter( 'terms_clauses', function ( $clauses, $taxonomies ) {
	if ( 1 !== count( $taxonomies ) || 'team_department' === $taxonomies[0] || isset( $_GET['orderby'] ) ) {
		return $clauses;
	}
	$scopes = get_option( 'asfar_ordered_scopes', array() );
	if ( empty( $scopes[ 'term:' . $taxonomies[0] ] ) ) {
		return $clauses;
	}
	global $wpdb;
	$clauses['orderby'] = "ORDER BY COALESCE((SELECT CAST(meta_value AS SIGNED) FROM {$wpdb->termmeta} WHERE term_id = t.term_id AND meta_key = 'display_order' LIMIT 1), 0) ASC, t.term_id ASC";
	$clauses['order'] = '';
	return $clauses;
}, 10, 2 );
