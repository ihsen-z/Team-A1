<?php
/**
 * Listing produits et filtres — maquette « 1c » (mobile 390 px, desktop 1440 px).
 *
 * ── Méthode ─────────────────────────────────────────────────────────────────
 *
 * WooCommerce fait déjà tout le travail de requête : `min_price` / `max_price`
 * pour le prix, `filter_{attribut}` pour les attributs, `orderby` pour le tri,
 * la pagination. Le thème n'ajoute qu'UN filtre absent du classique — « En
 * stock » (`en_stock=1`) — et présente le reste. Les liens produits par le
 * listing sont donc les mêmes que ceux de WooCommerce : ils se partagent, se
 * mettent en favori et fonctionnent sans JavaScript.
 *
 * Le formulaire de filtres est un simple GET. Les cases des attributs
 * s'envoient sous la forme `ev_f[couleur][]=vert` ; un redirect
 * (`evasions_canonical_filters_redirect`) les convertit vers l'écriture native
 * `filter_couleur=vert,bleu` de WooCommerce. Sans JavaScript, le filtre marche
 * donc quand même, et l'adresse finale reste propre.
 *
 * ── Rien n'est inventé (§60) ────────────────────────────────────────────────
 *
 * Chaque filtre affiché vient d'une donnée réelle : bornes de prix des produits,
 * attributs et valeurs qui existent, catégories qui contiennent des produits.
 * Aucun groupe (« Léger », « Étanche »…) n'est écrit dans le code : la maquette
 * en montre à titre d'exemple, la boutique en propose selon ses attributs. Le
 * cœur « favoris » de la maquette n'est pas repris : rien ne le fait fonctionner.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Cette requête est-elle un listing de produits (boutique, catégorie, étiquette, recherche) ?
 *
 * @return bool
 */
function evasions_is_listing(): bool {
	if ( ! function_exists( 'is_shop' ) ) {
		return false;
	}

	return is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) );
}

/**
 * Nombre de produits par page : un multiple de 2 (mobile) et de 3 (desktop avec filtres).
 *
 * @return int
 */
function evasions_products_per_page(): int {
	return 12;
}
add_filter( 'loop_shop_per_page', 'evasions_products_per_page', 20 );

/**
 * Le gabarit d'un produit dans une boucle de listing est celui du thème.
 *
 * Utilise le filtre `wc_get_template_part` prévu par WooCommerce pour cela :
 * on remplace `content-product.php` par une carte du thème, seulement dans les
 * listings (les shortcodes de contenu gardent le rendu de WooCommerce).
 *
 * @param string $template Path found by WooCommerce.
 * @param string $slug     Template slug.
 * @param string $name     Template name.
 * @return string
 */
function evasions_listing_item_template( $template, $slug, $name ) {
	if ( 'content' === $slug && 'product' === $name && evasions_is_listing() ) {
		return get_theme_file_path( 'template-parts/listing-item.php' );
	}

	return $template;
}
add_filter( 'wc_get_template_part', 'evasions_listing_item_template', 10, 3 );

/**
 * Les clés de la requête qui sont des filtres (par opposition au tri, à la recherche, à la page).
 *
 * @return string[]
 */
function evasions_filter_keys(): array {
	$keys = array( 'min_price', 'max_price', 'en_stock', 'ev_f' );

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture seule de la requête publique.
	foreach ( array_keys( $_GET ) as $key ) {
		if ( is_string( $key ) && 0 === strpos( $key, 'filter_' ) ) {
			$keys[] = $key;
		}
	}

	return $keys;
}

/**
 * Déclare à evasions-core les paramètres d'adresse inventés par ce thème.
 *
 * Le plugin est seul juge de ce qui est indexable (`\Evasions\Core\Seo` : un
 * seul propriétaire pour tout ce qui sort dans le `<head>`), et le thème ne
 * pose aucune balise. Mais le plugin ne peut pas deviner les noms qu'un thème
 * donne à ses propres paramètres : `en_stock` (filtre absent de WooCommerce),
 * `ev_f` (écriture du formulaire avant redirection), `ev_submit` (marqueur
 * d'envoi) et `tous` (« Voir tous les produits », qui redonne la même
 * catégorie sous une autre présentation). Les déclarer ici, c'est nommer ce
 * qu'on a inventé — pas décider de l'indexation (audit SEO-03).
 *
 * Si evasions-core est absent, ce filtre n'est simplement jamais appelé.
 *
 * @param array<int,string> $names Noms déjà connus du plugin.
 * @return array<int,string>
 */
