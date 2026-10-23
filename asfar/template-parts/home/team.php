<?php
$section = get_field( 'team_section' ) ?: array();
$departments = asfar_team_departments();
if ( empty( $section['enabled'] ) || ! $departments ) {
	return;
}
?>
<section class="mt-dk-team" id="dkTeam">
	<span id="leadership"></span>
	<div class="mt-dk-wrap">
		<div class="mt-dk-team__panel mt-dk-rise">
			<h2 class="mt-dk-label"><?php echo esc_html( $section['heading'] ?? '' ); ?></h2>
			<p class="mt-dk-sub mt-dk-team__sub"><?php echo asfar_lines( $section['description'] ?? '' ); ?></p>
		</div>
		<div class="mt-dk-team__stage mt-dk-rise">
			<div class="mt-dk-team__rail">
				<?php foreach ( $departments as $index => $department ) : ?>
					<?php $members = asfar_team_members( $department->term_id ); ?>
					<div class="mt-asfar-team-group" data-team-group="<?php echo esc_attr( $department->term_id ); ?>" <?php echo 0 === $index ? '' : 'hidden'; ?>>
						<?php if ( $members ) : ?>
							<?php get_template_part( 'template-parts/team/card', null, array( 'member' => $members[0], 'class' => 'mt-dk-feat' ) ); ?>
						<?php endif; ?>
						<div class="mt-dk-team__gridwrap">
							<div class="mt-dk-team__grid">
								<?php foreach ( array_slice( $members, 1 ) as $member ) : ?>
									<?php get_template_part( 'template-parts/team/card', null, array( 'member' => $member, 'class' => 'mt-dk-mem' ) ); ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="mt-dk-team__foot">
			<div class="mt-dk-tabs" role="tablist" aria-label="<?php echo esc_attr( $section['heading'] ?? '' ); ?>">
				<?php foreach ( $departments as $index => $department ) : ?>
					<button class="mt-dk-tab <?php echo 0 === $index ? 'mt-is-active' : ''; ?>" type="button" data-group="<?php echo esc_attr( $department->term_id ); ?>" role="tab" aria-selected="<?php echo 0 === $index ? 'true' : 'false'; ?>">
						<?php echo esc_html( $department->name ); ?>
					</button>
				<?php endforeach; ?>
			</div>
			<?php get_template_part( 'template-parts/arrows' ); ?>
			<?php asfar_button( $section['button'] ?? array() ); ?>
		</div>
	</div>
</section>
