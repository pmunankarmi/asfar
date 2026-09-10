<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header();
while ( have_posts() ) { the_post(); asfar_render_page(); }
get_footer();
