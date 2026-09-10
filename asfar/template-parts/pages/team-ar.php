<main id="top">
<div class="teampage section">
<div class="wrap">
<div class="teampage__head">
<h1 class="statement reveal"><?php echo esc_html( asfar_value('c_1808', get_the_ID()) ); ?></h1>
<p class="teampage__lede reveal"><?php echo esc_html( asfar_value('c_1809', get_the_ID()) ); ?></p>
</div>
<section class="teampage__group">
<h2 class="teampage__gtitle reveal"><?php echo esc_html( asfar_value('c_1810', get_the_ID()) ); ?></h2>
<div class="teampage__grid"><?php asfar_team_page( 'board' ); ?></div>
</section>
<section class="teampage__group">
<h2 class="teampage__gtitle reveal"><?php echo esc_html( asfar_value('c_1859', get_the_ID()) ); ?></h2>
<div class="teampage__grid"><?php asfar_team_page( 'committees' ); ?></div>
</section>
<section class="teampage__group">
<h2 class="teampage__gtitle reveal"><?php echo esc_html( asfar_value('c_1916', get_the_ID()) ); ?></h2>
<div class="teampage__grid"><?php asfar_team_page( 'leadership' ); ?></div>
</section>
<p class="teampage__backwrap"><a class="article__back" href="<?php echo esc_url( asfar_link('c_1964', get_the_ID()) ); ?>"><?php echo esc_html( asfar_value('c_1965', get_the_ID()) ); ?></a></p>
</div>
</div>
</main>