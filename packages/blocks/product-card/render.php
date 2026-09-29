<?php
/**
 * Rendu du bloc factory/product-card.
 *
 * Réunit trois cartes qui n'étaient distinctes que par accident : la carte
 * complète, la compacte et l'élément de listing. Un attribut « variante »
 * suffit à les couvrir, et une seule carte se corrige à un seul endroit.
 *
 * @package FactoryCore
 *
 * @var array    $attributes Attributs du bloc.
 * @var string   $content    Contenu interne.
 * @var WP_Block $block      Instance du bloc.
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

// Sans WooCommerce, ce bloc n'a rien à montrer : mieux vaut rien qu'une
// coquille cassée sur un site vitrine où il aurait été collé par erreur.
if (! function_exists('wc_get_product')) {
	return '';
}

// Trois provenances possibles, de la plus explicite à la plus implicite :
// l'attribut, le contexte d'une boucle de requête, puis le produit courant.
$produit_id = isset($attributes['produitId']) ? absint($attributes['produitId']) : 0;

if (0 === $produit_id && isset($block) && $block instanceof WP_Block) {
	$produit_id = isset($block->context['postId']) ? absint($block->context['postId']) : 0;
}

$produit = $produit_id > 0 ? wc_get_product($produit_id) : null;

if (! $produit instanceof WC_Product) {
	global $product;
	$produit = $product instanceof WC_Product ? $product : null;
}

if (! $produit instanceof WC_Product || ! $produit->is_visible()) {
	return '';
}

$variante = isset($attributes['variante']) ? (string) $attributes['variante'] : 'complete';

if (! in_array($variante, array( 'complete', 'compacte', 'ligne' ), true)) {
	$variante = 'complete';
}

// La variante compacte se veut dense : badge et note y sont retirés d'office.
$badge  = 'compacte' !== $variante && ! empty($attributes['afficheBadge']);
$note   = 'compacte' !== $variante && ! empty($attributes['afficheNote']);
$bouton = ! empty($attributes['afficheBouton']);

$niveau = isset($attributes['niveauTitre']) ? absint($attributes['niveauTitre']) : 3;
$niveau = max(2, min(4, $niveau));
$tag    = 'h' . $niveau;

$prioritaire = ! empty($attributes['prioritaire']);

$attrs_image = array( 'class' => 'factory-card__img', 'decoding' => 'async' );

if ($prioritaire) {
	$attrs_image['loading']       = 'eager';
	$attrs_image['fetchpriority'] = 'high';
} else {
	$attrs_image['loading'] = 'lazy';
}

$image_id = (int) $produit->get_image_id();
$moyenne  = (float) $produit->get_average_rating();
$nb_avis  = (int) $produit->get_review_count();

$wrapper = get_block_wrapper_attributes(
	array( 'class' => 'factory-card factory-card--' . sanitize_html_class($variante) )
);
?>
<article <?php echo $wrapper; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- échappé par get_block_wrapper_attributes(). ?>>
	<a class="factory-card__lien" href="<?php echo esc_url((string) $produit->get_permalink()); ?>">
		<span class="factory-card__media">
			<?php if ($image_id > 0) : ?>
				<?php
				echo wp_get_attachment_image($image_id, 'factory-card', false, $attrs_image); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- wp_get_attachment_image() échappe ses attributs.
				?>
			<?php else : ?>
				<span class="factory-card__img factory-card__img--vide" aria-hidden="true"></span>
			<?php endif; ?>

			<?php if ($badge && ! $produit->is_in_stock()) : ?>
				<span class="factory-card__badge factory-card__badge--rupture">
					<?php esc_html_e('Épuisé', 'factory-core'); ?>
				</span>
			<?php elseif ($badge && $produit->is_on_sale()) : ?>
				<span class="factory-card__badge factory-card__badge--promo">
					<?php esc_html_e('Promo', 'factory-core'); ?>
				</span>
			<?php endif; ?>
		</span>

		<<?php echo esc_html($tag); ?> class="factory-card__titre">
			<?php echo esc_html($produit->get_name()); ?>
		</<?php echo esc_html($tag); ?>>
	</a>

	<?php if ($note && $nb_avis > 0 && $moyenne > 0) : ?>
		<?php /* Le remplissage voyage en pourcentage : aucune image, aucune police d'icônes. */ ?>
		<p class="factory-card__note">
			<span class="factory-card__etoiles" style="--factory-rating:<?php echo esc_attr((string) round($moyenne * 20, 1)); ?>%"
				role="img"
				aria-label="<?php
				/* translators: 1 : note moyenne, 2 : nombre d'avis. */
				echo esc_attr(sprintf(__('Note %1$s sur 5, %2$d avis', 'factory-core'), number_format_i18n($moyenne, 1), $nb_avis));
				?>"></span>
			<span class="factory-card__nb-avis">(<?php echo esc_html(number_format_i18n($nb_avis)); ?>)</span>
		</p>
	<?php endif; ?>

	<p class="factory-card__prix">
		<?php echo wp_kses_post((string) $produit->get_price_html()); ?>
	</p>

	<?php if ($bouton && function_exists('wc_implode_html_attributes')) : ?>
		<?php
		/*
		 * Le lien d'ajout au panier de WooCommerce porte son propre nonce et
		 * ses classes AJAX : on le reprend tel quel plutôt que de le
		 * reconstruire, sinon l'ajout sans rechargement cesse de fonctionner.
		 */
		echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup WooCommerce, échappé en amont par le cœur.
			'woocommerce_loop_add_to_cart_link',
			sprintf(
				'<a href="%s" data-quantity="1" class="button factory-card__bouton %s" %s>%s</a>',
				esc_url($produit->add_to_cart_url()),
				$produit->is_purchasable() && $produit->is_in_stock() ? 'add_to_cart_button ajax_add_to_cart' : '',
				wc_implode_html_attributes(
					array(
						'data-product_id'  => $produit->get_id(),
						'data-product_sku' => $produit->get_sku(),
						'aria-label'       => $produit->add_to_cart_description(),
						'rel'              => 'nofollow',
					)
				),
				esc_html($produit->add_to_cart_text())
			),
			$produit,
			array()
		);
		?>
	<?php endif; ?>
</article>
