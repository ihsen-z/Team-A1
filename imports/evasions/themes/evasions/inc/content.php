<?php
/**
 * Données affichées par la homepage et le gabarit : promesses, menus, liens, avis.
 *
 * Règle du projet (§60) : rien n'est inventé. Une promesse, un avis, un
 * réseau social n'apparaît que s'il repose sur quelque chose de réel — un
 * numéro configuré, un avis publié, un compte renseigné. Sinon le bloc
 * disparaît, il n'affiche pas un texte de remplissage.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valeur d'une constante EVASIONS_* du serveur, ou chaîne vide.
 *
 * Les constantes sont posées par le mu-plugin `evasions-env` ; une constante
 * absente ou vide veut dire « non configuré ».
 *
 * @param string $name Constant name.
 * @return string
 */
function evasions_const( string $name ): string {
	return defined( $name ) ? trim( (string) constant( $name ) ) : '';
}

/**
 * Un canal de contact humain est-il configuré ?
 *
 * Sert à conditionner « service client » : sans numéro, la boutique
 * promettrait une écoute qu'aucun client ne pourrait joindre.
 *
 * @return bool
 */
function evasions_has_contact(): bool {
	if ( class_exists( '\Evasions\Core\Config' ) && '' !== \Evasions\Core\Config::instance()->whatsapp_number() ) {
		return true;
	}

	return '' !== evasions_const( 'EVASIONS_WHATSAPP_NUMBER' ) || '' !== evasions_const( 'EVASIONS_CONTACT_PHONE' );
}

/**
 * Promesses de la barre supérieure.
 *
 * Elles viennent du plugin, où le propriétaire les édite (« EVASIONS →
 * Pages »). Si le plugin est absent, deux promesses que le tunnel COD tient
 * de toute façon prennent le relais.
 *
 * @return array<int, array{icon:string, icon_url:string, label:string, accent:bool}>
 */
function evasions_promises(): array {
	if ( class_exists( '\Evasions\Core\Topbar_Content' ) ) {
		return \Evasions\Core\Topbar_Content::promises();
	}

	return array(
		array(
			'icon'     => 'card',
			'icon_url' => '',
			'label'    => __( 'Paiement à la livraison', 'evasions' ),
			'accent'   => true,
		),
		array(
			'icon'     => 'truck',
			'icon_url' => '',
			'label'    => __( 'Livraison partout en Tunisie', 'evasions' ),
			'accent'   => false,
		),
	);
}

/**
 * Les arguments de réassurance (bandeau et bloc « Pourquoi choisir »).
 *
 * Deux faits que le tunnel COD tient de toute façon (livraison, paiement à la
 * livraison). Le 3e argument (« Produits sélectionnés et testés ») s'édite dans
 * le Customizer (Apparence → Personnaliser → EVASIONS) et reste vide par défaut :
 * c'est une promesse à vérifier (§60).
 *
 * L'argument « service client » est ajouté seulement si un contact est
 * configuré : il dépend de la config, pas d'un texte éditable. Filtrable :
 * `evasions_benefits`.
 *
 * @return array<int, array{icon:string, title:string, sub:string}>
 */
function evasions_benefits(): array {
	// Deux faits appliqués par le tunnel COD.
	$benefits = array(
		array(
			'icon'  => 'truck',
			'title' => __( 'Livraison', 'evasions' ),
			'sub'   => __( 'partout en Tunisie', 'evasions' ),
		),
		array(
			'icon'  => 'card',
			'title' => __( 'Paiement', 'evasions' ),
			'sub'   => __( 'à la livraison', 'evasions' ),
		),
	);

	// 3e argument (« Produits sélectionnés et testés »), éditable dans le
	// Customizer, vide par défaut : rien n'est affirmé sans confirmation (§60).
	$benefit3_title = trim( (string) evasions_setting( 'evasions_benefit3_title' ) );
	if ( '' !== $benefit3_title ) {
		$benefits[] = array(
			'icon'  => 'check',
			'title' => $benefit3_title,
			'sub'   => trim( (string) evasions_setting( 'evasions_benefit3_sub' ) ),
		);
	}

	if ( evasions_has_contact() ) {
		$benefits[] = array(
			'icon'  => 'clock',
			'title' => __( 'Service client', 'evasions' ),
			'sub'   => __( 'à votre écoute', 'evasions' ),
		);
	}

	return (array) apply_filters( 'evasions_benefits', $benefits );
}