function evasions_declare_facet_params( $names ): array {
	return array_merge( (array) $names, array( 'en_stock', 'ev_f', 'ev_submit', 'tous' ) );
}
add_filter( 'evasions_seo_facet_params', 'evasions_declare_facet_params' );

/**
 * Filtre « En stock » : ajouté à la requête principale de WooCommerce.
 *
 * @param WP_Query $query Main product query.
 * @return void
 */
function evasions_stock_filter( $query ): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- filtre public en lecture seule.
	if ( empty( $_GET['en_stock'] ) ) {
		return;
	}

	$meta   = array_filter( (array) $query->get( 'meta_query' ) );
	$meta[] = array(
		'key'   => '_stock_status',
		'value' => 'instock',
	);
	$query->set( 'meta_query', $meta );
}
add_action( 'woocommerce_product_query', 'evasions_stock_filter' );

/**
 * Convertit l'adresse de filtres du formulaire en écriture native de WooCommerce.
 *
 * Fonction pure, testable : elle reçoit les paramètres, rend l'adresse.
 *  - `ev_f[couleur][]=vert&ev_f[couleur][]=bleu` → `filter_couleur=vert,bleu` (+ `query_type_couleur=or`) ;
 *  - un paramètre vide ou à zéro (`en_stock=`) disparaît ;
 *  - `paged` est retiré : un nouveau filtre revient à la première page.
 *
 * @param array<string,mixed> $params Query parameters of the request.
 * @param string              $base   URL without query string.
 * @return string
 */
function evasions_canonical_filter_url( array $params, string $base ): string {
	$groups = isset( $params['ev_f'] ) && is_array( $params['ev_f'] ) ? $params['ev_f'] : array();
	unset( $params['ev_f'], $params['paged'] );

	foreach ( $groups as $attribute => $terms ) {
		$attribute = sanitize_key( (string) $attribute );
		$terms     = array_filter( array_map( 'sanitize_title', array_map( 'strval', (array) $terms ) ) );

		if ( '' !== $attribute && array() !== $terms ) {
			$params[ 'filter_' . $attribute ]     = implode( ',', array_unique( $terms ) );
			$params[ 'query_type_' . $attribute ] = 'or';
		}
	}

	$params = array_filter(
		$params,
		static fn( $value ) => ! ( '' === $value || '0' === $value || 0 === $value || array() === $value )
	);

	return add_query_arg( $params, $base );
}

/**
 * Redirige l'envoi du formulaire de filtres vers l'adresse propre.
 *
 * @return void
 */
