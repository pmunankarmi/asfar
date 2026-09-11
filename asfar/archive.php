<?php
/** Standard archive for news, projects, team members and taxonomy terms. */
get_header();
?>
<main id="top" class="asfar-content wrap">
	<h1><?php echo is_home() ? esc_html( asfar_option( 'archive_title' ) ) : wp_kses_post( get_the_archive_title() ); ?></h1>
	<div class="news__grid">
		<?php while ( have_posts() ) : the_post(); ?>
			<article>
				<a href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'medium' ); ?></a>
				<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
			</article>
		<?php endwhile; ?>
	</div>
	<?php the_posts_pagination(); ?>
</main>
<?php get_footer(); ?>
