<?php
/** Version-controlled ACF definitions and bidirectional shared-logo synchronization. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function asfar_field( $name, $type = 'text', $children = array() ) {
 $field = array( 'key' => 'field_asfar_' . $name, 'name' => $name, 'label' => ucwords( str_replace( '_', ' ', $name ) ), 'type' => $type );
 if ( 'repeater' === $type ) { $field['sub_fields'] = $children; $field['layout'] = 'block'; $field['button_label'] = 'Add item'; }
 if ( in_array( $type, array( 'image', 'file' ), true ) ) { $field['return_format'] = 'id'; }
 if ( 'textarea' === $type ) { $field['new_lines'] = ''; $field['rows'] = 3; }
 return $field;
}
function asfar_structured_fields() {
 $team_group = asfar_field( 'group', 'select' );
 $team_group['choices'] = array( 'board' => 'Board', 'leadership' => 'Leadership', 'committees' => 'Committees' );
 return array(
  asfar_field( 'show_team', 'true_false' ),
  asfar_field( 'faq', 'repeater', array( asfar_field( 'question' ), asfar_field( 'answer', 'textarea' ) ) ),
  asfar_field( 'partners', 'repeater', array( asfar_field( 'name' ), asfar_field( 'image', 'image' ), asfar_field( 'url', 'url' ) ) ),
  asfar_field( 'sectors', 'repeater', array( asfar_field( 'label' ), asfar_field( 'image', 'image' ) ) ),
  asfar_field( 'footer_logo', 'image' ), asfar_field( 'contact_email', 'email' ), asfar_field( 'notification_email', 'email' ),
  asfar_field( 'social_links', 'repeater', array( asfar_field( 'social_label' ), asfar_field( 'social_url', 'url' ) ) ),
  asfar_field( 'hero_marks', 'repeater', array( asfar_field( 'name' ), asfar_field( 'cta' ), asfar_field( 'href' ) ) ),
  asfar_field( 'team', 'repeater', array( $team_group, asfar_field( 'name' ), asfar_field( 'role' ), asfar_field( 'bio', 'textarea' ), asfar_field( 'photo', 'image' ) ) ),
  asfar_field( 'portfolio_title' ),
  asfar_field( 'portfolio', 'repeater', array( asfar_field( 'hot' ), asfar_field( 'name' ), asfar_field( 'label' ), asfar_field( 'bg', 'image' ), asfar_field( 'mark_x', 'number' ), asfar_field( 'mark_y', 'number' ), asfar_field( 'stats', 'repeater', array( asfar_field( 'value' ), asfar_field( 'label' ) ) ), asfar_field( 'company' ), asfar_field( 'title' ), asfar_field( 'body', 'textarea' ), asfar_field( 'img', 'image' ) ) ),
  asfar_field( 'close' ), asfar_field( 'bio_empty' ), asfar_field( 'bio_label' ), asfar_field( 'form_success', 'textarea' ), asfar_field( 'form_error', 'textarea' ), asfar_field( 'form_invalid', 'textarea' ), asfar_field( 'form_subject' ), asfar_field( 'not_found' ), asfar_field( 'back_home' ), asfar_field( 'archive_title' ),
 );
}
function asfar_unique_fields( $fields, $scope ) {
 foreach ( $fields as &$field ) {
  $field['key'] = 'field_asfar_' . $scope . '_' . $field['name'];
  if ( isset( $field['sub_fields'] ) ) { $field['sub_fields'] = asfar_unique_fields( $field['sub_fields'], $scope . '_' . $field['name'] ); }
 }
 return $fields;
}
add_action( 'acf/init', function () {
 if ( ! function_exists( 'acf_add_options_page' ) ) { return; }
 acf_add_options_page( array( 'page_title' => 'Theme Settings', 'menu_slug' => 'asfar-settings', 'post_id' => 'asfar_shared', 'capability' => 'manage_options', 'redirect' => false ) );
 acf_add_local_field_group( array( 'key' => 'group_asfar_shared', 'title' => 'Shared branding', 'fields' => array( asfar_field( 'site_logo', 'image' ), asfar_field( 'dark_logo', 'image' ) ), 'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'asfar-settings' ) ) ) ) );
 acf_add_local_field_group( array( 'key' => 'group_asfar_news_meta', 'title' => 'News metadata', 'fields' => array( asfar_field( 'display_date' ), asfar_field( 'external_url', 'url' ) ), 'location' => array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ) ) );
 $groups = json_decode( file_get_contents( __DIR__ . '/fields.json' ), true );
 foreach ( array( 'en', 'ar' ) as $lang ) {
  acf_add_options_sub_page( array( 'page_title' => 'Theme Settings — ' . strtoupper( $lang ), 'menu_title' => strtoupper( $lang ) . ' Content', 'menu_slug' => 'asfar-settings-' . $lang, 'parent_slug' => 'asfar-settings', 'post_id' => 'global_' . $lang, 'capability' => 'manage_options' ) );
  $fields = array_merge( asfar_unique_fields( asfar_structured_fields(), 'global_' . $lang ), $groups[ 'global_' . $lang ] );
  // ACF tabs keep shared navigation, content, and forms easy to find.
  acf_add_local_field_group( array( 'key' => 'group_asfar_global_' . $lang, 'title' => 'Global ' . strtoupper( $lang ) . ' content', 'fields' => $fields, 'location' => array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'asfar-settings-' . $lang ) ) ) ) );
 }
 foreach ( $groups as $layout => $fields ) {
  if ( str_starts_with( $layout, 'global_' ) ) { continue; }
  acf_add_local_field_group( array( 'key' => 'group_asfar_' . str_replace( '-', '_', $layout ), 'title' => 'Page content — ' . $layout, 'fields' => $fields, 'location' => array( array( array( 'param' => 'asfar_layout', 'operator' => '==', 'value' => $layout ) ) ) ) );
 }
 acf_add_local_field_group( array( 'key' => 'group_asfar_native', 'title' => 'Article / page paragraphs', 'fields' => array( asfar_field( 'paragraphs', 'repeater', array( asfar_field( 'text', 'textarea' ) ) ) ), 'location' => array( array( array( 'param' => 'asfar_layout', 'operator' => '==', 'value' => '' ) ) ) ) );
} );
add_filter( 'acf/location/rule_types', function ( $choices ) { $choices['Post']['asfar_layout'] = 'ASFAR layout'; return $choices; } );
add_filter( 'acf/location/rule_match/asfar_layout', function ( $match, $rule, $screen ) { return (string) get_post_meta( $screen['post_id'] ?? 0, '_asfar_layout', true ) === (string) $rule['value']; }, 10, 3 );
function asfar_sync_logo( $id ) {
 static $syncing = false;
 if ( $syncing ) { return; }
 $syncing = true;
 $id = absint( $id );
 if ( function_exists( 'update_field' ) && ( absint( get_theme_mod( 'custom_logo' ) ) !== $id || absint( asfar_value( 'site_logo', 'asfar_shared' ) ) !== $id ) ) { update_field( 'field_asfar_dark_logo', 0, 'asfar_shared' ); }
 if ( $id ) { set_theme_mod( 'custom_logo', $id ); } else { remove_theme_mod( 'custom_logo' ); }
 if ( function_exists( 'update_field' ) ) { update_field( 'field_asfar_site_logo', $id, 'asfar_shared' ); }
 else { update_option( 'asfar_shared_site_logo', $id ); }
 $syncing = false;
}
add_action( 'acf/save_post', function ( $post_id ) {
 if ( 'asfar_shared' === $post_id ) { asfar_sync_logo( asfar_value( 'site_logo', 'asfar_shared' ) ); }
}, 20 );
add_action( 'updated_option', function ( $name, $old, $new ) {
 if ( 'theme_mods_' . get_option( 'stylesheet' ) === $name && ( $old['custom_logo'] ?? 0 ) !== ( $new['custom_logo'] ?? 0 ) ) { asfar_sync_logo( $new['custom_logo'] ?? 0 ); }
}, 10, 3 );
add_action( 'added_option', function ( $name, $value ) {
 if ( 'theme_mods_' . get_option( 'stylesheet' ) === $name && is_array( $value ) ) { asfar_sync_logo( $value['custom_logo'] ?? 0 ); }
}, 10, 2 );
add_filter( 'acf/update_value', function ( $value, $post_id, $field ) {
 if ( ! str_starts_with( $field['key'] ?? '', 'field_asfar_' ) ) { return $value; }
 if ( 'text' === ( $field['type'] ?? '' ) ) { return ( preg_match( '/^\s/', (string) $value ) ? ' ' : '' ) . sanitize_text_field( $value ) . ( preg_match( '/\s$/', (string) $value ) ? ' ' : '' ); }
 if ( 'textarea' === ( $field['type'] ?? '' ) ) { return sanitize_textarea_field( $value ); }
 return $value;
}, 10, 3 );
