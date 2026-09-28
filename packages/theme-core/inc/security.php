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
add_filter('xmlrpc_enabled', '__return_false');

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

	return $headers;
}
