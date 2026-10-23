<?php
/** Native translated Supplier Portal pages and their editable content. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
add_action( 'acf/init', function () {
 $fields = array();
 $sections = array(
  'Introduction' => array( 'eyebrow' => 'Small Heading', 'heading' => 'Page Heading', 'introduction' => 'Introduction' ),
  'Registration' => array( 'registration_tab' => 'Registration Tab Label', 'registration_intro' => 'Registration Introduction' ),
  'Sign In' => array( 'signin_tab' => 'Sign In Tab Label', 'signin_heading' => 'Card Heading', 'signin_description' => 'Card Description', 'signin_label' => 'Button Label', 'signin_url' => 'Sign In URL' ),
  'Contact' => array( 'inquiry_heading' => 'Inquiry Heading', 'inquiry_description' => 'Inquiry Description', 'inquiry_label' => 'Contact Button Label', 'back_label' => 'Back to Home Label' ),
 );
 foreach ( $sections as $title => $names ) {
  $fields[] = asfar_field( 'tab_' . sanitize_key( $title ), $title, 'tab' );
  foreach ( $names as $name => $label ) {
   $fields[] = str_ends_with( $name, '_url' ) ? asfar_field( $name, $label, 'url' ) : asfar_text( $name, $label, str_contains( $name, 'description' ) || str_contains( $name, 'intro' ) );
  }
  if ( 'Registration' === $title ) {
   $fields[] = asfar_repeater( 'registration_cards', 'Registration Options', array( asfar_text( 'heading', 'Heading' ), asfar_text( 'description', 'Description', true ), asfar_text( 'label', 'Button Label' ), asfar_field( 'url', 'Registration URL', 'url' ) ), 'Add registration option' );
  }
 }
 asfar_register_field_group( 'supplier', 'Supplier Portal', $fields, array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'templates/template-supplier.php' ) ) ) );
} );

function asfar_install_supplier_portal() {
 if ( get_option( 'asfar_supplier_revision' ) || ! function_exists( 'update_field' ) || ! function_exists( 'pll_save_post_translations' ) || ! function_exists( 'PLL' ) || (int) get_option( 'asfar_content_schema_version' ) < 2 ) { return; }
 foreach ( array( 'ar', 'en' ) as $lang ) { if ( ! PLL()->model->get_language( $lang ) ) { return; } }
 asfar_with_content_lock( function () {
  if ( get_option( 'asfar_supplier_revision' ) ) { return; }
  $seed = json_decode( file_get_contents( __DIR__ . '/supplier-seed.json' ), true );
  $pages = array();
  foreach ( $seed as $lang => $values ) {
   $slug = 'supplier-portal' . ( 'ar' === $lang ? '-ar' : '' );
   $page = get_page_by_path( $slug );
   if ( $page && 'templates/template-supplier.php' !== get_post_meta( $page->ID, '_wp_page_template', true ) ) {
    throw new RuntimeException( 'A page already uses ' . $slug . '. Assign the ASFAR Supplier Portal template to use it.' );
   }
   $id = $page ? $page->ID : wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_name' => $slug, 'post_title' => $values['heading'], 'meta_input' => array( '_wp_page_template' => 'templates/template-supplier.php' ) ), true );
   if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
   pll_set_post_language( $id, $lang );
   foreach ( $values as $name => $value ) {
    // Retry safely after partial setup; never replace an editor's saved content.
    if ( ! metadata_exists( 'post', $id, $name ) ) { update_field( 'field_asfar_supplier_' . $name, $value, $id ); }
   }
   $pages[$lang] = $id;
  }
  pll_save_post_translations( $pages );
  $locations = get_nav_menu_locations();
  $translated = PLL()->options['nav_menus'][get_stylesheet()]['drawer'] ?? array();
  foreach ( $pages as $lang => $page_id ) {
   $slot = 'drawer' . ( pll_default_language() === $lang ? '' : '___' . $lang );
   $menu = $translated[$lang] ?? ( $locations[$slot] ?? 0 );
   if ( ! $menu ) { throw new RuntimeException( 'Assign the ' . $lang . ' expanded navigation menu to finish Supplier Portal setup.' ); }
   $items = wp_get_nav_menu_items( $menu ) ?: array();
   $exists = false;
   $position = count( $items ) + 1;
   foreach ( $items as $item ) {
    if ( 'page' === $item->object && (int) $item->object_id === (int) $page_id ) { $exists = true; }
    if ( str_contains( $item->url, '#contact' ) ) { $position = (int) $item->menu_order; }
   }
   if ( $exists ) { continue; }
   $result = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => $seed[$lang]['heading'], 'menu-item-object-id' => $page_id, 'menu-item-object' => 'page', 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish', 'menu-item-position' => $position ) );
   if ( is_wp_error( $result ) ) { throw new RuntimeException( $result->get_error_message() ); }
   foreach ( $items as $item ) {
    if ( $item->menu_order >= $position ) { wp_update_post( array( 'ID' => $item->ID, 'menu_order' => $item->menu_order + 1 ) ); }
   }
  }
  update_option( 'asfar_supplier_revision', '2026-09-20', false );
 } );
}
add_action( 'admin_init', function () {
 if ( ! current_user_can( 'manage_options' ) ) { return; }
 try { asfar_install_supplier_portal(); }
 catch ( Throwable $error ) {
  add_action( 'admin_notices', function () use ( $error ) { echo '<div class="notice notice-error"><p>Supplier Portal setup: ' . esc_html( $error->getMessage() ) . '</p></div>'; } );
 }
}, 60 );
