<?php
/** Native, translatable content types. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function asfar_register_content_types() {
	register_post_type( 'asfar_team', array(
		'labels' => array( 'name' => 'Team', 'singular_name' => 'Team Member', 'add_new_item' => 'Add Team Member', 'edit_item' => 'Edit Team Member' ),
		'public' => true,
		'show_in_rest' => true,
		'menu_icon' => 'dashicons-businessperson',
		'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
		'rewrite' => array( 'slug' => 'team-member' ),
		'has_archive' => 'team-members',
	) );
	register_taxonomy( 'team_department', array( 'asfar_team' ), array(
		'labels' => array( 'name' => 'Team Types', 'singular_name' => 'Team Type', 'add_new_item' => 'Add Team Type', 'edit_item' => 'Edit Team Type' ),
		'public' => true,
		'hierarchical' => true,
		'show_admin_column' => true,
		'show_in_rest' => true,
		'rewrite' => array( 'slug' => 'team-department' ),
	) );
	register_post_type( 'asfar_project', array(
		'labels' => array( 'name' => 'Projects', 'singular_name' => 'Project', 'add_new_item' => 'Add Project', 'edit_item' => 'Edit Project' ),
		'public' => true,
		'show_in_rest' => true,
		'menu_icon' => 'dashicons-location-alt',
		'supports' => array( 'title', 'thumbnail', 'page-attributes' ),
		'rewrite' => array( 'slug' => 'project' ),
		'has_archive' => 'projects',
	) );
}
add_action( 'init', 'asfar_register_content_types' );

add_filter( 'pll_get_post_types', function ( $types ) {
	$types['asfar_team'] = 'asfar_team';
	$types['asfar_project'] = 'asfar_project';
	return $types;
} );
add_filter( 'pll_get_taxonomies', function ( $taxonomies ) {
	$taxonomies['team_department'] = 'team_department';
	return $taxonomies;
} );
