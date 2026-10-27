<?php
/** Rich-text editing and compatibility with the former paragraph repeaters. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/** Load existing paragraphs until the new editor has been saved, even if saved empty. */
function asfar_load_editor_content( $value, $post_id, $field ) {
	$prefix = 'field_asfar_home_about_about_section_body_content' === $field['key'] ? 'about_section_' : '';
	if ( ! is_numeric( $post_id ) || metadata_exists( 'post', $post_id, $prefix . 'body_content' ) ) {
		return $value;
	}
	$count = (int) get_post_meta( $post_id, $prefix . 'paragraphs', true );
	$paragraphs = array();
	for ( $index = 0; $index < $count; $index++ ) {
		$text = get_post_meta( $post_id, $prefix . 'paragraphs_' . $index . '_text', true );
		if ( '' !== trim( (string) $text ) ) {
			$paragraphs[] = '<p>' . nl2br( esc_html( $text ) ) . '</p>';
		}
	}
	return $paragraphs ? implode( "\n", $paragraphs ) : $value;
}

foreach ( array( 'home_about_about_section', 'page', 'article' ) as $context ) {
	add_filter( 'acf/load_value/key=field_asfar_' . $context . '_body_content', 'asfar_load_editor_content', 10, 3 );
}

/** Expose the same formatting controls in Classic Editor and ACF editors. */
function asfar_editor_buttons( $buttons ) {
	return array_values( array_unique( array_merge( $buttons, array( 'alignjustify', 'fontsizeselect', 'forecolor', 'backcolor' ) ) ) );
}
add_filter( 'mce_buttons_2', 'asfar_editor_buttons' );

add_filter( 'acf/fields/wysiwyg/toolbars', function ( $toolbars ) {
	foreach ( $toolbars as &$toolbar ) {
		// Keep these controls visible in the first row without duplicating them.
		$controls = array( 'alignjustify', 'fontsizeselect', 'forecolor', 'backcolor' );
		foreach ( $toolbar as &$row ) {
			$row = array_values( array_diff( $row, $controls ) );
		}
		unset( $row );
		$toolbar[1] = asfar_editor_buttons( $toolbar[1] ?? array() );
	}
	return $toolbars;
} );

add_filter( 'tiny_mce_before_init', function ( $settings ) {
	$settings['fontsize_formats'] = '12px 14px 16px 18px 20px 24px 28px 32px 36px 48px';
	return $settings;
} );
