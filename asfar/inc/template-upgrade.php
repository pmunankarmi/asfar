<?php
/** Preserve native page-template assignments after moving selectable templates. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function asfar_upgrade_template_paths() {
	if ( (int) get_option( 'asfar_template_folder_version' ) >= 2 ) { return; }
	foreach ( array( 'home', 'news', 'team', 'faq' ) as $name ) {
		$old = 'template-' . $name . '.php';
		$pages = get_posts( array( 'post_type' => 'page', 'lang' => '', 'post_status' => 'any', 'posts_per_page' => -1, 'fields' => 'ids', 'meta_key' => '_wp_page_template', 'meta_value' => $old, 'suppress_filters' => true ) );
		foreach ( $pages as $id ) { update_post_meta( $id, '_wp_page_template', 'templates/' . $old ); }
	}
	update_option( 'asfar_template_folder_version', 2, false );
}
add_action( 'admin_init', function () {
	if ( current_user_can( 'manage_options' ) ) { asfar_upgrade_template_paths(); }
}, 15 );