function evasions_canonical_filters_redirect(): void {
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture seule de la requête publique.
	if ( ! evasions_is_listing() || ( ! isset( $_GET['ev_f'] ) && ! isset( $_GET['ev_submit'] ) ) ) {
		return;
	}

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- nettoyé champ par champ dans evasions_canonical_filter_url().
	$params = wp_unslash( $_GET );
	unset( $params['ev_submit'] );

	$base = remove_query_arg( array_keys( $_GET ), get_pagenum_link( 1, false ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$url  = evasions_canonical_filter_url( $params, $base );
	$url  = evasions_drop_price_extremes( $url );

	wp_safe_redirect( $url, 302 );
	exit;
}
add_action( 'template_redirect', 'evasions_canonical_filters_redirect', 5 );

/**
 * Retire du prix les bornes du catalogue : « de 20 à 300 » quand 20 et 300 sont les extrêmes ne filtre rien.
 *
 * @param string $url Canonical filter URL.
 * @return string
 */
function evasions_drop_price_extremes( string $url ): string {
	$bounds = evasions_price_bounds();

	if ( null === $bounds ) {
		return $url;
	}

	$query = wp_parse_args( (string) wp_parse_url( $url, PHP_URL_QUERY ) );

	if ( isset( $query['min_price'] ) && (float) $query['min_price'] <= $bounds['min'] ) {
		$url = remove_query_arg( 'min_price', $url );
	}

	if ( isset( $query['max_price'] ) && (float) $query['max_price'] >= $bounds['max'] ) {
		$url = remove_query_arg( 'max_price', $url );
	}

	return $url;
}

/**
 * Bornes de prix du catalogue affiché, arrondies à l'entier : le prix le plus bas et le plus haut.
 *
 * Le calcul (requête SQL sur la table d'index de WooCommerce, catégorie et
 * sous-catégories comprises) a été remonté dans evasions-core
 * (`\Evasions\Core\Catalog::price_bounds()`) : c'est une règle de catalogue,
 * elle doit survivre à un changement de thème (audit ARCH-03). Le thème ne
 * fournit plus que le contexte — quelle catégorie est affichée — et présente le
 * résultat.
 *
 * Sans evasions-core, aucune borne : le groupe « Prix » du formulaire de
 * filtres disparaît (`template-parts/listing-filters.php` teste `null`), plutôt
 * que d'afficher un curseur aux bornes inventées (§60).
 *
 * @return array{min:int,max:int}|null Null quand il n'y a rien à filtrer (moins de deux prix distincts).
 */
function evasions_price_bounds(): ?array {
	if ( ! class_exists( '\Evasions\Core\Catalog' ) ) {
		return null;
	}

	$term = is_product_category() ? get_queried_object() : null;

	return \Evasions\Core\Catalog::price_bounds( $term instanceof WP_Term ? $term : null );
}

/**
 * Les groupes d'attributs proposés en filtre : ceux qui existent, avec les valeurs qu'utilisent des produits.
 *
 * @return array<int,array{slug:string,label:string,terms:array<int,array{slug:string,name:string,checked:bool}>}>
 */
function evasions_attribute_filters(): array {
	if ( ! function_exists( 'wc_get_attribute_taxonomies' ) ) {
		return array();
	}

	$chosen = class_exists( 'WC_Query' ) ? WC_Query::get_layered_nav_chosen_attributes() : array();
	$groups = array();

	foreach ( wc_get_attribute_taxonomies() as $attribute ) {
		$taxonomy = wc_attribute_taxonomy_name( $attribute->attribute_name );
		$terms    = get_terms(
			array(
				'taxonomy'   => $taxonomy,
				'hide_empty' => true,
			)
		);

		if ( is_wp_error( $terms ) || array() === $terms ) {
			continue;
		}

		$selected = $chosen[ $taxonomy ]['terms'] ?? array();
		$items    = array();

		foreach ( $terms as $term ) {
			$items[] = array(
				'slug'    => $term->slug,
				'name'    => $term->name,
				'checked' => in_array( $term->slug, $selected, true ),
			);
		}

		$groups[] = array(
			'slug'  => $attribute->attribute_name,
			'label' => '' !== $attribute->attribute_label ? $attribute->attribute_label : wc_attribute_label( $taxonomy ),
			'terms' => $items,
		);
	}

	return $groups;
}

/**
 * Les catégories proposées comme liens de navigation dans la colonne de filtres.
 *
 * Sur la boutique : les catégories de premier niveau qui contiennent des
 * produits. Dans une catégorie : ses sous-catégories. Aucune quand il n'y en a pas.
 *
 * @return array<int,array{name:string,url:string,count:int}>
 */
function evasions_category_links(): array {
	if ( ! taxonomy_exists( 'product_cat' ) ) {
		return array();
	}

	$parent = 0;
	if ( is_product_category() ) {
		$queried = get_queried_object();
		$parent  = $queried instanceof WP_Term ? $queried->term_id : 0;
	} elseif ( ! is_shop() ) {
		return array();
	}

	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $parent,
			'hide_empty' => true,
			'exclude'    => (int) get_option( 'default_product_cat' ),
		)
	);

	if ( is_wp_error( $terms ) ) {
		return array();
	}

	$links = array();
	foreach ( $terms as $term ) {
		$url = get_term_link( $term );

		if ( is_string( $url ) ) {
			$links[] = array(
				'name'  => $term->name,
				'url'   => $url,
				'count' => (int) $term->count,
			);
		}
	}

	return $links;
}

/**
 * Filtres actifs : une pastille par filtre, avec l'adresse qui l'enlève.
 *
 * @return array<int,array{label:string,url:string}>
 */
