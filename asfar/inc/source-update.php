<?php
/** Apply the September 11 source revision once, preserving subsequent admin edits. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function asfar_update_source_revision() {
	if ( get_option( 'asfar_source_revision' ) === '2026-09-11' || ! function_exists( 'PLL' ) || ! function_exists( 'update_field' ) ) { return; }
	$pages = get_option( 'asfar_page_map', array() );
	if ( empty( $pages['index'] ) || empty( $pages['index-ar'] ) || (int) get_option( 'asfar_content_schema_version' ) < 2 ) { return; }
	asfar_with_content_lock( function () use ( $pages ) {
		if ( get_option( 'asfar_source_revision' ) === '2026-09-11' ) { return; }
		$backup = array( 'polylang' => get_option( 'polylang' ), 'front_page' => get_option( 'page_on_front' ), 'posts_page' => get_option( 'page_for_posts' ), 'homes' => array(), 'urls' => array(), 'menu_urls' => array() );
		foreach ( $pages as $id ) { $backup['urls'][ get_permalink( $id ) ] = $id; }
		foreach ( wp_get_nav_menus() as $menu ) {
			if ( ! str_starts_with( $menu->name, 'ASFAR ' ) || str_starts_with( $menu->name, 'ASFAR Menu Backup' ) ) { continue; }
			foreach ( wp_get_nav_menu_items( $menu ) ?: array() as $item ) { if ( 'custom' === $item->type ) { $backup['menu_urls'][ $item->ID ] = $item->url; } }
		}
		foreach ( array( 'index', 'index-ar' ) as $slug ) { $backup['homes'][ $pages[$slug] ] = get_post_meta( $pages[$slug] ); }
		update_option( 'asfar_source_revision_backup', $backup, false );
		foreach ( array( 'index' => "Investing in\npromising cities.", 'index-ar' => 'نستثمر في المدن والواعدة' ) as $slug => $heading ) {
			$id = $pages[$slug];
			$about = asfar_rows( 'about_section', $id );
			if ( in_array( trim( $about['card_heading'] ?? '' ), array( '', "Investing\nthrough partnership.", 'Investing through partnership.', 'نستثمر بالشراكة' ), true ) ) {
				$about['card_heading'] = $heading;
				update_field( 'field_asfar_home_about_about_section', $about, $id );
			}
			$animation = asfar_rows( 'banner_animation', $id );
			$animation['enabled'] = 1;
			update_field( 'field_asfar_home_banner_banner_animation', $animation, $id );
		}
		// Let Polylang update its translated menus and language caches as well.
		$result = PLL()->model->update_default_lang( 'ar' );
		if ( is_wp_error( $result ) && $result->has_errors() ) { throw new RuntimeException( $result->get_error_message() ); }
		PLL()->options['hide_default'] = 1;
		PLL()->options['redirect_lang'] = 0;
		PLL()->options['browser'] = 0;
		if ( is_object( PLL()->options ) && method_exists( PLL()->options, 'save' ) ) { PLL()->options->save(); }
		else { update_option( 'polylang', PLL()->options ); }
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $pages['index-ar'] );
		update_option( 'page_for_posts', 0 );
		foreach ( $backup['menu_urls'] as $id => $url ) {
			$parts = explode( '#', $url, 2 );
			if ( isset( $backup['urls'][ $parts[0] ] ) ) {
				update_post_meta( $id, '_menu_item_url', get_permalink( $backup['urls'][ $parts[0] ] ) . ( isset( $parts[1] ) ? '#' . $parts[1] : '' ) );
			}
		}
		flush_rewrite_rules( false );
		update_option( 'asfar_source_revision', '2026-09-11', false );
	} );
}
add_action( 'admin_init', function () {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	try { asfar_update_source_revision(); }
	catch ( Throwable $error ) {
		add_action( 'admin_notices', function () use ( $error ) { echo '<div class="notice notice-error"><p>ASFAR source update: ' . esc_html( $error->getMessage() ) . '</p></div>'; } );
	}
}, 40 );
