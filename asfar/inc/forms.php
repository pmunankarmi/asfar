<?php
/** Durable submissions, authenticated administration and safe CSV export. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function asfar_submissions_table() { global $wpdb; return $wpdb->prefix . 'asfar_submissions'; }
function asfar_schema() {
 if ( '1' === get_option( 'asfar_schema_version' ) ) { return; }
 global $wpdb;
 require_once ABSPATH . 'wp-admin/includes/upgrade.php';
 $table = asfar_submissions_table();
 dbDelta( "CREATE TABLE {$table} (
 id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
 created_at datetime NOT NULL,
 language varchar(8) NOT NULL,
 name varchar(200) NOT NULL,
 email varchar(254) NOT NULL,
 message longtext NOT NULL,
 mail_status varchar(20) NOT NULL DEFAULT 'pending',
 request_key varchar(64) NOT NULL,
 PRIMARY KEY  (id),
 UNIQUE KEY request_key (request_key),
 KEY created_at (created_at),
 KEY language (language)
 ) " . $wpdb->get_charset_collate() . ';' );
 if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table ) ) ) === $table ) { update_option( 'asfar_schema_version', '1', false ); }
}
add_action( 'after_switch_theme', 'asfar_schema' );
add_action( 'admin_init', 'asfar_schema' );
function asfar_form_hidden() {
 wp_nonce_field( 'asfar_contact', '_asfar_nonce', false );
 echo '<input type="hidden" name="action" value="asfar_contact"><input type="hidden" name="language" value="' . esc_attr( asfar_language() ) . '"><input type="hidden" name="request_key" value="' . esc_attr( wp_generate_uuid4() ) . '"><div class="asfar-honeypot" aria-hidden="true"><input type="text" name="website" tabindex="-1" autocomplete="off" aria-label="Website"></div>';
}
function asfar_input( $key ) { return isset( $_POST[ $key ] ) && is_string( $_POST[ $key ] ) ? wp_unslash( $_POST[ $key ] ) : ''; }
function asfar_form_reply( $ok, $code, $language ) {
 $text = asfar_option( $code, $language );
 if ( wp_doing_ajax() ) {
  if ( $ok ) { wp_send_json_success( array( 'message' => $text ) ); }
  wp_send_json_error( array( 'message' => $text ), 400 );
 }
 // A readable response also works without JavaScript; no submitted PII enters a URL.
 wp_die( esc_html( $text ), '', array( 'response' => $ok ? 200 : 400, 'back_link' => true ) );
}
function asfar_contact() {
 $language = 'ar' === asfar_input( 'language' ) ? 'ar' : 'en';
 if ( 'POST' !== ( $_SERVER['REQUEST_METHOD'] ?? '' ) || ! wp_verify_nonce( asfar_input( '_asfar_nonce' ), 'asfar_contact' ) || asfar_input( 'website' ) ) { asfar_form_reply( false, 'form_error', $language ); }
 $name = sanitize_text_field( asfar_input( 'name' ) );
 $email_raw = trim( asfar_input( 'email' ) );
 $email = sanitize_email( $email_raw );
 $message = sanitize_textarea_field( asfar_input( 'message' ) );
 $request = asfar_input( 'request_key' );
 if ( ! $name || ( function_exists( 'mb_strlen' ) ? mb_strlen( $name ) : strlen( $name ) ) > 200 || ! is_email( $email ) || $email !== $email_raw || strlen( $email ) > 254 || ! trim( $message ) || strlen( $message ) > 20000 || ! preg_match( '/^[a-f0-9-]{36}$/i', $request ) ) { asfar_form_reply( false, 'form_invalid', $language ); }
 $rate_key = 'asfar_rate_' . hash_hmac( 'sha256', $_SERVER['REMOTE_ADDR'] ?? '', wp_salt( 'nonce' ) );
 $count = (int) get_transient( $rate_key );
 if ( $count >= 5 ) { asfar_form_reply( false, 'form_error', $language ); }
 set_transient( $rate_key, $count + 1, 10 * MINUTE_IN_SECONDS );
 asfar_schema();
 global $wpdb;
 $table = asfar_submissions_table();
 $request_key = hash( 'sha256', wp_json_encode( array( $request, $name, $email, $message ) ) );
 if ( $wpdb->get_var( $wpdb->prepare( "SELECT id FROM {$table} WHERE request_key = %s", $request_key ) ) ) { asfar_form_reply( true, 'form_success', $language ); }
 $saved = $wpdb->insert( $table, array( 'created_at' => current_time( 'mysql', true ), 'language' => $language, 'name' => $name, 'email' => $email, 'message' => $message, 'request_key' => $request_key, 'mail_status' => 'pending' ), array( '%s', '%s', '%s', '%s', '%s', '%s', '%s' ) );
 if ( false === $saved ) { asfar_form_reply( false, 'form_error', $language ); }
 $id = $wpdb->insert_id;
 $recipient = sanitize_email( asfar_option( 'notification_email', $language ) );
 if ( ! is_email( $recipient ) ) { $recipient = get_option( 'admin_email' ); }
 try {
  $sent = wp_mail( $recipient, sanitize_text_field( asfar_option( 'form_subject', $language ) ), $name . "\n" . $email . "\n\n" . $message, array( 'Reply-To: ' . $email ) );
 } catch ( Throwable $error ) { $sent = false; }
 $wpdb->update( $table, array( 'mail_status' => $sent ? 'sent' : 'failed' ), array( 'id' => $id ), array( '%s' ), array( '%d' ) );
 asfar_form_reply( true, 'form_success', $language );
}
foreach ( array( 'admin_post_asfar_contact', 'admin_post_nopriv_asfar_contact', 'wp_ajax_asfar_contact', 'wp_ajax_nopriv_asfar_contact' ) as $hook ) { add_action( $hook, 'asfar_contact' ); }
add_action( 'admin_menu', function () { add_menu_page( 'Form Submissions', 'Form Submissions', 'manage_options', 'asfar-submissions', 'asfar_submissions_admin', 'dashicons-email-alt', 26 ); } );
function asfar_submission_filters() {
 global $wpdb;
 $where = '1=1';
 $search = isset( $_GET['s'] ) && is_string( $_GET['s'] ) ? sanitize_text_field( wp_unslash( $_GET['s'] ) ) : '';
 $lang = isset( $_GET['language'] ) && is_string( $_GET['language'] ) ? sanitize_key( $_GET['language'] ) : '';
 $status = isset( $_GET['status'] ) && is_string( $_GET['status'] ) ? sanitize_key( $_GET['status'] ) : '';
 if ( $search ) { $like = '%' . $wpdb->esc_like( $search ) . '%'; $where .= $wpdb->prepare( ' AND (name LIKE %s OR email LIKE %s OR message LIKE %s)', $like, $like, $like ); }
 if ( in_array( $lang, array( 'en', 'ar' ), true ) ) { $where .= $wpdb->prepare( ' AND language = %s', $lang ); }
 if ( in_array( $status, array( 'sent', 'failed', 'pending' ), true ) ) { $where .= $wpdb->prepare( ' AND mail_status = %s', $status ); }
 return array( $where, $search, $lang, $status );
}
function asfar_submissions_admin() {
 if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Forbidden', '', array( 'response' => 403 ) ); }
 global $wpdb;
 $table = asfar_submissions_table();
 echo '<div class="wrap"><h1>Form Submissions</h1>';
 $id = isset( $_GET['submission'] ) ? absint( $_GET['submission'] ) : 0;
 if ( $id ) {
  $row = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$table} WHERE id = %d", $id ), ARRAY_A );
  if ( $row ) { foreach ( array( 'id', 'created_at', 'language', 'name', 'email', 'message', 'mail_status' ) as $key ) { echo '<h3>' . esc_html( $key ) . '</h3><p style="white-space:pre-wrap" dir="auto">' . esc_html( $row[ $key ] ) . '</p>'; } }
  echo '</div>'; return;
 }
 list( $where, $search, $lang, $status ) = asfar_submission_filters();
 $page = max( 1, absint( $_GET['paged'] ?? 1 ) );
 $total = (int) $wpdb->get_var( "SELECT COUNT(*) FROM {$table} WHERE {$where}" );
 $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where} ORDER BY id DESC LIMIT %d OFFSET %d", 20, ( $page - 1 ) * 20 ), ARRAY_A );
 echo '<form method="get"><input type="hidden" name="page" value="asfar-submissions"><input aria-label="Search submissions" type="search" name="s" value="' . esc_attr( $search ) . '"><select name="language" aria-label="Language">';
 foreach ( array( '' => 'All languages', 'en' => 'English', 'ar' => 'Arabic' ) as $value => $label ) { echo '<option value="' . esc_attr( $value ) . '" ' . selected( $lang, $value, false ) . '>' . esc_html( $label ) . '</option>'; }
 echo '</select><select name="status" aria-label="Email status">';
 foreach ( array( '' => 'All email statuses', 'sent' => 'Sent', 'failed' => 'Failed', 'pending' => 'Pending' ) as $value => $label ) { echo '<option value="' . esc_attr( $value ) . '" ' . selected( $status, $value, false ) . '>' . esc_html( $label ) . '</option>'; }
 echo '</select><button class="button">Filter</button></form>';
 $export = wp_nonce_url( add_query_arg( array( 'action' => 'asfar_export', 's' => $search, 'language' => $lang, 'status' => $status ), admin_url( 'admin-post.php' ) ), 'asfar_export' );
 echo '<p><a class="button" href="' . esc_url( $export ) . '">CSV Export</a></p><table class="widefat striped"><thead><tr><th>ID</th><th>Received (UTC)</th><th>Name</th><th>Email</th><th>Language</th><th>Email status</th></tr></thead><tbody>';
 foreach ( $rows as $row ) {
  $url = add_query_arg( array( 'page' => 'asfar-submissions', 'submission' => $row['id'] ), admin_url( 'admin.php' ) );
  echo '<tr><td><a href="' . esc_url( $url ) . '">' . absint( $row['id'] ) . '</a></td>';
  foreach ( array( 'created_at', 'name', 'email', 'language', 'mail_status' ) as $key ) { echo '<td>' . esc_html( $row[ $key ] ) . '</td>'; }
  echo '</tr>';
 }
 echo '</tbody></table>';
 echo wp_kses_post( paginate_links( array( 'base' => add_query_arg( 'paged', '%#%' ), 'format' => '', 'current' => $page, 'total' => max( 1, (int) ceil( $total / 20 ) ) ) ) );
 echo '</div>';
}
function asfar_csv_cell( $value ) {
 $value = (string) $value;
 if ( preg_match( '/^[\s\x00-\x20\x{FEFF}]*[=+@-]/u', $value ) || preg_match( '/^[\t\r\n]/', $value ) ) { return "'" . $value; }
 return $value;
}
add_action( 'admin_post_asfar_export', function () {
 if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Forbidden', '', array( 'response' => 403 ) ); }
 check_admin_referer( 'asfar_export' );
 global $wpdb;
 $table = asfar_submissions_table();
 list( $where ) = asfar_submission_filters();
 nocache_headers();
 header( 'Content-Type: text/csv; charset=utf-8' );
 header( 'Content-Disposition: attachment; filename="asfar-submissions.csv"' );
 $stream = fopen( 'php://output', 'w' );
 fwrite( $stream, "\xEF\xBB\xBF" );
 $columns = array( 'id', 'created_at', 'language', 'name', 'email', 'message', 'mail_status' );
 fputcsv( $stream, $columns, ',', '"', '' );
 $last = 0;
 do {
  $rows = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM {$table} WHERE {$where} AND id > %d ORDER BY id ASC LIMIT 500", $last ), ARRAY_A );
  foreach ( $rows as $row ) { $cells = array(); foreach ( $columns as $column ) { $cells[] = asfar_csv_cell( $row[ $column ] ); } fputcsv( $stream, $cells, ',', '"', '' ); $last = (int) $row['id']; }
 } while ( count( $rows ) === 500 );
 fclose( $stream ); exit;
} );