function evasions_active_filters(): array {
	$base  = get_pagenum_link( 1, false );
	$chips = array();
	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture seule de la requête publique.
	$get   = wp_unslash( $_GET );

	if ( ! empty( $get['en_stock'] ) ) {
		$chips[] = array(
			'label' => __( 'En stock', 'evasions' ),
			'url'   => remove_query_arg( 'en_stock', $base ),
		);
	}

	$bounds = evasions_price_bounds();
	if ( null !== $bounds && ( isset( $get['min_price'] ) || isset( $get['max_price'] ) ) ) {
		$min = isset( $get['min_price'] ) ? max( $bounds['min'], (int) $get['min_price'] ) : $bounds['min'];
		$max = isset( $get['max_price'] ) ? min( $bounds['max'], (int) $get['max_price'] ) : $bounds['max'];

		if ( $min > $bounds['min'] || $max < $bounds['max'] ) {
			$chips[] = array(
				/* translators: 1: minimum price, 2: maximum price */
				'label' => sprintf( __( 'Prix : %1$s – %2$s', 'evasions' ), evasions_plain_price( $min ), evasions_plain_price( $max ) ),
				'url'   => remove_query_arg( array( 'min_price', 'max_price' ), $base ),
			);
		}
	}

	foreach ( evasions_attribute_filters() as $group ) {
		foreach ( $group['terms'] as $term ) {
			if ( ! $term['checked'] ) {
				continue;
			}

			$remaining = array_values(
				array_diff(
					array_map(
						static fn( $t ) => $t['slug'],
						array_filter( $group['terms'], static fn( $t ) => $t['checked'] )
					),
					array( $term['slug'] )
				)
			);
			$key       = 'filter_' . $group['slug'];

			$chips[] = array(
				'label' => $term['name'],
				'url'   => array() === $remaining
					? remove_query_arg( array( $key, 'query_type_' . $group['slug'] ), $base )
					: add_query_arg( $key, implode( ',', $remaining ), $base ),
			);
		}
	}

	return $chips;
}

/**
 * Adresse du listing sans aucun filtre (le tri et la recherche sont conservés).
 *
 * @return string
 */
function evasions_reset_filters_url(): string {
	return remove_query_arg( array_merge( array_keys( evasions_filter_params() ), array( 'paged' ) ), get_pagenum_link( 1, false ) );
}

/**
 * Les paramètres de filtre présents dans la requête, avec leur valeur.
 *
 * @return array<string,mixed>
 */
function evasions_filter_params(): array {
	$params = array();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture seule de la requête publique.
	foreach ( $_GET as $key => $value ) {
		if ( is_string( $key ) && ( in_array( $key, evasions_filter_keys(), true ) || 0 === strpos( $key, 'query_type_' ) ) ) {
			$params[ $key ] = $value;
		}
	}

	return $params;
}

/**
 * Les paramètres de la requête qui ne sont pas des filtres, à reporter dans le formulaire.
 *
 * Un formulaire GET remplace la chaîne de requête de son action : avec des
 * permaliens simples, la catégorie elle-même () en fait partie.
 * Le tri, la recherche et la vue « tous les produits » aussi. La page revient à 1.
 *
 * @return array<string,string>
 */
function evasions_carried_params(): array {
	$carried = array();
	$filters = evasions_filter_params();

	// phpcs:ignore WordPress.Security.NonceVerification.Recommended -- lecture seule de la requête publique.
	foreach ( wp_unslash( $_GET ) as $key => $value ) {
		if ( is_string( $key ) && is_scalar( $value ) && ! array_key_exists( $key, $filters ) && ! in_array( $key, array( 'paged', 'ev_submit' ), true ) ) {
			$carried[ $key ] = (string) $value;
		}
	}

	return $carried;
}

/**
 * Prix sans balise, à l'entier : « 120 TND ».
 *
 * @param int|float $amount Amount.
 * @return string
 */
function evasions_plain_price( $amount ): string {
	return html_entity_decode( wp_strip_all_tags( wc_price( $amount, array( 'decimals' => 0 ) ) ), ENT_QUOTES, 'UTF-8' );
}

/**
 * Titre du listing : celui de la maquette, sinon le nom de la catégorie.
 *
 * @return string
 */
function evasions_listing_title(): string {
	if ( is_search() ) {
		/* translators: %s: search query */
		return sprintf( __( 'Résultats pour « %s »', 'evasions' ), get_search_query( false ) );
	}

	if ( is_shop() || evasions_is_all_view() ) {
		return __( 'Tous les produits', 'evasions' );
	}

	return (string) single_term_title( '', false );
}

