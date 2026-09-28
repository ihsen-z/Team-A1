<?php
/**
 * Bandeau photo « Pourquoi choisir… » — bas de la boutique, des catégories et
 * des univers.
 *
 * Titre, image de fond, couleurs, pictogrammes et libellés viennent du
 * Customizer (`evasions_why()`) : rien n'est écrit en dur ici (§60). Sans
 * argument renseigné, on reprend ceux de la réassurance de l'accueil.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$why = evasions_why();

if ( ! $why['enabled'] || array() === $why['items'] ) {
	return;
}

// Les couleurs choisies deviennent des variables locales, que la feuille de
// style lit avec un repli sur les jetons du thème.
$style = '--ev-cols:' . (int) count( $why['items'] ) . ';';
foreach ( $why['colors'] as $name => $hex ) {
	$style .= '--ev-why-' . $name . ':' . $hex . ';';
}
?>
<section class="ev-why-wrap" aria-labelledby="ev-why-title">
	<div class="ev-container">
		<div class="ev-why" style="<?php echo esc_attr( $style ); ?>">
			<?php echo evasions_section_image( 'prefooter', 'prefooter.webp', array( 'img_class' => 'ev-why__img', 'width' => 1200, 'height' => 800, 'loading' => 'lazy', 'decoding' => 'async' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup image échappé. ?>
			<div class="ev-why__shade" aria-hidden="true"></div>
			<h2 id="ev-why-title"><?php echo esc_html( $why['title'] ); ?></h2>
			<ul class="ev-why__list">
				<?php foreach ( $why['items'] as $item ) : ?>
					<li>
						<span class="ev-why__icon"><?php echo evasions_icon( $item['icon'], 24 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?></span>
						<span><?php echo esc_html( $item['title'] ); ?><?php if ( '' !== $item['sub'] ) : ?><br><?php echo esc_html( $item['sub'] ); ?><?php endif; ?></span>
					</li>
				<?php endforeach; ?>
			</ul>
		</div>
	</div>
</section>
