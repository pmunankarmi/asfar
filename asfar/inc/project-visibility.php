<?php
/** Add the translated Taif portfolio entries once, preserving editor changes. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function asfar_install_taif_project() {
 if ( get_option( 'asfar_taif_revision' ) || ! function_exists( 'update_field' ) || ! function_exists( 'pll_save_post_translations' ) || (int) get_option( 'asfar_content_schema_version' ) < 2 ) { return; }
 asfar_with_content_lock( function () {
  if ( get_option( 'asfar_taif_revision' ) ) { return; }
  $translations = array();
  foreach ( array( 'en' => 'Taif', 'ar' => 'الطائف' ) as $lang => $title ) {
   $existing = get_posts( array( 'post_type' => 'asfar_project', 'post_status' => array( 'publish', 'draft', 'private', 'pending', 'future' ), 'posts_per_page' => 1, 'lang' => $lang, 'meta_key' => 'map_region', 'meta_value' => 'taif', 'suppress_filters' => false ) );
   $id = $existing ? $existing[0]->ID : wp_insert_post( array( 'post_type' => 'asfar_project', 'post_status' => 'publish', 'post_title' => $title, 'post_name' => 'taif', 'menu_order' => 3, 'meta_input' => array( 'map_region' => 'taif' ) ), true );
   if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
   pll_set_post_language( $id, $lang );
   $values = array( 'map_region' => 'taif', 'map_label' => $title, 'map_x' => 280, 'map_y' => 440, 'show_on_homepage' => 1 );
   foreach ( $values as $name => $value ) {
    if ( ! metadata_exists( 'post', $id, $name ) ) { update_field( 'field_asfar_project_' . $name, $value, $id ); }
   }
   $translations[$lang] = $id;
   $homes = get_posts( array( 'post_type' => 'page', 'posts_per_page' => -1, 'lang' => $lang, 'meta_key' => '_wp_page_template', 'meta_value' => 'templates/template-home.php', 'suppress_filters' => false ) );
   foreach ( $homes as $home ) {
    $ids = get_post_meta( $home->ID, 'portfolio_section_projects', true );
    $ids = is_array( $ids ) ? $ids : array();
    if ( ! in_array( (int) $id, array_map( 'intval', $ids ), true ) ) {
     $ids[] = $id;
     update_field( 'field_asfar_home_portfolio_portfolio_section_projects', $ids, $home->ID );
    }
   }
  }
  pll_save_post_translations( $translations );
  update_option( 'asfar_taif_revision', 1, false );
 } );
}
add_action( 'admin_init', function () {
 if ( ! current_user_can( 'manage_options' ) ) { return; }
 try { asfar_install_taif_project(); }
 catch ( Throwable $error ) {
  add_action( 'admin_notices', function () use ( $error ) { echo '<div class="notice notice-error"><p>Taif setup: ' . esc_html( $error->getMessage() ) . '</p></div>'; } );
 }
}, 65 );