/**
 * Les libellés de tri : ceux de la maquette (« Plus populaires »), en français.
 *
 * @param array<string,string> $options Sort options.
 * @return array<string,string>
 */
function evasions_orderby_labels( $options ) {
	$labels = array(
		'menu_order' => __( 'Notre sélection', 'evasions' ),
		'popularity' => __( 'Plus populaires', 'evasions' ),
		'rating'     => __( 'Mieux notés', 'evasions' ),
		'date'       => __( 'Plus récents', 'evasions' ),
		'price'      => __( 'Prix croissant', 'evasions' ),
		'price-desc' => __( 'Prix décroissant', 'evasions' ),
	);

	foreach ( $labels as $key => $label ) {
		if ( isset( $options[ $key ] ) ) {
			$options[ $key ] = $label;
		}
	}

	return $options;
}
add_filter( 'woocommerce_catalog_orderby', 'evasions_orderby_labels' );

/**
 * Pagination : chiffres carrés, flèches « ‹ » et « › » avec un libellé pour les lecteurs d'écran.
 *
 * @param array<string,mixed> $args Pagination arguments.
 * @return array<string,mixed>
 */
function evasions_pagination_args( $args ) {
	$args['prev_text'] = '<span aria-hidden="true">‹</span><span class="screen-reader-text">' . esc_html__( 'Page précédente', 'evasions' ) . '</span>';
	$args['next_text'] = '<span aria-hidden="true">›</span><span class="screen-reader-text">' . esc_html__( 'Page suivante', 'evasions' ) . '</span>';
	$args['end_size']  = 1;
	$args['mid_size']  = 1;

	return $args;
}
add_filter( 'woocommerce_pagination_args', 'evasions_pagination_args' );

/**
 * « 42 produits », sans « affichage de 1 à 12 ».
 *
 * @param int $total Number of products found.
 * @return string
 */
function evasions_products_count_label( int $total ): string {
	/* translators: %d: number of products */
	return sprintf( _n( '%d produit', '%d produits', $total, 'evasions' ), $total );
}

/**
 * La boucle affiche notre barre (titre, compte, tri) : on retire celle de WooCommerce.
 *
 * @return void
 */
function evasions_listing_hooks(): void {
	if ( ! evasions_is_listing() ) {
		return;
	}

	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
	remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );
	remove_action( 'woocommerce_no_products_found', 'wc_no_products_found' );
	add_action( 'woocommerce_no_products_found', 'evasions_no_products_found' );
}
add_action( 'wp', 'evasions_listing_hooks' );

/**
 * Aucun produit : on le dit, et on propose de retirer les filtres quand il y en a.
 *
 * @return void
 */
function evasions_no_products_found(): void {
	echo '<div class="ev-empty"><p>';
	echo esc_html( array() === evasions_active_filters() ? __( 'Aucun produit dans cette sélection pour le moment.', 'evasions' ) : __( 'Aucun produit ne correspond à ces filtres.', 'evasions' ) );
	echo '</p>';

	if ( array() !== evasions_active_filters() ) {
		echo '<a class="ev-btn ev-btn--primary" href="' . esc_url( evasions_reset_filters_url() ) . '">' . esc_html__( 'Réinitialiser les filtres', 'evasions' ) . '</a>';
	}

	echo '</div>';
}

/**
 * Feuille de style et script du listing.
 *
 * @return void
 */
function evasions_listing_assets(): void {
	if ( ! evasions_is_listing() && ! evasions_is_universe() ) {
		return;
	}

	wp_enqueue_style(
		'evasions-listing',
		get_template_directory_uri() . '/assets/css/listing.css',
		array( 'evasions' ),
		evasions_asset_version( 'assets/css/listing.css' )
	);
}
add_action( 'wp_enqueue_scripts', 'evasions_listing_assets', 30 );

/**
 * Les pages boutique, catégorie et univers finissent par le bandeau
 * « Pourquoi choisir… » (l'accueil, lui, finit par le bloc « communauté »).
 *
 * @return void
 */
function evasions_listing_footer_band(): void {
	get_template_part( 'template-parts/why' );
}
