<?php
/** Named ACF fields organized by editing screen and homepage section. */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function asfar_field( $name, $label, $type = 'text', $settings = array() ) {
	return array_merge(
		array( 'name' => $name, 'label' => $label, 'type' => $type ),
		$settings
	);
}

function asfar_text( $name, $label, $multiline = false ) {
	return asfar_field( $name, $label, $multiline ? 'textarea' : 'text', $multiline ? array( 'rows' => 3, 'new_lines' => '' ) : array() );
}

function asfar_repeater( $name, $label, $fields, $button = 'Add item' ) {
	return asfar_field( $name, $label, 'repeater', array( 'layout' => 'block', 'sub_fields' => $fields, 'button_label' => $button ) );
}

function asfar_group_field( $name, $label, $fields ) {
	return asfar_field( $name, $label, 'group', array( 'layout' => 'block', 'sub_fields' => $fields ) );
}

function asfar_image_field( $name, $label ) {
	return asfar_field( $name, $label, 'image', array( 'return_format' => 'id', 'preview_size' => 'medium' ) );
}

function asfar_link_field( $name, $label ) {
	return asfar_field( $name, $label, 'link', array( 'return_format' => 'array' ) );
}

function asfar_page_field( $name, $label ) {
	return asfar_field( $name, $label, 'post_object', array( 'post_type' => array( 'page' ), 'return_format' => 'id', 'allow_null' => 1 ) );
}

function asfar_field_keys( $fields, $prefix ) {
	foreach ( $fields as &$field ) {
		$field['key'] = 'field_asfar_' . $prefix . '_' . $field['name'];
		if ( isset( $field['sub_fields'] ) ) {
			$field['sub_fields'] = asfar_field_keys( $field['sub_fields'], $prefix . '_' . $field['name'] );
		}
	}
	return $fields;
}

