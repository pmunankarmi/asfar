<?php
$section = asfar_section( 'team' );
$departments = asfar_team_departments();
if ( empty( $section['enabled'] ) ) {
	return;
}
?>
<section class="dk-team" id="dkTeam">
	<span id="leadership"></span>
	<div class="dk-wrap">
		<div class="dk-team__panel dk-rise">
			<h2 class="dk-label"><?php echo esc_html( $section['heading'] ?? '' ); ?></h2>
			<p class="dk-sub dk-team__sub"><?php echo asfar_lines( $section['description'] ?? '' ); ?></p>
		</div>
		<div class="dk-team__stage dk-rise">
			<div class="dk-team__rail">
				<?php foreach ( $departments as $index => $department ) : ?>
					<?php $members = asfar_team_members( $department->term_id ); ?>
					<div class="asfar-team-group" data-team-group="<?php echo esc_attr( $department->term_id ); ?>" <?php echo 0 === $index ? '' : 'hidden'; ?>>
						<?php if ( $members ) : ?>
							<?php get_template_part( 'template-parts/team/card', null, array( 'member' => $members[0], 'class' => 'dk-feat' ) ); ?>
						<?php endif; ?>
						<div class="dk-team__gridwrap">
							<div class="dk-team__grid">
								<?php foreach ( array_slice( $members, 1 ) as $member ) : ?>
									<?php get_template_part( 'template-parts/team/card', null, array( 'member' => $member, 'class' => 'dk-mem' ) ); ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="dk-team__foot">
			<div class="dk-tabs" role="tablist" aria-label="<?php echo esc_attr( $section['heading'] ?? '' ); ?>">
				<?php foreach ( $departments as $index => $department ) : ?>
					<button class="dk-tab <?php echo 0 === $index ? 'is-active' : ''; ?>" type="button" data-group="<?php echo esc_attr( $department->term_id ); ?>" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>">
						<?php echo esc_html( $department->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<?php get_template_part( 'template-parts/arrows' ); ?>
			<?php asfar_button( $section['button'] ?? array() ); ?>
		</div>
	</div>
</section>