/**
 * Bandeau « Pourquoi choisir… » des pages boutique, catégorie et univers.
 *
 * Titre, image, couleurs et les quatre arguments (pictogramme, titre,
 * sous-titre) sont éditables depuis le thème (Customizer → EVASIONS → Boutique
 * — pourquoi choisir). Un argument sans titre n'est pas rendu (§60) ; par
 * défaut, les arguments sont ceux de la réassurance (`evasions_benefits()`),
 * qui ne sortent que des faits appliqués par le tunnel COD.
 *
 * @return array{enabled:bool, title:string, items:array<int, array{icon:string, title:string, sub:string}>, colors:array<string,string>}
 */
function evasions_why(): array {
	$title = trim( (string) evasions_setting( 'evasions_why_title' ) );

	$colors = array();
	foreach ( array(
		'title'    => 'evasions_why_color_title',
		'text'     => 'evasions_why_color_text',
		'icon-bg'  => 'evasions_why_color_icon_bg',
		'icon'     => 'evasions_why_color_icon',
	) as $name => $key ) {
		$hex = sanitize_hex_color( (string) evasions_setting( $key ) );
		if ( $hex ) {
			$colors[ $name ] = $hex;
		}
	}

	// Les quatre emplacements du Customizer ; vides, on retombe sur la
	// réassurance de l'accueil pour ne pas laisser le bandeau nu.
	$items = array();
	for ( $i = 1; $i <= 4; $i++ ) {
		$item_title = trim( (string) evasions_setting( 'evasions_why_item' . $i . '_title' ) );

		if ( '' === $item_title ) {
			continue;
		}

		$items[] = array(
			'icon'  => evasions_icon_name( (string) evasions_setting( 'evasions_why_item' . $i . '_icon' ) ),
			'title' => $item_title,
			'sub'   => trim( (string) evasions_setting( 'evasions_why_item' . $i . '_sub' ) ),
		);
	}

	if ( array() === $items ) {
		$items = evasions_benefits();
	}

	return array(
		'enabled' => (bool) evasions_setting( 'evasions_why_enabled' ),
		/* translators: %s: site name */
		'title'   => '' !== $title ? $title : sprintf( __( 'Pourquoi choisir %s ?', 'evasions' ), get_bloginfo( 'name' ) ),
		'items'   => $items,
		'colors'  => $colors,
	);
}

/**
 * Un nom de pictogramme connu du thème, ou « check » par repli.
 *
 * Le Customizer propose une liste fermée, mais un réglage importé d'ailleurs
 * pourrait nommer un pictogramme absent : on ne rend jamais un SVG vide.
 *
 * @param string $name Nom demandé.
 * @return string
 */
function evasions_icon_name( string $name ): string {
	$name = trim( $name );

	return isset( evasions_icon_paths()[ $name ] ) ? $name : 'check';
}

/**
 * Accroche de l'accueil : texte éditable dans le Customizer, avec repli.
 *
 * @return array{title_lead:string, title_em:string, subtitle:string, cta_primary:string, cta_secondary:string}
 */
function evasions_hero(): array {
	return array(
		'title_lead'    => (string) evasions_setting( 'evasions_hero_title_lead' ),
		'title_em'      => (string) evasions_setting( 'evasions_hero_title_em' ),
		'subtitle'      => (string) evasions_setting( 'evasions_hero_subtitle' ),
		'cta_primary'   => (string) evasions_setting( 'evasions_hero_cta_primary' ),
		'cta_secondary' => (string) evasions_setting( 'evasions_hero_cta_secondary' ),
	);
}

/**
 * Bannière du milieu de page : texte éditable dans le Customizer, avec repli.
 *
 * @return array{title:string, text:string, cta:string}
 */
function evasions_home_banner(): array {
	return array(
		'title' => (string) evasions_setting( 'evasions_banner_title' ),
		'text'  => (string) evasions_setting( 'evasions_banner_text' ),
		'cta'   => (string) evasions_setting( 'evasions_banner_cta' ),
	);
}

