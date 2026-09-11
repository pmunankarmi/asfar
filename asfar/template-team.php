<?php
/** Template Name: ASFAR Team */
get_header();
?>
<main id="top">
	<div class="teampage section">
		<div class="wrap">
			<?php while ( have_posts() ) : the_post(); ?>
				<div class="teampage__head">
					<h1 class="statement reveal"><?php the_title(); ?></h1>
					<p class="teampage__lede reveal"><?php echo asfar_lines( asfar_value( 'introduction' ) ); ?></p>
				</div>
				<?php foreach ( asfar_team_departments() as $department ) : ?>
					<section class="teampage__group">
						<h2 class="teampage__gtitle reveal"><?php echo esc_html( $department->name ); ?></h2>
						<div class="teampage__grid">
							<?php foreach ( asfar_team_members( $department->term_id ) as $member ) : ?>
								<?php get_template_part( 'template-parts/team/card', null, array( 'member' => $member, 'class' => 'member' ) ); ?>
							<?php endforeach; ?>
						</div>
					</section>
				<?php endforeach; ?>
			<?php endwhile; ?>
		</div>
	</div>
</main>
<?php get_footer(); ?>
