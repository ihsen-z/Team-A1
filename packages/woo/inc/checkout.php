<?php
/**
 * Commande en cartes numérotées.
 *
 * WooCommerce rend le formulaire de commande en deux blocs (facturation,
 * livraison) que rien ne sépare visuellement. Un tunnel long sans repères fait
 * abandonner : les champs sont regroupés en cartes numérotées, et le
 * récapitulatif devient un aside qui reste visible.
 *
 * Aucun gabarit n'est copié : tout passe par les hooks, pour que le module
 * survive à une mise à jour de WooCommerce.
 *
 * @package FactoryCore\Woo
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Champs qui relèvent du contact plutôt que de l'adresse.
 *
 * @return string[]
 */
function factory_woo_checkout_contact_keys(): array {
	return (array) apply_filters(
		'factory_woo_checkout_contact_keys',
		array( 'billing_first_name', 'billing_last_name', 'billing_phone', 'billing_email' )
	);
}

add_action('wp', 'factory_woo_checkout_swap_forms');
/**
 * Remplace le rendu natif du formulaire par les cartes.
 *
 * Accroché sur `wp` : la requête est résolue, donc `is_checkout()` répond
 * juste, et le gabarit n'a pas encore été rendu.
 *
 * @return void
 */
function factory_woo_checkout_swap_forms(): void {
	if (! function_exists('is_checkout') || ! is_checkout() || is_checkout_pay_page()) {
		return;
	}

	$checkout = WC()->checkout();

	if (! $checkout instanceof WC_Checkout) {
		return;
	}

	remove_action('woocommerce_checkout_billing', array( $checkout, 'checkout_form_billing' ));
	remove_action('woocommerce_checkout_shipping', array( $checkout, 'checkout_form_shipping' ));
	add_action('woocommerce_checkout_billing', 'factory_woo_checkout_cards');
}

/**
 * Ouvre une carte numérotée.
 *
 * @param int    $numero Rang de la carte.
 * @param string $titre  Titre de la section.
 * @return void
 */
function factory_woo_checkout_card_open(int $numero, string $titre): void {
	printf(
		'<section class="factory-cocard"><h2 class="factory-cocard__titre"><span class="factory-cocard__num" aria-hidden="true">%1$s</span>%2$s</h2><div class="factory-cocard__corps">',
		esc_html((string) $numero),
		esc_html($titre)
	);
}

/**
 * Ferme une carte.
 *
 * @return void
 */
function factory_woo_checkout_card_close(): void {
	echo '</div></section>';
}

/**
 * Rend le formulaire de commande en cartes.
 *
 * @param WC_Checkout|null $checkout Instance de commande.
 * @return void
 */
function factory_woo_checkout_cards($checkout = null): void {
	$checkout = $checkout instanceof WC_Checkout ? $checkout : WC()->checkout();

	if (! $checkout instanceof WC_Checkout) {
		return;
	}

	$champs  = $checkout->get_checkout_fields('billing');
	$contact = factory_woo_checkout_contact_keys();
	$numero  = 0;

	// --- Carte 1 : contact -------------------------------------------------
	++$numero;
	factory_woo_checkout_card_open($numero, __('Vos coordonnées', 'factory-core'));

	foreach ($champs as $cle => $champ) {
		if (! in_array($cle, $contact, true)) {
			continue;
		}

		woocommerce_form_field($cle, $champ, $checkout->get_value($cle));
	}

	factory_woo_checkout_card_close();

	// --- Carte 2 : adresse -------------------------------------------------
	++$numero;
	factory_woo_checkout_card_open($numero, __('Adresse de livraison', 'factory-core'));

	foreach ($champs as $cle => $champ) {
		if (in_array($cle, $contact, true)) {
			continue;
		}

		woocommerce_form_field($cle, $champ, $checkout->get_value($cle));
	}

	/**
	 * Permet d'insérer un contenu propre au site dans la carte d'adresse
	 * (créneau de livraison, point relais) sans copier ce gabarit.
	 */
	do_action('factory_woo_checkout_after_address');

	factory_woo_checkout_card_close();

	// --- Carte 3 : compte, si WooCommerce le propose -----------------------
	if ($checkout->is_registration_enabled() && ! is_user_logged_in()) {
		++$numero;
		factory_woo_checkout_card_open($numero, __('Votre compte', 'factory-core'));

		foreach ($checkout->get_checkout_fields('account') as $cle => $champ) {
			woocommerce_form_field($cle, $champ, $checkout->get_value($cle));
		}

		factory_woo_checkout_card_close();
	}
}

add_action('woocommerce_checkout_before_order_review_heading', 'factory_woo_aside_open');
/**
 * Ouvre l'aside du récapitulatif.
 *
 * @return void
 */
function factory_woo_aside_open(): void {
	echo '<aside class="factory-corecap" aria-label="' . esc_attr__('Récapitulatif de la commande', 'factory-core') . '">';
}

add_action('woocommerce_checkout_after_order_review', 'factory_woo_aside_close');
/**
 * Ferme l'aside du récapitulatif.
 *
 * @return void
 */
function factory_woo_aside_close(): void {
	echo '</aside>';
}

add_filter('woocommerce_order_button_text', 'factory_woo_order_button_text');
/**
 * Libellé du bouton de commande.
 *
 * « Commander » dit ce qui se passe ; « Passer la commande » est plus long
 * sans être plus clair.
 *
 * @return string
 */
function factory_woo_order_button_text(): string {
	return __('Commander', 'factory-core');
}
