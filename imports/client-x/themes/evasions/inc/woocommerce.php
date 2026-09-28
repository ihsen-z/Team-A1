<?php
/**
 * Pont WooCommerce : produits de la homepage, badges, étoiles, compteur du panier.
 *
 * Tout ce qui s'affiche vient de WooCommerce. Aucun prix, aucune note, aucun
 * nombre d'avis n'est écrit ici (§60).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce est-il actif ?
 *
 * @return bool
 */
function evasions_has_woocommerce(): bool {
	return class_exists( 'WooCommerce' );
}

/**
 * Les produits mis en avant de l'accueil.
 *
 * Ordre de préférence : produits mis en avant par le propriétaire (étoile
 * WooCommerce), sinon les plus vendus, sinon les plus récents. Une boutique
 * neuve affiche donc quelque chose de vrai dès son premier produit.
 *
 * @param int    $limit    Number of products.
 * @param string $category Optional product category slug (its sub-categories included).
 * @return array<int, WC_Product>
 */
function evasions_featured_products( int $limit = 4, string $category = '' ): array {
	if ( ! evasions_has_woocommerce() ) {
		return array();
	}

	$base = array(
		'status'     => 'publish',
		'visibility' => 'catalog',
		'limit'      => $limit,
		'orderby'    => 'date',
		'order'      => 'DESC',
	);

	if ( '' !== $category ) {
		$base['category'] = array( $category );
	}

	$featured = wc_get_products( $base + array( 'featured' => true ) );
	if ( count( $featured ) >= $limit ) {
		return array_slice( $featured, 0, $limit );
	}

	$found = $featured;
	$ids   = array_map( static fn( $p ) => $p->get_id(), $featured );

	foreach ( array( array( 'meta_key' => 'total_sales', 'orderby' => 'meta_value_num' ), array( 'orderby' => 'date' ) ) as $order ) {
		if ( count( $found ) >= $limit ) {
			break;
		}

		// array_merge : les clés de droite l'emportent, donc le tri du palier et la limite restante remplacent ceux de la base.
		$more = wc_get_products(
			array_merge(
				$base,
				$order,
				array(
					'exclude' => $ids,
					'limit'   => $limit - count( $found ),
				)
			)
		);

		foreach ( $more as $product ) {
			$found[] = $product;
			$ids[]   = $product->get_id();
		}
	}

	return array_slice( $found, 0, $limit );
}

/**
 * Badge d'une carte produit : « −25 % », « Nouveau » ou « Best-seller ».
 *
 * La RÈGLE — quelle remise est annoncée, à partir de quelle ancienneté un
 * produit est « Nouveau », quelle étiquette pose « Best-seller » — appartient à
 * evasions-core (`\Evasions\Core\Catalog::badge()`). Elle y a été remontée
 * parce qu'un changement de thème l'aurait emportée avec lui, alors que c'est
 * une décision commerciale (audit ARCH-03). Ici on ne fait que traduire le fait
 * rendu par le plugin en libellé et en classe CSS : c'est de la présentation,
 * donc c'est la place du thème, avec le reste de la micro-copie française.
 *
 * Sans evasions-core, aucun badge : mieux vaut ne rien afficher qu'annoncer une
 * remise ou une nouveauté qu'on ne sait plus établir (§60).
 *
 * @param WC_Product $product Product.
 * @return array{label:string, modifier:string}|null
 */
function evasions_product_badge( WC_Product $product ): ?array {
	if ( ! class_exists( '\Evasions\Core\Catalog' ) ) {
		return null;
	}

	$fact = \Evasions\Core\Catalog::badge( $product );

	if ( null === $fact ) {
		return null;
	}

	switch ( $fact['type'] ) {
		case 'sale':
			return array(
				/* translators: %d: discount percentage */
				'label'    => sprintf( __( '-%d%%', 'evasions' ), (int) $fact['percent'] ),
				'modifier' => 'sale',
			);

		case 'best':
			return array(
				'label'    => __( 'Best-seller', 'evasions' ),
				'modifier' => 'best',
			);

		case 'new':
			return array(
				'label'    => __( 'Nouveau', 'evasions' ),
				'modifier' => 'new',
			);
	}

	// Un type que ce thème ne sait pas habiller : rien plutôt qu'un badge vide.
	return null;
}

