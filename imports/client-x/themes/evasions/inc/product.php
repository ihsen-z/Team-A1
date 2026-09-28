<?php
/**
 * Fiche produit — maquette « Fiche produit » (mobile 390 px, desktop 1440 px).
 *
 * ── Méthode : les hooks de WooCommerce, pas ses gabarits ───────────────────
 *
 * Le thème ne copie pas `content-single-product.php` ni `tabs.php`. Copier un
 * gabarit WooCommerce, c'est hériter de son ancienne version : à la mise à jour
 * suivante, un correctif de sécurité ou de compatibilité (HPOS, blocs, variations)
 * n'atteint pas la copie. On réordonne donc le résumé par ses hooks, on renomme
 * et on complète les onglets par leur filtre, et la mise en page se joue dans le
 * CSS. Le plugin EVASIONS Core, qui s'accroche aux mêmes hooks, continue de
 * fonctionner.
 *
 * ── Rien n'est inventé (§60) ────────────────────────────────────────────────
 *
 * Prix, stock, note, répartition des étoiles, nombre d'avis : tout vient de
 * WooCommerce. « Retour sous 7 jours », affirmé par la maquette, n'est pas repris
 * tant que la politique de retour n'est pas décidée : le pictogramme n'apparaît
 * que si la page « Retours » existe.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Charge la feuille de style des pages produit, et elle seule.
 *
 * @return void
 */
