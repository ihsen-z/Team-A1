<?php
/**
 * Module e-commerce du socle.
 *
 * Chargé uniquement sur les projets marchands (preset `ecommerce`). Tout ce
 * qu'il contient est du WooCommerce pur : aucune adhérence à un thème parent
 * commercial, pour que le module survive à un changement de thème.
 *
 * @package FactoryCore\Woo
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

// Sans WooCommerce, le module ne s'active pas : un site vitrine qui l'aurait
// reçu par erreur reste fonctionnel.
if (! class_exists('WooCommerce')) {
	return;
}

define('FACTORY_WOO_DIR', __DIR__);

require_once FACTORY_WOO_DIR . '/inc/tunnel.php';
require_once FACTORY_WOO_DIR . '/inc/reassurance.php';
require_once FACTORY_WOO_DIR . '/inc/checkout.php';

add_action('wp_enqueue_scripts', 'factory_woo_assets', 22);
/**
 * Charge la feuille du module, uniquement sur les pages du parcours d'achat.
 *
 * @return void
 */
function factory_woo_assets(): void {
	if (0 === factory_woo_tunnel_step()) {
		return;
	}

	$fichier = FACTORY_WOO_DIR . '/assets/tunnel.css';

	if (! file_exists($fichier)) {
		return;
	}

	wp_enqueue_style(
		'factory-woo-tunnel',
		get_theme_file_uri('woo/assets/tunnel.css'),
		array(),
		// filemtime() plutôt qu'un numéro de version : un correctif poussé sans
		// bump de version resterait invisible derrière le cache du navigateur.
		(string) filemtime($fichier)
	);
}
