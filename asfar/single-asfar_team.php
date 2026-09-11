<?php
/** Individual team member. */
get_header();
?>
<main id="top" class="asfar-content wrap">
	<?php while ( have_posts() ) : the_post(); ?>
		<article>
			<h1><?php the_title(); ?></h1>
			<p><?php echo esc_html( asfar_value( 'job_title' ) ); ?></p>
			<?php the_post_thumbnail( 'large' ); ?>
			<p><?php echo asfar_lines( asfar_value( 'biography' ) ); ?></p>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