/**
 * Bandeau « appel à l'aventure » de la fiche produit (maquette CTA Aventure).
 *
 * Tout est éditable depuis le thème (Customizer → EVASIONS → Fiche produit) :
 * textes, couleurs, image de fond, libellé et lien du bouton. Le thème ne
 * calcule rien ; le lien par défaut est simplement la boutique.
 *
 * Une couleur vide n'est pas rendue : le jeton --ev-* de la feuille de style
 * s'applique alors (§2.1, aucune couleur en dur dans les gabarits).
 *
 * @return array{enabled:bool, title:string, text:string, button:string, url:string, colors:array<string,string>}
 */
function evasions_product_cta(): array {
	$url = trim( (string) evasions_setting( 'evasions_cta_url' ) );

	$colors = array();
	foreach ( array(
		'title'    => 'evasions_cta_color_title',
		'text'     => 'evasions_cta_color_text',
		'btn'      => 'evasions_cta_color_btn',
		'btn-text' => 'evasions_cta_color_btn_text',
	) as $name => $key ) {
		$hex = sanitize_hex_color( (string) evasions_setting( $key ) );
		if ( $hex ) {
			$colors[ $name ] = $hex;
		}
	}

	return array(
		'enabled' => (bool) evasions_setting( 'evasions_cta_enabled' ),
		'title'   => trim( (string) evasions_setting( 'evasions_cta_title' ) ),
		'text'    => trim( (string) evasions_setting( 'evasions_cta_text' ) ),
		'button'  => trim( (string) evasions_setting( 'evasions_cta_button' ) ),
		'url'     => '' !== $url ? $url : evasions_shop_url(),
		'colors'  => $colors,
	);
}

/**
 * Formulaire d'inscription à la newsletter du bloc « communauté ».
 *
 * Le thème ne stocke aucune adresse : le formulaire poste chez le fournisseur
 * d'e-mailing. Son adresse d'action vient du Customizer (« Communauté —
 * adresse du formulaire »), sinon de la constante
 * `EVASIONS_NEWSLETTER_FORM_ACTION` (pont `.env`, mu-plugin) ; le nom du champ
 * e-mail vient de `EVASIONS_NEWSLETTER_FIELD`. Rien n'est stocké côté
 * WordPress : il n'y a donc pas de donnée métier à faire survivre au thème.
 *
 * Le champ est toujours rendu, comme dans la maquette. Tant qu'aucune adresse
 * n'est renseignée, `action` est vide : le formulaire recharge la page sans
 * rien envoyer, et un avertissement le signale en administration.
 *
 * @return array{action:string, field:string, placeholder:string}
 */
function evasions_newsletter_form(): array {
	$action = trim( (string) evasions_setting( 'evasions_community_form_action' ) );

	if ( '' === $action && defined( 'EVASIONS_NEWSLETTER_FORM_ACTION' ) ) {
		$action = trim( (string) EVASIONS_NEWSLETTER_FORM_ACTION );
	}

	if ( '' !== $action && ! wp_http_validate_url( $action ) ) {
		$action = '';
	}

	$field = defined( 'EVASIONS_NEWSLETTER_FIELD' ) ? trim( (string) EVASIONS_NEWSLETTER_FIELD ) : '';

	return array(
		'action'      => $action,
		// Mailchimp et Brevo nomment ce champ « EMAIL » : repli raisonnable.
		'field'       => '' !== $field ? $field : 'EMAIL',
		'placeholder' => trim( (string) evasions_setting( 'evasions_community_placeholder' ) ),
	);
}

/**
 * Bloc « communauté » de l'accueil (maquette « EVASIONS Newsletter »).
 *
 * Même principe que `evasions_product_cta()` : textes, couleurs et image
 * éditables depuis le thème, aucune donnée métier. Le bloc porte le formulaire
 * d'inscription (`evasions_newsletter_form()`), comme la maquette.
 *
 * @return array{enabled:bool, title:string, text:string, button:string, colors:array<string,string>}
 */