function evasions_product_assets(): void {
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style(
			'evasions-product',
			get_template_directory_uri() . '/assets/css/product.css',
			array( 'evasions' ),
			evasions_asset_version( 'assets/css/product.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_product_assets', 30 );

/**
 * Fil d'Ariane : « Accueil › Camping & Randonnée › Lampe… ».
 *
 * @param array<string,mixed> $defaults Breadcrumb defaults.
 * @return array<string,mixed>
 */
function evasions_breadcrumb_defaults( $defaults ) {
	$defaults['delimiter']   = '<span class="ev-breadcrumb__sep" aria-hidden="true"> › </span>';
	$defaults['wrap_before'] = '<nav class="woocommerce-breadcrumb ev-breadcrumb" aria-label="' . esc_attr__( 'Fil d’Ariane', 'evasions' ) . '">';
	$defaults['home']        = __( 'Accueil', 'evasions' );

	return $defaults;
}
add_filter( 'woocommerce_breadcrumb_defaults', 'evasions_breadcrumb_defaults' );

/**
 * Réordonne le résumé du produit.
 *
 * Ordre du design : titre, note, prix, stock, points forts, formulaire d'achat,
 * réassurance. Les hooks par défaut de WooCommerce sont retirés puis remplacés
 * par des équivalents qui écrivent le balisage du design.
 *
 * Le formulaire de commande express du plugin s'insère à la priorité 26, entre
 * les points forts et la réassurance, comme la maquette « Fiche Produit &
 * Tunnel » le prescrit. C'est le plugin qui le pose et qui décide s'il sort ;
 * le thème ne fait que constater. Voir `evasions_express_order_enabled()`.
 *
 * @return void
 */
function evasions_product_hooks(): void {
	if ( ! function_exists( 'is_product' ) || ! is_product() ) {
		return;
	}

	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	add_action( 'woocommerce_before_single_product_summary', 'evasions_single_badge', 10 );

	foreach ( array(
		array( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 ),
		array( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 ),
		array( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 ),
		array( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 ),
		array( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 ),
		array( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 ),
		array( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 ),
	) as $hook ) {
		remove_action( $hook[0], $hook[1], $hook[2] );
	}

	add_action( 'woocommerce_single_product_summary', 'evasions_single_rating', 10 );
	add_action( 'woocommerce_single_product_summary', 'evasions_single_price', 15 );
	add_action( 'woocommerce_single_product_summary', 'evasions_single_stock', 18 );
	add_action( 'woocommerce_single_product_summary', 'evasions_single_weight', 19 );
	add_action( 'woocommerce_single_product_summary', 'evasions_single_highlights', 20 );
	add_action( 'woocommerce_after_single_product_summary', 'evasions_product_aside', 2 );
	add_action( 'woocommerce_after_single_product_summary', 'evasions_single_related', 20 );

}
add_action( 'wp', 'evasions_product_hooks' );

/**
 * Colonne d'accompagnement de la fiche produit : « pour qui », réassurance, WhatsApp.
 *
 * Ces trois blocs vivaient dans le résumé, serrés entre le prix et le
 * formulaire. Sur desktop, ils laissaient la colonne de la galerie vide sur
 * toute la hauteur du formulaire, et repoussaient le bouton de commande hors de
 * l'écran. Ils sont désormais rendus ensemble, sous les photos.
 *
 * Un seul conteneur, et non trois blocs posés côte à côte dans la grille : le
 * résumé doit enjamber exactement autant de rangées qu'il y a d'éléments à sa
 * gauche. Avec un conteneur, ce nombre vaut toujours deux — les photos, puis
 * cette colonne — quel que soit le nombre de blocs qu'elle contient ou qu'un
 * produit sans « pour qui » et une boutique sans WhatsApp laissent tomber.
 *
 * Sur mobile la grille n'a qu'une colonne : les trois blocs s'empilent après le
 * formulaire de commande, donc sans repousser ni le prix ni le bouton sous le
 * pli (décision du 27 septembre 2026).
 *
 * @return void
 */
function evasions_product_aside(): void {
	ob_start();
	evasions_single_audience();
	evasions_single_reassurance();
	evasions_single_whatsapp();
	$inner = (string) ob_get_clean();

	// Aucun des trois n'a de contenu : pas de conteneur vide dans la grille.
	if ( '' === trim( $inner ) ) {
		return;
	}

	echo '<div class="ev-product-aside">' . $inner . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Déjà échappé par les trois fonctions qui l'ont composé.
}

/**
 * Signale à la feuille de style que le formulaire express est en place.
 *
 * Le thème ne décide rien ici : il expose au CSS ce que le plugin a décidé.
 * C'est cette classe qui permet de masquer le formulaire d'achat de WooCommerce
 * quand la carte express le double — et seulement quand JavaScript répond,
 * puisque la carte express en dépend. Sans JavaScript, la classe `ev-js` est
 * absente, le formulaire de WooCommerce reste affiché et le client peut
 * commander. Voir `assets/css/product.css`.
 *
 * @param array<int,string> $classes Classes du conteneur produit.
 * @return array<int,string>
 */
function evasions_product_express_class( $classes ) {
	if ( function_exists( 'is_product' ) && is_product() && evasions_express_order_enabled() ) {
		$classes[] = 'ev-has-express';
	}

	return $classes;
}
add_filter( 'woocommerce_post_class', 'evasions_product_express_class' );

/**
 * Le formulaire de commande express du plugin doit-il s'afficher sur la fiche produit ?
 *
 * Oui par défaut depuis la maquette « Fiche Produit & Tunnel » (27 septembre
 * 2026), qui le prescrit : le trafic venu d'une publicité atterrit sur une
 * fiche produit et commande sur place. Cette décision renverse celle qui
 * l'éteignait, prise quand la maquette précédente ne le prévoyait pas.
 *
 * Le parcours WooCommerce classique reste affiché juste en dessous : le
 * formulaire express s'ajoute, il ne remplace rien — c'est lui qui sert de
 * repli quand le JavaScript ne s'exécute pas. Pour l'éteindre :
 * `add_filter( 'evasions_express_order_enabled', '__return_false' );`
 *
 * Le thème ne décide pas : il lit. L'interrupteur et le filtre appartiennent au
 * plugin (`Express_Order::enabled()`), qui les applique à ses trois points
 * d'entrée d'un coup — affichage, feuilles et endpoint du jeton. Le thème
 * démontait auparavant le seul hook d'affichage, ce qui laissait l'endpoint
 * répondre dans le vide (audit QA-17). Sans le plugin, la fonction répond
 * « non », et aucun gabarit du thème ne s'appuie dessus.
 *
 * @return bool
 */
function evasions_express_order_enabled(): bool {
	return class_exists( '\Evasions\Core\Express_Order' )
		? \Evasions\Core\Express_Order::enabled()
		: false;
}

/**
 * Le produit courant, ou null.
 *
 * @return WC_Product|null
 */
function evasions_current_product(): ?WC_Product {
	global $product;

	return $product instanceof WC_Product ? $product : null;
}

/**
 * Badge sur la galerie : « −25 % », « Nouveau » ou « Best-seller ».
 *
 * Mêmes règles que sur la homepage (`evasions_product_badge()`).
 *
 * @return void
 */
function evasions_single_badge(): void {
	$product = evasions_current_product();
	$badge   = $product ? evasions_product_badge( $product ) : null;

	if ( $badge ) {
		printf(
			'<span class="ev-badge ev-badge--%1$s ev-single-badge">%2$s</span>',
			esc_attr( $badge['modifier'] ),
			esc_html( $badge['label'] )
		);
	}
}

/**
 * Note et nombre d'avis, seulement s'il y a des avis.
 *
 * @return void
 */
function evasions_single_rating(): void {
	$product = evasions_current_product();

	if ( ! $product || ! wc_review_ratings_enabled() || $product->get_review_count() < 1 ) {
		return;
	}

	$reviews = (int) $product->get_review_count();

	printf(
		'<a class="ev-single-rating" href="#reviews">%1$s <span>%2$s</span></a>',
		evasions_stars( (float) $product->get_average_rating() ), // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup échappé par evasions_stars().
		esc_html(
			sprintf(
				/* translators: 1: average rating, 2: number of reviews */
				_n( '%1$s (%2$d avis)', '%1$s (%2$d avis)', $reviews, 'evasions' ),
				number_format_i18n( (float) $product->get_average_rating(), 1 ),
				$reviews
			)
		)
	);
}

/**
 * Prix : montant actuel, ancien prix barré, pourcentage de remise.
 *
 * Le pourcentage est calculé sur les prix réels du produit. Un produit variable
 * garde la fourchette de prix de WooCommerce : la remise n'y a pas de sens unique.
 *
 * @return void
 */
function evasions_single_price(): void {
	$product = evasions_current_product();

	if ( ! $product ) {
		return;
	}

	if ( ! $product->is_type( 'simple' ) || ! $product->is_on_sale() ) {
		echo '<div class="ev-price"><span class="ev-price__now">' . wp_kses_post( $product->get_price_html() ) . '</span></div>';
		return;
	}

	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();
	$badge   = evasions_product_badge( $product );

	echo '<div class="ev-price">';
	echo '<span class="ev-price__now">' . wp_kses_post( wc_price( wc_get_price_to_display( $product ) ) ) . '</span>';
	echo '<del class="ev-price__was">' . wp_kses_post( wc_price( wc_get_price_to_display( $product, array( 'price' => $regular ) ) ) ) . '</del>';

	if ( $badge && 'sale' === $badge['modifier'] && $regular > 0 && $sale > 0 ) {
		echo '<span class="ev-badge ev-badge--sale ev-price__off">' . esc_html( $badge['label'] ) . '</span>';
	}

	echo '</div>';
}

/**
 * Disponibilité : « En stock », « Sur commande » ou « Rupture de stock ».
 *
 * Aucune quantité n'est affichée : « plus que 3 ! » serait un compteur
 * d'urgence, que le document directeur (§19) écarte.
 *
 * @return void
 */
function evasions_single_stock(): void {
	$product = evasions_current_product();

	if ( ! $product ) {
		return;
	}

	$status = $product->get_stock_status();

	$labels = array(
		'instock'     => array( 'is-in', __( 'En stock', 'evasions' ) ),
		'onbackorder' => array( 'is-backorder', __( 'Sur commande', 'evasions' ) ),
		'outofstock'  => array( 'is-out', __( 'Rupture de stock', 'evasions' ) ),
	);

	$label = $labels[ $status ] ?? $labels['instock'];

	printf( '<span class="ev-stock %1$s">%2$s</span>', esc_attr( $label[0] ), esc_html( $label[1] ) );
}

/**
 * Supprime le texte de stock natif du formulaire d'achat.
 *
 * WooCommerce l'imprime dans le formulaire (« 20 in stock ») : c'est la quantité
 * exacte, que le thème n'affiche pas (voir `evasions_single_stock()`), et un
 * second indicateur de disponibilité.
 *
 * @return string
 */
function evasions_hide_native_stock_html(): string {
	return '';
}
add_filter( 'woocommerce_get_stock_html', 'evasions_hide_native_stock_html' );

/**
 * Poids du produit, avec repère (§7).
 *
 * Le poids natif de WooCommerce, mis en avant sous forme de pastille plutôt que
 * caché dans l'onglet « Caractéristiques ». Rien n'est inventé : le repère
 * n'apparaît que si le propriétaire a renseigné un poids.
 *
 * @return void
 */
function evasions_single_weight(): void {
	$product = evasions_current_product();

	if ( ! $product ) {
		return;
	}

	$weight = (string) $product->get_weight();

	if ( '' === $weight ) {
		return;
	}

	$unit = get_option( 'woocommerce_weight_unit', 'kg' );

	printf(
		'<p class="ev-weight"><span class="ev-weight__label">%1$s</span><span class="ev-weight__value">%2$s %3$s</span></p>',
		esc_html__( 'Poids', 'evasions' ),
		esc_html( wc_format_localized_decimal( $weight ) ),
		esc_html( $unit )
	);
}

/**
 * Bloc « pour qui / pas pour qui » (§7).
 *
 * Deux listes courtes venues du plugin (`Product_Audience`), où le propriétaire
 * les édite. Sans saisie, le bloc n'existe pas : §60 interdit d'inventer à qui
 * un produit convient.
 *
 * @return void
 */
function evasions_single_audience(): void {
	$product = evasions_current_product();

	if ( ! $product || ! class_exists( '\Evasions\Core\Product_Audience' ) ) {
		return;
	}

	$audience = \Evasions\Core\Product_Audience::instance();
	$for      = $audience->for_who( $product );
	$against  = $audience->not_for_who( $product );

	if ( array() === $for && array() === $against ) {
		return;
	}

	echo '<div class="ev-audience">';

	if ( array() !== $for ) {
		echo '<div class="ev-audience__col ev-audience__col--for"><h3>' . esc_html__( 'Pour qui', 'evasions' ) . '</h3><ul>';
		foreach ( $for as $line ) {
			echo '<li><span class="ev-audience__icon">' . evasions_icon( 'check', 14 ) . '</span><span>' . esc_html( $line ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne.
		}
		echo '</ul></div>';
	}

	if ( array() !== $against ) {
		echo '<div class="ev-audience__col ev-audience__col--against"><h3>' . esc_html__( 'Pas pour qui', 'evasions' ) . '</h3><ul>';
		foreach ( $against as $line ) {
			echo '<li><span class="ev-audience__icon">' . evasions_icon( 'close', 14 ) . '</span><span>' . esc_html( $line ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne.
		}
		echo '</ul></div>';
	}

	echo '</div>';
}

/**
 * Points forts : les puces de la description courte, avec une coche.
 *
 * Le propriétaire les écrit comme une liste dans le champ « Description courte »
 * de WooCommerce. Sans liste, le texte est affiché tel quel, en paragraphe.
 *
 * @return void
 */
function evasions_single_highlights(): void {
	$product = evasions_current_product();

	if ( ! $product ) {
		return;
	}

	$short = trim( (string) $product->get_short_description() );

	if ( '' === $short ) {
		return;
	}

	if ( preg_match_all( '/<li[^>]*>(.*?)<\/li>/is', $short, $matches ) && array() !== $matches[1] ) {
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributs échappés dans evasions_readmore_attrs().
		echo '<ul class="ev-checks"' . evasions_readmore_attrs() . '>';
		foreach ( $matches[1] as $item ) {
			echo '<li><span class="ev-checks__icon">' . evasions_icon( 'check', 14 ) . '</span><span>' . wp_kses_post( trim( $item ) ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne.
		}
		echo '</ul>';
		return;
	}

	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributs échappés dans evasions_readmore_attrs(), contenu par wp_kses_post().
	echo '<div class="ev-single-short"' . evasions_readmore_attrs() . '>' . wp_kses_post( wpautop( $short ) ) . '</div>';
}

/**
 * Attributs qui rendent un bloc de description repliable.
 *
 * Le pli est posé par le script, pas par le serveur : lui seul sait si le texte
 * dépasse réellement la hauteur, ce qui dépend de la largeur de l'écran et de la
 * police effectivement chargée. Sans JavaScript, rien n'est replié et la
 * description reste lisible en entier — un texte tronqué sans bouton pour
 * l'ouvrir serait pire que pas de pli du tout.
 *
 * Les libellés voyagent dans des attributs plutôt que dans le script : le
 * JavaScript du thème n'a pas de canal de traduction, et la micro-copie doit
 * rester dans le PHP avec le reste des chaînes.
 *
 * @return string Attributs prêts à coller dans une balise ouvrante.
 */
function evasions_readmore_attrs(): string {
	return sprintf(
		' data-ev-readmore data-ev-readmore-more="%1$s" data-ev-readmore-less="%2$s"',
		esc_attr__( 'Lire la suite', 'evasions' ),
		esc_attr__( 'Réduire', 'evasions' )
	);
}

/**
 * Rangée de réassurance sous le bouton d'achat.
 *
 * Livraison et paiement à la livraison sont appliqués par le tunnel de commande.
 * Le troisième pictogramme renvoie à la page « Retours » et n'existe que si
 * elle est publiée : la maquette dit « Retour sous 7 jours », une durée que rien
 * n'établit encore. Filtrable : `evasions_product_reassurance`.
 *
 * @return void
 */
function evasions_single_reassurance(): void {
	$items = array(
		array(
			'icon'  => 'truck',
			'label' => __( 'Livraison en Tunisie', 'evasions' ),
			'url'   => '',
		),
		array(
			'icon'  => 'card',
			'label' => __( 'Paiement à la livraison', 'evasions' ),
			'url'   => '',
		),
	);

	$returns = evasions_page_link( 'retours', __( 'Politique de retour', 'evasions' ) );
	if ( $returns ) {
		$items[] = array(
			'icon'  => 'shield',
			'label' => $returns['label'],
			'url'   => $returns['url'],
		);
	}

	$items = (array) apply_filters( 'evasions_product_reassurance', $items );

	if ( array() === $items ) {
		return;
	}

	echo '<ul class="ev-reassure" style="--ev-cols:' . (int) count( $items ) . '">';
	foreach ( $items as $item ) {
		$label = esc_html( $item['label'] );
		$label = '' !== $item['url'] ? '<a href="' . esc_url( $item['url'] ) . '">' . $label . '</a>' : $label;

		echo '<li><span class="ev-reassure__icon">' . evasions_icon( $item['icon'], 16 ) . '</span><span>' . $label . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne, libellé et URL échappés ci-dessus.
	}
	echo '</ul>';
}

/*
 * Le bouton « Commander maintenant » a été retiré (27 septembre 2026).
 *
 * Il renommait le bouton d'ajout au panier de WooCommerce et détournait la
 * redirection qui suit, pour mener droit au checkout. La carte de commande
 * express, désormais posée juste au-dessus par le plugin, fait la même chose
 * sans quitter la page — et mieux, puisqu'elle prend les coordonnées sur place.
 * Deux appels à l'action qui promettent la même chose à quelques centimètres
 * l'un de l'autre font hésiter, et l'ancien tenait la promesse la plus lente.
 *
 * Ce qui est parti : `evasions_single_add_to_cart_text()` (le libellé),
 * `evasions_buy_now_field()` (le marqueur `ev_buy_now`), `evasions_is_buy_now()`,
 * `evasions_force_redirect_after_add()` et `evasions_buy_now_redirect()`. Le
 * réglage WooCommerce n'avait jamais été touché : il n'y a rien à remettre en
 * place. Le formulaire d'achat de WooCommerce, lui, reste rendu — c'est le
 * repli sans JavaScript — mais le CSS le masque quand la carte express le
 * double. Voir `evasions_product_express_class()`.
 */


/**
 * Boutons − et + autour du champ quantité.
 *
 * Amélioration progressive : sans JavaScript, le champ numérique natif reste
 * utilisable.
 *
 * @return void
 */
function evasions_quantity_minus(): void {
	echo '<button type="button" class="ev-qty__btn" data-ev-step="-1" aria-label="' . esc_attr__( 'Diminuer la quantité', 'evasions' ) . '">−</button>';
}
add_action( 'woocommerce_before_quantity_input_field', 'evasions_quantity_minus' );

/**
 * Bouton + après le champ quantité.
 *
 * @return void
 */
function evasions_quantity_plus(): void {
	echo '<button type="button" class="ev-qty__btn" data-ev-step="1" aria-label="' . esc_attr__( 'Augmenter la quantité', 'evasions' ) . '">+</button>';
}
add_action( 'woocommerce_after_quantity_input_field', 'evasions_quantity_plus' );

/**
 * Retire le bouton WhatsApp flottant de la fiche produit.
 *
 * La fiche porte déjà un bloc WhatsApp dédié, avec le nom et le prix du
 * produit écrits dans le message : le bouton flottant y ouvre une
 * conversation vide, donc il fait double emploi en moins bien.
 *
 * Et il gêne. Mesuré à 375 × 812 : le bouton flottant (303–359 × 740–796)
 * recouvre « Ajouter au panier » (292–344 × 760–812), et elementFromPoint au
 * CENTRE de ce bouton renvoie le SVG de WhatsApp — un doigt posé au milieu de
 * la cible ouvre WhatsApp au lieu d'ajouter au panier. Contrairement au
 * panier, un décalage fixe ne suffirait pas : ici les boutons défilent, donc
 * un décalage ne ferait que déplacer la collision (audit UX-14).
 *
 * Le thème ne touche pas au bouton du plugin : il répond au filtre que le
 * plugin expose pour ça.
 *
 * @param bool $rendre Décision en amont.
 * @return bool
 */
function evasions_hide_float_on_product( bool $rendre ): bool {
	return is_product() ? false : $rendre;
}
add_filter( 'evasions_whatsapp_float_enabled', 'evasions_hide_float_on_product' );

/**
 * Bloc WhatsApp de la fiche produit — maquette « Fiche Produit & Tunnel » §1.
 *
 * Pourquoi ici, alors qu'un bouton WhatsApp flotte déjà sur tout le site : le
 * bouton flottant ouvre une conversation vide, et le client doit alors décrire
 * lui-même le produit qu'il regarde. Celui-ci part avec le nom et le prix déjà
 * écrits, donc le vendeur sait de quoi on lui parle dès le premier message. Sur
 * une boutique qui encaisse à la livraison, cette conversation est souvent ce
 * qui décide la commande.
 *
 * Découplage : le thème n'écrit ni le numéro ni l'URL. Il donne un texte à
 * `Evasions\Core\WhatsApp::link()`, qui appartient au plugin parce que le
 * numéro est une donnée métier — elle doit survivre à un changement de thème.
 * Sans le plugin, ou sans numéro configuré, le bloc n'est pas rendu (§60) :
 * mieux vaut rien qu'un bouton qui n'ouvre rien.
 *
 * Non repris de la maquette : « Réponse du lundi au samedi, 9h–18h ». Aucune
 * source n'établit ces horaires, et un horaire faux se paie par un client qui
 * attend une réponse qui ne viendra pas (§60).
 *
 * @return void
 */
function evasions_single_whatsapp(): void {
	if ( ! class_exists( '\Evasions\Core\WhatsApp' ) || ! class_exists( '\Evasions\Core\Config' ) ) {
		return;
	}

	if ( '' === \Evasions\Core\Config::instance()->whatsapp_number() ) {
		return;
	}

	$product = evasions_current_product();

	if ( ! $product ) {
		return;
	}

	/*
	 * Le prix affiché est celui que WooCommerce rend ailleurs sur la page — le
	 * thème ne le recalcule pas. `wc_price()` sort du HTML (symbole, espace
	 * insécable) : on le ramène en texte brut, puisqu'il part dans l'URL d'un
	 * message et non dans la page.
	 */
	$price = html_entity_decode(
		wp_strip_all_tags( wc_price( wc_get_price_to_display( $product ) ) ),
		ENT_QUOTES,
		'UTF-8'
	);

	$message = sprintf(
		/* translators: 1: product name, 2: formatted price. */
		__( 'Bonjour EVASIONS, je suis intéressé(e) par « %1$s » (%2$s). Est-il disponible ?', 'evasions' ),
		$product->get_name(),
		$price
	);

	$url = \Evasions\Core\WhatsApp::link( $message );

	if ( '' === $url ) {
		return;
	}

	?>
	<aside class="ev-wa-product">
		<?php
		/*
		 * `aria-label` sur l'ancre, et le reste en `aria-hidden` : le nom
		 * accessible calculé faisait 172 caractères, puisqu'il ramassait le
		 * titre, le bouton et tout l'aperçu du message (audit A11Y-EX-09). Il
		 * annonce maintenant l'action et l'ouverture d'un nouvel onglet, que
		 * rien ne signalait (A11Y-EX-10).
		 */
		?>
		<a
			class="ev-wa-product__link"
			href="<?php echo esc_url( $url ); ?>"
			target="_blank"
			rel="noopener"
			aria-label="<?php esc_attr_e( 'Écrivez-nous sur WhatsApp au sujet de ce produit (ouvre un nouvel onglet)', 'evasions' ); ?>"
		>
			<?php
			/*
			 * Pictogramme, phrase et bouton sont des enfants directs du lien :
			 * ce sont les trois colonnes de la rangée. Les envelopper dans un
			 * conteneur intermédiaire, comme c'était le cas, les aurait fait
			 * compter pour une seule colonne.
			 */
			?>
			<span class="ev-wa-product__icon" aria-hidden="true"><?php echo evasions_icon( 'whatsapp', 20 ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne. ?></span>
			<span class="ev-wa-product__title" aria-hidden="true"><?php esc_html_e( 'Une question ? Écrivez-nous sur WhatsApp', 'evasions' ); ?></span>
			<span class="ev-wa-product__cta" aria-hidden="true"><?php esc_html_e( 'Ouvrir WhatsApp', 'evasions' ); ?></span>
			<?php
			/*
			 * Le message est montré avant le départ : le client voit ce qu'il
			 * envoie en son nom, il ne le découvre pas dans WhatsApp.
			 *
			 * Il vient APRÈS le bouton dans le balisage, parce qu'il occupe la
			 * rangée du dessous : placé avant, il pousserait le bouton sur une
			 * troisième rangée. L'ordre de lecture n'en souffre pas — le lien
			 * entier est une seule cible, annoncée d'un bloc.
			 */
			?>
			<span class="ev-wa-product__preview" aria-hidden="true"><?php echo esc_html( $message ); ?></span>
		</a>
	</aside>
	<?php
}

/**
 * Onglets : Description, Caractéristiques, Avis (n), FAQ.
 *
 * @param array<string,array<string,mixed>> $tabs Tabs built by WooCommerce.
 * @return array<string,array<string,mixed>>
 */
function evasions_product_tabs( $tabs ) {
	$product = evasions_current_product();

	if ( ! $product ) {
		return $tabs;
	}

	$has_video = class_exists( '\Evasions\Core\Product_Video' ) && array() !== \Evasions\Core\Product_Video::instance()->data( $product );

	if ( isset( $tabs['description'] ) || $has_video ) {
		$tabs['description'] = array(
			'title'    => __( 'Description', 'evasions' ),
			'priority' => 10,
			'callback' => 'evasions_tab_description',
		);
	}

	if ( isset( $tabs['additional_information'] ) ) {
		$tabs['additional_information']['title'] = __( 'Caractéristiques', 'evasions' );
	}

	if ( isset( $tabs['reviews'] ) ) {
		$tabs['reviews']['title']    = sprintf(
			/* translators: %d: number of reviews */
			__( 'Avis (%d)', 'evasions' ),
			(int) $product->get_review_count()
		);
		$tabs['reviews']['callback'] = 'evasions_tab_reviews';
	}

	if ( class_exists( '\Evasions\Core\Product_Faq' ) && array() !== \Evasions\Core\Product_Faq::instance()->pairs( $product ) ) {
		$tabs['faq'] = array(
			'title'    => __( 'FAQ', 'evasions' ),
			'priority' => 40,
			'callback' => 'evasions_tab_faq',
		);
	}

	return $tabs;
}
add_filter( 'woocommerce_product_tabs', 'evasions_product_tabs', 20 );

// Le titre « Description » est déjà celui de l'onglet : pas de second titre dans le panneau.
add_filter( 'woocommerce_product_description_heading', '__return_empty_string' );
add_filter( 'woocommerce_product_additional_information_heading', '__return_empty_string' );

/**
 * Panneau Description : le texte du propriétaire, et la vidéo si elle existe.
 *
 * La vidéo vient du plugin (`Product_Video`) : le plugin stocke l'adresse et
 * fabrique le lecteur, le thème n'en fait que la présentation (règle 6).
 *
 * @return void
 */
function evasions_tab_description(): void {
	$product = evasions_current_product();
	$video   = ( $product && class_exists( '\Evasions\Core\Product_Video' ) ) ? \Evasions\Core\Product_Video::instance()->data( $product ) : array();
	$player  = array() !== $video ? \Evasions\Core\Product_Video::instance()->player( $video['url'] ) : '';

	echo '<div class="ev-desc' . ( '' !== $player ? ' has-video' : '' ) . '">';
	// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- Attributs échappés dans evasions_readmore_attrs().
	echo '<div class="ev-desc__text entry-content"' . evasions_readmore_attrs() . '>';
	the_content();
	echo '</div>';

	if ( '' !== $player ) {
		echo '<aside class="ev-desc__video">';
		if ( '' !== $video['title'] ) {
			echo '<h3>' . esc_html( $video['title'] ) . '</h3>';
		}
		echo '<div class="ev-desc__player">' . $player . '</div>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- lecteur produit par Product_Video (oEmbed WordPress ou <video> à adresse échappée).
		if ( '' !== $video['description'] ) {
			echo '<p>' . esc_html( $video['description'] ) . '</p>';
		}
		echo '</aside>';
	}

	echo '</div>';
}

/**
 * Panneau Avis : résumé chiffré, puis la liste et le formulaire de WooCommerce.
 *
 * @return void
 */
function evasions_tab_reviews(): void {
	evasions_reviews_summary();
	comments_template();
}

/**
 * Résumé des avis : moyenne, nombre, et répartition réelle par nombre d'étoiles.
 *
 * Les pourcentages sont calculés sur les avis réellement enregistrés.
 *
 * @return void
 */
function evasions_reviews_summary(): void {
	$product = evasions_current_product();

	if ( ! $product || ! wc_review_ratings_enabled() ) {
		return;
	}

	$total = (int) $product->get_rating_count();

	if ( $total < 1 ) {
		return;
	}

	$counts = $product->get_rating_counts();

	echo '<div class="ev-rating-summary">';
	echo '<div class="ev-rating-summary__head"><strong>' . esc_html( number_format_i18n( (float) $product->get_average_rating(), 1 ) ) . '</strong>';
	echo '<span>' . evasions_stars( (float) $product->get_average_rating() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- markup échappé par evasions_stars().
	/* translators: %d: number of reviews */
	echo '<small>' . esc_html( sprintf( _n( '%d avis', '%d avis', $total, 'evasions' ), $total ) ) . '</small></span></div>';

	echo '<ul class="ev-rating-summary__bars">';
	for ( $star = 5; $star >= 1; $star-- ) {
		$count   = (int) ( $counts[ $star ] ?? 0 );
		$percent = (int) round( ( $count / $total ) * 100 );

		printf(
			'<li><span>%1$s</span><span class="ev-bar"><span style="width:%2$d%%"></span></span><span>%2$d%%</span></li>',
			esc_html(
				/* translators: %d: number of stars */
				sprintf( _n( '%d étoile', '%d étoiles', $star, 'evasions' ), $star )
			),
			(int) $percent
		);
	}
	echo '</ul></div>';
}

/**
 * Panneau FAQ : les questions saisies dans la fiche produit.
 *
 * Balisage `<details>` : ouvert et fermé sans JavaScript, accessible au clavier.
 *
 * @return void
 */
function evasions_tab_faq(): void {
	$product = evasions_current_product();
	$pairs   = ( $product && class_exists( '\Evasions\Core\Product_Faq' ) ) ? \Evasions\Core\Product_Faq::instance()->pairs( $product ) : array();

	if ( array() === $pairs ) {
		return;
	}

	echo '<div class="ev-faq">';
	foreach ( $pairs as $pair ) {
		echo '<details class="ev-faq__item"><summary>' . esc_html( $pair['q'] ) . '</summary><div>' . wp_kses_post( wpautop( $pair['a'] ) ) . '</div></details>';
	}
	echo '</div>';
}

/**
 * « Produits complémentaires » : les ventes incitatives du produit, sinon des produits proches.
 *
 * Ce que le propriétaire a relié à la main (Données produit → Produits liés)
 * passe avant ce que WooCommerce déduit. Le titre dit laquelle des deux
 * situations s'applique : « Complétez votre équipement » n'a de sens que pour
 * des produits choisis pour compléter celui-ci.
 *
 * Les cartes sont celles du listing (`template-parts/product-card`), **sans
 * bouton et entièrement cliquables** (§6.2). Elles portaient jusqu'ici un
 * bouton « + Ajouter » hérité de la variante compacte : deux grammaires de
 * carte coexistaient sur le même site, et le bloc 17 du §7.1 décrit bien une
 * suite de cartes de catalogue, pas une rangée d'ajouts au panier (audit
 * UX-01). La variante compacte (`product-card-compact.php`) reste en service
 * au panier, où l'ajout est le geste attendu.
 *
 * @return void
 */
function evasions_single_related(): void {
	$product = evasions_current_product();

	if ( ! $product ) {
		return;
	}

	$ids     = array_filter( array_map( 'absint', $product->get_upsell_ids() ) );
	$curated = array() !== $ids;

	if ( ! $curated ) {
		$ids = wc_get_related_products( $product->get_id(), 4 );
	}

	$products = array_filter( array_map( 'wc_get_product', array_slice( array_values( $ids ), 0, 4 ) ) );
	$products = array_filter(
		$products,
		static fn( $candidate ) => $candidate instanceof WC_Product && $candidate->is_visible()
	);

	if ( array() === $products ) {
		return;
	}

	echo '<section class="ev-related" aria-labelledby="ev-related-title">';
	echo '<h2 id="ev-related-title">' . esc_html( $curated ? __( 'Produits complémentaires', 'evasions' ) : __( 'Vous pourriez aussi aimer', 'evasions' ) ) . '</h2>';

	if ( $curated ) {
		echo '<p class="ev-related__sub">' . esc_html__( 'Complétez votre équipement', 'evasions' ) . '</p>';
	}

	echo '<div class="ev-grid ev-grid--products">';
	foreach ( $products as $related ) {
		// Sous le h2 de la section, le titre d'une carte est un h3 : pas de saut de niveau.
		get_template_part(
			'template-parts/product-card',
			null,
			array(
				'product'   => $related,
				'title_tag' => 'h3',
			)
		);
	}
	echo '</div></section>';
}
