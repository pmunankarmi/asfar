<?php
/** Template Name: ASFAR Supplier Portal */
if ( ! function_exists( 'get_field' ) ) {
 get_template_part( 'template-parts/content-fallback' );
 return;
}
get_header();
while ( have_posts() ) : the_post();
 get_template_part( 'template-parts/supplier-portal' );
endwhile;
get_footer();
