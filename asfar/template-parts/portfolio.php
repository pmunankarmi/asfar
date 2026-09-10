<?php if ( ! defined( 'ABSPATH' ) ) { exit; } $rows = $args['rows']; ?>
<section class="dk-map" id="dkMap">
<div class="dk-map__bgs" aria-hidden="true">
<?php foreach ( $rows as $i => $row ) { ?><div class="dk-map__bg <?php echo 0 === $i ? 'is-active' : ''; ?>" style="background-image:url('<?php echo esc_url( wp_get_attachment_url( $row['bg'] ) ); ?>')"></div><?php } ?>
</div><div class="dk-map__in"><h2 class="dk-map__title dk-rise"><?php echo esc_html( asfar_option( 'portfolio_title' ) ); ?></h2>
<div class="dk-map__stage dk-rise" aria-hidden="true"><?php asfar_render_map_artwork(); ?><div class="dk-map__pin"></div><div class="dk-map__pinlab"></div></div>
<aside class="dk-map__panel dk-rise" aria-live="polite">
<?php foreach ( $rows as $i => $row ) { ?>
<div class="asfar-portfolio-pane" data-hot="<?php echo esc_attr( $row['hot'] ); ?>" data-x="<?php echo esc_attr( $row['mark_x'] ); ?>" data-y="<?php echo esc_attr( $row['mark_y'] ); ?>" data-label="<?php echo esc_attr( $row['label'] ); ?>" <?php echo 0 === $i ? '' : 'hidden'; ?>>
<h3 class="dk-map__region dk-map__swap"><?php echo esc_html( $row['name'] ); ?></h3><div class="dk-map__rows">
<?php foreach ( (array) $row['stats'] as $stat ) { ?><div class="dk-map__row dk-map__swap"><p class="dk-map__val"><?php echo esc_html( $stat['value'] ); ?></p><p class="dk-map__lab"><?php echo esc_html( $stat['label'] ); ?></p></div><?php } ?>
</div></div><?php } ?></aside></div>
<div class="dk-map__dashes" role="tablist" aria-label="<?php echo esc_attr( asfar_option( 'portfolio_title' ) ); ?>">
<?php foreach ( $rows as $i => $row ) { ?><button type="button" role="tab" aria-selected="<?php echo 0 === $i ? 'true' : 'false'; ?>" class="<?php echo 0 === $i ? 'is-active' : ''; ?>" aria-label="<?php echo esc_attr( $row['name'] ); ?>"></button><?php } ?></div></section>
