<?php
/** Content access: no runtime dependency on seed data or JavaScript dictionaries. */
if ( ! defined( 'ABSPATH' ) ) { exit; }
function asfar_language() {
 $lang = function_exists( 'pll_current_language' ) ? pll_current_language( 'slug' ) : '';
 if ( ! $lang && is_singular() ) { $lang = get_post_meta( get_the_ID(), '_asfar_language', true ); }
 return 'ar' === $lang ? 'ar' : 'en';
}
function asfar_rows( $name ) { $rows = asfar_option( $name ); return is_array( $rows ) ? $rows : array(); }
function asfar_options_id() { return 'global_' . asfar_language(); }
function asfar_value( $name, $context = null ) {
 $context = $context ?? get_the_ID();
 if ( function_exists( 'get_field' ) ) { $value = get_field( $name, $context ); }
 else { $value = is_numeric( $context ) ? get_post_meta( $context, $name, true ) : get_option( $context . '_' . $name, '' ); }
 return false === $value || null === $value ? '' : $value;
}
function asfar_option( $name, $lang = null ) { return asfar_value( $name, 'global_' . ( $lang ?? asfar_language() ) ); }
function asfar_media( $name, $context = null ) { return wp_get_attachment_url( absint( asfar_value( $name, $context ) ) ) ?: ''; }
function asfar_resolve_url( $url ) {
 if ( ! is_string( $url ) ) { return ''; }
 if ( str_starts_with( $url, '#' ) ) { return asfar_home_url() . $url; }
 $parts = explode( '#', $url, 2 );
 $slug = preg_replace( '/\.html$/', '', $parts[0] );
 $map = get_option( 'asfar_page_map', array() );
 if ( isset( $map[ $slug ] ) ) {
  $id = $map[ $slug ];
  if ( function_exists( 'pll_get_post' ) ) { $id = pll_get_post( $id, asfar_language() ) ?: $id; }
  if ( 'publish' !== get_post_status( $id ) ) { return asfar_home_url(); }
  return get_permalink( $id ) . ( isset( $parts[1] ) ? '#' . $parts[1] : '' );
 }
 return esc_url_raw( $url );
}
function asfar_link( $name, $context = null ) {
 $link = asfar_value( $name, $context );
 return asfar_resolve_url( is_array( $link ) ? ( $link['url'] ?? '' ) : $link );
}
function asfar_home_url() {
 if ( function_exists( 'pll_home_url' ) ) { return pll_home_url( asfar_language() ); }
 return home_url( '/' );
}
function asfar_logo( $dark = false ) {
 $id = absint( get_theme_mod( 'custom_logo' ) );
 if ( ! $id ) { return ''; }
 if ( $dark ) { $id = absint( asfar_value( 'dark_logo', 'asfar_shared' ) ) ?: $id; }
 return wp_get_attachment_url( $id ) ?: '';
}
function asfar_languages() {
 if ( ! function_exists( 'pll_the_languages' ) ) { return; }
 $languages = pll_the_languages( array( 'raw' => 1, 'hide_if_empty' => 0, 'hide_if_no_translation' => 0 ) );
 foreach ( (array) $languages as $language ) {
  if ( $language['current_lang'] ) { continue; }
  $url = ! empty( $language['no_translation'] ) ? pll_home_url( $language['slug'] ) : $language['url'];
  printf( '<a class="nav__lang-opt" href="%s" lang="%s" hreflang="%s">%s</a>', esc_url( $url ), esc_attr( $language['slug'] ), esc_attr( $language['slug'] ), esc_html( $language['name'] ) );
 }
}
function asfar_marks() {
 $marks = asfar_option( 'hero_marks' );
 if ( ! is_array( $marks ) ) { return array(); }
 foreach ( $marks as &$mark ) { $mark['href'] = asfar_resolve_url( $mark['href'] ?? '' ); }
 unset( $mark );
 return $marks ?: array();
}
function asfar_render_page() {
 $layout = get_post_meta( get_the_ID(), '_asfar_layout', true );
 if ( $layout && preg_match( '/^[a-z0-9-]+$/', $layout ) && file_exists( get_template_directory() . '/template-parts/pages/' . $layout . '.php' ) ) {
  get_template_part( 'template-parts/pages/' . $layout );
 } else {
  echo '<main id="top" class="asfar-content"><h1>' . esc_html( get_the_title() ) . '</h1>';
  foreach ( ( asfar_value( 'paragraphs' ) ?: array() ) as $row ) { echo '<p>' . nl2br( esc_html( $row['text'] ?? '' ) ) . '</p>'; }
  echo '</main>';
 }
}
function asfar_team_home() {
 $team = asfar_rows( 'team' );
 foreach ( array( 'board', 'leadership', 'committees' ) as $group ) {
  $members = array_values( array_filter( (array) $team, function ( $m ) use ( $group ) { return $m['group'] === $group; } ) );
  echo '<div class="asfar-team-group" data-team-group="' . esc_attr( $group ) . '"' . ( 'board' !== $group ? ' hidden' : '' ) . '>';
  foreach ( $members as $i => $member ) {
   if ( 1 === $i ) { echo '<div class="dk-team__gridwrap"><div class="dk-team__grid">'; }
   $class = 0 === $i ? 'dk-feat' : 'dk-mem';
   printf( '<article class="%s member--opens" data-name="%s" data-role="%s" data-photo="%s" data-bio="%s"><span class="%s__ph member__ph">', esc_attr( $class ), esc_attr( $member['name'] ), esc_attr( $member['role'] ), esc_url( wp_get_attachment_url( absint( $member['photo'] ) ) ?: '' ), esc_attr( $member['bio'] ), esc_attr( $class ) );
   if ( $member['photo'] ) { echo wp_get_attachment_image( $member['photo'], 'large', false, array( 'alt' => '' ) ); }
   printf( '</span><div class="%s__txt"><h3><button type="button" class="%s__name member__name" aria-expanded="false">%s</button></h3><p class="%s__role">%s</p></div></article>', esc_attr( $class ), esc_attr( $class ), esc_html( $member['name'] ), esc_attr( $class ), esc_html( $member['role'] ) );
  }
  if ( count( $members ) > 1 ) { echo '</div></div>'; }
  echo '</div>';
 }
}
function asfar_portfolio() {
 $rows = asfar_rows( 'portfolio' );
 get_template_part( 'template-parts/portfolio', null, array( 'rows' => $rows ) );
}
function asfar_faq() {
 foreach ( asfar_rows( 'faq' ) as $row ) {
  echo '<div class="dk-faq__item"><button class="dk-faq__btn" type="button" aria-expanded="false"><span>' . esc_html( $row['question'] ) . '</span><span class="dk-faq__sign" aria-hidden="true"></span></button><div class="dk-faq__awrap"><p class="dk-faq__a">' . esc_html( $row['answer'] ) . '</p></div></div>';
 }
}
function asfar_partners() {
 foreach ( asfar_rows( 'partners' ) as $row ) {
  if ( $row['url'] ) { echo '<a href="' . esc_url( $row['url'] ) . '">'; }
  echo wp_get_attachment_image( $row['image'], 'full', false, array( 'alt' => $row['name'], 'loading' => 'lazy' ) );
  if ( $row['url'] ) { echo '</a>'; }
 }
}
function asfar_sectors() {
 foreach ( asfar_rows( 'sectors' ) as $row ) {
  echo '<div class="dk-sector">' . wp_get_attachment_image( $row['image'], 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ) . '<span class="dk-sector__pol" aria-hidden="true"></span><span class="dk-sector__name">' . esc_html( $row['label'] ) . '</span></div>';
 }
}
function asfar_social() {
 echo '<div class="dk-wrap asfar-social">';
 asfar_navigation( 'footer' );
 foreach ( asfar_rows( 'social_links' ) as $row ) { echo '<a href="' . esc_url( $row['social_url'] ) . '">' . esc_html( $row['social_label'] ) . '</a> '; }
 echo '</div>';
}
function asfar_team_page( $group ) {
 foreach ( asfar_rows( 'team' ) as $row ) {
  if ( $row['group'] !== $group ) { continue; }
  printf( '<article class="member member--opens reveal" data-name="%s" data-role="%s" data-photo="%s" data-bio="%s"><span class="member__ph">%s</span><h3><button type="button" class="member__name" aria-expanded="false" aria-controls="memberProfile">%s</button></h3><p>%s</p></article>', esc_attr( $row['name'] ), esc_attr( $row['role'] ), esc_url( wp_get_attachment_url( $row['photo'] ) ?: '' ), esc_attr( $row['bio'] ), $row['photo'] ? wp_get_attachment_image( $row['photo'], 'large', false, array( 'alt' => '' ) ) : '', esc_html( $row['name'] ), esc_html( $row['role'] ) );
 }
}
function asfar_news( $home = false ) {
 $query = new WP_Query( array( 'post_type' => 'post', 'posts_per_page' => 20, 'paged' => $home ? 1 : max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) ), 'lang' => asfar_language(), 'orderby' => 'date', 'order' => 'DESC', 'ignore_sticky_posts' => true ) );
 while ( $query->have_posts() ) {
  $query->the_post();
  $url = asfar_value( 'external_url' ) ?: get_permalink();
  $date = asfar_value( 'display_date' ) ?: get_the_date();
  if ( $home ) {
   echo '<a class="dk-newscard" href="' . esc_url( $url ) . '"><p class="dk-newscard__date">' . esc_html( $date ) . '</p><span class="dk-newscard__ph">' . get_the_post_thumbnail( null, 'large', array( 'alt' => '', 'loading' => 'lazy' ) ) . '<span class="dk-newscard__grad" aria-hidden="true"></span><h3 class="dk-newscard__title">' . esc_html( get_the_title() ) . '</h3></span></a>';
  } else {
   echo '<article class="news__card reveal"><figure class="news__thumb">' . get_the_post_thumbnail( null, 'large', array( 'alt' => '', 'loading' => 'lazy' ) ) . '</figure><div class="news__body"><span class="news__pill">' . esc_html( $date ) . '</span><h3 class="news__title">' . esc_html( get_the_title() ) . '</h3></div><a class="news__link" href="' . esc_url( $url ) . '"><span>' . esc_html( get_the_title() ) . '</span></a></article>';
  }
 }
 if ( ! $home && $query->max_num_pages > 1 ) { echo '<nav class="asfar-pagination">' . wp_kses_post( paginate_links( array( 'total' => $query->max_num_pages, 'current' => max( 1, get_query_var( 'paged' ), get_query_var( 'page' ) ) ) ) ) . '</nav>'; }
 wp_reset_postdata();
}
function asfar_navigation( $location ) {
 $locations = get_nav_menu_locations();
 $items = ! empty( $locations[ $location ] ) ? wp_get_nav_menu_items( $locations[ $location ] ) : array();
 foreach ( (array) $items as $item ) {
  echo '<a href="' . esc_url( asfar_resolve_url( $item->url ) ) . '"' . ( '_blank' === $item->target ? ' target="_blank" rel="noopener"' : '' ) . '>' . esc_html( $item->title ) . '</a>';
 }
}
