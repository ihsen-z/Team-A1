<?php
/**
 * Pages sans maquette : introuvable, article, compte client.
 *
 * Le design ne dessine ni la page 404 ni le compte. Ces écrans reprennent les
 * jetons du thème (couleurs, rayons, polices) et le contenu natif de WordPress
 * et de WooCommerce : aucun texte n'est inventé. L'habillage des e-mails
 * WooCommerce est passé au plugin (`Evasions\Core\Install::style_emails()`) :
 * c'est un réglage de la boutique, pas une feuille de style du thème.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Feuille de style de la page 404 et du compte client.
 *
 * @return void
 */
function evasions_pages_assets(): void {
	if ( is_404() || is_search() || is_archive() ) {
		wp_enqueue_style( 'evasions-404', get_template_directory_uri() . '/assets/css/pages.css', array( 'evasions' ), evasions_asset_version( 'assets/css/pages.css' ) );
	}

	if ( function_exists( 'is_account_page' ) && is_account_page() ) {
		wp_enqueue_style( 'evasions-account', get_template_directory_uri() . '/assets/css/account.css', array( 'evasions', 'evasions-woocommerce' ), evasions_asset_version( 'assets/css/account.css' ) );
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_pages_assets', 30 );

