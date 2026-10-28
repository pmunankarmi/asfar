<?php
/** Template Name: ASFAR Team */
if ( ! function_exists( 'get_field' ) ) {
	get_template_part( 'template-parts/content-fallback' );
	return;
}
get_header();
?>
<main id="top">
	<div class="mt-teampage mt-section">
		<div class="mt-wrap">
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="mt-teampage__head">
					<h1 class="mt-statement mt-reveal"><?php the_title(); ?></h1>
					<p class="mt-teampage__lede mt-reveal"><?php echo asfar_lines( get_field( 'introduction' ) ); ?></p>
				</div>
				<?php foreach ( asfar_team_departments() as $department ) : ?>
					<section class="mt-teampage__group">
						<h2 class="mt-teampage__gtitle mt-reveal"><?php echo esc_html( $department->name ); ?></h2>
						<div class="mt-teampage__grid">
							<?php foreach ( asfar_team_members( $department->term_id ) as $member ) : ?>
								<?php get_template_part( 'template-parts/team/card', null, array( 'member' => $member, 'class' => 'mt-member' ) ); ?>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			<?php endwhile; ?>
		</div>
	</div>
</main>
<?php get_footer(); ?>
