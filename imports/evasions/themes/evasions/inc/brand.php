<?php
/**
 * Identité de marque : icône du site, visuel de partage, image de remplacement.
 *
 * Les fichiers viennent de la charte (`brand/` à la racine du projet, générés
 * depuis le logo par `brand/tools/`). Le thème n'en embarque que ce qui sert.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * URL d'un fichier d'icône du thème.
 *
 * @param string $file File name inside assets/img/icons.
 * @return string
 */
function evasions_icon_url( string $file ): string {
	return get_template_directory_uri() . '/assets/img/icons/' . $file;
}

/**
 * Icônes de l'onglet, de l'écran d'accueil du téléphone et couleur du navigateur.
 *
 * Si le propriétaire a choisi une icône dans Apparence → Personnaliser → Identité
 * du site, WordPress émet la sienne : le thème s'efface (son choix prime).
 *
 * @return void
 */
function evasions_favicons(): void {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	printf( '<link rel="icon" href="%s" type="image/svg+xml">' . "\n", esc_url( evasions_icon_url( 'favicon.svg' ) ) );
	printf( '<link rel="icon" href="%s" sizes="32x32" type="image/png">' . "\n", esc_url( evasions_icon_url( 'favicon-32x32.png' ) ) );
	printf( '<link rel="icon" href="%s" sizes="16x16" type="image/png">' . "\n", esc_url( evasions_icon_url( 'favicon-16x16.png' ) ) );
	printf( '<link rel="shortcut icon" href="%s">' . "\n", esc_url( evasions_icon_url( 'favicon.ico' ) ) );
	printf( '<link rel="apple-touch-icon" href="%s">' . "\n", esc_url( evasions_icon_url( 'apple-touch-icon.png' ) ) );
	printf( '<link rel="mask-icon" href="%s" color="#165A32">' . "\n", esc_url( evasions_icon_url( 'safari-pinned-tab.svg' ) ) );
	printf( '<link rel="manifest" href="%s">' . "\n", esc_url( evasions_icon_url( 'site.webmanifest' ) ) );
	echo '<meta name="theme-color" content="#165A32">' . "\n";
}
add_action( 'wp_head', 'evasions_favicons', 2 );

/**
 * Sert notre `favicon.ico` quand un navigateur ou un robot le demande à la racine du site.
 *
 * Sans cela WordPress répond avec son propre logo. Le choix du propriétaire
 * (icône du site) reste prioritaire.
 *
 * @return void
 */
function evasions_serve_favicon(): void {
	if ( function_exists( 'has_site_icon' ) && has_site_icon() ) {
		return;
	}

	$file = get_theme_file_path( 'assets/img/icons/favicon.ico' );

	if ( ! is_readable( $file ) ) {
		return;
	}

	header( 'Content-Type: image/x-icon' );
	header( 'Cache-Control: public, max-age=604800' );
	readfile( $file ); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_system_operations_readfile -- fichier statique du thème.
	exit;
}
add_action( 'do_faviconico', 'evasions_serve_favicon', 1 );

/**
 * Image d'un produit sans photo : la nôtre, pas la grisée de WooCommerce.
 *
 * @return string
 */
function evasions_placeholder_image(): string {
	return evasions_img( 'placeholder-product.svg' );
}
add_filter( 'woocommerce_placeholder_img_src', 'evasions_placeholder_image' );
