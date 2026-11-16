<?php
/** Isolated upgrade test: translations, menu order, retries and edit preservation. */
define( 'ABSPATH', __DIR__ );
$options = array( 'asfar_content_schema_version' => 2 );
$posts = $meta = $translations = array();
$menus = array( 10 => array( (object) array( 'ID' => 101, 'object' => 'custom', 'object_id' => 0, 'url' => '/#faq', 'menu_order' => 1 ), (object) array( 'ID' => 102, 'object' => 'custom', 'object_id' => 0, 'url' => '/#contact', 'menu_order' => 2 ) ), 20 => array( (object) array( 'ID' => 201, 'object' => 'custom', 'object_id' => 0, 'url' => '/en/#contact', 'menu_order' => 1 ) ) );
function add_action( $hook, $callback, ...$args ) { global $hooks; $hooks[$hook][] = $callback; }
function add_filter( ...$args ) {}
function sanitize_key( $key ) { return preg_replace( '/[^a-z0-9_-]/', '', strtolower( $key ) ); }
function acf_add_local_field_group( $group ) {
 $keys = array();
 $walk = function ( $fields ) use ( &$walk, &$keys ) { foreach ( $fields as $field ) { if ( isset( $keys[$field['key']] ) ) throw new Exception( 'Duplicate field key' ); $keys[$field['key']] = true; if ( isset( $field['sub_fields'] ) ) $walk( $field['sub_fields'] ); } };
 $walk( $group['fields'] );
}
function get_option( $key, $default = false ) { global $options; return $options[$key] ?? $default; }
function update_option( $key, $value, ...$args ) { global $options; $options[$key] = $value; }
function asfar_with_content_lock( $callback ) { $callback(); }
function PLL() { return (object) array( 'options' => array(), 'model' => new class { function get_language( $lang ) { return $lang; } } ); }
function pll_default_language() { return 'ar'; }
function pll_set_post_language( $id, $lang ) {}
function pll_save_post_translations( $pages ) { global $translations; $translations = $pages; }
function get_stylesheet() { return 'asfar'; }
function get_nav_menu_locations() { return array( 'drawer' => 10, 'drawer___en' => 20 ); }
function get_page_by_path( $slug ) { global $posts; foreach ( $posts as $p ) { if ( $p->post_name === $slug ) return $p; } return null; }
function wp_insert_post( $args, $error ) { global $posts, $meta; $id = count( $posts ) + 1; $posts[$id] = (object) array_merge( $args, array( 'ID' => $id ) ); $meta[$id] = $args['meta_input']; return $id; }
function get_post_meta( $id, $key, $single ) { global $meta; return $meta[$id][$key] ?? ''; }
function metadata_exists( $type, $id, $key ) { global $meta; return array_key_exists( $key, $meta[$id] ); }
function update_field( $key, $value, $id ) { global $meta; $meta[$id][str_replace( 'field_asfar_supplier_', '', $key )] = $value; }
function is_wp_error( $value ) { return false; }
function wp_get_nav_menu_items( $menu ) { global $menus; return $menus[$menu]; }
function wp_update_nav_menu_item( $menu, $id, $args ) { global $menus; $id = 300 + count( $menus[$menu] ); $menus[$menu][] = (object) array( 'ID' => $id, 'object' => 'page', 'object_id' => $args['menu-item-object-id'], 'url' => '/supplier/', 'menu_order' => $args['menu-item-position'] ); return $id; }
function wp_update_post( $args ) { global $menus; foreach ( $menus as $items ) foreach ( $items as $item ) if ( $item->ID === $args['ID'] ) $item->menu_order = $args['menu_order']; }
require __DIR__ . '/../asfar/inc/fields.php';
require __DIR__ . '/../asfar/inc/supplier.php';
$register = array_pop( $hooks['acf/init'] );
$register();
asfar_install_supplier_portal();
if ( count( $posts ) !== 2 || count( $translations ) !== 2 || count( $menus[10] ) !== 3 || $menus[10][2]->menu_order !== 2 || $menus[10][1]->menu_order !== 3 ) throw new Exception( 'Setup or menu order failed' );
$meta[1]['heading'] = 'Editor changed this';
unset( $options['asfar_supplier_revision'] );
asfar_install_supplier_portal();
if ( count( $posts ) !== 2 || count( $menus[10] ) !== 3 || count( $menus[20] ) !== 2 || $meta[1]['heading'] !== 'Editor changed this' ) throw new Exception( 'Retry duplicated or overwrote content' );
asfar_install_supplier_portal();
echo "PASS: translated pages, menu placement, retry safety and edit preservation\n";
