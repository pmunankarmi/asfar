<?php
/** Shared data access. Templates contain markup; WordPress contains content. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function asfar_language() {
	$language = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
	if ( ! $language && is_singular() && function_exists( 'pll_get_post_language' ) ) {
		$language = pll_get_post_language( get_queried_object_id(), 'slug' );
	}
	return $language ?: 'en';
}

function asfar_value( $name, $context = null ) {
	$context = $context ?? get_the_ID();
	if ( function_exists( 'get_field' ) ) {
		$value = get_field( $name, $context );
	} else {
		$value = is_numeric( $context ) ? get_post_meta( $context, $name, true ) : get_option( $context . '_' . $name, '' );
	}
	return null === $value || false === $value ? '' : $value;
}

function asfar_rows( $name, $context = null ) {
	$rows = asfar_value( $name, $context );
	return is_array( $rows ) ? $rows : array();
}

function asfar_section( $name ) {
	return asfar_rows( $name . '_section' );
}

function asfar_option( $name, $language = null ) {
	$language = $language ?? asfar_language();
	if ( 'notification_email' === $name ) {
		foreach ( asfar_rows( 'notification_recipients', 'asfar_shared' ) as $recipient ) {
			if ( $language === $recipient['language'] ) {
				return $recipient['email'];
			}
		}
		return '';
	}
	$value = asfar_value( $name, 'asfar_shared' );
	if ( isset( asfar_ui_labels()[ $name ] ) ) {
		return asfar_translate( (string) $value, $language );
	}
	return $value;
}

function asfar_translate( $text, $language = null ) {
	if ( function_exists( 'pll_translate_string' ) && '' !== $text ) {
		return pll_translate_string( $text, $language ?? asfar_language() );
	}
	return $text;
}

function asfar_lines( $text ) {
	return nl2br( esc_html( (string) $text ) );
}

/** Preserve the source title's line breaks and emphasis, including older imports. */
function asfar_portfolio_heading( $text ) {
	$source_titles = array(
		'ASFAR Investment Portfolio' => "ASFAR\nInvestment\nPortfolio",
		'محفظة أسفار الاستثمارية' => "محفظة أسفار\nالاستثمارية",
	);
	$text = $source_titles[ $text ] ?? (string) $text;
	$lines = preg_split( '/\R/u', $text );
	$first = array_shift( $lines );
	return '<span class="mt-dk-map__title-a">' . esc_html( $first ) . '</span>'
		. ( $lines ? '<br>' . asfar_lines( implode( "\n", $lines ) ) : '' );
}

/** Restore square-metre notation flattened by the original content import. */
function asfar_statistic_value( $text ) {
	return preg_replace( '/(?<![\p{L}\p{N}])([mMم])(?:2|²)(?![\p{L}\p{N}])/u', '$1<sup>2</sup>', esc_html( (string) $text ) );
}

function asfar_attachment_url( $id ) {
	return wp_get_attachment_url( absint( $id ) ) ?: '';
}

function asfar_logo( $dark = false ) {
	$id = absint( get_theme_mod( 'custom_logo' ) );
	if ( ! $id ) {
		return '';
	}
	return asfar_attachment_url( $dark ? ( asfar_value( 'dark_logo', 'asfar_shared' ) ?: $id ) : $id );
}

function asfar_home_url() {
	return function_exists( 'pll_home_url' ) ? pll_home_url( asfar_language() ) : home_url( '/' );
}

function asfar_translated_post( $id ) {
	$id = absint( $id );
	if ( $id && function_exists( 'pll_get_post' ) ) {
		$id = pll_get_post( $id, asfar_language() ) ?: $id;
	}
	return 'publish' === get_post_status( $id ) ? $id : 0;
}

function asfar_resolve_url( $url ) {
	if ( ! is_string( $url ) ) {
		return '';
	}
	if ( str_starts_with( $url, '#' ) ) {
		return asfar_home_url() . asfar_anchor_url( $url );
	}
	$url = asfar_anchor_url( $url );
	$parts = explode( '#', $url, 2 );
	$slug = preg_replace( '/\.html$/', '', $parts[0] );
	$map = get_option( 'asfar_page_map', array() );
	if ( isset( $map[ $slug ] ) ) {
		$id = asfar_translated_post( $map[ $slug ] );
		return $id ? get_permalink( $id ) . ( isset( $parts[1] ) ? '#' . $parts[1] : '' ) : asfar_home_url();
	}
	return esc_url_raw( $url );
}

