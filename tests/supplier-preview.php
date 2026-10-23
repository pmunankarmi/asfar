<?php
$seed = json_decode( file_get_contents( __DIR__ . '/../asfar/inc/supplier-seed.json' ), true );
$data = $seed[$argv[1] ?? 'en'];
$index = -1;
function get_field( $name ) { global $data; return $data[$name] ?? ''; }
function have_rows( $name ) { global $data, $index; if ( $index + 1 < count( $data[$name] ) ) return true; $index = -1; return false; }
function the_row() { global $index; ++$index; }
function get_row_index() { global $index; return $index + 1; }
function get_sub_field( $name ) { global $data, $index; return $data['registration_cards'][$index][$name]; }
function esc_html( $value ) { return htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' ); }
function esc_attr( $value ) { return esc_html( $value ); }
function esc_url( $value ) { return esc_attr( $value ); }
function asfar_home_url() { return '/'; }
require __DIR__ . '/../asfar/template-parts/supplier-portal.php';
