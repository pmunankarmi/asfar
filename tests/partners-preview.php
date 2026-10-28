<?php
/** Isolated template fixture: no WordPress database required. */
$count = max( 0, min( 30, (int) ( $_GET['count'] ?? 9 ) ) );
$direction = ( $_GET['dir'] ?? 'ltr' ) === 'rtl' ? 'rtl' : 'ltr';
$logos = array();
for ( $i = 0; $i < $count; ++$i ) { $logos[] = array( 'logo' => $i % 9 + 1, 'name' => 'Partner ' . ( $i + 1 ), 'website' => $i % 2 ? 'https://example.com' : '' ); }
function get_field( $name ) { return array( 'heading' => 'Our<br>Partners' ); }
function asfar_rows( ...$args ) { global $logos; return $logos; }
function asfar_lines( $s ) { return $s; }
function asfar_translate( $s ) { return $s; }
function asfar_language() { global $direction; return 'rtl' === $direction ? 'ar' : 'en'; }
function esc_url( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function esc_attr( $s ) { return htmlspecialchars( $s, ENT_QUOTES ); }
function wp_get_attachment_image( $id, $size, $icon, $attrs ) { return '<img width="150" height="150" src="../media/asfar-media/img/partners/p' . $id . '.png" alt="' . esc_attr( $attrs['alt'] ) . '">'; }
?>
<!doctype html><html dir="<?php echo $direction; ?>"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<link rel="stylesheet" href="../asfar/assets/css/app.css"><link rel="stylesheet" href="../asfar/assets/css/deck.css">
<!-- Reproduce the stale first-child rule that stacked every wrapped logo. -->
<style>.mt-dk-partners__card img:nth-child(1){position:absolute;inset-inline-start:5.832%;top:13.284%;width:7.529%;height:28.044%}</style>
<link rel="stylesheet" href="../asfar/assets/css/partners.css"><link rel="stylesheet" href="../asfar/assets/css/swiper-bundle.min.css">
<style>body{margin:0} .mt-dk-partners{padding-block:40px}</style>
<?php require __DIR__ . '/../asfar/template-parts/home/partners.php'; ?>
<?php if ( $count > 10 ) : ?><script src="../asfar/assets/js/vendor/swiper-bundle.min.js"></script><script src="../asfar/assets/js/partners.js"></script><?php endif; ?>
</html>