function asfar_home_field_groups() {
	return array(
		'banner' => array( '01 — Homepage Banner', array(
			asfar_repeater( 'banner_slides', 'Banner Slides', array(
				asfar_text( 'eyebrow', 'Small Heading' ),
				asfar_text( 'heading', 'Main Heading', true ),
				asfar_text( 'highlight', 'Highlighted Heading', true ),
				asfar_image_field( 'image', 'Background Image' ),
				asfar_field( 'video', 'Film File', 'file', array( 'return_format' => 'id', 'mime_types' => 'mp4,webm' ) ),
				asfar_text( 'video_label', 'Watch Film Button Text' ),
				asfar_link_field( 'button', 'Page / Website Button' ),
			), 'Add banner slide' ),
			asfar_field( 'banner_button_help', 'Persistent Banner Buttons', 'message', array( 'message' => 'The film and page buttons from the first banner slide stay visible across all slides, matching the latest design.' ) ),
			asfar_group_field( 'banner_animation', 'Landscape Animation', array(
				asfar_field( 'enabled', 'Enable Landscape Film', 'true_false', array( 'ui' => 1, 'default_value' => 0, 'instructions' => 'Optional original landscape animation. Leave off to cycle through all banner slides.' ) ),
				asfar_text( 'heading', 'Animation Introduction', true ),
				asfar_text( 'subtitle', 'Animation Subtitle' ),
				asfar_repeater( 'stops', 'Landscape Captions', array( asfar_text( 'name', 'Destination Name' ), asfar_link_field( 'button', 'Destination Link' ) ), 'Add landscape caption' ),
			) ),
		) ),
		'about' => array( '02 — About ASFAR', array(
			asfar_group_field( 'about_section', 'About ASFAR', array(
				asfar_text( 'heading', 'Section Heading', true ),
				asfar_text( 'subtitle', 'Company Description', true ),
				asfar_text( 'card_heading', 'Card Heading', true ),
				asfar_repeater( 'paragraphs', 'About Paragraphs', array( asfar_text( 'text', 'Paragraph', true ) ), 'Add paragraph' ),
				asfar_text( 'ownership', 'Ownership Label' ),
			) ),
		) ),
		'vision' => array( '03 — Vision and Mission', array(
			asfar_group_field( 'vision_section', 'Vision and Mission', array(
				asfar_text( 'heading', 'Section Heading', true ),
				asfar_repeater( 'cards', 'Vision Cards', array( asfar_text( 'heading', 'Card Heading' ), asfar_text( 'description', 'Card Description', true ) ), 'Add vision card' ),
			) ),
		) ),
		'impact' => array( '04 — Investment Impact', array(
			asfar_group_field( 'impact_section', 'Investment Impact', array(
				asfar_text( 'heading', 'Section Heading' ),
				asfar_repeater( 'statistics', 'Statistics', array( asfar_text( 'value', 'Value and Unit' ), asfar_text( 'label', 'Description', true ) ), 'Add statistic' ),
			) ),
		) ),
		'portfolio' => array( '05 — Project Portfolio', array(
			asfar_group_field( 'portfolio_section', 'Project Portfolio', array(
				asfar_text( 'heading', 'Section Heading', true ),
				asfar_field( 'projects', 'Featured Projects', 'relationship', array( 'post_type' => array( 'asfar_project' ), 'return_format' => 'id', 'filters' => array( 'search' ), 'instructions' => 'Choose and drag projects into display order. Edit project details under Projects.' ) ),
			) ),
		) ),
		'investments' => array( '06 — Strategic Investments', array(
			asfar_group_field( 'investments_section', 'Strategic Investments', array(
				asfar_text( 'heading', 'Section Heading', true ),
				asfar_text( 'description', 'Introduction', true ),
				asfar_repeater( 'sectors', 'Investment Sectors', array( asfar_text( 'label', 'Sector Name' ), asfar_image_field( 'image', 'Sector Image' ) ), 'Add sector' ),
			) ),
		) ),
		'news' => array( '07 — News', array(
			asfar_group_field( 'news_section', 'News', array(
				asfar_text( 'heading', 'Section Heading' ),
				asfar_link_field( 'button', 'View All News Link' ),
				asfar_field( 'count', 'Number of News Items', 'number', array( 'min' => 1, 'max' => 50, 'default_value' => 20 ) ),
			) ),
		) ),
		'team' => array( '08 — Team', array(
			asfar_group_field( 'team_section', 'Team', array(
				asfar_field( 'enabled', 'Show Team Section', 'true_false', array( 'ui' => 1, 'default_value' => 1 ) ),
				asfar_text( 'heading', 'Section Heading' ),
				asfar_text( 'description', 'Introduction', true ),
				asfar_link_field( 'button', 'View All Team Link' ),
			) ),
		) ),
		'partners' => array( '09 — Partners', array(
			asfar_group_field( 'partners_section', 'Partners', array(
				asfar_text( 'heading', 'Section Heading', true ),
				asfar_field( 'help', 'Partner Logos', 'message', array( 'message' => 'Add and reorder logos under the separate Partners menu in the WordPress admin.' ) ),
			) ),
		) ),
		'faq' => array( '10 — Frequently Asked Questions', array(
			asfar_group_field( 'faq_section', 'Frequently Asked Questions', array(
				asfar_text( 'heading', 'Section Heading', true ),
				asfar_page_field( 'page', 'FAQ Source Page' ),
				asfar_field( 'help', 'Editing Questions', 'message', array( 'message' => 'Questions are managed on the selected FAQ page. The homepage automatically uses its current-language translation.' ) ),
			) ),
		) ),
	);
}

