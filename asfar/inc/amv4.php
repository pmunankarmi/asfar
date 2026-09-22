<?php
/** AMV4 wording and the narrowly scoped source-content correction. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function asfar_amv4_labels() {
 return array(
  'Overview' => 'نظرة عامة',
  'The journey' => 'الرحلة',
  'Back to Media Center' => 'العودة إلى المركز الإعلامي',
 );
}

function asfar_amv4_label( $text ) {
 $translated = asfar_translate( $text );
 // Supply source Arabic wording until the strings have been saved in Polylang.
 if ( $translated === $text && 'ar' === asfar_language() ) {
  return asfar_amv4_labels()[ $text ] ?? $text;
 }
 return $translated;
}

add_action( 'admin_init', function () {
 if ( function_exists( 'pll_register_string' ) ) {
  foreach ( asfar_amv4_labels() as $text => $arabic ) {
   pll_register_string( $text, $text, 'ASFAR Theme' );
  }
 }
}, 30 );

function asfar_update_amv4_content() {
 if ( get_option( 'asfar_amv4_revision' ) || ! function_exists( 'get_field' ) || ! function_exists( 'pll_get_post_language' ) || ! class_exists( 'PLL_MO' ) || ! function_exists( 'PLL' ) || (int) get_option( 'asfar_content_schema_version' ) < 2 ) { return; }
 if ( ! PLL()->model->get_language( 'ar' ) ) { return; }
 $pages = get_posts( array( 'post_type' => 'page', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'lang' => '', 'suppress_filters' => true, 'meta_key' => '_wp_page_template', 'meta_value' => 'templates/template-home.php' ) );
 foreach ( $pages as $id ) {
  if ( 'ar' !== pll_get_post_language( $id ) ) { continue; }
  $about = get_field( 'about_section', $id );
  if ( is_array( $about ) && 'نستثمر في المدن والواعدة' === ( $about['card_heading'] ?? '' ) ) {
   $about['card_heading'] = 'نستثمر في المدن الواعدة';
   update_field( 'field_asfar_home_about_about_section', $about, $id );
  }
 }
 asfar_migrate_strings( asfar_amv4_labels() );
 update_option( 'asfar_amv4_revision', '2026-09-17', false );
}
add_action( 'admin_init', function () {
 if ( current_user_can( 'manage_options' ) ) { asfar_update_amv4_content(); }
}, 45 );
