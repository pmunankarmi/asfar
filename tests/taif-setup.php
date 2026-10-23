<?php
/** In-memory WordPress/ACF contract test, including real nested-field naming. */
define( 'ABSPATH', __DIR__ );
$options = array( 'asfar_content_schema_version' => 2, 'asfar_taif_revision' => 1 );
$meta = array( 10 => array( 'portfolio_section_projects' => array( 1, 2 ), 'portfolio_section_heading' => 'Edited English heading' ), 20 => array( 'portfolio_section_projects' => array( 3, 4 ), 'portfolio_section_heading' => 'Edited Arabic heading' ), 101 => array( 'map_region' => 'taif', 'show_on_homepage' => '0', 'summary' => 'Editor content' ), 102 => array( 'map_region' => 'taif', 'show_on_homepage' => '1' ) );
$insertions = 0;
function add_action( ...$args ) {}
function get_option( $key ) { global $options; return $options[$key] ?? false; }
function update_option( $key, $value, ...$args ) { global $options; $options[$key] = $value; }
function asfar_with_content_lock( $callback ) { $callback(); }
function get_posts( $query ) { return array( (object) array( 'ID' => ( 'asfar_project' === $query['post_type'] ? ( 'en' === $query['lang'] ? 101 : 102 ) : ( 'en' === $query['lang'] ? 10 : 20 ) ) ) ); }
function wp_insert_post( ...$args ) { global $insertions; ++$insertions; throw new Exception( 'Duplicate project' ); }
function is_wp_error( $value ) { return false; }
function pll_set_post_language( ...$args ) {}
function pll_save_post_translations( $values ) { if ( $values !== array( 'en' => 101, 'ar' => 102 ) ) { throw new Exception( 'Translations lost' ); } }
function metadata_exists( $type, $id, $key ) { global $meta; return array_key_exists( $key, $meta[$id] ?? array() ); }
function get_post_meta( $id, $key, $single ) { global $meta; return $meta[$id][$key] ?? ''; }
function get_field( $key, $id ) { global $meta; return array( 'heading' => $meta[$id]['portfolio_section_heading'], 'projects' => $meta[$id]['portfolio_section_projects'] ); }
function update_field( $key, $value, $id ) {
 global $meta;
 if ( 'field_asfar_home_portfolio_portfolio_section' === $key ) {
  foreach ( $value as $name => $child ) { $meta[$id]['portfolio_section_' . $name] = $child; }
 } elseif ( 'field_asfar_home_portfolio_portfolio_section_projects' === $key ) {
  // This is how ACF resolves a child key passed to update_field by itself.
  $meta[$id]['projects'] = $value;
 } else { $meta[$id][str_replace( 'field_asfar_project_', '', $key )] = $value; }
}
require __DIR__ . '/../asfar/inc/project-visibility.php';
function check( $condition, $message ) { if ( ! $condition ) { throw new Exception( $message ); } echo 'PASS ' . $message . "\n"; }
asfar_install_taif_project();
check( $meta[10]['portfolio_section_projects'] === array( 1, 2, 101 ), 'English homepage selection repaired in existing order' );
check( $meta[20]['portfolio_section_projects'] === array( 3, 4, 102 ), 'Arabic homepage selection repaired' );
check( $meta[10]['portfolio_section_heading'] === 'Edited English heading', 'Portfolio heading preserved' );
check( $meta[101]['show_on_homepage'] === '0' && $meta[101]['summary'] === 'Editor content', 'Editor content and hidden setting preserved' );
check( $options['asfar_taif_revision'] === 2, 'Repair completion recorded' );
$before = $meta;
asfar_install_taif_project();
check( $meta === $before && $insertions === 0, 'Retry makes no duplicates or changes' );