function asfar_theme_fields() {
	$fields = array(
		asfar_image_field( 'site_logo', 'Site Logo' ),
		asfar_image_field( 'dark_logo', 'Dark Logo Variant' ),
		asfar_image_field( 'footer_logo', 'Footer Logo' ),
		asfar_image_field( 'ownership_logo', 'Ownership / PIF Logo' ),
		asfar_field( 'contact_email', 'Contact Email', 'email' ),
		asfar_repeater( 'notification_recipients', 'Form Notification Recipients', array(
			asfar_field( 'language', 'Language', 'select', array( 'choices' => array( 'en' => 'English', 'ar' => 'Arabic' ) ) ),
			asfar_field( 'email', 'Notification Email', 'email' ),
		), 'Add language recipient' ),
		asfar_repeater( 'social_links', 'Social Links', array( asfar_text( 'label', 'Name' ), asfar_field( 'url', 'Website URL', 'url' ) ), 'Add social link' ),
	);
	foreach ( asfar_ui_labels() as $name => $label ) {
		$field = asfar_text( $name, $label, in_array( $name, array( 'copyright', 'form_success', 'form_error', 'form_invalid' ), true ) );
		if ( 'copyright' === $name ) { $field['instructions'] = 'Use [year] for the current year, for example: © [year] ASFAR. Keep [year] in each Polylang translation. Existing fixed years update automatically.'; }
		$fields[] = $field;
	}
	// Tabs organize existing fields without changing their names, keys or saved values.
	$by_name = array_column( $fields, null, 'name' );
	$tabs = array(
		'branding' => array( 'Branding', array( 'site_logo', 'dark_logo', 'site_name' ) ),
		'footer' => array( 'Footer & Contact', array( 'footer_logo', 'ownership_logo', 'ownership_label', 'contact_email', 'social_links', 'copyright', 'contact_button', 'back_top' ) ),
		'form' => array( 'Contact Form', array( 'notification_recipients', 'form_subject', 'name_label', 'name_placeholder', 'email_label', 'email_placeholder', 'message_label', 'message_placeholder', 'send_label', 'form_success', 'form_error', 'form_invalid' ) ),
		'interface' => array( 'Interface Labels', array( 'home_label', 'primary_label', 'open_menu', 'close_menu', 'menu_label', 'close', 'bio_empty', 'bio_label', 'not_found', 'back_home', 'archive_title', 'previous_label', 'next_label', 'banner_label' ) ),
	);
	$organized = array(
		asfar_field( 'translation_help', 'English & Arabic', 'message', array(
			'message' => 'Set the source wording here. Manage English and Arabic translations under <a href="' . esc_url( admin_url( 'admin.php?page=mlang_strings' ) ) . '">Languages → Translations</a>.',
		) ),
	);
	foreach ( $tabs as $name => $tab ) {
		$organized[] = asfar_field( 'tab_' . $name, $tab[0], 'tab', array( 'placement' => 'top' ) );
		if ( 'interface' === $name ) {
			$organized[] = asfar_field( 'interface_help', 'Navigation & Accessibility', 'message', array( 'message' => 'These labels describe navigation, buttons and dialogs, including text used by screen readers. Usually you only need to edit their translations under Languages → Translations.' ) );
		}
		foreach ( $tab[1] as $field_name ) { $organized[] = $by_name[ $field_name ]; }
	}
	return $organized;
}

function asfar_register_field_group( $id, $title, $fields, $location, $order = 0 ) {
	acf_add_local_field_group( array(
		'key'        => 'group_asfar_' . $id,
		'title'      => $title,
		'fields'     => asfar_field_keys( $fields, $id ),
		'location'   => $location,
		'menu_order' => $order,
		'style'      => 'default',
	) );
}

