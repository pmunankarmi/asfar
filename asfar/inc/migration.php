<?php
/** One-time migration from numbered fields to native posts and named ACF sections. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function asfar_legacy_option( $scope, $name, $default ) {
	$value = get_option( $scope . '_' . $name, null );
	if ( null === $value ) {
		return asfar_import_value( $default );
	}
	// ACF Pro stores repeaters as a row count, with each child in a separate option.
	if ( is_array( $default ) && array_is_list( $default ) && is_numeric( $value ) ) {
		$rows = array();
		for ( $i = 0; $i < (int) $value; $i++ ) {
			$row = array();
			foreach ( $default[0] ?? array() as $key => $sample ) {
				$row[ $key ] = asfar_legacy_option( $scope, $name . '_' . $i . '_' . $key, is_array( $sample ) && ! isset( $sample['asset'] ) ? $sample : '' );
			}
			$rows[] = $row;
		}
		return $rows;
	}
	return $value;
}

function asfar_legacy_value( $descriptor, $post_id, $defaults ) {
	if ( is_string( $descriptor ) && preg_match( '/^c_\d+$/', $descriptor ) ) {
		return metadata_exists( 'post', $post_id, $descriptor ) ? get_post_meta( $post_id, $descriptor, true ) : asfar_import_value( $defaults[ $descriptor ] ?? '' );
	}
	if ( is_array( $descriptor ) ) {
		if ( isset( $descriptor['join'] ) ) {
			return implode( $descriptor['separator'] ?? "\n", array_map( function ( $part ) use ( $post_id, $defaults ) { return asfar_legacy_value( $part, $post_id, $defaults ); }, $descriptor['join'] ) );
		}
		if ( isset( $descriptor['link'] ) ) {
			$url = asfar_legacy_value( $descriptor['link'][0], $post_id, $defaults );
			return array( 'url' => is_array( $url ) ? $url['url'] : $url, 'title' => asfar_legacy_value( $descriptor['link'][1], $post_id, $defaults ), 'target' => is_array( $url ) ? ( $url['target'] ?? '' ) : '' );
		}
		foreach ( $descriptor as &$value ) {
			$value = asfar_legacy_value( $value, $post_id, $defaults );
		}
	}
	return $descriptor;
}

function asfar_migrate_field( $group, $name, $value, $context ) {
	$key = 'field_asfar_' . $group . '_' . $name;
	$exists = is_numeric( $context ) ? metadata_exists( 'post', $context, $name ) : null !== get_option( $context . '_' . $name, null );
	if ( ! $exists ) {
		update_field( $key, $value, $context );
	} elseif ( is_numeric( $context ) ) {
		update_post_meta( $context, '_' . $name, $key );
	} else {
		update_option( '_' . $context . '_' . $name, $key, false );
	}
}

function asfar_migration_post( $key, $language, $type, $title, $order = 0 ) {
	$map = get_option( 'asfar_content_map', array() );
	$identity = $key . '_' . $language;
	if ( ! empty( $map[ $identity ] ) && get_post( $map[ $identity ] ) ) {
		return $map[ $identity ];
	}
	$id = wp_insert_post( array( 'post_type' => $type, 'post_title' => $title, 'post_status' => 'publish', 'menu_order' => $order ), true );
	if ( is_wp_error( $id ) ) {
		throw new RuntimeException( $id->get_error_message() );
	}
	pll_set_post_language( $id, $language );
	$map[ $identity ] = $id;
	update_option( 'asfar_content_map', $map, false );
	return $id;
}

function asfar_migrate_strings( $pairs ) {
	if ( ! class_exists( 'PLL_MO' ) || ! function_exists( 'PLL' ) ) {
		throw new RuntimeException( 'This Polylang version cannot import existing string translations.' );
	}
	$language = PLL()->model->get_language( 'ar' );
	$mo = new PLL_MO();
	$mo->import_from_db( $language );
	foreach ( $pairs as $english => $arabic ) {
		if ( '' !== (string) $english && ! isset( $mo->entries[ $english ] ) ) {
			$mo->add_entry( array( 'singular' => (string) $english, 'translations' => array( (string) $arabic ) ) );
		}
	}
	$mo->export_to_db( $language );
}

function asfar_migrate_menus( $options ) {
	$mods = get_option( 'theme_mods_' . get_stylesheet(), array() );
	$locations = $mods['nav_menu_locations'] ?? array();
	$translated_menus = PLL()->options['nav_menus'] ?? array();
	foreach ( $translated_menus[ get_stylesheet() ] ?? array() as $location => $languages ) {
		foreach ( $languages as $language => $menu ) {
			$locations[ $location . ( pll_default_language() === $language ? '' : '___' . $language ) ] = $menu;
		}
	}
	foreach ( array( 'en', 'ar' ) as $language ) {
		$start = 'en' === $language ? array( 'primary' => 280, 'drawer' => 295 ) : array( 'primary' => 8, 'drawer' => 23 );
		foreach ( $start as $location => $first ) {
			$slot = $location . ( pll_default_language() === $language ? '' : '___' . $language );
			if ( ! empty( $locations[ $slot ] ) ) {
				continue;
			}
			$name = 'ASFAR ' . ucfirst( $location ) . ' ' . strtoupper( $language );
			$existing = wp_get_nav_menu_object( $name );
			$menu = $existing ? $existing->term_id : wp_create_nav_menu( $name );
			if ( is_wp_error( $menu ) ) {
				throw new RuntimeException( $menu->get_error_message() );
			}
			if ( ! wp_get_nav_menu_items( $menu ) ) {
				for ( $i = 0; $i < ( 'primary' === $location ? 5 : 8 ); $i++ ) {
					$link = $options[ $language ][ 'c_' . ( $first + 2 * $i ) ];
					$url = is_array( $link ) ? $link['url'] : $link;
					$parts = explode( '#', $url, 2 );
					$map = get_option( 'asfar_page_map', array() );
					$slug = preg_replace( '/\.html$/', '', $parts[0] ) ?: ( 'ar' === $language ? 'index-ar' : 'index' );
					if ( isset( $map[ $slug ] ) ) {
						$id = pll_get_post( $map[ $slug ], $language ) ?: $map[ $slug ];
						$url = get_permalink( $id ) . ( isset( $parts[1] ) ? '#' . $parts[1] : '' );
					}
					$result = wp_update_nav_menu_item( $menu, 0, array( 'menu-item-title' => $options[ $language ][ 'c_' . ( $first + 2 * $i + 1 ) ], 'menu-item-url' => $url, 'menu-item-status' => 'publish', 'menu-item-type' => 'custom' ) );
					if ( is_wp_error( $result ) ) {
						throw new RuntimeException( $result->get_error_message() );
					}
				}
			}
			$locations[ $slot ] = $menu;
		}
	}
	// Polylang stores language-to-menu assignments separately from WordPress theme mods.
	foreach ( $locations as $slot => $menu ) {
		$parts = explode( '___', $slot, 2 );
		$translated_menus[ get_stylesheet() ][ $parts[0] ][ $parts[1] ?? pll_default_language() ] = (int) $menu;
	}
	PLL()->options['nav_menus'] = $translated_menus;
	if ( is_object( PLL()->options ) && method_exists( PLL()->options, 'save' ) ) {
		PLL()->options->save();
	} else {
		update_option( 'polylang', PLL()->options );
	}
	set_theme_mod( 'nav_menu_locations', $locations );
}

/** Atomic option insertion prevents overlapping admin requests importing twice. */
function asfar_with_content_lock( $callback ) {
	static $owned = false;
	if ( $owned ) { return $callback(); }
	$started = (int) get_option( 'asfar_content_lock', 0 );
	if ( $started && $started < time() - 1800 ) { delete_option( 'asfar_content_lock' ); }
	if ( ! add_option( 'asfar_content_lock', time(), '', false ) ) {
		throw new RuntimeException( 'Another ASFAR content operation is running. Please wait for it to finish.' );
	}
	$owned = true;
	try { return $callback(); }
	finally { $owned = false; delete_option( 'asfar_content_lock' ); }
}

