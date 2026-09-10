<?php
/** Run against an isolated WordPress installation, never production. */
$_SERVER['HTTP_HOST'] = '127.0.0.1:8877'; $_SERVER['REQUEST_URI'] = '/wp-admin/'; define( 'WP_ADMIN', true );
require rtrim( $argv[1], '/' ) . '/wp-load.php';
require_once ABSPATH . 'wp-admin/includes/file.php';
wp_set_current_user( 1 );
$package = dirname( __DIR__ ) . '/dist/asfar-media.zip';
asfar_install_media_zip( $package );
$first = array();
foreach ( asfar_media_manifest() as $path => $hash ) {
 $id = absint( get_option( 'asfar_asset_' . md5( 'assets/' . $path ) ) );
 if ( ! $id || ! get_post( $id ) || ! is_file( get_attached_file( $id ) ) ) { throw new RuntimeException( 'Image not registered: ' . $path ); }
 if ( ! str_contains( wp_get_attachment_url( $id ), '/uploads/' ) ) { throw new RuntimeException( 'Image is outside uploads.' ); }
 $first[ $path ] = $id;
}
echo 'PASS: ' . count( $first ) . " images registered in uploads/Media Library.\n";
asfar_install_media_zip( $package );
foreach ( $first as $path => $id ) {
 if ( $id !== absint( get_option( 'asfar_asset_' . md5( 'assets/' . $path ) ) ) ) { throw new RuntimeException( 'Repeat upload changed an attachment.' ); }
}
echo "PASS: Repeat media installation reuses attachment IDs.\n";
$bad = wp_tempnam( 'invalid-media.zip' );
$zip = new ZipArchive(); $zip->open( $bad, ZipArchive::CREATE | ZipArchive::OVERWRITE ); $zip->addFromString( 'asfar-media/../../unsafe.php', '<?php' ); $zip->close();
$rejected = false;
try { asfar_install_media_zip( $bad ); } catch ( RuntimeException $error ) { $rejected = true; }
wp_delete_file( $bad );
if ( ! $rejected ) { throw new RuntimeException( 'Unsafe archive accepted.' ); }
echo "PASS: Unsafe media archive rejected.\n";
asfar_verify_media_package();
ob_start(); asfar_render_map_artwork(); $svg = ob_get_clean();
if ( ! str_contains( $svg, '<svg' ) ) { throw new RuntimeException( 'Uploaded map failed to render.' ); }
echo "PASS: Map artwork renders from uploads.\n";
