<?php
/**
 * Page univers — maquette « 1b » (Camping & Randonnée, Plage).
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * La catégorie de premier niveau affichée comme un univers, ou null.
 *
 * Un univers est une catégorie de premier niveau qui a des sous-catégories
 * contenant des produits. Dès qu'un filtre, un tri ou une page est demandé, ou
 * que le lien « Voir tous les produits » est suivi (`?tous=1`), on est sur un
 * listing.
 *
 * @return WP_Term|null
 */
function evasions_universe_term(): ?WP_Term {
	static $cache = array();

	if ( ! function_exists( 'is_product_category' ) || ! is_product_category() ) {
		return null;
	}

	$term = get_queried_object();
	if ( ! $term instanceof WP_Term || 0 !== (int) $term->parent ) {
		return null;
	}

	if ( ! array_key_exists( $term->term_id, $cache ) ) {
		$children                = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'parent'     => $term->term_id,
				'hide_empty' => true,
				'fields'     => 'ids',
			)
		);
		$cache[ $term->term_id ] = ( ! is_wp_error( $children ) && array() !== $children ) ? $term : null;
	}

	return $cache[ $term->term_id ];
}

/**
 * Le lien « Voir tous les produits » d'un univers a-t-il été suivi ?
 *
 * @return bool
 */
function evasions_is_all_view(): bool {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture seule de la requête publique.
	return null !== evasions_universe_term() && isset( $_GET['tous'] );
}

/**
 * Cette requête doit-elle afficher la page univers plutôt que le listing ?
 *
 * @return bool
 */
function evasions_is_universe(): bool {
	if ( null === evasions_universe_term() || evasions_is_all_view() ) {
		return false;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture seule de la requête publique.
	$asked = array_intersect( array_keys( $_GET ), array_merge( evasions_filter_keys(), array( 'orderby' ) ) );

	return array() === $asked && (int) get_query_var( 'paged' ) < 2;
}

/**
 * Adresse du listing complet d'un univers (« Voir tous les produits »).
 *
 * @param WP_Term $term Universe category.
 * @return string
 */
function evasions_universe_all_url( WP_Term $term ): string {
	$link = get_term_link( $term );

	return is_string( $link ) ? add_query_arg( 'tous', '1', $link ) : evasions_shop_url();
}

/**
 * L'image d'une catégorie (miniature WooCommerce) en grande taille, ou ''.
 *
 * @param WP_Term $term  Category.
 * @param string  $size  Image size.
 * @return string
 */
function evasions_term_image( WP_Term $term, string $size = 'full' ): string {
	$id = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );

	return $id > 0 ? (string) wp_get_attachment_image_url( $id, $size ) : '';
}

/**
 * Le hero de l'univers : image et phrase.
 *
 * Image : celle que le propriétaire a posée sur la catégorie, sinon celle de
 * l'univers de la maquette, sinon aucune (un fond uni). Phrase : la description
 * de la catégorie, sinon la phrase de la maquette, sinon rien.
 *
 * @param WP_Term $term Universe category.
 * `id` est la pièce jointe de la catégorie (0 si l'image vient du thème) : avec elle, le
 * gabarit sert une image responsive (`srcset`) au lieu du fichier d'origine.
 *
 * @return array{image:string,tagline:string,id:int}
 */
function evasions_universe_hero( WP_Term $term ): array {
	$known = evasions_universes()[ $term->slug ] ?? array();
	$id    = (int) get_term_meta( $term->term_id, 'thumbnail_id', true );
	$image = evasions_term_image( $term );
	$id    = '' !== $image ? $id : 0;

	if ( '' === $image && ! empty( $known['image'] ) ) {
		$image = evasions_img( $known['image'] );
	}

	$tagline = trim( wp_strip_all_tags( $term->description ) );

	return array(
		'image'   => $image,
		'tagline' => '' !== $tagline ? $tagline : (string) ( $known['tagline'] ?? '' ),
		'id'      => $id,
	);
}

/**
 * Le bloc éditorial de l'univers, ou null quand la maquette n'en dessine pas pour lui.
 *
 * @param WP_Term $term Universe category.
 * @return array{title:string,text:string,image:string}|null
 */
function evasions_universe_editorial( WP_Term $term ): ?array {
	$block = evasions_universes()[ $term->slug ]['editorial'] ?? null;

	return is_array( $block ) ? $block : null;
}

/**
 * Les sous-catégories de l'univers qui contiennent des produits.
 *
 * @param WP_Term $term Universe category.
 * @return array<int,array{name:string,url:string,image:string}>
 */
function evasions_universe_children( WP_Term $term ): array {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $term->term_id,
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$children = array();
	foreach ( $terms as $child ) {
		$url = get_term_link( $child );

		if ( is_string( $url ) ) {
			$children[] = array(
				'name'  => $child->name,
				'url'   => $url,
				'image' => evasions_term_image( $child, 'thumbnail' ),
			);
		}
	}

	return $children;
}

/**
 * Fil d'Ariane du listing complet : « Accueil › Camping & Randonnée › Tous les produits ».
 *
 * @param array<int,array{0:string,1:string}> $crumbs Breadcrumb items.
 * @return array<int,array{0:string,1:string}>
 */
function evasions_all_view_crumbs( $crumbs ) {
	if ( evasions_is_all_view() ) {
		$crumbs[] = array( __( 'Tous les produits', 'evasions' ), '' );
	}

	return $crumbs;
}
add_filter( 'woocommerce_get_breadcrumb', 'evasions_all_view_crumbs' );
