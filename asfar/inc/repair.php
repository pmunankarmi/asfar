<?php
/** Administrator-only reconciliation of imported content. No automatic cleanup. */
if ( ! defined( 'ABSPATH' ) ) { exit; }

function asfar_content_duplicates() {
	$canonical = array_merge( get_option( 'asfar_page_map', array() ), get_option( 'asfar_content_map', array() ) );
	$ids = array_values( array_unique( array_map( 'absint', $canonical ) ) );
	$identities = array();
	foreach ( $ids as $id ) {
		if ( get_post( $id ) && 'trash' !== get_post_status( $id ) ) {
			$key = get_post_type( $id ) . ':' . pll_get_post_language( $id ) . ':' . get_the_title( $id );
			$identities[ $key ] = $id;
		}
	}
	$duplicates = array();
	$posts = get_posts( array( 'post_type' => array( 'asfar_team', 'asfar_project', 'page', 'post' ), 'post_status' => array( 'publish', 'draft', 'pending', 'private' ), 'posts_per_page' => -1, 'suppress_filters' => true ) );
	foreach ( $posts as $post ) {
		$key = $post->post_type . ':' . pll_get_post_language( $post->ID ) . ':' . get_the_title( $post );
		if ( ! in_array( $post->ID, $ids, true ) && isset( $identities[ $key ] ) ) {
			$duplicates[ $post->ID ] = $identities[ $key ];
		}
	}
	return $duplicates;
}

function asfar_menu_duplicates() {
	$result = array();
	foreach ( wp_get_nav_menus() as $menu ) {
		if ( ! str_starts_with( $menu->name, 'ASFAR ' ) || str_starts_with( $menu->name, 'ASFAR Menu Backup' ) ) { continue; }
		$seen = array();
		foreach ( wp_get_nav_menu_items( $menu->term_id ) ?: array() as $item ) {
			$key = $item->title . ':' . $item->url . ':' . $item->menu_item_parent;
			if ( isset( $seen[ $key ] ) ) { $result[ $item->ID ] = $menu->term_id; }
			else { $seen[ $key ] = $item->ID; }
		}
	}
	return $result;
}

function asfar_fill_missing_value( $current, $source ) {
	if ( '' === $current || null === $current || false === $current || array() === $current ) { return $source; }
	if ( is_array( $current ) && is_array( $source ) && ! array_is_list( $source ) ) {
		foreach ( $source as $key => $value ) { $current[ $key ] = asfar_fill_missing_value( $current[ $key ] ?? null, $value ); }
	}
	return $current;
}

function asfar_repair_field( $group, $name, $source, $id ) {
	$current = asfar_value( $name, $id );
	$value = asfar_fill_missing_value( $current, $source );
	if ( $current !== $value ) { update_field( 'field_asfar_' . $group . '_' . $name, $value, $id ); }
}

