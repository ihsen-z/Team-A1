<?php
/**
 * Carte produit de la homepage.
 *
 * Prix, note, nombre d'avis et disponibilité viennent de WooCommerce. La note
 * n'apparaît que si le produit a au moins un avis : « 4,8 (0 avis) » ne serait
 * pas une information mais une affirmation vide.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$product = $args['product'] ?? null;

// Niveau du titre : h3 sous un titre de section (homepage), h2 sous le h1 d'un listing.
$title_tag = $args['title_tag'] ?? 'h3';
$title_tag = in_array( $title_tag, array( 'h2', 'h3' ), true ) ? $title_tag : 'h3';

if ( ! $product instanceof WC_Product ) {
	return;
}

$badge   = evasions_product_badge( $product );
$reviews = (int) $product->get_review_count();
$link    = $product->get_permalink();

/*
 * Chargement de l'image (audit PERF-03). Par défaut `lazy` : une carte est
 * presque toujours hors du premier écran. L'appelant qui sait que sa carte est
 * la première d'un listing passe `lcp => true` : cette image-là EST le premier
 * écran, la mettre en lazy retarde le LCP de la boutique. `fetchpriority="high"`
 * est réservé à elle seule (§17.1) — les autres cartes restent en lazy.
 */
$img_attr = ! empty( $args['lcp'] )
	? array(
		'loading'       => 'eager',
		'fetchpriority' => 'high',
	)
	: array( 'loading' => 'lazy' );

// Carte sans bouton, entièrement cliquable (§6.2) : un seul lien, sur le titre,
// dont la zone active couvre toute la carte (`.ev-card__link::after`). L'image
// est décorative (le titre porte le nom accessible), donc pas un second lien.
?>
<article class="ev-card">
	<div class="ev-card__media">
		<?php echo $product->get_image( 'woocommerce_thumbnail', $img_attr ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup d'image WooCommerce. ?>
		<?php if ( $badge ) : ?>
			<span class="ev-badge ev-badge--<?php echo esc_attr( $badge['modifier'] ); ?>"><?php echo esc_html( $badge['label'] ); ?></span>
		<?php endif; ?>
	</div>

	<div class="ev-card__body">
		<<?php echo esc_attr( $title_tag ); ?> class="ev-card__title"><a class="ev-card__link" href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></<?php echo esc_attr( $title_tag ); ?>>

		<?php if ( $reviews > 0 ) : ?>
			<div class="ev-card__rating">
				<?php echo evasions_stars( (float) $product->get_average_rating() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup construit et échappé par evasions_stars(). ?>
				<span>
					<?php
					echo esc_html(
						sprintf(
							/* translators: 1: average rating, 2: number of reviews */
							_n( '%1$s (%2$d avis)', '%1$s (%2$d avis)', $reviews, 'evasions' ),
							number_format_i18n( (float) $product->get_average_rating(), 1 ),
							$reviews
						)
					);
					?>
				</span>
			</div>
		<?php endif; ?>

		<div class="ev-card__price"><?php echo wp_kses_post( $product->get_price_html() ); ?></div>
	</div>
</article>
