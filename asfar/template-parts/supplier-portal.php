<main id="top">
  <section class="mt-sp mt-section">
    <div class="mt-wrap">
      <header class="mt-sp__head">
        <p class="mt-sp__eyebrow"><?php echo esc_html( get_field( 'eyebrow' ) ); ?></p>
        <h1 class="mt-sp__title"><?php echo esc_html( get_field( 'heading' ) ); ?></h1>
        <p class="mt-sp__lede"><?php echo esc_html( get_field( 'introduction' ) ); ?></p>
      </header>

      <div class="mt-sp-tabs">
        <div class="mt-sp-tablist" role="tablist" aria-label="<?php echo esc_attr( get_field( 'heading' ) ); ?>">
          <button type="button" class="mt-sp-tab" role="tab" id="sptab-reg" aria-controls="sppanel-reg" aria-selected="true"><?php echo esc_html( get_field( 'registration_tab' ) ); ?></button>
          <button type="button" class="mt-sp-tab" role="tab" id="sptab-signin" aria-controls="sppanel-signin" aria-selected="false"><?php echo esc_html( get_field( 'signin_tab' ) ); ?></button>
        </div>

        <div class="mt-sp-panel" id="sppanel-reg" role="tabpanel" aria-labelledby="sptab-reg" tabindex="0">
          <p class="mt-sp__lede" style="margin-block-end:clamp(18px,2vw,34px)"><?php echo esc_html( get_field( 'registration_intro' ) ); ?></p>
          <div class="mt-sp-grid">
            <?php while ( have_rows( 'registration_cards' ) ) : the_row(); ?>
            <article class="mt-sp-card">
              <span class="mt-sp-card__ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="<?php echo 1 === get_row_index() ? 'M12 3v18M3 12h18' : 'M20 6 9 17l-5-5'; ?>"/></svg></span>
              <h2 class="mt-sp-card__title"><?php echo esc_html( get_sub_field( 'heading' ) ); ?></h2>
              <p class="mt-sp-card__desc"><?php echo esc_html( get_sub_field( 'description' ) ); ?></p>
              <a class="mt-sp-btn mt-sp-btn--solid mt-sp-card__cta" href="<?php echo esc_url( get_sub_field( 'url' ) ); ?>" target="_blank" rel="noopener noreferrer"> <?php echo esc_html( get_sub_field( 'label' ) ); ?> <span class="mt-sp-btn__arrow" aria-hidden="true">→</span></a>
            </article>
            <?php endwhile; ?>
          </div>
        </div>

        <div class="mt-sp-panel" id="sppanel-signin" role="tabpanel" aria-labelledby="sptab-signin" tabindex="0">
          <article class="mt-sp-card mt-sp-card--wide mt-sp-signin">
            <span class="mt-sp-card__ico" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg></span>
            <h2 class="mt-sp-card__title"><?php echo esc_html( get_field( 'signin_heading' ) ); ?></h2>
            <p class="mt-sp-card__desc"><?php echo esc_html( get_field( 'signin_description' ) ); ?></p>
            <a class="mt-sp-btn mt-sp-btn--solid mt-sp-card__cta" href="<?php echo esc_url( get_field( 'signin_url' ) ); ?>" target="_blank" rel="noopener noreferrer"><?php echo esc_html( get_field( 'signin_label' ) ); ?> <span class="mt-sp-btn__arrow" aria-hidden="true">→</span></a>
          </article>
        </div>
      </div>

      <aside class="mt-sp-inquiry">
        <p class="mt-sp-inquiry__txt"><strong><?php echo esc_html( get_field( 'inquiry_heading' ) ); ?></strong><span><?php echo esc_html( get_field( 'inquiry_description' ) ); ?></span></p>
        <a class="mt-sp-btn mt-sp-btn--solid" href="#contact" data-partner><?php echo esc_html( get_field( 'inquiry_label' ) ); ?></a>
      </aside>

      <a class="mt-sp-back" href="<?php echo esc_url( asfar_home_url() . '#top' ); ?>"><?php echo esc_html( get_field( 'back_label' ) ); ?></a>
    </div>
  </section>
</main>
