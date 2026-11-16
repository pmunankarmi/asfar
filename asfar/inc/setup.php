<?php
/** Explicit, idempotent initial content import. Existing fields are never reset. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
add_action( 'admin_menu', function () { add_theme_page( 'ASFAR Setup', 'ASFAR Setup', 'manage_options', 'asfar-setup', 'asfar_setup_page' ); } );
function asfar_setup_page() {
 if ( ! current_user_can( 'manage_options' ) ) { return; }
 if ( current_user_can( 'update_themes' ) ) { echo '<div class="wrap"><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="asfar_check_updates">'; wp_nonce_field( 'asfar_check_updates' ); submit_button( 'Check GitHub for theme updates', 'secondary' ); echo '</form></div>'; }
 echo '<div class="wrap"><h1>ASFAR Setup</h1><h2>1. Install images in the Media Library</h2><p>Images are supplied separately in asfar-media.zip. Upload this package first. Existing attachments are reused.</p><form enctype="multipart/form-data" method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="asfar_media_upload"><input type="file" name="asfar_media" accept=".zip" required>';
 wp_nonce_field( 'asfar_media_upload' ); submit_button( 'Upload image package' ); echo '</form><p>Alternatively, extract asfar-media.zip into wp-content/uploads using your hosting file manager, then register the files below.</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="asfar_media_upload"><input type="hidden" name="register_only" value="1">';
 wp_nonce_field( 'asfar_media_upload' ); submit_button( 'Register images already in uploads', 'secondary' ); echo '</form><h2>2. Populate content</h2>';
 echo '<p>First activate ACF Pro and Polylang, and add English (en) and Arabic (ar). Import creates pages, articles and media. It fills only fields never initialized, preserving your edits and intentionally cleared values. It does not change your homepage assignment.</p><form method="post" action="' . esc_url( admin_url( 'admin-post.php' ) ) . '"><input type="hidden" name="action" value="asfar_seed">';
 wp_nonce_field( 'asfar_seed' );
 submit_button( 'Populate existing website content' );
 echo '</form><p>After import, assign the ASFAR Homepage template to the imported homepages and select the English homepage in Settings → Reading. Translation pairs are linked automatically.</p></div>';
}
function asfar_import_asset( $relative ) {
 if ( ! $relative ) { return 0; }
 $key = 'asfar_asset_' . md5( $relative );
 $existing = absint( get_option( $key ) );
 if ( $existing && get_post( $existing ) ) { return $existing; }
 $image = str_starts_with( $relative, 'assets/img/' );
 $path = $image ? substr( $relative, 7 ) : '';
 if ( $image ) {
  if ( ! isset( asfar_media_manifest()[ $path ] ) ) { throw new RuntimeException( 'Unknown image in content import.' ); }
  $source = realpath( asfar_uploads_media_dir() . '/' . $path );
  $root = realpath( asfar_uploads_media_dir() );
 } else {
  $source = realpath( get_template_directory() . '/' . $relative );
  $root = realpath( get_template_directory() . '/assets' );
 }
 if ( ! $source || ! $root || ! str_starts_with( $source, $root . DIRECTORY_SEPARATOR ) || ! is_file( $source ) ) { throw new RuntimeException( 'Install the media package before importing content: ' . $relative ); }
 $upload = wp_upload_dir();
 if ( $upload['error'] ) { throw new RuntimeException( $upload['error'] ); }
 if ( $image ) {
  // Register the original file directly. Do not duplicate images into the theme.
  $dest = $source;
  $filename = basename( $source );
 } else {
  $dir = $upload['basedir'] . '/asfar';
  wp_mkdir_p( $dir );
  $filename = substr( md5( $relative ), 0, 10 ) . '-' . sanitize_file_name( basename( $relative ) );
  $dest = $dir . '/' . $filename;
  if ( ! copy( $source, $dest ) ) { throw new RuntimeException( 'Unable to import media.' ); }
 }
 $type = wp_check_filetype( $filename );
 $mime = 'svg' === strtolower( pathinfo( $filename, PATHINFO_EXTENSION ) ) ? 'image/svg+xml' : $type['type'];
 // SVGs here are checksum-verified supplied artwork. This does not permit arbitrary SVG uploads.
 $id = wp_insert_attachment( array( 'post_title' => pathinfo( basename( $relative ), PATHINFO_FILENAME ), 'post_mime_type' => $mime ?: 'application/octet-stream', 'post_status' => 'inherit' ), $dest, 0, true );
 if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
 require_once ABSPATH . 'wp-admin/includes/image.php';
 if ( 'image/svg+xml' !== $mime && ! str_starts_with( $path, 'img/morph/' ) && str_starts_with( (string) $mime, 'image/' ) ) { wp_update_attachment_metadata( $id, wp_generate_attachment_metadata( $id, $dest ) ); }
 update_option( $key, $id, false );
 return $id;
}
function asfar_import_value( $value ) {
 if ( is_array( $value ) && isset( $value['asset'] ) ) { return asfar_import_asset( $value['asset'] ); }
 if ( is_array( $value ) ) { foreach ( $value as &$item ) { $item = asfar_import_value( $item ); } }
 return $value;
}
function asfar_seed_field( $name, $value, $context, $key ) {
 $exists = is_numeric( $context ) ? metadata_exists( 'post', $context, $name ) : false !== get_option( $context . '_' . $name, false );
 if ( $exists ) { return; }
 if ( is_numeric( $context ) ) { update_post_meta( $context, $name, asfar_import_value( $value ) ); } else { update_option( $context . '_' . $name, asfar_import_value( $value ), false ); }
}
function asfar_seed() {
 return asfar_with_content_lock( 'asfar_seed_content' );
}
function asfar_seed_content() {
 asfar_verify_media_package();
 if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'pll_save_post_translations' ) ) { throw new RuntimeException( 'Activate ACF Pro and Polylang first.' ); }
 $langs = pll_languages_list( array( 'fields' => 'slug' ) );
 if ( ! in_array( 'en', $langs, true ) || ! in_array( 'ar', $langs, true ) ) { throw new RuntimeException( 'Create English (en) and Arabic (ar) in Polylang first.' ); }
 $data = json_decode( file_get_contents( __DIR__ . '/seed.json' ), true );
 $map = get_option( 'asfar_page_map', array() );
 foreach ( $data['pages'] as $slug => $page ) {
  if ( empty( $map[ $slug ] ) || ! get_post( $map[ $slug ] ) ) {
   $id = wp_insert_post( array( 'post_type' => $page['type'], 'post_title' => $page['title'], 'post_name' => $slug, 'post_status' => 'publish' ), true );
   if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
   $map[ $slug ] = $id;
   update_post_meta( $id, '_asfar_layout', $slug );
   update_post_meta( $id, '_asfar_language', $page['lang'] );
   if ( in_array( $slug, array( 'index', 'index-ar' ), true ) ) { update_post_meta( $id, '_wp_page_template', 'templates/template-home.php' ); }
   pll_set_post_language( $id, $page['lang'] );
   update_option( 'asfar_page_map', $map, false );
  }
  foreach ( $page['values'] as $name => $value ) { asfar_seed_field( $name, $value, $map[ $slug ], 'field_asfar_' . $name ); }
 }
 foreach ( $data['news'] as $article ) {
  foreach ( array( 'en', 'ar' ) as $lang ) {
   $slug = 'news-' . $article['slug'] . ( 'ar' === $lang ? '-ar' : '' );
   if ( empty( $map[ $slug ] ) ) {
    $id = wp_insert_post( array( 'post_type' => 'post', 'post_status' => 'publish', 'post_title' => $article[ $lang ]['title'], 'post_name' => $slug ), true );
    if ( is_wp_error( $id ) ) { throw new RuntimeException( $id->get_error_message() ); }
    $map[ $slug ] = $id;
    pll_set_post_language( $id, $lang );
    update_post_meta( $id, '_asfar_language', $lang );
    update_option( 'asfar_page_map', $map, false );
   }
   $id = $map[ $slug ];
   if ( ! get_post_meta( $id, '_asfar_news_initialized', true ) ) {
    $date = strtotime( $article['en']['date'] ) ?: time();
    wp_update_post( array( 'ID' => $id, 'post_title' => $article[ $lang ]['title'], 'post_date' => gmdate( 'Y-m-d H:i:s', $date ), 'post_date_gmt' => gmdate( 'Y-m-d H:i:s', $date ) ) );
    set_post_thumbnail( $id, asfar_import_asset( $article['img'] ) );
    asfar_seed_field( 'display_date', $article[ $lang ]['date'], $id, 'field_asfar_display_date' );
    if ( empty( $article[ $lang ]['paras'] ) ) {
     asfar_seed_field( 'external_url', 'https://thefinanceworld.com/the-evolving-role-of-the-cfo-from-financial-steward-to-strategic-leader/', $id, 'field_asfar_external_url' );
    }
    update_post_meta( $id, '_asfar_news_initialized', 1 );
   }
  }
 }
 foreach ( $map as $slug => $id ) {
  if ( ! str_ends_with( $slug, '-ar' ) && isset( $map[ $slug . '-ar' ] ) && ! pll_get_post( $id, 'ar' ) ) { pll_save_post_translations( array( 'en' => $id, 'ar' => $map[ $slug . '-ar' ] ) ); }
 }
 foreach ( $data['options'] as $scope => $values ) {
  foreach ( $values as $name => $value ) {
   $key = str_starts_with( $name, 'c_' ) ? 'field_asfar_' . $name : 'field_asfar_' . $scope . '_' . $name;
   asfar_seed_field( $name, $value, $scope, $key );
  }
 }
 if ( false === get_option( 'asfar_logo_initialized', false ) ) {
  asfar_sync_logo( get_theme_mod( 'custom_logo' ) ?: asfar_import_asset( 'assets/img/asfar-logo-ondark.svg' ) );
  update_field( 'field_asfar_settings_dark_logo', asfar_import_asset( 'assets/img/asfar-logo-dark.svg' ), 'asfar_shared' );
  update_option( 'asfar_logo_initialized', 1, false );
 }
 update_option( 'asfar_seed_complete', 1, false );
 asfar_migrate();
}
add_action( 'admin_post_asfar_seed', function () {
 if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Forbidden', '', array( 'response' => 403 ) ); }
 check_admin_referer( 'asfar_seed' );
 try { asfar_seed(); } catch ( Throwable $error ) { wp_die( esc_html( $error->getMessage() ) ); }
 wp_safe_redirect( admin_url( 'themes.php?page=asfar-setup&imported=1' ) ); exit;
} );
if ( defined( 'WP_CLI' ) && WP_CLI ) { WP_CLI::add_command( 'asfar seed', function () { asfar_seed(); WP_CLI::success( 'ASFAR content populated; existing values preserved.' ); } ); }
