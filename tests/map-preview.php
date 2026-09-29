<?php
/** Render the real portfolio template with fixture ACF/WordPress records. */
define( 'ABSPATH', __DIR__ );
$fixture = json_decode( stream_get_contents( STDIN ), true );
$projects = $fixture['projects'];
$cursor = -1;
$active_project = 0;
function add_action( ...$args ) {}
function add_shortcode( ...$args ) {}
function is_singular() { return false; }
function absint( $value ) { return abs( (int) $value ); }
function get_post_status( $id ) { return 'publish'; }
function get_posts( $query ) { global $projects; return array_map( fn( $i ) => (object) array( 'ID' => $i ), array_keys( $projects ) ); }
function get_the_title( $post ) { global $projects; return $projects[$post->ID]['name']; }
function get_post_thumbnail_id( $post ) { return $post->ID + 1; }
function wp_get_attachment_url( $id ) { global $projects; return '/media/' . preg_replace( '~^(assets/|/media/)~', '', $projects[$id - 1]['bg'] ); }
function get_option( ...$args ) { return false; }
function wp_upload_dir() { return array( 'basedir' => dirname( __DIR__ ) . '/media', 'baseurl' => '' ); }
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return esc_html( $text ); }
function esc_url( $text ) { return esc_html( $text ); }
function get_permalink( $id ) { return '#project-' . $id; }
function get_the_ID() { return 0; }
function get_field( $name, $id = null ) {
 global $projects, $fixture;
 if ( 'portfolio_section' === $name ) { return array( 'heading' => $fixture['heading'], 'projects' => array_keys( $projects ) ); }
 $project = $projects[$id] ?? array();
 return match( $name ) {
  'map_region' => $project['hot'], 'map_label' => $project['label'],
  'map_x' => $project['mark'][0] ?? 0, 'map_y' => $project['mark'][1] ?? 0,
  'statistics' => $project['stats'], default => false,
 };
}
function have_rows( $field, $id ) {
 global $projects, $cursor, $active_project;
 $active_project = $id;
 if ( $cursor + 1 < count( $projects[$id]['stats'] ) ) { return true; }
 $cursor = -1;
 return false;
}
function the_row() { global $cursor; ++$cursor; }
function get_sub_field( $field ) {
 global $projects, $cursor, $active_project;
 return str_replace( '<sup>2</sup>', '²', $projects[$active_project]['stats'][$cursor]['value' === $field ? 0 : 1] );
}
function get_template_part( $slug, $name = null, $args = array() ) { require dirname( __DIR__ ) . '/asfar/' . $slug . '.php'; }
require __DIR__ . '/../asfar/inc/content.php';
require __DIR__ . '/../asfar/inc/media.php';
// get_field must return a nonempty relationship; fixture IDs start at 1.
$projects = array_combine( range( 1, count( $projects ) ), $projects );
get_template_part( 'template-parts/home/projects' );
