<?php
$_SERVER['HTTP_HOST'] = '127.0.0.1:8877';
$_SERVER['REQUEST_URI'] = '/wp-admin/';
require rtrim( $argv[1], '/' ) . '/wp-load.php';
$map = get_option( 'asfar_page_map' );
foreach ( array( 'index', 'index-ar' ) as $key ) { update_post_meta( $map[$key], '_wp_page_template', 'template-home.php' ); }
update_option( 'asfar_template_folder_version', 1 );
asfar_upgrade_template_paths();
foreach ( array( 'index', 'index-ar' ) as $key ) {
 if ( get_post_meta( $map[$key], '_wp_page_template', true ) !== 'templates/template-home.php' ) { throw new RuntimeException( 'Template not migrated: ' . $key ); }
}
if ( asfar_anchor_url( '/en/#mt-philosophy' ) !== '/en/#philosophy' || asfar_anchor_url( '/en/#philosophy' ) !== '/en/#philosophy' ) { throw new RuntimeException( 'Anchor normalization failed.' ); }
echo "PASS: Version-one migration repair updates both homepages and restores clean anchors.\n";
