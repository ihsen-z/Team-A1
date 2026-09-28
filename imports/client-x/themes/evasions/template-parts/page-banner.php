<?php
/**
 * Bandeau photo des pages Panier et Checkout.
 *
 * Titre et sous-titre de la maquette. Le sous-titre du checkout ne promet pas
 * « quelques étapes » : le checkout tient sur une page.
 *
 * Le sous-titre du checkout reprend mot pour mot la rassurance du §14.2,
 * « Vous payez à la réception ». Le §14.2 n'en autorise aucune variante : le
 * client doit retrouver la même phrase de la fiche produit au paiement, sinon
 * il se demande si c'est bien la même promesse. La formule précédente
 * (« Vous ne payez qu'à la réception du colis. ») en était une troisième
 * version, après celle de la barre de promesses du plugin.
 *
 * Cette photo est l'image du premier écran du panier et du checkout, et la mesure
 * du 28 septembre 2026 la donne élément LCP de /commander/ : elle porte donc
 * fetchpriority="high", que le §17 réserve à cette seule image (28/09/2026).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

$is_checkout = function_exists( 'is_checkout' ) && is_checkout();

$title    = $is_checkout ? __( 'Finalisez votre commande', 'evasions' ) : __( 'Votre panier', 'evasions' );
$subtitle = $is_checkout
	? __( 'Vous payez à la réception', 'evasions' )
	: __( 'Vérifiez vos articles et passez votre commande en toute simplicité.', 'evasions' );
$image    = $is_checkout ? 'banner-randonneur.webp' : 'univers-plage.webp';
?>
<section class="ev-page-banner" aria-labelledby="ev-page-banner-title">
	<?php echo evasions_picture( $image, array( 'img_class' => 'ev-page-banner__img', 'width' => 612, 'height' => 408, 'decoding' => 'async', 'fetchpriority' => 'high' ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup <picture> échappé dans evasions_picture(). ?>
	<div class="ev-page-banner__shade" aria-hidden="true"></div>
	<div class="ev-container ev-page-banner__copy">
		<h1 id="ev-page-banner-title"><?php echo esc_html( $title ); ?></h1>
		<p><?php echo esc_html( $subtitle ); ?></p>
	</div>
</section>
