<?php
/** Global strings use Polylang; page, project and team content use translated posts. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function asfar_ui_labels() {
	return array(
		'site_name' => 'Site Name / Logo Alternative Text',
		'home_label' => 'Home Link Label',
		'primary_label' => 'Primary Navigation Label',
		'open_menu' => 'Open Menu Label',
		'close_menu' => 'Close Menu Label',
		'menu_label' => 'Expanded Navigation Label',
		'contact_button' => 'Contact Button Text',
		'name_label' => 'Form: Name Label',
		'name_placeholder' => 'Form: Name Placeholder',
		'email_label' => 'Form: Email Label',
		'email_placeholder' => 'Form: Email Placeholder',
		'message_label' => 'Form: Message Label',
		'message_placeholder' => 'Form: Message Placeholder',
		'send_label' => 'Form: Submit Button',
		'form_success' => 'Form: Success Message',
		'form_error' => 'Form: Error Message',
		'form_invalid' => 'Form: Validation Message',
		'form_subject' => 'Form: Notification Subject',
		'ownership_label' => 'Ownership Logo Alternative Text',
		'copyright' => 'Copyright Text',
		'back_top' => 'Back to Top Label',
		'close' => 'Close Dialog Label',
		'bio_empty' => 'Missing Biography Message',
		'bio_label' => 'Biography Dialog Label',
		'not_found' => '404 Heading',
		'back_home' => 'Return Home Label',
		'archive_title' => 'News Archive Heading',
		'previous_label' => 'Previous Item Label',
		'next_label' => 'Next Item Label',
		'banner_label' => 'Banner Navigation Label',
	);
}

function asfar_register_translations() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	foreach ( asfar_ui_labels() as $name => $label ) {
		pll_register_string( $label, (string) asfar_value( $name, 'asfar_shared' ), 'ASFAR Theme', true );
	}
	foreach ( asfar_rows( 'partner_logos', 'asfar_partners' ) as $partner ) {
		pll_register_string( 'Partner: ' . $partner['name'], $partner['name'], 'ASFAR Partners' );
	}
	foreach ( asfar_rows( 'social_links', 'asfar_shared' ) as $social ) {
		pll_register_string( 'Social: ' . $social['label'], $social['label'], 'ASFAR Theme' );
	}
}
add_action( 'admin_init', 'asfar_register_translations', 30 );
add_action( 'acf/save_post', 'asfar_register_translations', 30 );

function asfar_sync_logo( $id ) {
	static $syncing = false;
	if ( $syncing ) {
		return;
	}
	$syncing = true;
	$id = absint( $id );
	if ( function_exists( 'update_field' ) && ( absint( get_theme_mod( 'custom_logo' ) ) !== $id || absint( asfar_value( 'site_logo', 'asfar_shared' ) ) !== $id ) ) {
		update_field( 'field_asfar_settings_dark_logo', 0, 'asfar_shared' );
	}
	if ( $id ) {
		set_theme_mod( 'custom_logo', $id );
	} else {
		remove_theme_mod( 'custom_logo' );
	}
	if ( function_exists( 'update_field' ) ) {
		update_field( 'field_asfar_settings_site_logo', $id, 'asfar_shared' );
	} else {
		update_option( 'asfar_shared_site_logo', $id );
	}
	$syncing = false;
}
add_action( 'acf/save_post', function ( $post_id ) {
	if ( 'asfar_shared' === $post_id ) {
		asfar_sync_logo( asfar_value( 'site_logo', 'asfar_shared' ) );
	}
}, 20 );
add_action( 'updated_option', function ( $name, $old, $new ) {
	if ( 'theme_mods_' . get_option( 'stylesheet' ) === $name && ( $old['custom_logo'] ?? 0 ) !== ( $new['custom_logo'] ?? 0 ) ) {
		asfar_sync_logo( $new['custom_logo'] ?? 0 );
	}
}, 10, 3 );
add_action( 'added_option', function ( $name, $value ) {
	if ( 'theme_mods_' . get_option( 'stylesheet' ) === $name && is_array( $value ) ) {
		asfar_sync_logo( $value['custom_logo'] ?? 0 );
	}
}, 10, 2 );
