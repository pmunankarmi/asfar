<?php
$section = get_field( 'partners_section' ) ?: array();
$logos = array_values( array_filter( asfar_rows( 'partner_logos', 'asfar_partners' ), function ( $row ) { return ! empty( $row['logo'] ); } ) );
$slider = count( $logos ) > 10;
if ( ! $logos ) { return; }

?>
<?php get_template_part( 'template-parts/wave', null, array( 'color' => '#FCF7EE', 'background' => '#FFFFFF' ) ); ?>
<section class="mt-dk-partners" id="partners">
 <div class="mt-dk-wrap mt-dk-partners__grid">
  <h2 class="mt-dk-label mt-dk-rise"><?php echo asfar_lines( $section['heading'] ?? '' ); ?></h2>
  <div class="mt-dk-partners__card mt-dk-partners__flexible mt-dk-rise">
   <div class="<?php echo $slider ? 'mt-partner-swiper' : 'mt-partner-grid'; ?>" <?php echo $slider ? 'data-partner-slider' : ''; ?>>
    <div class="mt-partner-track">
     <?php foreach ( $logos as $logo ) : ?>
      <div class="mt-partner-slide">
       <?php if ( ! empty( $logo['website'] ) ) : ?><a href="<?php echo esc_url( $logo['website'] ); ?>"><?php endif; ?>
       <?php echo wp_get_attachment_image( $logo['logo'], 'full', false, array( 'alt' => asfar_translate( $logo['name'] ?? '' ), 'loading' => 'lazy' ) ); ?>
       <?php if ( ! empty( $logo['website'] ) ) : ?></a><?php endif; ?>
      </div>
     <?php endforeach; ?>
    </div>
   </div>
   <?php if ( $slider ) : ?>
    <div class="mt-partner-controls">
     <button type="button" class="mt-partner-prev mt-dk-arrow" aria-label="<?php echo esc_attr( asfar_language() === 'ar' ? 'الشركاء السابقون' : 'Previous partners' ); ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
       <path d="M15 4 7 12l8 8" />
      </svg>
     </button>
     <button type="button" class="mt-partner-next mt-dk-arrow" aria-label="<?php echo esc_attr( asfar_language() === 'ar' ? 'الشركاء التاليون' : 'Next partners' ); ?>">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
       <path d="M9 4l8 8-8 8" />
      </svg>
     </button>
    </div>
   <?php endif; ?>
  </div>
 </div>
</section>
