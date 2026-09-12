<?php
/** Keep stored links working with the theme's prefixed HTML IDs. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function asfar_anchor_url( $url ) {
	if ( ! is_string( $url ) || ! str_contains( $url, '#' ) ) { return $url; }
	$host = wp_parse_url( $url, PHP_URL_HOST );
	if ( $host && $host !== wp_parse_url( home_url(), PHP_URL_HOST ) ) { return $url; }
	static $ids;
	if ( null === $ids ) { $ids = json_decode( file_get_contents( __DIR__ . '/dom-ids.json' ), true ); }
	$parts = explode( '#', $url, 2 );
	if ( in_array( $parts[1], $ids, true ) ) { $parts[1] = 'mt-' . $parts[1]; }
	return implode( '#', $parts );
}
add_filter( 'nav_menu_link_attributes', function ( $attributes ) {
	if ( isset( $attributes['href'] ) ) { $attributes['href'] = asfar_anchor_url( $attributes['href'] ); }
	return $attributes;
} );
