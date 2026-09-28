<?php
/**
 * En-tête du site : barre de promesses, logo, panier, navigation (recherche
 * dans le menu). Réduit au logo sur le checkout.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="ev-skip" href="#content"><?php esc_html_e( 'Aller au contenu', 'evasions' ); ?></a>

<?php
// Tunnel de paiement : en-tête réduit au logo (§3), sans la barre de promesses.
$evasions_checkout = function_exists( 'is_checkout' ) && is_checkout() && ! is_wc_endpoint_url( 'order-received' );

if ( ! $evasions_checkout ) {
	get_template_part( 'template-parts/topbar' );
}
get_template_part( 'template-parts/site-header' );
?>

<main id="content" class="ev-main">
