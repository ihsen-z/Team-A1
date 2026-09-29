<?php
/**
 * Réassurance à source unique.
 *
 * Une seule liste alimente la fiche produit, le panier et la commande. Trois
 * listes écrites séparément finiraient par promettre trois choses différentes,
 * et c'est le genre d'écart qu'un client découvre après l'achat.
 *
 * @package FactoryCore\Woo
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Lignes de réassurance du site.
 *
 * Aucune valeur par défaut : une promesse commerciale ne s'invente pas. Le
 * site qui n'a rien configuré n'affiche aucun cadre.
 *
 * @return array<int, array{titre: string, sousTitre: string}>
 */
function factory_woo_reassurance_items(): array {
	$brut = get_option('factory_reassurance_items', array());

	if (! is_array($brut)) {
		return array();
	}

	$items = array();

	foreach ($brut as $ligne) {
		if (! is_array($ligne) || empty($ligne['titre'])) {
			continue;
		}

		$items[] = array(
			'titre'     => sanitize_text_field((string) $ligne['titre']),
			'sousTitre' => isset($ligne['sousTitre']) ? sanitize_text_field((string) $ligne['sousTitre']) : '',
		);
	}

	/**
	 * Permet à un plugin compagnon de fournir les lignes à la place de l'option.
	 *
	 * @param array<int, array{titre: string, sousTitre: string}> $items Lignes de réassurance.
	 */
	return (array) apply_filters('factory_woo_reassurance_items', $items);
}

/**
 * Rend la réassurance.
 *
 * @param string $variante fiche | tunnel.
 * @return void
 */
function factory_woo_reassurance(string $variante = 'tunnel'): void {
	$items = factory_woo_reassurance_items();

	if (array() === $items) {
		return;
	}

	if (! in_array($variante, array( 'fiche', 'tunnel' ), true)) {
		$variante = 'tunnel';
	}

	printf('<ul class="factory-reassure factory-reassure--%s" role="list">', esc_attr($variante));

	foreach ($items as $item) {
		printf(
			'<li class="factory-reassure__item"><span class="factory-reassure__titre">%s</span>%s</li>',
			esc_html($item['titre']),
			'' !== $item['sousTitre']
				? '<span class="factory-reassure__sous">' . esc_html($item['sousTitre']) . '</span>'
				: ''
		);
	}

	echo '</ul>';
}

add_action('woocommerce_proceed_to_checkout', 'factory_woo_reassurance_panier', 30);
/**
 * Réassurance sous le bouton de commande, dans le panier.
 *
 * @return void
 */
function factory_woo_reassurance_panier(): void {
	factory_woo_reassurance('tunnel');
}

add_action('woocommerce_review_order_after_submit', 'factory_woo_reassurance_commande', 10);
/**
 * Réassurance sous le bouton de paiement.
 *
 * @return void
 */
function factory_woo_reassurance_commande(): void {
	factory_woo_reassurance('tunnel');
}

add_action('woocommerce_single_product_summary', 'factory_woo_reassurance_fiche', 45);
/**
 * Réassurance sur la fiche produit, après le bouton d'ajout au panier.
 *
 * @return void
 */
function factory_woo_reassurance_fiche(): void {
	factory_woo_reassurance('fiche');
}
