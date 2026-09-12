<?php
/** Image files live in WordPress uploads, independently of theme releases. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function asfar_uploads_media_dir() { $uploads = wp_upload_dir(); return $uploads['basedir'] . '/asfar-media'; }
function asfar_uploads_media_url() { $uploads = wp_upload_dir(); return $uploads['baseurl'] . '/asfar-media'; }
function asfar_media_manifest() {
 static $manifest;
 if ( null === $manifest ) { $manifest = json_decode( file_get_contents( __DIR__ . '/media-manifest.json' ), true ); }
 return $manifest;
}
function asfar_original_image_url( $path ) {
 if ( ! isset( asfar_media_manifest()[ $path ] ) ) { return ''; }
 $id = absint( get_option( 'asfar_asset_' . md5( 'assets/' . $path ) ) );
 return ( $id ? wp_get_attachment_url( $id ) : '' ) ?: asfar_uploads_media_url() . '/' . $path;
}
function asfar_enqueue_image_styles() {
 $images = json_decode( file_get_contents( __DIR__ . '/image-styles.json' ), true );
 $css = ':root{';
 foreach ( $images as $variable => $path ) {
  $css .= $variable . ':url("' . esc_url( asfar_original_image_url( $path ) ) . '");';
 }
 wp_add_inline_style( 'asfar', $css . '}' );
}
function asfar_render_map_artwork() {
 $path = 'img/asfar-map-deck.svg';
 $file = asfar_uploads_media_dir() . '/' . $path;
 // Inline only the exact supplied artwork, never an arbitrary uploaded SVG.
 if ( is_file( $file ) && hash_equals( asfar_media_manifest()[ $path ], hash_file( 'sha256', $file ) ) ) {
  $svg = file_get_contents( $file );
  $svg = preg_replace_callback( '/\b(class|id)="([^"]+)"/', function ( $match ) {
   $names = preg_split( '/\s+/', trim( $match[2] ) );
   return $match[1] . '="' . implode( ' ', array_map( function ( $name ) { return 'mt-' . $name; }, $names ) ) . '"';
  }, $svg );
  $svg = preg_replace( '/url\(#([^)]+)\)/', 'url(#mt-$1)', $svg );
  echo $svg; // Only checksum-verified bundled artwork is rendered.
 }
}
function asfar_verify_media_package() {
 foreach ( asfar_media_manifest() as $path => $hash ) {
  $file = asfar_uploads_media_dir() . '/' . $path;
  if ( ! is_file( $file ) || ! hash_equals( $hash, hash_file( 'sha256', $file ) ) ) { throw new RuntimeException( 'Install the supplied asfar-media.zip first. Missing or changed file: ' . $path ); }
 }
}
function asfar_install_media_zip( $file ) {
 if ( ! class_exists( 'ZipArchive' ) ) { throw new RuntimeException( 'PHP ZipArchive is unavailable. Extract asfar-media.zip into wp-content/uploads using your hosting file manager, then register the media.' ); }
 $zip = new ZipArchive();
 if ( true !== $zip->open( $file ) ) { throw new RuntimeException( 'Unable to read the media ZIP.' ); }
 try {
  $manifest = asfar_media_manifest();
  $entries = array();
  if ( $zip->numFiles > count( $manifest ) + 30 ) { throw new RuntimeException( 'Unexpected media package.' ); }
  // Verify the entire archive before writing. Unknown files, traversal paths,
  // duplicate members, modified SVGs and oversized entries are rejected.
  for ( $i = 0; $i < $zip->numFiles; $i++ ) {
   $entry = $zip->statIndex( $i );
   $name = $entry['name'];
   if ( str_ends_with( $name, '/' ) && preg_match( '~^asfar-media/(?:img/)?(?:[a-zA-Z0-9_-]+/)*$~', $name ) ) { continue; }
   $path = str_starts_with( $name, 'asfar-media/' ) ? substr( $name, 12 ) : '';
   if ( ! isset( $manifest[ $path ] ) || isset( $entries[ $path ] ) || $entry['size'] > 20 * MB_IN_BYTES ) { throw new RuntimeException( 'Unexpected or unsafe media ZIP entry.' ); }
   $contents = $zip->getFromIndex( $i );
   if ( false === $contents || ! hash_equals( $manifest[ $path ], hash( 'sha256', $contents ) ) ) { throw new RuntimeException( 'Media file does not match the supplied package.' ); }
   $entries[ $path ] = $i;
  }
  if ( count( $entries ) !== count( $manifest ) ) { throw new RuntimeException( 'The media package is incomplete.' ); }
  foreach ( $entries as $path => $index ) {
   $target = asfar_uploads_media_dir() . '/' . $path;
   if ( is_file( $target ) ) {
    if ( ! hash_equals( $manifest[ $path ], hash_file( 'sha256', $target ) ) ) { throw new RuntimeException( 'An existing image differs and was preserved: ' . $path ); }
    continue;
   }
   if ( ! wp_mkdir_p( dirname( $target ) ) || false === file_put_contents( $target, $zip->getFromIndex( $index ), LOCK_EX ) ) { throw new RuntimeException( 'The uploads folder is not writable.' ); }
  }
 } finally { $zip->close(); }
 asfar_register_media_package();
}
function asfar_register_media_package() {
 asfar_verify_media_package();
 foreach ( asfar_media_manifest() as $path => $hash ) { asfar_import_asset( 'assets/' . $path ); }
 update_option( 'asfar_media_installed', 1, false );
}
add_action( 'admin_post_asfar_media_upload', function () {
 if ( ! current_user_can( 'manage_options' ) || ! current_user_can( 'upload_files' ) ) { wp_die( 'Forbidden', '', array( 'response' => 403 ) ); }
 check_admin_referer( 'asfar_media_upload' );
 try {
  if ( ! empty( $_POST['register_only'] ) ) { asfar_register_media_package(); }
  else {
   $file = $_FILES['asfar_media'] ?? array();
   if ( ! isset( $file['tmp_name'], $file['error'] ) || UPLOAD_ERR_OK !== $file['error'] || ! is_uploaded_file( $file['tmp_name'] ) ) { throw new RuntimeException( 'Choose asfar-media.zip. Check the hosting upload limit if the upload failed.' ); }
   asfar_install_media_zip( $file['tmp_name'] );
  }
 } catch ( Throwable $error ) { wp_die( esc_html( $error->getMessage() ) ); }
 wp_safe_redirect( admin_url( 'themes.php?page=asfar-setup&media=1' ) ); exit;
} );
if ( defined( 'WP_CLI' ) && WP_CLI ) {
 WP_CLI::add_command( 'asfar media', function ( $args ) {
  if ( empty( $args[0] ) ) { asfar_register_media_package(); } else { asfar_install_media_zip( $args[0] ); }
  WP_CLI::success( 'Images installed in uploads and registered in the Media Library.' );
 } );
}

add_action( 'admin_notices', function () {
 if ( current_user_can( 'manage_options' ) && ! is_file( asfar_uploads_media_dir() . '/img/asfar-map-deck.svg' ) ) {
  echo '<div class="notice notice-warning"><p>ASFAR images are stored separately from the theme. Upload asfar-media.zip in Appearance → ASFAR Setup before using this version.</p></div>';
 }
} );