function evasions_community(): array {
	$colors = array();
	foreach ( array(
		'title'    => 'evasions_community_color_title',
		'text'     => 'evasions_community_color_text',
		'panel'    => 'evasions_community_color_panel',
		'btn'      => 'evasions_community_color_btn',
		'btn-text' => 'evasions_community_color_btn_text',
	) as $name => $key ) {
		$hex = sanitize_hex_color( (string) evasions_setting( $key ) );
		if ( $hex ) {
			$colors[ $name ] = $hex;
		}
	}

	return array(
		'enabled' => (bool) evasions_setting( 'evasions_community_enabled' ),
		'title'   => trim( (string) evasions_setting( 'evasions_community_title' ) ),
		'text'    => trim( (string) evasions_setting( 'evasions_community_text' ) ),
		'button'  => trim( (string) evasions_setting( 'evasions_community_button' ) ),
		'colors'  => $colors,
	);
}

/**
 * Titre d'une section d'accueil, éditable, avec repli.
 *
 * @param string $which    featured | reviews | universes.
 * @param string $fallback Texte par défaut du thème.
 * @return string
 */
function evasions_home_section_title( string $which, string $fallback ): string {
	$map = array(
		'featured'  => 'evasions_title_featured',
		'reviews'   => 'evasions_title_reviews',
		'universes' => 'evasions_title_universes',
	);

	$value = isset( $map[ $which ] ) ? trim( (string) evasions_setting( $map[ $which ] ) ) : '';

	return '' !== $value ? $value : $fallback;
}

/**
 * Slogan du pied de page, éditable, avec repli.
 *
 * @return string
 */
function evasions_home_footer_tagline(): string {
	$tagline = trim( (string) evasions_setting( 'evasions_footer_tagline' ) );

	return '' !== $tagline ? $tagline : __( 'Évasion · Nature · Aventure · Liberté', 'evasions' );
}

/**
 * URL de la boutique, ou de l'accueil si WooCommerce est absent.
 *
 * @return string
 */
function evasions_shop_url(): string {
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$url = wc_get_page_permalink( 'shop' );
		if ( $url ) {
			return $url;
		}
	}

	return home_url( '/' );
}

/**
 * URL d'une catégorie produit, ou de la boutique si elle n'existe pas.
 *
 * @param string $slug Category slug.
 * @return string
 */
function evasions_category_url( string $slug ): string {
	$link = get_term_link( $slug, 'product_cat' );

	return is_wp_error( $link ) ? evasions_shop_url() : (string) $link;
}

/**
 * Entrées de navigation par défaut : les deux univers, puis deux tris.
 *
 * Utilisées tant qu'aucun menu n'est affecté à l'emplacement « principal ».
 * `pill` marque celles qui figurent aussi dans la rangée de pastilles mobile.
 *
 * @return array<int, array{label:string, url:string, pill:bool}>
 */
function evasions_nav_items(): array {
	$shop = evasions_shop_url();

	return array(
		array(
			'label' => __( 'Plage', 'evasions' ),
			'url'   => evasions_category_url( 'plage' ),
			'pill'  => true,
		),
		array(
			'label' => __( 'Camping & Randonnée', 'evasions' ),
			'url'   => evasions_category_url( 'camping-randonnee' ),
			'pill'  => true,
		),
		array(
			'label' => __( 'Nouveautés', 'evasions' ),
			'url'   => add_query_arg( 'orderby', 'date', $shop ),
			'pill'  => true,
		),
		array(
			'label' => __( 'Meilleures ventes', 'evasions' ),
			'url'   => add_query_arg( 'orderby', 'popularity', $shop ),
			'pill'  => false,
		),
	);
}

/**
 * Réseaux sociaux configurés, dans l'ordre du design.
 *
 * @return array<int, array{key:string, label:string, short:string, url:string}>
 */
function evasions_socials(): array {
	$map = array(
		'facebook'  => array( 'Facebook', 'FB' ),
		'instagram' => array( 'Instagram', 'IG' ),
		'tiktok'    => array( 'TikTok', 'TT' ),
		'youtube'   => array( 'YouTube', 'YT' ),
	);

	$out = array();
	foreach ( $map as $key => $labels ) {
		// Même priorité que le numéro WhatsApp : la constante du serveur l'emporte,
		// sinon l'option saisie en administration. Ainsi le propriétaire renseigne
		// ses réseaux sans éditer un fichier, et le bloc reste masqué s'il n'en a pas (§60).
		$url = evasions_const( 'EVASIONS_SOCIAL_' . strtoupper( $key ) );
		if ( '' === $url ) {
			$url = trim( (string) evasions_setting( 'evasions_social_' . $key ) );
		}
		if ( '' !== $url && wp_http_validate_url( $url ) ) {
			$out[] = array(
				'key'   => $key,
				'label' => $labels[0],
				'short' => $labels[1],
				'url'   => $url,
			);
		}
	}

	return $out;
}

