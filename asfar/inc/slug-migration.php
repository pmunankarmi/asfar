<?php
/** Align linked translations and keep their old URLs working. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Collect published Arabic translations, with parents before children. */
function asfar_slug_plan() {
	$translations = array();

	foreach ( get_post_types( array( 'public' => true ) ) as $post_type ) {
		if ( 'attachment' === $post_type || ! pll_is_translated_post_type( $post_type ) ) {
			continue;
		}

		$english_posts = get_posts( array(
			'post_type'        => $post_type,
			'post_status'      => 'publish',
			'numberposts'      => -1,
			'lang'             => 'en',
			'suppress_filters' => false,
		) );

		foreach ( $english_posts as $english_post ) {
			$arabic_id = pll_get_post( $english_post->ID, 'ar' );
			$arabic_post = $arabic_id ? get_post( $arabic_id ) : null;

			if ( ! $arabic_post || 'publish' !== $arabic_post->post_status || $arabic_post->post_type !== $post_type ) {
				continue;
			}

			$translations[] = array(
				'id'       => $arabic_id,
				'source'   => $english_post->ID,
				'title'    => $arabic_post->post_title,
				'old_slug' => $arabic_post->post_name,
				'slug'     => $english_post->post_name,
				'old_url'  => get_permalink( $arabic_id ),
				'depth'    => count( get_post_ancestors( $arabic_id ) ),
			);
		}
	}

	usort( $translations, function ( $first, $second ) {
		return $first['depth'] <=> $second['depth'];
	} );

	return $translations;
}

/** Save original URLs before changing any slugs, so retries remain safe. */
function asfar_slug_apply() {
	if ( empty( PLL()->options['force_lang'] ) || ! get_option( 'permalink_structure' ) ) {
		throw new RuntimeException( 'Enable pretty permalinks and language-specific URLs before sharing slugs.' );
	}

	$translations = asfar_slug_plan();
	$history = get_option( 'asfar_slug_history', array() );

	// Include children whose own slug is unchanged: their parent URL may change.
	foreach ( $translations as $translation ) {
		$history[ $translation['old_url'] ] = $translation;
		if ( 'page' === get_post_type( $translation['id'] ) ) {
			// The front page can also be linked through its explicit page slug.
			$page_path = get_page_uri( $translation['id'] );
			$page_url = trailingslashit( pll_home_url( 'ar' ) ) . user_trailingslashit( $page_path );
			$history[ $page_url ] = $translation;
		}
	}
	update_option( 'asfar_slug_history', $history, false );

	$errors = array();
	foreach ( $translations as $translation ) {
		if ( $translation['old_slug'] === $translation['slug'] ) {
			continue;
		}

		$post = get_post( $translation['id'] );
		$available_slug = wp_unique_post_slug(
			$translation['slug'],
			$post->ID,
			$post->post_status,
			$post->post_type,
			$post->post_parent
		);

		if ( $available_slug !== $translation['slug'] ) {
			$errors[] = $translation['title'] . ': a same-language or reserved slug conflicts.';
			continue;
		}

		$result = wp_update_post( array(
			'ID'        => $translation['id'],
			'post_name' => $translation['slug'],
		), true );

		if ( is_wp_error( $result ) || get_post_field( 'post_name', $post->ID ) !== $translation['slug'] ) {
			$errors[] = $translation['title'] . ': could not update slug.';
		}
	}

	asfar_update_slug_menu_links( $history );
	return $errors;
}

/** Page menu items follow permalinks automatically; custom links need updating. */
function asfar_update_slug_menu_links( $history ) {
	foreach ( wp_get_nav_menus() as $menu ) {
		foreach ( wp_get_nav_menu_items( $menu->term_id ) ?: array() as $item ) {
			if ( 'custom' !== $item->type ) {
				continue;
			}

			// Keep the existing query string and section anchor after the new URL.
			$url_parts = preg_split( '/(?=[?#])/', $item->url, 2 );
			if ( isset( $history[ $url_parts[0] ] ) ) {
				$page_url = get_permalink( $history[ $url_parts[0] ]['id'] );
				update_post_meta( $item->ID, '_menu_item_url', $page_url . ( $url_parts[1] ?? '' ) );
			}
		}
	}
}

/** Run once, after the existing content and Supplier Portal migrations. */
function asfar_migrate_shared_slugs() {
	if ( ! current_user_can( 'manage_options' ) || get_option( 'asfar_shared_slugs_revision' ) ) {
		return;
	}

	if ( ! function_exists( 'pll_get_post' ) || (int) get_option( 'asfar_content_schema_version' ) < 2 ) {
		return;
	}

	if ( ! PLL()->model->get_language( 'en' ) || ! PLL()->model->get_language( 'ar' ) ) {
		return;
	}

	try {
		asfar_with_content_lock( function () {
			$errors = asfar_slug_apply();
			if ( $errors ) {
				throw new RuntimeException( implode( ' ', $errors ) );
			}
			update_option( 'asfar_shared_slugs_revision', '2026-09-20', false );
		} );
	} catch ( Throwable $error ) {
		add_action( 'admin_notices', function () use ( $error ) {
			echo '<div class="notice notice-error"><p>Shared language slugs: ' . esc_html( $error->getMessage() ) . '</p></div>';
		} );
	}
}
add_action( 'admin_init', 'asfar_migrate_shared_slugs', 90 );

/** Find an old page URL without redirecting a URL reassigned to another page. */
function asfar_old_slug_destination( $request_uri ) {
	$path = wp_parse_url( $request_uri, PHP_URL_PATH );

	foreach ( get_option( 'asfar_slug_history', array() ) as $old_url => $translation ) {
		$old_path = wp_parse_url( $old_url, PHP_URL_PATH );
		if ( untrailingslashit( $path ?? '' ) !== untrailingslashit( $old_path ?? '' ) ) {
			continue;
		}

		if ( is_singular() && (int) get_queried_object_id() !== (int) $translation['id'] ) {
			continue;
		}

		$destination = get_permalink( $translation['id'] );
		if ( ! $destination || 'publish' !== get_post_status( $translation['id'] ) ) {
			continue;
		}

		// An unchanged URL must never redirect to itself.
		$new_path = wp_parse_url( $destination, PHP_URL_PATH );
		if ( untrailingslashit( $new_path ) === untrailingslashit( $path ) ) {
			continue;
		}

		$query_string = wp_parse_url( $request_uri, PHP_URL_QUERY );
		return $destination . ( $query_string ? '?' . $query_string : '' );
	}

	return '';
}

/** WordPress does not retain old hierarchical page paths, so redirect them here. */
function asfar_redirect_old_slugs() {
	$request_method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
	if ( is_admin() || ! in_array( $request_method, array( 'GET', 'HEAD' ), true ) ) {
		return;
	}

	$destination = asfar_old_slug_destination( wp_unslash( $_SERVER['REQUEST_URI'] ?? '' ) );
	if ( $destination ) {
		wp_safe_redirect( $destination, 301, 'ASFAR shared slugs' );
		exit;
	}
}
add_action( 'template_redirect', 'asfar_redirect_old_slugs', 1 );
