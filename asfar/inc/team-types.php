<?php
/** Team Type ordering shared by the taxonomy screen and homepage tabs. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function asfar_sort_team_types( $terms ) {
	usort( $terms, function ( $first, $second ) {
		$first_order = (int) get_term_meta( $first->term_id, 'display_order', true );
		$second_order = (int) get_term_meta( $second->term_id, 'display_order', true );
		// A stable tie-breaker keeps equally ranked terms in the same order everywhere.
		return ( $first_order <=> $second_order ) ?: ( $first->term_id <=> $second->term_id );
	} );
	return $terms;
}

// Sort before WordPress paginates the taxonomy screen. Terms without metadata
// count as zero, matching the field default and the homepage comparator.
add_filter( 'terms_clauses', function ( $clauses, $taxonomies ) {
	if ( ! is_admin() || array( 'team_department' ) !== $taxonomies || isset( $_GET['orderby'] ) ) {
		return $clauses;
	}
	global $wpdb;
	$clauses['orderby'] = "ORDER BY COALESCE((SELECT CAST(meta_value AS SIGNED) FROM {$wpdb->termmeta} WHERE term_id = t.term_id AND meta_key = 'display_order' LIMIT 1), 0) ASC, t.term_id ASC";
	$clauses['order'] = '';
	return $clauses;
}, 10, 2 );

add_filter( 'manage_edit-team_department_columns', function ( $columns ) {
	$columns['asfar_menu_order'] = 'Menu Order';
	$columns['asfar_homepage'] = 'Homepage';
	return $columns;
} );

add_filter( 'manage_team_department_custom_column', function ( $content, $column, $term_id ) {
	if ( 'asfar_menu_order' === $column ) {
		return (string) (int) get_term_meta( $term_id, 'display_order', true );
	}
	if ( 'asfar_homepage' === $column ) {
		return '0' === get_term_meta( $term_id, 'show_on_homepage', true ) ? 'Hidden' : 'Shown';
	}
	return $content;
}, 10, 3 );