/**
 * Lien vers une page du site par son identifiant court, si elle est publiée.
 *
 * @param string $slug  Page slug.
 * @param string $label Link label.
 * @return array{label:string, url:string}|null
 */
function evasions_page_link( string $slug, string $label ): ?array {
	$page = get_page_by_path( $slug );

	if ( ! $page instanceof WP_Post || 'publish' !== $page->post_status ) {
		return null;
	}

	return array(
		'label' => $label,
		'url'   => (string) get_permalink( $page ),
	);
}

/**
 * Colonnes de liens du pied de page, groupées par thème.
 *
 * Deux colonnes : « La boutique » (les écrans éditoriaux — Packs, Le terrain,
 * Nos engagements) et « Aide » (suivi, informations pratiques, contact). Chaque
 * lien passe par `evasions_page_link()` : une page absente n'est pas listée, un
 * lien mort est un 404 offert au client là où il se rassure (§22). Une colonne
 * dont aucune page n'existe encore disparaît entièrement.
 *
 * @return array<int, array{title:string, links:array<int, array{label:string, url:string}>}>
 */
function evasions_footer_columns(): array {
	$boutique = array_values(
		array_filter(
			array(
				evasions_page_link( 'packs', __( 'Packs', 'evasions' ) ),
				evasions_page_link( 'le-terrain', __( 'Le terrain', 'evasions' ) ),
				evasions_page_link( 'nos-engagements', __( 'Nos engagements', 'evasions' ) ),
			)
		)
	);

	$aide = array_values(
		array_filter(
			array(
				evasions_page_link( 'suivi-commande', __( 'Suivi de commande', 'evasions' ) ),
				evasions_page_link( 'a-propos', __( 'À propos', 'evasions' ) ),
				evasions_page_link( 'livraison', __( 'Livraison', 'evasions' ) ),
				evasions_page_link( 'retours', __( 'Retours', 'evasions' ) ),
				evasions_page_link( 'faq', __( 'FAQ', 'evasions' ) ),
				evasions_page_link( 'contact', __( 'Contact', 'evasions' ) ),
			)
		)
	);

	$columns = array();

	if ( array() !== $boutique ) {
		$columns[] = array(
			'title' => __( 'La boutique', 'evasions' ),
			'links' => $boutique,
		);
	}

	if ( array() !== $aide ) {
		$columns[] = array(
			'title' => __( 'Aide', 'evasions' ),
			'links' => $aide,
		);
	}

	return $columns;
}

/**
 * Liens utiles du pied de page, à plat : uniquement les pages qui existent.
 *
 * Union des colonnes de `evasions_footer_columns()`. Conservée pour la
 * compatibilité et le contrôle « aucun lien mort » (§22).
 *
 * @return array<int, array{label:string, url:string}>
 */
function evasions_footer_links(): array {
	$links = array();

	foreach ( evasions_footer_columns() as $column ) {
		foreach ( $column['links'] as $link ) {
			$links[] = $link;
		}
	}

	return $links;
}

/**
 * Liens légaux : CGV, confidentialité, mentions — ceux qui existent.
 *
 * @return array<int, array{label:string, url:string}>
 */
function evasions_legal_links(): array {
	$links = array();

	if ( function_exists( 'wc_terms_and_conditions_page_id' ) ) {
		$terms_id = (int) wc_terms_and_conditions_page_id();
		if ( $terms_id > 0 && 'publish' === get_post_status( $terms_id ) ) {
			$links[] = array(
				'label' => __( 'CGV', 'evasions' ),
				'url'   => (string) get_permalink( $terms_id ),
			);
		}
	}

	$privacy = get_privacy_policy_url();
	if ( '' !== $privacy ) {
		$links[] = array(
			'label' => __( 'Politique de confidentialité', 'evasions' ),
			'url'   => $privacy,
		);
	}

	$legal = evasions_page_link( 'mentions-legales', __( 'Mentions légales', 'evasions' ) );
	if ( $legal ) {
		$links[] = $legal;
	}

	return $links;
}