function asfar_button( $button, $class = 'mt-dk-pill' ) {
	if ( ! is_array( $button ) || empty( $button['url'] ) || empty( $button['title'] ) ) {
		return;
	}
	get_template_part( 'template-parts/button', null, array( 'button' => $button, 'class' => $class ) );
}

function asfar_languages() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return;
	}
	$languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0, 'hide_if_no_translation' => 0 ) );
	get_template_part( 'template-parts/languages', null, array( 'languages' => $languages ?: array() ) );
}

function asfar_menu( $location ) {
	wp_nav_menu( array(
		'theme_location' => $location,
		'container' => false,
		'menu_class' => 'mt-asfar-menu-list',
		'fallback_cb' => false,
		'depth' => 1,
	) );
}

function asfar_team_departments() {
	$terms = get_terms( array( 'taxonomy' => 'team_department', 'hide_empty' => true, 'lang' => asfar_language() ) );
	if ( is_wp_error( $terms ) ) {
		return array();
	}
	$terms = array_filter( $terms, function ( $term ) {
		// Existing terms remain visible until an editor turns the switch off.
		return '0' !== get_term_meta( $term->term_id, 'show_on_homepage', true );
	} );
	$terms = asfar_sort_team_types( array_values( $terms ) );
	return $terms;
}

function asfar_team_members( $term_id ) {
	return get_posts( array(
		'suppress_filters' => false, 'post_type' => 'asfar_team', 'posts_per_page' => -1, 'lang' => asfar_language(),
		'orderby' => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
		'tax_query' => array( array( 'taxonomy' => 'team_department', 'field' => 'term_id', 'terms' => $term_id ) ),
	) );
}

function asfar_project_rows( $ids = null ) {
	if ( array() === $ids ) { return array(); }
	$query = array( 'suppress_filters' => false, 'post_type' => 'asfar_project', 'posts_per_page' => -1, 'lang' => asfar_language(), 'orderby' => 'menu_order', 'order' => 'ASC' );
	if ( $ids ) {
		$query['post__in'] = array_values( array_filter( array_map( 'asfar_translated_post', $ids ) ) );
		if ( ! $query['post__in'] ) {
			return array();
		}
		$query['orderby'] = 'post__in';
	}
	$rows = array();
	foreach ( get_posts( $query ) as $project ) {
		$map_background = asfar_value( 'map_background', $project->ID );
		$region = asfar_value( 'map_region', $project->ID );
		$background_url = $map_background ? asfar_attachment_url( $map_background ) : '';
		if ( ! $background_url ) {
			// The AMV4 reference shows the rose landscape for Strategic Investments.
			$background_url = 'all' === $region ? asfar_original_image_url( 'img/taif-roses.jpg' ) : asfar_attachment_url( get_post_thumbnail_id( $project ) );
		}
		$rows[] = array(
			'id' => $project->ID,
			'name' => get_the_title( $project ),
			'bg' => get_post_thumbnail_id( $project ),
			'background_url' => $background_url,
			'hot' => $region,
			'label' => asfar_value( 'map_label', $project->ID ),
			'mark_x' => asfar_value( 'map_x', $project->ID ),
			'mark_y' => asfar_value( 'map_y', $project->ID ),
			'stats' => asfar_rows( 'statistics', $project->ID ),
		);
	}
	return $rows;
}

function asfar_faq_items( $page_id ) {
	$page_id = asfar_translated_post( $page_id );
	return $page_id ? asfar_rows( 'faq_items', $page_id ) : array();
}

function asfar_news_query( $home = false ) {
	$section = asfar_section( 'news' );
	$query = array(
		'post_type'           => 'post',
		'post_status'         => 'publish',
		'posts_per_page'      => $home ? max( 1, (int) ( $section['count'] ?? 20 ) ) : 20,
		'paged'               => $home ? 1 : max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) ),
		'lang'                => asfar_language(),
		'ignore_sticky_posts' => true,
		'orderby'             => array( 'date' => 'DESC', 'ID' => 'ASC' ),
	);

	// Homepage news is opt-in. The full news archive keeps every published post.
	if ( $home ) {
		$query['meta_key'] = 'show_on_homepage';
		$query['meta_value'] = '1';
	}

	return new WP_Query( $query );
}

/** Current year follows the timezone configured in WordPress Settings. */
add_shortcode( 'year', function () {
	return wp_date( 'Y' );
} );

function asfar_copyright() {
	$text = asfar_option( 'copyright' );
	// Keep previously saved copyright lines current until the editor adds [year].
	if ( ! has_shortcode( $text, 'year' ) ) {
		$text = preg_replace( '/(?<![0-9])20[0-9]{2}(?![0-9])/', '[year]', $text, 1 );
	}
	return do_shortcode( $text );
}
