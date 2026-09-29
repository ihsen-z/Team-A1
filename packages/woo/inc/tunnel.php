<?php
/**
 * Parcours d'achat : fil d'étapes, en-tête, et corps de page.
 *
 * @package FactoryCore\Woo
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Étape du parcours affichée par la page courante.
 *
 * @return int 1 panier, 2 commande, 3 confirmation, 0 hors parcours.
 */
function factory_woo_tunnel_step(): int {
	if (! function_exists('is_cart')) {
		return 0;
	}

	if (is_cart()) {
		return 1;
	}

	if (is_order_received_page()) {
		return 3;
	}

	// La page de paiement d'une commande existante n'est pas une étape du
	// parcours : le client y arrive par un lien, pas par le tunnel.
	if (is_checkout() && ! is_checkout_pay_page()) {
		return 2;
	}

	return 0;
}

add_filter('body_class', 'factory_woo_tunnel_body_class');
/**
 * Marque les pages du parcours, pour que le thème puisse alléger son habillage.
 *
 * @param string[] $classes Classes du corps de page.
 * @return string[]
 */
function factory_woo_tunnel_body_class(array $classes): array {
	$etape = factory_woo_tunnel_step();

	if (0 === $etape) {
		return $classes;
	}

	$classes[] = 'factory-tunnel';
	$classes[] = 'factory-tunnel--etape-' . $etape;

	return $classes;
}

add_action('woocommerce_before_main_content', 'factory_woo_tunnel_header', 5);
/**
 * En-tête du parcours : titre de l'étape et fil des étapes.
 *
 * Accroché sur un hook WooCommerce et non sur un hook de thème : le module
 * doit fonctionner sous n'importe quel thème.
 *
 * @return void
 */
function factory_woo_tunnel_header(): void {
	$etape = factory_woo_tunnel_step();

	if (0 === $etape) {
		return;
	}

	$titres = array(
		1 => __('Votre panier', 'factory-core'),
		2 => __('Votre commande', 'factory-core'),
		3 => __('Commande confirmée', 'factory-core'),
	);

	printf(
		'<header class="factory-tunnel__entete"><h1 class="factory-tunnel__titre">%s</h1>',
		esc_html($titres[ $etape ] ?? '')
	);

	factory_woo_tunnel_steps($etape);

	echo '</header>';
}

/**
 * Fil des étapes.
 *
 * Rendu en liste ordonnée : l'ordre des étapes est une information, pas une
 * décoration. L'étape courante porte aria-current pour les lecteurs d'écran.
 *
 * @param int $courante Numéro de l'étape en cours.
 * @return void
 */
function factory_woo_tunnel_steps(int $courante): void {
	$labels = array(
		1 => __('Panier', 'factory-core'),
		2 => __('Commande', 'factory-core'),
		3 => __('Confirmation', 'factory-core'),
	);

	echo '<ol class="factory-steps">';

	foreach ($labels as $numero => $label) {
		$etat = 'todo';

		if ($numero < $courante) {
			$etat = 'done';
		} elseif ($numero === $courante) {
			$etat = 'current';
		}

		printf(
			'<li class="factory-steps__item is-%1$s"%2$s><span class="factory-steps__puce" aria-hidden="true">%3$s</span><span class="factory-steps__label">%4$s</span></li>',
			esc_attr($etat),
			'current' === $etat ? ' aria-current="step"' : '',
			'done' === $etat ? '&check;' : esc_html((string) $numero),
			esc_html($label)
		);
	}

	echo '</ol>';
}

add_filter('woocommerce_shipping_package_name', 'factory_woo_package_name');
/**
 * Renomme « Colis 1 » quand il n'y a qu'un seul colis.
 *
 * @param string $nom Nom du colis proposé par WooCommerce.
 * @return string
 */
function factory_woo_package_name($nom): string {
	$paquets = WC()->shipping() ? WC()->shipping()->get_packages() : array();

	if (count($paquets) > 1) {
		return (string) $nom;
	}

	return __('Livraison', 'factory-core');
}
