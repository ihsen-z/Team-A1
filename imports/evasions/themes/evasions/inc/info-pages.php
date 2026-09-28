<?php
/**
 * Pages d'information : Packs, FAQ, CGV (maquette « EVASIONS Pages Infos »).
 *
 * Le corps de ces trois pages est mis en scène par le thème
 * (`page-packs.php`, `page-faq.php`, `page-cgv.php`) ; l'en-tête et le pied de
 * page ne changent pas. Le **contenu** reste celui de la page WordPress,
 * installée par evasions-core et modifiable en administration : ces fonctions
 * ne font que **lire** ce contenu et le découper (titres, questions, articles)
 * pour l'habiller. Rien n'est écrit en dur, donc rien n'est inventé (§60) : si
 * la page ne suit pas la structure attendue, on affiche le contenu tel quel.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Le gabarit d'une page d'information est-il à l'écran ?
 *
 * @param string $slug packs | faq | cgv.
 * @return bool
 */
function evasions_is_info_page( string $slug ): bool {
	return is_page() && get_post_field( 'post_name', get_queried_object_id() ) === $slug;
}

/**
 * Feuille de style des trois pages d'information, et elle seule.
 *
 * @return void
 */
function evasions_info_assets(): void {
	if ( evasions_is_info_page( 'packs' ) || evasions_is_info_page( 'faq' ) || evasions_is_info_page( 'cgv' ) ) {
		wp_enqueue_style(
			'evasions-info',
			get_template_directory_uri() . '/assets/css/info.css',
			array( 'evasions' ),
			evasions_asset_version( 'assets/css/info.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_info_assets', 30 );

/**
 * Contenu de la page courante, filtres WordPress appliqués.
 *
 * @return string
 */
function evasions_page_content(): string {
	return (string) apply_filters( 'the_content', (string) get_post_field( 'post_content', get_queried_object_id() ) );
}

/**
 * Découpe un contenu en sections de niveau `$tag`.
 *
 * Rend une liste de { title, html } : le texte du titre, puis tout ce qui le
 * suit jusqu'au titre suivant. Ce qui précède le premier titre est rendu à
 * part, sous la clé `intro` du premier élément retourné par l'appelant : il
 * s'agit en général d'une note d'avertissement.
 *
 * @param string $html Contenu HTML.
 * @param string $tag  h2 | h3.
 * @return array{intro:string, sections:array<int, array{title:string, html:string}>}
 */
function evasions_split_by_heading( string $html, string $tag ): array {
	$parts = preg_split( '#<' . $tag . '\b[^>]*>(.*?)</' . $tag . '>#is', $html, -1, PREG_SPLIT_DELIM_CAPTURE );

	if ( ! is_array( $parts ) || count( $parts ) < 3 ) {
		return array(
			'intro'    => $html,
			'sections' => array(),
		);
	}

	$intro    = (string) array_shift( $parts );
	$sections = array();

	// Après le découpage, les éléments vont par deux : titre, puis contenu.
	for ( $i = 0; $i + 1 < count( $parts ); $i += 2 ) {
		$title = trim( wp_strip_all_tags( (string) $parts[ $i ] ) );

		if ( '' === $title ) {
			continue;
		}

		$sections[] = array(
			'title' => $title,
			'html'  => trim( (string) $parts[ $i + 1 ] ),
		);
	}

	return array(
		'intro'    => trim( $intro ),
		'sections' => $sections,
	);
}

/**
 * Questions de la FAQ, groupées par rubrique.
 *
 * Structure attendue de la page : un `<h2>` par rubrique, un `<h3>` par
 * question, la réponse suivant la question. Une page sans `<h3>` ne donne
 * aucune question : le gabarit affiche alors le contenu tel quel.
 *
 * @return array<int, array{title:string, items:array<int, array{q:string, a:string}>}>
 */
function evasions_faq_groups(): array {
	$top    = evasions_split_by_heading( evasions_page_content(), 'h2' );
	$blocks = array() !== $top['sections']
		? $top['sections']
		: array( array( 'title' => '', 'html' => $top['intro'] ) );

	$groups = array();

	foreach ( $blocks as $block ) {
		$inner = evasions_split_by_heading( $block['html'], 'h3' );

		if ( array() === $inner['sections'] ) {
			continue;
		}

		$items = array();
		foreach ( $inner['sections'] as $section ) {
			$items[] = array(
				'q' => $section['title'],
				'a' => $section['html'],
			);
		}

		$groups[] = array(
			'title' => $block['title'],
			'items' => $items,
		);
	}

	return $groups;
}

/**
 * Articles des CGV : numéro, titre et corps.
 *
 * Structure attendue : un `<h2>` par article, numéroté ou non (« 4. Paiement »
 * comme « Paiement »). Le numéro affiché suit l'ordre de la page ; celui écrit
 * dans le titre, s'il existe, est retiré du libellé pour ne pas le doubler.
 *
 * @return array{intro:string, articles:array<int, array{num:int, anchor:string, title:string, html:string}>}
 */
function evasions_cgv_articles(): array {
	$split = evasions_split_by_heading( evasions_page_content(), 'h2' );

	$articles = array();
	foreach ( $split['sections'] as $index => $section ) {
		$num   = $index + 1;
		$title = (string) preg_replace( '/^\s*\d+\s*[.)–—-]\s*/u', '', $section['title'] );

		$articles[] = array(
			'num'    => $num,
			'anchor' => 'cgv-' . $num,
			'title'  => '' !== trim( $title ) ? trim( $title ) : $section['title'],
			'html'   => $section['html'],
		);
	}

	return array(
		'intro'    => $split['intro'],
		'articles' => $articles,
	);
}

/**
 * Pictogramme d'un article de CGV, choisi d'après son titre.
 *
 * L'ordre des articles varie d'une boutique à l'autre : un pictogramme fixé au
 * rang finirait par illustrer « Prix » avec une tente. On reconnaît donc le
 * sujet dans le titre, avec un document comme repli neutre.
 *
 * @param string $title Titre de l'article.
 * @return string
 */
function evasions_cgv_icon( string $title ): string {
	$map = array(
		'paiement'   => 'card',
		'payer'      => 'card',
		'prix'       => 'tag',
		'tarif'      => 'tag',
		'livraison'  => 'truck',
		'expédition' => 'truck',
		'retour'     => 'return',
		'rétractation' => 'return',
		'commande'   => 'bag',
		'panier'     => 'bag',
		'produit'    => 'tent',
		'garantie'   => 'shield',
		'donnée'     => 'lock',
		'personnel'  => 'lock',
		'confidentialité' => 'lock',
		'droit'      => 'scale',
		'litige'     => 'scale',
		'juridiction' => 'scale',
	);

	$needle = function_exists( 'mb_strtolower' ) ? mb_strtolower( $title ) : strtolower( $title );

	foreach ( $map as $word => $icon ) {
		if ( false !== strpos( $needle, $word ) ) {
			return $icon;
		}
	}

	return 'doc';
}

/**
 * Packs à présenter : les produits de la catégorie « packs ».
 *
 * Le thème ne décide ni du prix ni de la composition : il affiche les produits
 * que la boutique range dans cette catégorie. Sans catégorie ni produit, la
 * page retombe sur son contenu rédigé (§60 : pas de faux pack).
 *
 * @return array{terms:array<int, WP_Term>, products:array<int, WC_Product>}
 */
function evasions_packs_products(): array {
	$empty = array(
		'terms'    => array(),
		'products' => array(),
	);

	if ( ! function_exists( 'wc_get_products' ) ) {
		return $empty;
	}

	$parent = get_term_by( 'slug', 'packs', 'product_cat' );

	if ( ! $parent instanceof WP_Term ) {
		return $empty;
	}

	$products = wc_get_products(
		array(
			'status'   => 'publish',
			'limit'    => 24,
			'orderby'  => 'menu_order',
			'order'    => 'ASC',
			'category' => array( $parent->slug ),
		)
	);

	if ( ! is_array( $products ) || array() === $products ) {
		return $empty;
	}

	// Pastilles de filtre : les sous-catégories qui contiennent vraiment un pack.
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => $parent->term_id,
			'hide_empty' => true,
		)
	);

	return array(
		'terms'    => is_wp_error( $terms ) ? array() : $terms,
		'products' => $products,
	);
}

/**
 * Identifiants des sous-catégories « packs » d'un produit, pour le filtre.
 *
 * @param WC_Product $product Produit.
 * @return string Liste de slugs séparés par une espace.
 */
function evasions_pack_terms( WC_Product $product ): string {
	$slugs = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'slugs' ) );

	return is_wp_error( $slugs ) ? '' : implode( ' ', $slugs );
}
