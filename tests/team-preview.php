<?php
/** Render the native team section without a WordPress database. */
$fixture = json_decode( stream_get_contents( STDIN ), true );
function get_field( $name, $id = null ) {
 global $fixture;
 if ( 'team_section' === $name ) { return array( 'enabled' => true, 'heading' => $fixture['heading'], 'description' => '', 'button' => array() ); }
 return $fixture['members'][$id - 1]['biography' === $name ? 'bio' : 'role'] ?? '';
}
function asfar_team_departments() { global $fixture; return array_map( fn( $g ) => (object) array( 'term_id' => $g['id'], 'name' => $g['name'] ), $fixture['groups'] ); }
function asfar_team_members( $group ) { global $fixture; $out = array(); foreach ( $fixture['members'] as $i => $m ) { if ( $m['group'] === $group ) $out[] = (object) array( 'ID' => $i + 1 ); } return $out; }
function get_the_title( $member ) { global $fixture; return $fixture['members'][$member->ID - 1]['name']; }
function get_post_thumbnail_id( $member ) { global $fixture; return empty( $fixture['members'][$member->ID - 1]['photo'] ) ? 0 : $member->ID; }
function asfar_attachment_url( $photo ) { global $fixture; return $photo ? '/media/img/team/' . $fixture['members'][$photo - 1]['photo'] : ''; }
function wp_get_attachment_image( $photo, ...$args ) { return $photo ? '<img src="' . esc_attr( asfar_attachment_url( $photo ) ) . '" alt="">' : ''; }
function esc_html( $s ) { return htmlspecialchars( (string) $s, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $s ) { return esc_html( $s ); }
function esc_url( $s ) { return esc_html( $s ); }
function asfar_lines( $s ) { return nl2br( esc_html( $s ) ); }
function asfar_option( $s ) { return $s; }
function asfar_language() { global $fixture; return $fixture['lang'] ?? 'en'; }
function asfar_button( ...$args ) {}
function get_template_part( $slug, $name = null, $args = array() ) { require dirname( __DIR__ ) . '/asfar/' . $slug . '.php'; }
if ( ( $argv[1] ?? '' ) === 'page' ) {
 function get_header() {}
 function get_footer() {}
 function have_posts() { static $calls = 0; return 0 === $calls++; }
 function the_post() {}
 function the_title() { global $fixture; echo esc_html( $fixture['heading'] ); }
 require dirname( __DIR__ ) . '/asfar/templates/template-team.php';
} else {
 get_template_part( 'template-parts/home/team' );
}
