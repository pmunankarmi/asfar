<?php
/** Run only against an isolated imported WordPress fixture. */
$_SERVER['HTTP_HOST'] = '127.0.0.1:8877';
$_SERVER['REQUEST_URI'] = '/wp-admin/';
define( 'WP_ADMIN', true );
require rtrim( $argv[1], '/' ) . '/wp-load.php';
wp_set_current_user( 1 );
function check_repair( $condition, $label ) {
 if ( ! $condition ) { throw new RuntimeException( $label ); }
 echo 'PASS: ' . $label . "\n";
}
add_option( 'asfar_content_lock', time(), '', false );
$called = false;
try { asfar_with_content_lock( function () use ( &$called ) { $called = true; } ); }
catch ( RuntimeException $error ) {}
finally { delete_option( 'asfar_content_lock' ); }
check_repair( ! $called, 'Overlapping content operation cannot acquire the import lock' );
$map = get_option( 'asfar_content_map' );
$id = $map['team_0_ar'];
$duplicate = wp_insert_post( array( 'post_type' => 'asfar_team', 'post_title' => get_the_title( $id ), 'post_status' => 'publish' ) );
pll_set_post_language( $duplicate, 'ar' );
$project = $map['project_0_en'];
$summary = get_field( 'summary', $project );
update_field( 'field_asfar_project_summary', '', $project );
$pages = get_option( 'asfar_page_map' );
$home = $pages['index-ar'];
$portfolio = get_field( 'portfolio_section', $home );
$faq_section = get_field( 'faq_section', $home );
$project_duplicate = wp_insert_post( array( 'post_type' => 'asfar_project', 'post_title' => get_the_title( $map['project_0_ar'] ), 'post_status' => 'publish' ) );
$faq_duplicate = wp_insert_post( array( 'post_type' => 'page', 'post_title' => get_the_title( $map['faq_ar'] ), 'post_status' => 'publish' ) );
pll_set_post_language( $project_duplicate, 'ar' );
pll_set_post_language( $faq_duplicate, 'ar' );
$changed_portfolio = $portfolio;
$changed_portfolio['projects'] = array( $project_duplicate, $map['project_1_ar'] );
$changed_faq = $faq_section;
$changed_faq['page'] = $faq_duplicate;
update_field( 'field_asfar_home_portfolio_portfolio_section', $changed_portfolio, $home );
update_field( 'field_asfar_home_faq_faq_section', $changed_faq, $home );
$menu = wp_create_nav_menu( 'ASFAR Repair Test ' . time() );
$items = array();
for ( $i = 0; $i < 2; $i++ ) { $items[] = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => 'Duplicate link fixture', 'menu-item-url' => home_url( '/#about' ), 'menu-item-status' => 'publish' ) ); }
try {
 check_repair( isset( asfar_content_duplicates()[ $duplicate ] ), 'Duplicate translated team member identified' );
 $result = asfar_repair_imported_content();
 check_repair( get_field( 'portfolio_section', $home )['projects'] == array( $map['project_0_ar'], $map['project_1_ar'] ), 'Homepage project references remapped while preserving selection order' );
 check_repair( get_field( 'faq_section', $home )['page'] == $map['faq_ar'], 'Homepage FAQ reference remapped to canonical page' );
 check_repair( 'trash' === get_post_status( $duplicate ) && 'publish' === get_post_status( $id ), 'Only the duplicate is moved to Trash' );
 check_repair( get_field( 'summary', $project ) === $summary, 'Missing project summary restored from supplied content' );
 check_repair( pll_get_post( $map['team_0_en'], 'ar' ) === $id, 'Canonical team translation pair retained' );
 check_repair( count( wp_get_nav_menu_items( $menu ) ) === 1 && get_post( $items[1] ), 'Duplicate menu link archived without deleting its record' );
 $again = asfar_repair_imported_content();
 check_repair( 0 === $again['duplicates'] && 0 === $again['menu_items'], 'Second repair finds no remaining duplicates' );
} finally {
 update_field( 'field_asfar_home_portfolio_portfolio_section', $portfolio, $home );
 update_field( 'field_asfar_home_faq_faq_section', $faq_section, $home );
 wp_delete_post( $project_duplicate, true );
 wp_delete_post( $faq_duplicate, true );
 wp_delete_post( $duplicate, true );
 update_field( 'field_asfar_project_summary', $summary, $project );
 foreach ( $items as $item ) { wp_delete_post( $item, true ); }
 wp_delete_nav_menu( $menu );
}
