<?php
/**
 * Bandeau « Prêt pour votre prochaine aventure ? » — bas de la fiche produit.
 *
 * Maquette « EVASIONS CTA Aventure » : photo plein cadre, dégradé sombre
 * (latéral en desktop, vertical en mobile), titre, sous-titre et un bouton.
 * Contenu, couleurs, image et lien viennent du Customizer
 * (`evasions_product_cta()`) : rien n'est écrit en dur ici (§60).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$cta = evasions_product_cta();

// Sans titre ni bouton, il n'y a pas de message à porter : on ne rend rien.
if ( ! $cta['enabled'] || ( '' === $cta['title'] && '' === $cta['button'] ) ) {
	return;
}

// Les couleurs choisies sont passées en variables locales, que la feuille de
// style lit avec un repli sur les jetons du thème.
$style = '';
foreach ( $cta['colors'] as $name => $hex ) {
	$style .= '--ev-cta-' . $name . ':' . $hex . ';';
}
?>
<section class="ev-cta-wrap" <?php echo '' !== $style ? 'style="' . esc_attr( $style ) . '"' : ''; ?> <?php echo '' !== $cta['title'] ? 'aria-labelledby="ev-cta-title"' : ''; ?>>
	<div class="ev-container">
		<div class="ev-cta">
			<?php
			echo evasions_section_image( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup image aux URL/attributs échappés.
				'cta',
				'banner-randonneur.webp',
				array(
					'img_class' => 'ev-cta__img',
					'width'     => 612,
					'height'    => 408,
					'loading'   => 'lazy',
					'decoding'  => 'async',
					'size'      => 'full',
				)
			);
			?>
			<div class="ev-cta__shade" aria-hidden="true"></div>
			<div class="ev-cta__copy">
				<?php if ( '' !== $cta['title'] ) : ?>
					<h2 id="ev-cta-title" class="ev-cta__title"><?php echo esc_html( $cta['title'] ); ?></h2>
				<?php endif; ?>
				<?php if ( '' !== $cta['text'] ) : ?>
					<p class="ev-cta__text"><?php echo esc_html( $cta['text'] ); ?></p>
				<?php endif; ?>
				<?php if ( '' !== $cta['button'] ) : ?>
					<a class="ev-cta__btn" href="<?php echo esc_url( $cta['url'] ); ?>"><?php echo esc_html( $cta['button'] ); ?></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>