add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}
	$home_location = array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'templates/template-home.php' ) ) );
	foreach ( asfar_home_field_groups() as $id => $group ) {
		asfar_register_field_group( 'home_' . $id, $group[0], $group[1], $home_location, (int) substr( $group[0], 0, 2 ) );
	}
	asfar_register_field_group( 'team', 'Team Member Details', array(
		asfar_text( 'job_title', 'Job Title / Position' ),
		asfar_text( 'biography', 'Biography', true ),
	), array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'asfar_team' ) ) ) );
	asfar_register_field_group( 'department', 'Team Type Display Settings', array(
		asfar_field( 'display_order', 'Menu Order', 'number', array(
			'default_value' => 0,
			'instructions' => 'Lower numbers appear first in Team Types and the homepage tabs.',
		) ),
		asfar_field( 'show_on_homepage', 'Show on Homepage', 'true_false', array(
			'default_value' => 1,
			'ui' => 1,
			'instructions' => 'Show this team type and its members in the homepage team section.',
		) ),
	), array( array( array( 'param' => 'taxonomy', 'operator' => '==', 'value' => 'team_department' ) ) ) );
	asfar_register_field_group( 'project', 'Project Details', array(
		asfar_text( 'company', 'Investment Company' ),
		asfar_text( 'headline', 'Project Headline' ),
		asfar_text( 'summary', 'Project Summary', true ),
		asfar_text( 'map_label', 'Map Label' ),
		asfar_image_field( 'map_background', 'Homepage Map Background' ),
		asfar_field( 'map_region', 'Map Region', 'select', array( 'choices' => array( 'baha' => 'Al-Baha', 'yanbu' => 'Yanbu', 'asir' => 'Aseer', 'taif' => 'Taif', 'all' => 'Strategic Investments' ) ) ),
		asfar_field( 'map_x', 'Map Marker X', 'number' ),
		asfar_field( 'map_y', 'Map Marker Y', 'number' ),
		asfar_repeater( 'statistics', 'Project Statistics', array( asfar_text( 'value', 'Value and Unit' ), asfar_text( 'label', 'Description' ) ), 'Add statistic' ),
		asfar_image_field( 'detail_image', 'Detail Image' ),
	), array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'asfar_project' ) ) ) );
	asfar_register_field_group( 'faq', 'Frequently Asked Questions', array(
		asfar_text( 'introduction', 'Page Introduction', true ),
		asfar_repeater( 'faq_items', 'Questions and Answers', array( asfar_text( 'question', 'Question' ), asfar_text( 'answer', 'Answer', true ) ), 'Add question' ),
	), array( array( array( 'param' => 'page_template', 'operator' => '==', 'value' => 'templates/template-faq.php' ) ) ) );
	asfar_register_field_group( 'page', 'Page Content', array(
		asfar_text( 'introduction', 'Page Introduction', true ),
		asfar_repeater( 'paragraphs', 'Page Paragraphs', array( asfar_text( 'text', 'Paragraph', true ) ), 'Add paragraph' ),
	), array( array(
		array( 'param' => 'post_type', 'operator' => '==', 'value' => 'page' ),
		array( 'param' => 'page_template', 'operator' => '!=', 'value' => 'templates/template-home.php' ),
		array( 'param' => 'page_template', 'operator' => '!=', 'value' => 'templates/template-faq.php' ),
	) ) );
	asfar_register_field_group( 'article', 'News Article Content', array(
		asfar_field( 'show_on_homepage', 'Show on Homepage', 'true_false', array(
			'default_value' => 0,
			'ui' => 1,
			'instructions' => 'Enable to include this story on the homepage in all linked translations. Changing this switch also updates the linked English and Arabic posts.',
		) ),
		asfar_text( 'display_date', 'Display Date' ),
		asfar_text( 'category_label', 'Category Label' ),
		asfar_repeater( 'paragraphs', 'Article Paragraphs', array( asfar_text( 'text', 'Paragraph', true ) ), 'Add paragraph' ),
		asfar_field( 'external_url', 'External Article URL', 'url' ),
	), array( array( array( 'param' => 'post_type', 'operator' => '==', 'value' => 'post' ) ) ) );
	if ( function_exists( 'acf_add_options_page' ) ) {
		acf_add_options_page( array( 'page_title' => 'Theme Settings', 'menu_slug' => 'asfar-settings', 'post_id' => 'asfar_shared', 'capability' => 'manage_options', 'redirect' => false ) );
		acf_add_options_page( array( 'page_title' => 'Partners', 'menu_title' => 'Partners', 'menu_slug' => 'asfar-partners', 'post_id' => 'asfar_partners', 'icon_url' => 'dashicons-groups', 'capability' => 'manage_options', 'redirect' => false ) );
		asfar_register_field_group( 'settings', 'Branding, Footer and Form Settings', asfar_theme_fields(), array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'asfar-settings' ) ) ) );
		asfar_register_field_group( 'partners', 'Partner Logos', array(
			asfar_repeater( 'partner_logos', 'Partners', array( asfar_text( 'name', 'Partner Name' ), asfar_image_field( 'logo', 'Partner Logo' ), asfar_field( 'website', 'Partner Website', 'url' ) ), 'Add partner' ),
		), array( array( array( 'param' => 'options_page', 'operator' => '==', 'value' => 'asfar-partners' ) ) ) );
	}
} );

add_filter( 'acf/update_value', function ( $value, $post_id, $field ) {
	if ( ! str_starts_with( $field['key'] ?? '', 'field_asfar_' ) ) {
		return $value;
	}
	if ( 'text' === ( $field['type'] ?? '' ) ) {
		return sanitize_text_field( $value );
	}
	if ( 'textarea' === ( $field['type'] ?? '' ) ) {
		return sanitize_textarea_field( $value );
	}
	return $value;
}, 10, 3 );
