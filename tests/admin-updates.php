<?php
/** Run only against an isolated local WordPress fixture. */
$_SERVER['HTTP_HOST'] = '127.0.0.1:8877';
$_SERVER['REQUEST_URI'] = '/wp-admin/';
require rtrim( $argv[1], '/' ) . '/wp-load.php';
wp_set_current_user( 1 );
$calls = 0;
add_filter( 'pre_http_request', function ( $pre, $args, $url ) use ( &$calls ) {
 if ( ! str_contains( $url, 'api.github.com/repos/pmunankarmi/asfar/releases/latest' ) ) { return $pre; }
 ++$calls;
 return array( 'response' => array( 'code' => 200 ), 'body' => wp_json_encode( array( 'tag_name' => 'v9.0.0', 'assets' => array( array( 'name' => 'asfar.zip', 'browser_download_url' => 'https://github.com/pmunankarmi/asfar/releases/download/v9.0.0/asfar.zip' ) ) ) ) );
}, 10, 3 );
$previous = get_site_transient( 'update_themes' );
set_transient( 'asfar_github_release', array(), HOUR_IN_SECONDS );
set_site_transient( 'update_themes', (object) array( 'checked' => array( 'other-theme' => '1.0' ), 'response' => array( 'other-theme' => array( 'new_version' => '2.0' ) ) ) );
delete_transient( 'asfar_admin_update_check' );
asfar_refresh_admin_theme_update();
$updates = get_site_transient( 'update_themes' );
if ( ( $updates->response['asfar']['new_version'] ?? '' ) !== '9.0.0' || ! isset( $updates->response['other-theme'] ) ) { throw new RuntimeException( 'Admin refresh failed or removed another update.' ); }
asfar_refresh_admin_theme_update();
if ( 1 !== $calls ) { throw new RuntimeException( 'Refresh was not throttled.' ); }
delete_transient( 'asfar_admin_update_check' );
 $heartbeat = apply_filters( 'heartbeat_received', array() );
if ( empty( $heartbeat['asfar_theme_update']['url'] ) ) { throw new RuntimeException( 'Heartbeat update missing.' ); }
if ( 2 !== $calls ) { throw new RuntimeException( 'Heartbeat refresh missing.' ); }
wp_set_current_user( 0 );
asfar_refresh_admin_theme_update();
if ( 2 !== $calls ) { throw new RuntimeException( 'Unauthorized refresh.' ); }
if ( isset( apply_filters( 'heartbeat_received', array() )['asfar_theme_update'] ) ) { throw new RuntimeException( 'Unauthorized Heartbeat data.' ); }
set_transient( 'asfar_github_release', array(), 1 );
set_site_transient( 'update_themes', $previous );
delete_transient( 'asfar_github_release' );
echo "PASS: Admin update discovery, stale cache refresh, two-minute throttle, permissions and preservation of other updates.\n";
