<?php
/** Standard page template. */
get_header();
?>
<main id="top" class="asfar-content wrap">
	<?php while ( have_posts() ) : the_post(); ?>
		<h1><?php the_title(); ?></h1>
		<p><?php echo asfar_lines( asfar_value( 'introduction' ) ); ?></p>
		<?php foreach ( asfar_rows( 'paragraphs' ) as $paragraph ) : ?>
			<p><?php echo asfar_lines( $paragraph['text'] ); ?></p>
		<?php endforeach; ?>
		<?php the_content(); ?>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
