<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }
get_header(); ?>
<main id="top" class="asfar-content"><h1><?php echo esc_html( asfar_option( 'archive_title' ) ); ?></h1>
<?php while ( have_posts() ) { the_post(); ?>
<article><h2><a href="<?php the_permalink(); ?>"><?php echo esc_html( get_the_title() ); ?></a></h2><?php the_post_thumbnail( 'medium' ); ?></article>
<?php } the_posts_pagination(); ?></main>
<?php get_footer(); ?>