function asfar_migrate() {
	if ( 2 <= (int) get_option( 'asfar_content_schema_version' ) ) { return; }
	return asfar_with_content_lock( 'asfar_run_migration' );
}

function asfar_run_migration() {
	if ( 2 <= (int) get_option( 'asfar_content_schema_version' ) ) {
		return;
	}
	if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'pll_save_term_translations' ) ) {
		throw new RuntimeException( 'Activate ACF Pro and Polylang before migrating content.' );
	}
	foreach ( array( 'en', 'ar' ) as $language ) {
		if ( ! in_array( $language, pll_languages_list(), true ) ) {
			throw new RuntimeException( 'Add English and Arabic in Polylang before migrating content.' );
		}
	}
	$data = json_decode( file_get_contents( __DIR__ . '/seed.json' ), true );
	$descriptors = json_decode( file_get_contents( __DIR__ . '/migration-map.json' ), true );
	$pages = get_option( 'asfar_page_map', array() );
	$options = array();
	foreach ( array( 'en', 'ar' ) as $language ) {
		foreach ( $data['options'][ 'global_' . $language ] as $name => $default ) {
			$options[ $language ][ $name ] = asfar_legacy_option( 'global_' . $language, $name, $default );
		}
		$options[ $language ]['social_links'] = asfar_legacy_option( 'global_' . $language, 'social_links', array( array( 'social_label' => '', 'social_url' => '' ) ) );
		if ( null === get_option( 'global_' . $language . '_social_links', null ) ) { $options[ $language ]['social_links'] = array(); }
	}
	$terms = get_option( 'asfar_department_map', array() );
	$projects = array();
	foreach ( array( 'en', 'ar' ) as $language ) {
		$home = 'ar' === $language ? 'index-ar' : 'index';
		if ( empty( $pages[ $home ] ) ) {
			throw new RuntimeException( 'Populate the initial website content in ASFAR Setup first.' );
		}
		$home_id = $pages[ $home ];
		$defaults = $data['pages'][ $home ]['values'];
		$opt = $options[ $language ];
		$groups = 'en' === $language ? array( 'board' => 511, 'leadership' => 512, 'committees' => 513 ) : array( 'board' => 235, 'leadership' => 236, 'committees' => 237 );
		foreach ( $groups as $group => $label ) {
			$key = $group . '_' . $language;
			if ( empty( $terms[ $key ] ) || ! term_exists( $terms[ $key ], 'team_department' ) ) {
				$term = wp_insert_term( asfar_legacy_value( 'c_' . $label, $home_id, $defaults ), 'team_department', array( 'slug' => $key ) );
				if ( is_wp_error( $term ) ) {
					if ( 'term_exists' !== $term->get_error_code() ) { throw new RuntimeException( $term->get_error_message() ); }
					$term = array( 'term_id' => $term->get_error_data() );
				}
				$terms[ $key ] = (int) $term['term_id'];
				pll_set_term_language( $terms[ $key ], $language );
				update_term_meta( $terms[ $key ], 'display_order', array_search( $group, array_keys( $groups ), true ) );
				update_option( 'asfar_department_map', $terms, false );
			}
		}
		foreach ( $opt['team'] as $index => $member ) {
			$id = asfar_migration_post( 'team_' . $index, $language, 'asfar_team', $member['name'], $index );
			asfar_migrate_field( 'team', 'job_title', $member['role'], $id );
			asfar_migrate_field( 'team', 'biography', $member['bio'], $id );
			if ( ! get_post_meta( $id, '_asfar_member_migrated', true ) ) {
				set_post_thumbnail( $id, $member['photo'] );
				wp_set_object_terms( $id, $terms[ $member['group'] . '_' . $language ], 'team_department' );
				update_post_meta( $id, '_asfar_member_migrated', 1 );
			}
		}
		foreach ( $opt['portfolio'] as $index => $project ) {
			$id = asfar_migration_post( 'project_' . $index, $language, 'asfar_project', $project['name'], $index );
			$projects[ $language ][] = $id;
			foreach ( array( 'company' => 'company', 'headline' => 'title', 'summary' => 'body', 'map_label' => 'label', 'map_region' => 'hot', 'map_x' => 'mark_x', 'map_y' => 'mark_y', 'statistics' => 'stats', 'detail_image' => 'img' ) as $field => $old ) {
				asfar_migrate_field( 'project', $field, $project[ $old ] ?? '', $id );
			}
			if ( ! get_post_meta( $id, '_asfar_project_migrated', true ) ) {
				set_post_thumbnail( $id, $project['bg'] );
				update_post_meta( $id, '_asfar_project_migrated', 1 );
			}
		}
		$faq = asfar_migration_post( 'faq', $language, 'page', 'ar' === $language ? 'الأسئلة الشائعة' : 'Frequently Asked Questions' );
		if ( ! get_post_meta( $faq, '_wp_page_template', true ) ) { update_post_meta( $faq, '_wp_page_template', 'templates/template-faq.php' ); }
		asfar_migrate_field( 'faq', 'faq_items', $opt['faq'], $faq );
		$sections = asfar_legacy_value( $descriptors['home'][ $language ], $home_id, $defaults );
		$sections['banner_animation']['stops'] = array_map( function ( $mark ) { return array( 'name' => $mark['name'], 'button' => array( 'url' => $mark['href'], 'title' => $mark['cta'], 'target' => '' ) ); }, $opt['hero_marks'] );
		$sections['portfolio_section'] = array( 'heading' => $opt['portfolio_title'], 'projects' => $projects[ $language ] ?? array() );
		$sections['investments_section']['sectors'] = $opt['sectors'];
		$sections['team_section']['enabled'] = $opt['show_team'];
		$sections['faq_section']['page'] = $faq;
		foreach ( $sections as $name => $value ) {
			$group = str_starts_with( $name, 'banner_' ) ? 'banner' : str_replace( '_section', '', $name );
			asfar_migrate_field( 'home_' . $group, $name, $value, $home_id );
		}
	}
	foreach ( array( 'board', 'leadership', 'committees' ) as $group ) {
		pll_save_term_translations( array( 'en' => $terms[ $group . '_en' ], 'ar' => $terms[ $group . '_ar' ] ) );
	}
	$map = get_option( 'asfar_content_map', array() );
	foreach ( $map as $key => $id ) {
		if ( str_ends_with( $key, '_en' ) && isset( $map[ substr( $key, 0, -3 ) . '_ar' ] ) && ! pll_get_post( $id, 'ar' ) ) {
			pll_save_post_translations( array( 'en' => $id, 'ar' => $map[ substr( $key, 0, -3 ) . '_ar' ] ) );
		}
	}
	foreach ( $descriptors['pages'] as $slug => $description ) {
		if ( empty( $pages[ $slug ] ) ) { continue; }
		$id = $pages[ $slug ];
		$values = asfar_legacy_value( $description, $id, $data['pages'][ $slug ]['values'] );
		if ( ! get_post_meta( $id, '_asfar_page_migrated', true ) ) {
			if ( get_the_title( $id ) === $data['pages'][ $slug ]['title'] && $values['title'] ) { wp_update_post( array( 'ID' => $id, 'post_title' => $values['title'] ) ); }
			if ( ! empty( $values['image'] ) && ! has_post_thumbnail( $id ) ) { set_post_thumbnail( $id, $values['image'] ); }
			if ( 'page' === get_post_type( $id ) ) { update_post_meta( $id, '_wp_page_template', str_starts_with( $slug, 'team' ) ? 'templates/template-team.php' : 'templates/template-news.php' ); }
			update_post_meta( $id, '_asfar_page_migrated', 1 );
		}
		foreach ( $values as $name => $value ) {
			if ( in_array( $name, array( 'title', 'image' ), true ) ) { continue; }
			asfar_migrate_field( 'post' === get_post_type( $id ) ? 'article' : 'page', $name, $value, $id );
		}
	}
	// External news items have no legacy page template, but still need ACF references.
	foreach ( $data['news'] as $article ) {
		foreach ( array( 'en', 'ar' ) as $language ) {
			$slug = 'news-' . $article['slug'] . ( 'ar' === $language ? '-ar' : '' );
			if ( empty( $pages[ $slug ] ) ) { continue; }
			$id = $pages[ $slug ];
			asfar_migrate_field( 'article', 'display_date', $article[ $language ]['date'], $id );
			asfar_migrate_field( 'article', 'external_url', get_post_meta( $id, 'external_url', true ), $id );
			asfar_migrate_field( 'article', 'paragraphs', array_map( function ( $text ) { return array( 'text' => $text ); }, $article[ $language ]['paras'] ), $id );
		}
	}
	$labels = array( 'site_name' => array( 273, 1 ), 'home_label' => array( 275, 3 ), 'primary_label' => array( 279, 7 ), 'open_menu' => array( 291, 19 ), 'close_menu' => array( 292, 20 ), 'menu_label' => array( 293, 21 ), 'contact_button' => array( 314, 42 ), 'name_label' => array( 317, 45 ), 'name_placeholder' => array( 318, 46 ), 'email_label' => array( 319, 47 ), 'email_placeholder' => array( 320, 48 ), 'message_label' => array( 321, 49 ), 'message_placeholder' => array( 322, 50 ), 'send_label' => array( 323, 51 ), 'ownership_label' => array( 325, 53 ), 'copyright' => array( 326, 54 ), 'back_top' => array( 328, 56 ) );
	$pairs = array();
	foreach ( asfar_ui_labels() as $name => $label ) {
		if ( isset( $labels[ $name ] ) ) {
			$en = $options['en'][ 'c_' . $labels[ $name ][0] ];
			$ar = $options['ar'][ 'c_' . $labels[ $name ][1] ];
		} elseif ( isset( $options['en'][ $name ] ) ) {
			$en = $options['en'][ $name ];
			$ar = $options['ar'][ $name ];
		} else {
			$extra = array( 'previous_label' => array( 'Previous', 'السابق' ), 'next_label' => array( 'Next', 'التالي' ), 'banner_label' => array( 'Homepage banners', 'شرائح الصفحة الرئيسية' ) );
			list( $en, $ar ) = $extra[ $name ];
		}
		asfar_migrate_field( 'settings', $name, $en, 'asfar_shared' );
		$pairs[ $en ] = $ar;
	}
	foreach ( array( 'footer_logo', 'contact_email' ) as $name ) { asfar_migrate_field( 'settings', $name, $options['en'][ $name ], 'asfar_shared' ); }
	asfar_migrate_field( 'settings', 'ownership_logo', $options['en']['c_324'], 'asfar_shared' );
	foreach ( array( 'site_logo', 'dark_logo' ) as $name ) { asfar_migrate_field( 'settings', $name, get_option( 'asfar_shared_' . $name, get_theme_mod( 'custom_logo' ) ), 'asfar_shared' ); }
	asfar_migrate_field( 'settings', 'notification_recipients', array( array( 'language' => 'en', 'email' => $options['en']['notification_email'] ), array( 'language' => 'ar', 'email' => $options['ar']['notification_email'] ) ), 'asfar_shared' );
	$socials = array();
	foreach ( $options['en']['social_links'] as $i => $social ) {
		$socials[] = array( 'label' => $social['social_label'], 'url' => $social['social_url'] );
		$pairs[ $social['social_label'] ] = $options['ar']['social_links'][ $i ]['social_label'] ?? $social['social_label'];
	}
	asfar_migrate_field( 'settings', 'social_links', $socials, 'asfar_shared' );
	$partners = array();
	foreach ( $options['en']['partners'] as $i => $partner ) {
		$partners[] = array( 'name' => $partner['name'], 'logo' => $partner['image'], 'website' => $partner['url'] );
		$pairs[ $partner['name'] ] = $options['ar']['partners'][ $i ]['name'] ?? $partner['name'];
	}
	asfar_migrate_field( 'partners', 'partner_logos', $partners, 'asfar_partners' );
	asfar_migrate_strings( $pairs );
	asfar_migrate_menus( $options );
	asfar_register_translations();
	flush_rewrite_rules( false );
	update_option( 'asfar_content_schema_version', 2, false );
}

add_action( 'admin_init', function () {
	if ( current_user_can( 'manage_options' ) && get_option( 'asfar_seed_complete' ) && (int) get_option( 'asfar_content_schema_version' ) < 2 ) {
		try {
			asfar_migrate();
		} catch ( Throwable $error ) {
			add_action( 'admin_notices', function () use ( $error ) {
				echo '<div class="notice notice-error"><p>ASFAR content migration: ' . esc_html( $error->getMessage() ) . '</p></div>';
			} );
		}
	}
}, 20 );
if ( defined( 'WP_CLI' ) && WP_CLI ) {
	WP_CLI::add_command( 'asfar migrate', function () { asfar_migrate(); WP_CLI::success( 'ASFAR content migration complete.' ); } );
}
