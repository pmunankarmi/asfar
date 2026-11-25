<?php
/** Render native ACF sector markup against source fixture content. */
$fixture = json_decode( stream_get_contents( STDIN ), true );
$stack = array();
function esc_html( $text ) { return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $text ) { return esc_html( $text ); }
function asfar_lines( $text ) { return nl2br( esc_html( $text ) ); }
function asfar_language() { global $fixture; return $fixture['lang']; }
function asfar_option( $name ) { return 'previous_label' === $name ? 'Previous sector' : 'Next sector'; }
function have_rows( $name ) {
 global $stack, $fixture;
 $last = count( $stack ) - 1;
 if ( $last < 0 || $stack[$last]['name'] !== $name ) {
  $rows = $last < 0 ? array( $fixture['section'] ) : $stack[$last]['rows'][$stack[$last]['index']][$name];
  $stack[] = array( 'name' => $name, 'rows' => $rows, 'index' => -1 );
  ++$last;
 }
 if ( $stack[$last]['index'] + 1 < count( $stack[$last]['rows'] ) ) { return true; }
 array_pop( $stack );
 return false;
}
function the_row() { global $stack; ++$stack[count( $stack ) - 1]['index']; }
function get_sub_field( $name ) { global $stack; $row = end( $stack ); return $row['rows'][$row['index']][$name]; }
function wp_get_attachment_image( $image, ...$args ) { return '<img src="' . esc_attr( $image ) . '" alt="">'; }
function get_template_part( $slug, $name = null, $args = array() ) { require dirname( __DIR__ ) . '/asfar/' . $slug . '.php'; }
get_template_part( 'template-parts/home/investments' );
