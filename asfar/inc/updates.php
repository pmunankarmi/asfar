<?php
/** GitHub release updates using a packaged asset with a stable theme slug. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function asfar_release() {
 $cached = get_transient( 'asfar_github_release' );
 if ( false !== $cached ) { return $cached; }
 $headers = array( 'Accept' => 'application/vnd.github+json', 'X-GitHub-Api-Version' => '2022-11-28' );
 // Optional token is configured outside the theme and sent only to api.github.com.
 if ( defined( 'ASFAR_GITHUB_TOKEN' ) && ASFAR_GITHUB_TOKEN ) { $headers['Authorization'] = 'Bearer ' . ASFAR_GITHUB_TOKEN; }
 $response = wp_remote_get( 'https://api.github.com/repos/pmunankarmi/asfar/releases/latest', array( 'headers' => $headers, 'timeout' => 10 ) );
 if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { set_transient( 'asfar_github_release', array(), 15 * MINUTE_IN_SECONDS ); return array(); }
 $release = json_decode( wp_remote_retrieve_body( $response ), true );
 if ( ! is_array( $release ) || ! empty( $release['draft'] ) || ! empty( $release['prerelease'] ) || ! preg_match( '/^v?(\d+\.\d+\.\d+)$/', $release['tag_name'] ?? '', $matches ) ) { return array(); }
 $package = ''; $asset_api = '';
 foreach ( $release['assets'] ?? array() as $asset ) {
  if ( 'asfar.zip' === $asset['name'] ) { $package = $asset['browser_download_url']; $asset_api = $asset['url'] ?? ''; break; }
 }
 if ( ! str_starts_with( $package, 'https://github.com/pmunankarmi/asfar/releases/download/' ) ) { return array(); }
 $result = array( 'asset_api' => preg_match( '~^https://api\.github\.com/repos/pmunankarmi/asfar/releases/assets/[0-9]+$~', $asset_api ) ? $asset_api : '', 'version' => $matches[1], 'package' => $package, 'url' => 'https://github.com/pmunankarmi/asfar/releases' );
 set_transient( 'asfar_github_release', $result, 6 * HOUR_IN_SECONDS );
 return $result;
}
add_filter( 'pre_set_site_transient_update_themes', function ( $transient ) {
 if ( ! is_object( $transient ) || empty( $transient->checked ) ) { return $transient; }
 $slug = get_template();
 if ( ! isset( $transient->checked[ $slug ] ) ) { return $transient; }
 $release = asfar_release();
 if ( empty( $release['version'] ) ) { return $transient; }
 $item = array( 'theme' => $slug, 'new_version' => $release['version'], 'url' => $release['url'], 'package' => $release['package'], 'requires' => '6.6', 'requires_php' => '8.1' );
 if ( version_compare( $release['version'], $transient->checked[ $slug ], '>' ) ) { $transient->response[ $slug ] = $item; unset( $transient->no_update[ $slug ] ); }
 else { $transient->no_update[ $slug ] = $item; unset( $transient->response[ $slug ] ); }
 return $transient;
} );
add_filter( 'upgrader_source_selection', function ( $source, $remote_source, $upgrader, $extra ) {
 if ( ( $extra['type'] ?? '' ) !== 'theme' || ( $extra['theme'] ?? '' ) !== get_template() ) { return $source; }
 global $wp_filesystem;
 $style = $wp_filesystem->get_contents( trailingslashit( $source ) . 'style.css' );
 if ( ! $style || ! preg_match( '/Theme Name:\s*ASFAR\s*$/m', $style ) || ! $wp_filesystem->exists( trailingslashit( $source ) . 'functions.php' ) ) { return new WP_Error( 'asfar_invalid_package', 'Invalid ASFAR theme package.' ); }
 $target = trailingslashit( $remote_source ) . get_template() . '/';
 if ( untrailingslashit( $source ) !== untrailingslashit( $target ) && ! $wp_filesystem->move( $source, $target, true ) ) { return new WP_Error( 'asfar_package_rename', 'Unable to preserve the installed theme directory.' ); }
 return $target;
}, 10, 4 );
add_action( 'upgrader_process_complete', function () { delete_transient( 'asfar_github_release' ); }, 10, 0 );
// Private release downloads use the API only when the administrator supplies a token.
add_filter( 'upgrader_pre_download', function ( $reply, $package, $upgrader, $extra ) {
 if ( false !== $reply || ! defined( 'ASFAR_GITHUB_TOKEN' ) || ! ASFAR_GITHUB_TOKEN || ! str_starts_with( $package, 'https://github.com/pmunankarmi/asfar/releases/download/' ) ) { return $reply; }
 $release = asfar_release();
 if ( empty( $release['asset_api'] ) || $package !== $release['package'] ) { return $reply; }
 $temporary = wp_tempnam( 'asfar.zip' );
 $response = wp_remote_get( $release['asset_api'], array( 'headers' => array( 'Authorization' => 'Bearer ' . ASFAR_GITHUB_TOKEN, 'Accept' => 'application/octet-stream' ), 'timeout' => 120, 'redirection' => 0, 'stream' => true, 'filename' => $temporary ) );
 if ( ! is_wp_error( $response ) && in_array( wp_remote_retrieve_response_code( $response ), array( 301, 302, 303, 307, 308 ), true ) ) {
  $url = wp_remote_retrieve_header( $response, 'location' );
  if ( 'https' !== wp_parse_url( $url, PHP_URL_SCHEME ) || 'release-assets.githubusercontent.com' !== wp_parse_url( $url, PHP_URL_HOST ) ) { wp_delete_file( $temporary ); return new WP_Error( 'asfar_download_host', 'Unexpected release download host.' ); }
  // No Authorization header is forwarded to the signed asset URL.
  $response = wp_safe_remote_get( $url, array( 'timeout' => 120, 'redirection' => 0, 'stream' => true, 'filename' => $temporary ) );
 }
 if ( is_wp_error( $response ) || 200 !== wp_remote_retrieve_response_code( $response ) ) { wp_delete_file( $temporary ); return new WP_Error( 'asfar_download', 'Unable to download the GitHub release package.' ); }
 return $temporary;
}, 10, 4 );
add_action( 'admin_post_asfar_check_updates', function () {
 if ( ! current_user_can( 'update_themes' ) ) { wp_die( 'Forbidden', '', array( 'response' => 403 ) ); }
 check_admin_referer( 'asfar_check_updates' );
 delete_transient( 'asfar_github_release' );
 delete_site_transient( 'update_themes' );
 wp_update_themes();
 wp_safe_redirect( admin_url( 'update-core.php' ) ); exit;
} );