/**
 * Avis clients réels : les meilleurs avis publiés sur les produits.
 *
 * Lus dans WooCommerce (commentaires de type « review », approuvés, notés 4 ou
 * 5). Le design montre des avis de remplissage ; ici, tant qu'aucun vrai avis
 * n'existe, la liste est vide et la section n'est pas affichée (§60).
 *
 * Le nom est abrégé (« Sami B. ») : un avis public n'a pas à porter un nom
 * complet.
 *
 * @param int $limit Number of reviews.
 * @return array<int, array{rating:int, text:string, author:string, url:string}>
 */
function evasions_reviews( int $limit = 3 ): array {
	$comments = get_comments(
		array(
			'type'       => 'review',
			'status'     => 'approve',
			'post_status' => 'publish',
			'number'     => $limit,
			'orderby'    => 'comment_date_gmt',
			'order'      => 'DESC',
			'meta_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- 3 lignes, page d'accueil mise en cache par l'hébergeur.
				array(
					'key'     => 'rating',
					'value'   => 4,
					'compare' => '>=',
					'type'    => 'NUMERIC',
				),
			),
		)
	);

	$reviews = array();
	foreach ( $comments as $comment ) {
		$text = trim( wp_strip_all_tags( (string) $comment->comment_content ) );
		if ( '' === $text ) {
			continue;
		}

		$parts  = preg_split( '/\s+/', trim( (string) $comment->comment_author ) ) ?: array();
		$author = (string) ( $parts[0] ?? '' );
		if ( count( $parts ) > 1 ) {
			// Initiale du DÉBUT du nom de famille : « Sami Ben Salah » devient « Sami B. », pas « Sami S. ».
			$author .= ' ' . mb_strtoupper( mb_substr( (string) $parts[1], 0, 1 ) ) . '.';
		}

		$reviews[] = array(
			'rating' => (int) get_comment_meta( (int) $comment->comment_ID, 'rating', true ),
			'text'   => wp_trim_words( $text, 34 ),
			'author' => $author,
			'url'    => (string) get_comment_link( $comment ),
		);
	}

	return $reviews;
}

/**
 * Les deux univers du catalogue (§7-8 du document directeur), par identifiant de catégorie.
 *
 * `sub` : la ligne de la tuile de la homepage. `tagline` : la phrase sous le
 * titre de la page de l'univers (maquette « 1b »). `editorial` : le bloc photo
 * de la même page. Les textes de la maquette ne sont posés que pour l'univers
 * qu'elle dessine (Camping & Randonnée) : Plage n'a pas de maquette, elle ne
 * reçoit donc pas de texte écrit à sa place.
 *
 * L'éditorial de Camping (titre et texte) et le sous-titre de Plage s'éditent
 * dans le Customizer ; l'image de chaque univers aussi (sinon celle du thème).
 *
 * @return array<string,array<string,mixed>>
 */
function evasions_universes(): array {
	$camping_editorial = array(
		'title' => trim( (string) evasions_setting( 'evasions_camping_title' ) ),
		'text'  => trim( (string) evasions_setting( 'evasions_camping_text' ) ),
	);

	$plage_sub = trim( (string) evasions_setting( 'evasions_plage_sub' ) );

	return array(
		'plage'             => array(
			'title'   => __( 'Plage', 'evasions' ),
			'sub'     => $plage_sub,
			'image'   => 'univers-plage.webp',
			'img_key' => 'universe_plage',
		),
		'camping-randonnee' => array(
			'title'     => __( 'Camping & Randonnée', 'evasions' ),
			'sub'       => __( 'Nature · Aventure · Liberté', 'evasions' ),
			'tagline'   => __( 'Équipez vos prochaines aventures.', 'evasions' ),
			'image'     => 'univers-camping.webp',
			'img_key'   => 'universe_camping',
			'editorial' => array(
				'title' => $camping_editorial['title'],
				'text'  => $camping_editorial['text'],
				'image' => 'banner-randonneur.webp',
			),
		),
	);
}
