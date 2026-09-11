<?php
/** Individual project details. */
get_header();
?>
<main id="top" class="asfar-content wrap">
	<?php while ( have_posts() ) : the_post(); ?>
		<article>
			<p><?php echo esc_html( asfar_value( 'company' ) ); ?></p>
			<h1><?php the_title(); ?></h1>
			<figure class="article__figure"><?php the_post_thumbnail( 'large' ); ?></figure>
			<h2><?php echo esc_html( asfar_value( 'headline' ) ); ?></h2>
			<p><?php echo asfar_lines( asfar_value( 'summary' ) ); ?></p>
			<dl class="asfar-project-statistics">
				<?php foreach ( asfar_rows( 'statistics' ) as $statistic ) : ?>
					<div>
						<dt><?php echo esc_html( $statistic['label'] ); ?></dt>
						<dd><?php echo esc_html( $statistic['value'] ); ?></dd>
					</div>
				<?php endforeach; ?>
			</dl>
			<figure class="article__figure"><?php echo wp_get_attachment_image( asfar_value( 'detail_image' ), 'large' ); ?></figure>
		</article>
	<?php endwhile; ?>
</main>
<?php get_footer(); ?>
