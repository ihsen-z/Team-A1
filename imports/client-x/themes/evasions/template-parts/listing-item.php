<?php
/**
 * Un produit dans la boucle d'un listing : la carte du thème dans l'élément de liste de WooCommerce.
 *
 * Remplace `content-product.php` par le filtre `wc_get_template_part`
 * (voir `evasions_listing_item_template()`). Comme WooCommerce, un produit
 * invisible dans le catalogue n'est pas affiché.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

/*
 * Le rang est pris APRÈS le contrôle de visibilité : un produit masqué n'est
 * pas rendu, il ne doit pas consommer le rang 1 et priver la première carte
 * visible de son chargement prioritaire (audit PERF-03).
 */
$evasions_rank = evasions_listing_item_rank();
?>
<li <?php wc_product_class( 'ev-listing__item', $product ); ?>>
	<?php
	// Sous le h1 du listing, le titre d'une carte est un h2.
	get_template_part(
		'template-parts/product-card',
		null,
		array(
			'product'   => $product,
			'title_tag' => 'h2',
			// Seule la première carte est dans le premier écran : image immédiate et prioritaire (§17.1).
			'lcp'       => 1 === $evasions_rank,
		)
	);
	?>
</li>