function asfar_repair_imported_content() {
	return asfar_with_content_lock( function () {
		if ( ! function_exists( 'pll_save_post_translations' ) || ! function_exists( 'update_field' ) ) { throw new RuntimeException( 'Activate ACF Pro and Polylang first.' ); }
		$data = json_decode( file_get_contents( __DIR__ . '/seed.json' ), true );
		$map = get_option( 'asfar_content_map', array() );
		$pages = get_option( 'asfar_page_map', array() );
		$terms = get_option( 'asfar_department_map', array() );
		if ( empty( $map ) || empty( $pages ) ) { throw new RuntimeException( 'Populate the supplied content first.' ); }
		$duplicates = asfar_content_duplicates();
		$menus = asfar_menu_duplicates();
		$previous = get_option( 'asfar_last_content_repair_backup', array() );
		$references = array_replace( $previous['duplicates'] ?? array(), $duplicates );
		$backup = array( 'created_at' => gmdate( 'c' ), 'duplicates' => $references, 'menu_items' => $menus, 'posts' => array() );
		foreach ( array_unique( array_merge( array_values( $map ), array_values( $pages ) ) ) as $id ) {
			$backup['posts'][ $id ] = array( 'post' => get_post( $id, ARRAY_A ), 'meta' => get_post_meta( $id ) );
		}
		update_option( 'asfar_last_content_repair_backup', $backup, false );
		// Rewrite homepage relationships before retiring duplicate records.
		foreach ( array( 'index', 'index-ar' ) as $slug ) {
			$home_id = $pages[ $slug ] ?? 0;
			if ( ! $home_id ) { continue; }
			$portfolio = asfar_rows( 'portfolio_section', $home_id );
			$portfolio['projects'] = get_post_meta( $home_id, 'portfolio_section_projects', true );
			if ( ! empty( $portfolio['projects'] ) ) {
				$portfolio['projects'] = array_values( array_unique( array_map( function ( $id ) use ( $references ) { return $references[ $id ] ?? $id; }, $portfolio['projects'] ) ) );
				update_field( 'field_asfar_home_portfolio_portfolio_section', $portfolio, $home_id );
			}
			$faq_section = asfar_rows( 'faq_section', $home_id );
			$faq_section['page'] = get_post_meta( $home_id, 'faq_section_page', true );
			if ( isset( $references[ $faq_section['page'] ?? 0 ] ) ) {
				$faq_section['page'] = $references[ $faq_section['page'] ];
				update_field( 'field_asfar_home_faq_faq_section', $faq_section, $home_id );
			}
		}
		foreach ( array( 'en', 'ar' ) as $language ) {
			$source = asfar_import_value( $data['options'][ 'global_' . $language ] );
			foreach ( $source['team'] as $index => $member ) {
				$id = $map[ 'team_' . $index . '_' . $language ] ?? 0;
				if ( ! $id || ! get_post( $id ) ) { continue; }
				asfar_repair_field( 'team', 'job_title', $member['role'], $id );
				asfar_repair_field( 'team', 'biography', $member['bio'], $id );
				if ( ! has_post_thumbnail( $id ) ) { set_post_thumbnail( $id, $member['photo'] ); }
				if ( ! wp_get_object_terms( $id, 'team_department' ) && ! empty( $terms[ $member['group'] . '_' . $language ] ) ) {
					wp_set_object_terms( $id, (int) $terms[ $member['group'] . '_' . $language ], 'team_department' );
				}
			}
			$project_ids = array();
			foreach ( $source['portfolio'] as $index => $project ) {
				$id = $map[ 'project_' . $index . '_' . $language ] ?? 0;
				if ( ! $id || ! get_post( $id ) ) { continue; }
				$project_ids[] = $id;
				foreach ( array( 'company' => 'company', 'headline' => 'title', 'summary' => 'body', 'map_label' => 'label', 'map_region' => 'hot', 'map_x' => 'mark_x', 'map_y' => 'mark_y', 'statistics' => 'stats', 'detail_image' => 'img' ) as $field => $old ) {
					asfar_repair_field( 'project', $field, $project[ $old ] ?? '', $id );
				}
				if ( ! has_post_thumbnail( $id ) ) { set_post_thumbnail( $id, $project['bg'] ); }
			}
			$faq = $map[ 'faq_' . $language ] ?? 0;
			if ( $faq ) { asfar_repair_field( 'faq', 'faq_items', $source['faq'], $faq ); }
			$home = $pages[ 'ar' === $language ? 'index-ar' : 'index' ] ?? 0;
			if ( $home ) {
				asfar_repair_field( 'home_portfolio', 'portfolio_section', array( 'heading' => $source['portfolio_title'], 'projects' => $project_ids ), $home );
				asfar_repair_field( 'home_faq', 'faq_section', array( 'page' => $faq ), $home );
			}
		}
		// Relink the canonical records before retiring unreferenced duplicate imports.
		foreach ( $map as $key => $id ) {
			if ( str_ends_with( $key, '_en' ) && ! empty( $map[ substr( $key, 0, -3 ) . '_ar' ] ) ) {
				pll_save_post_translations( array( 'en' => $id, 'ar' => $map[ substr( $key, 0, -3 ) . '_ar' ] ) );
			}
		}
		foreach ( $duplicates as $id => $canonical ) { wp_trash_post( $id ); }
		if ( $menus ) {
			$backup_menu = wp_create_nav_menu( 'ASFAR Menu Backup ' . gmdate( 'Y-m-d H:i:s' ) );
			if ( is_wp_error( $backup_menu ) ) { throw new RuntimeException( $backup_menu->get_error_message() ); }
			foreach ( $menus as $id => $old_menu ) { wp_set_object_terms( $id, (int) $backup_menu, 'nav_menu' ); }
		}
		$result = array( 'duplicates' => count( $duplicates ), 'menu_items' => count( $menus ) );
		update_option( 'asfar_last_content_repair_result', $result, false );
		return $result;
	} );
}

add_action( 'admin_menu', function () {
	add_theme_page( 'ASFAR Content Check', 'ASFAR Content Check', 'manage_options', 'asfar-content-check', 'asfar_content_check_page' );
} );
function asfar_content_check_page() {
	if ( ! current_user_can( 'manage_options' ) ) { return; }
	if ( ! function_exists( 'pll_get_post_language' ) ) { echo '<div class="wrap"><p>Activate Polylang first.</p></div>'; return; }
	$duplicates = asfar_content_duplicates();
	$menus = asfar_menu_duplicates();
	?>
	<div class="wrap">
		<h1>ASFAR Content Check</h1>
		<p>This check compares existing records with the imported content map. Repair fills missing Team and Project details and FAQ data from the supplied files, restores translation links, and moves the listed duplicate posts to Trash. Duplicated menu links are moved to an unassigned backup menu.</p>
		<p><?php echo esc_html( count( $duplicates ) . ' duplicate content records; ' . count( $menus ) . ' duplicate menu links.' ); ?></p>
		<?php if ( isset( $_GET['repaired'] ) ) : ?>
			<div class="notice notice-success"><p>Content repair completed. Duplicate posts can be restored from Trash, and removed menu links are retained in the backup menu.</p></div>
		<?php endif; ?>
		<table class="widefat striped">
			<thead><tr><th>Duplicate ID</th><th>Title</th><th>Language</th><th>Retained ID</th></tr></thead>
			<tbody>
				<?php foreach ( $duplicates as $id => $canonical ) : ?>
					<tr><td><?php echo absint( $id ); ?></td><td><?php echo esc_html( get_the_title( $id ) ); ?></td><td><?php echo esc_html( pll_get_post_language( $id ) ); ?></td><td><?php echo absint( $canonical ); ?></td></tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
			<input type="hidden" name="action" value="asfar_repair_content">
			<?php wp_nonce_field( 'asfar_repair_content' ); ?>
			<?php submit_button( 'Repair imported content and archive duplicates' ); ?>
		</form>
	</div>
	<?php
}
add_action( 'admin_post_asfar_repair_content', function () {
	if ( ! current_user_can( 'manage_options' ) ) { wp_die( 'Forbidden', '', array( 'response' => 403 ) ); }
	check_admin_referer( 'asfar_repair_content' );
	try { asfar_repair_imported_content(); }
	catch ( Throwable $error ) { wp_die( esc_html( $error->getMessage() ) ); }
	wp_safe_redirect( admin_url( 'themes.php?page=asfar-content-check&repaired=1' ) );
	exit;
} );
