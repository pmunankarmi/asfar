<?php
/** Template Name: ASFAR FAQ */
get_header();
?>
<main id="top" class="asfar-content">
	<section class="dk-faq">
		<div class="dk-wrap">
			<?php while ( have_posts() ) : the_post(); ?>
				<h1 class="dk-h2"><?php the_title(); ?></h1>
				<p><?php echo asfar_lines( asfar_value( 'introduction' ) ); ?></p>
				<div class="dk-faq__list" id="dkFaq">
				<?php get_template_part( 'template-parts/faq-list', null, array( 'items' => asfar_rows( 'faq_items' ) ) ); ?>
				</div>
			<?php endwhile; ?>
		</div>
	</section>
</main>
<?php get_footer(); ?>