/**
 * Étoiles remplies proportionnellement à une note sur 5.
 *
 * Le remplissage est porté par une variable CSS : pas d'image, pas de police
 * d'icônes, et une note de 4,6 ne s'arrondit pas à 5 étoiles pleines.
 *
 * @param float $rating Average rating, 0–5.
 * @return string
 */
function evasions_stars( float $rating ): string {
	$percent = max( 0, min( 100, ( $rating / 5 ) * 100 ) );

	return sprintf(
		'<span class="ev-stars" style="--ev-rating:%s%%" role="img" aria-label="%s"></span>',
		esc_attr( (string) round( $percent, 1 ) ),
		/* translators: %s: average rating out of 5 */
		esc_attr( sprintf( __( 'Note : %s sur 5', 'evasions' ), number_format_i18n( $rating, 1 ) ) )
	);
}

/**
 * Nombre d'articles dans le panier.
 *
 * @return int
 */
function evasions_cart_count(): int {
	return ( evasions_has_woocommerce() && WC()->cart ) ? (int) WC()->cart->get_cart_contents_count() : 0;
}

/**
 * Markup du compteur du panier, tel que rafraîchi par les fragments WooCommerce.
 *
 * Le compteur est vide à zéro : une pastille « 0 » attire l'œil sur une
 * absence, l'inverse de ce qu'on cherche.
 *
 * @return string
 */
function evasions_cart_count_html(): string {
	$count = evasions_cart_count();

	return sprintf(
		'<span class="ev-cart-count" data-count="%1$d"%2$s>%1$d</span>',
		$count,
		0 === $count ? ' hidden' : ''
	);
}

/**
 * Rafraîchit le compteur du panier après un ajout en AJAX.
 *
 * @param array<string,string> $fragments Fragments to refresh.
 * @return array<string,string>
 */
function evasions_cart_fragments( $fragments ) {
	$fragments['.ev-cart-count'] = evasions_cart_count_html();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'evasions_cart_fragments' );

/**
 * Le texte du bouton d'ajout au panier des cartes : celui du design.
 *
 * @param string $text Default button text.
 * @param WC_Product $product Product.
 * @return string
 */
function evasions_add_to_cart_text( $text, $product ) {
	return ( $product instanceof WC_Product && $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock() )
		? __( 'Ajouter au panier', 'evasions' )
		: $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'evasions_add_to_cart_text', 10, 2 );

/**
 * Habille le bouton « Ajouter au panier » des cartes, sans lui retirer ce dont WooCommerce a besoin.
 *
 * Passer `class` directement à `woocommerce_template_loop_add_to_cart()` REMPLACE
 * les classes de WooCommerce — dont `add_to_cart_button` et `ajax_add_to_cart`,
 * que son script cherche pour ajouter au panier sans recharger la page. On
 * ajoute donc les nôtres ici, après coup. Constaté à l'exécution : avec
 * `class`, le clic rechargeait la page.
 *
 * @param array<string,mixed> $args Button arguments.
 * @return array<string,mixed>
 */
function evasions_card_button_args( $args ) {
	// Variante compacte (produits complémentaires) : bouton en contour. Le marqueur `ev-compact-btn` sert au filtre de libellé ci-dessous.
	if ( ! empty( $args['ev_compact'] ) ) {
		$args['class'] = trim( ( $args['class'] ?? '' ) . ' ev-btn ev-btn--outline ev-card__cta ev-compact-btn' );
		unset( $args['ev_compact'] );
	}

	return $args;
}
add_filter( 'woocommerce_loop_add_to_cart_args', 'evasions_card_button_args' );

/**
 * Libellé « + Ajouter » sur les boutons compacts.
 *
 * Le texte d'un bouton de boucle vient du produit, pas des arguments : on le
 * remplace dans le lien fini, uniquement pour les boutons marqués compacts et
 * seulement quand c'est le libellé d'ajout au panier (un produit variable garde
 * « Choisir des options »).
 *
 * @param string $html Link markup.
 * @return string
 */
function evasions_compact_button_label( $html ) {
	if ( false === strpos( (string) $html, 'ev-compact-btn' ) ) {
		return $html;
	}

	return str_replace( '>' . __( 'Ajouter au panier', 'evasions' ) . '<', '>+ ' . __( 'Ajouter', 'evasions' ) . '<', (string) $html );
}
add_filter( 'woocommerce_loop_add_to_cart_link', 'evasions_compact_button_label' );
