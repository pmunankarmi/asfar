<?php
$member = $args['member'];
$class = $args['class'] ?? 'mt-member';
$name = get_the_title( $member );
$role = get_field( 'job_title', $member->ID );
$biography = get_field( 'biography', $member->ID );
$photo = get_post_thumbnail_id( $member );
?>
<article class="<?php echo esc_attr( $class ); ?> mt-member--opens" data-name="<?php echo esc_attr( $name ); ?>" data-role="<?php echo esc_attr( $role ); ?>" data-photo="<?php echo esc_url( asfar_attachment_url( $photo ) ); ?>" data-bio="<?php echo esc_attr( $biography ); ?>">
	<span class="<?php echo esc_attr( $class ); ?>__ph mt-member__ph">
		<?php echo wp_get_attachment_image( $photo, 'large', false, array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
	</span>
	<div class="<?php echo esc_attr( $class ); ?>__txt">
		<h3>
			<button type="button" class="<?php echo esc_attr( $class ); ?>__name mt-member__name" aria-expanded="false">
				<?php echo esc_html( $name ); ?>
			</button>
		</h3>
		<p class="<?php echo esc_attr( $class ); ?>__role"><?php echo esc_html( $role ); ?></p>
	</div>
</article>
