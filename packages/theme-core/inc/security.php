<?php
/**
 * Durcissement appliqué à tous les sites de l'atelier.
 *
 * @package FactoryCore
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

// L'énumération des utilisateurs via ?author=N sert au bruteforce.
add_action('template_redirect', 'factory_core_block_author_scan');
/**
 * Bloque l'énumération des auteurs sur le front.
 */
function factory_core_block_author_scan(): void {
	if (is_admin() || ! isset($_GET['author'])) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return;
	}

	wp_safe_redirect(home_url('/'), 301);
	exit;
}

// XML-RPC n'est utilisé par aucun de nos sites et reste une surface d'attaque.
// `xmlrpc_enabled` seul ne suffit pas : les méthodes pingback restent exposées
// et sont testées avant toute vérification de mot de passe.
add_filter('xmlrpc_enabled', '__return_false');

add_filter('xmlrpc_methods', 'factory_core_drop_pingback_methods');
/**
 * Retire les méthodes de pingback exposées par XML-RPC.
 *
 * @param array<string, mixed> $methods Méthodes XML-RPC déclarées.
 * @return array<string, mixed>
 */
function factory_core_drop_pingback_methods(array $methods): array {
	unset(
		$methods['pingback.ping'],
		$methods['pingback.extensions.getPingbacks'],
		$methods['X-Pingback']
	);

	return $methods;
}

add_filter('bloginfo_url', 'factory_core_drop_pingback_url', 10, 2);
/**
 * Masque l'URL du point d'entrée pingback.
 *
 * @param string $output Valeur renvoyée par bloginfo().
 * @param string $show   Information demandée.
 * @return string
 */
function factory_core_drop_pingback_url(string $output, string $show): string {
	return 'pingback_url' === $show ? '' : $output;
}

add_filter('rest_endpoints', 'factory_core_restrict_user_endpoints');
/**
 * Réserve les endpoints REST « users » aux utilisateurs connectés.
 *
 * @param array<string, mixed> $endpoints Endpoints REST enregistrés.
 * @return array<string, mixed>
 */
function factory_core_restrict_user_endpoints(array $endpoints): array {
	foreach (array( '/wp/v2/users', '/wp/v2/users/(?P<id>[\d]+)' ) as $route) {
		if (! isset($endpoints[ $route ])) {
			continue;
		}

		foreach ($endpoints[ $route ] as $index => $handler) {
			$endpoints[ $route ][ $index ]['permission_callback'] = static function () {
				return is_user_logged_in();
			};
		}
	}

	return $endpoints;
}

add_filter('oembed_response_data', 'factory_core_drop_oembed_author');
/**
 * Retire l'identifiant de l'auteur des réponses oEmbed.
 *
 * L'énumération des auteurs est bloquée côté front, mais oEmbed la rouvrait :
 * le nom et l'URL de l'auteur y transitent sans contrôle.
 *
 * @param array<string, mixed> $data Données oEmbed.
 * @return array<string, mixed>
 */
function factory_core_drop_oembed_author(array $data): array {
	unset($data['author_name'], $data['author_url']);

	return $data;
}

// La version de WordPress fuite aussi par les flux et par oEmbed, pas seulement
// par la balise <meta generator> retirée dans setup.php.
add_filter('the_generator', '__return_empty_string');

add_filter('wp_headers', 'factory_core_security_headers');
/**
 * Ajoute les en-têtes de sécurité de base.
 *
 * @param array<string, string> $headers En-têtes HTTP.
 * @return array<string, string>
 */
function factory_core_security_headers(array $headers): array {
	$headers['X-Content-Type-Options'] = 'nosniff';
	$headers['Referrer-Policy']        = 'strict-origin-when-cross-origin';
	$headers['X-Frame-Options']        = 'SAMEORIGIN';

	// Annonce le point d'entrée XML-RPC, désactivé plus haut.
	unset($headers['X-Pingback']);

	return $headers;
}
