<?php
/** Isolated test of the narrow AMV4 correction; no WordPress database required. */
define( 'ABSPATH', __DIR__ );
$options = array( 'asfar_content_schema_version' => 2 );
$fields = array( 1 => array( 'card_heading' => 'نستثمر في المدن والواعدة', 'paragraphs' => array( 'Keep Arabic content' ) ), 2 => array( 'card_heading' => 'Custom Arabic heading' ), 3 => array( 'card_heading' => 'English heading' ) );
function add_action( ...$args ) {}
function get_option( $name ) { global $options; return $options[$name] ?? false; }
function update_option( $name, $value, ...$args ) { global $options; $options[$name] = $value; }
function get_posts( $args ) { if ( $args['lang'] !== '' ) { throw new Exception( 'Must include all languages.' ); } return array( 1, 2, 3 ); }
function get_field( $name, $id ) { global $fields; return $fields[$id]; }
function update_field( $name, $value, $id ) { global $fields; $fields[$id] = $value; }
function pll_get_post_language( $id ) { return 3 === $id ? 'en' : 'ar'; }
class PLL_MO {}
function PLL() { return (object) array( 'model' => new class { function get_language( $language ) { return $language; } } ); }
function asfar_migrate_strings( $pairs ) { if ( count( $pairs ) !== 3 ) { throw new Exception( 'Missing new UI translations.' ); } }
require __DIR__ . '/../asfar/inc/amv4.php';
asfar_update_amv4_content();
if ( $fields[1]['card_heading'] !== 'نستثمر في المدن الواعدة' || $fields[1]['paragraphs'] !== array( 'Keep Arabic content' ) || $fields[2]['card_heading'] !== 'Custom Arabic heading' || $fields[3]['card_heading'] !== 'English heading' ) { throw new Exception( 'Correction overwrote content.' ); }
$fields[1]['card_heading'] = 'Later edit';
asfar_update_amv4_content();
if ( $fields[1]['card_heading'] !== 'Later edit' ) { throw new Exception( 'Migration repeated.' ); }
echo "PASS: Exact source typo corrected, custom/English/other fields preserved, migration is idempotent.\n";
