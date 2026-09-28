<?php
/**
 * Panier et checkout — maquettes « Panier » et « Checkout » (mobile 390 px, desktop 1440 px).
 *
 * ── Pourquoi le panier et le checkout classiques (shortcodes) ───────────────
 *
 * WooCommerce 11 crée ces deux pages en blocs. Le design, lui, regroupe les
 * champs en sections (« Vos informations », « Adresse de livraison »), préfixe
 * le téléphone par +216 et libelle la ville « Délégation ». Le plugin EVASIONS
 * Core prépare déjà les champs pour le checkout classique (téléphone en
 * premier, e-mail facultatif, gouvernorat obligatoire, libellés bilingues). Le
 * checkout par blocs n'expose pas ces leviers : sa mise en page se joue dans
 * l'éditeur, pas dans le code.
 *
 * Les RÈGLES du plugin (téléphone tunisien, coût interne, UTM, signaux CAPI)
 * s'appliquent aux deux modes — voir `Blocks_Checkout` — : ce choix porte sur la
 * mise en page, pas sur la sécurité du tunnel.
 *
 * ── Méthode ─────────────────────────────────────────────────────────────────
 *
 * Comme pour la fiche produit, aucun gabarit WooCommerce n'est copié : hooks et
 * filtres pour le contenu, CSS pour la mise en page (`cart-checkout.css`). Un
 * tableau de panier devient une liste de cartes par CSS ; le récapitulatif du
 * checkout est sorti de son conteneur par `display: contents`.
 *
 * ── Rien n'est inventé (§60) ────────────────────────────────────────────────
 *
 * Non repris de la maquette : « économisez 10 % » (aucune offre n'existe),
 * « livraison estimée 2 à 4 jours » (aucun délai n'est établi) et
 * « informations cryptées » (rien ne permet de garantir la formule).
 *
 * La frise d'étapes, elle, est reprise depuis la maquette « Fiche Produit &
 * Tunnel ». Elle avait été écartée quand elle ne devait coiffer que la page
 * Commander : un indicateur qui ne change jamais est trompeur. La maquette la
 * pose désormais sur les trois écrans — Panier, Commande, Confirmation — où
 * elle avance réellement d'un cran à chaque page, et dit au client combien il
 * lui reste à faire. L'objection tombe avec la raison qui la portait.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Le panier et le checkout de cette boutique sont-ils les gabarits classiques ?
 *
 * @return bool
 */
function evasions_is_cart_or_checkout(): bool {
	return function_exists( 'is_cart' ) && ( is_cart() || ( is_checkout() && ! is_wc_endpoint_url( 'order-received' ) ) );
}

/**
 * Feuille de style du panier et du checkout.
 *
 * @return void
 */
function evasions_cart_checkout_assets(): void {
	if ( function_exists( 'is_cart' ) && ( is_cart() || is_checkout() ) ) {
		wp_enqueue_style(
			'evasions-cart-checkout',
			get_template_directory_uri() . '/assets/css/cart-checkout.css',
			array( 'evasions', 'evasions-woocommerce' ),
			evasions_asset_version( 'assets/css/cart-checkout.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_cart_checkout_assets', 30 );

/**
 * Libellés du panier et du checkout, ceux de la maquette.
 *
 * Ces textes appartiennent à WooCommerce (domaine `woocommerce`) : le plus
 * propre est de les surcharger à l'affichage, pas de copier le gabarit qui les
 * imprime.
 *
 * @param string $translation Translated text.
 * @param string $text        Original text.
 * @param string $domain      Text domain.
 * @return string
 */
function evasions_cart_strings( $translation, $text, $domain ) {
	if ( 'woocommerce' !== $domain ) {
		return $translation;
	}

	static $map = null;

	if ( null === $map ) {
		$map = array(
			'Cart totals'          => __( 'Récapitulatif de votre commande', 'evasions' ),
			'Proceed to checkout'  => __( 'Passer à la commande', 'evasions' ),
			'Your order'           => __( 'Votre commande', 'evasions' ),
			'Billing details'      => __( 'Vos informations', 'evasions' ),
			'Additional information' => __( 'Informations complémentaires', 'evasions' ),
		);
	}

	return $map[ $text ] ?? $translation;
}
add_filter( 'gettext', 'evasions_cart_strings', 10, 3 );

/**
 * Bouton de validation du checkout : « Confirmer — je paie à la réception ».
 *
 * Le §14.1 prescrit deux libellés distincts, et non le même deux fois :
 * « Commander — je paie à la réception » sur la fiche produit, que porte la
 * carte de commande express du plugin, et « Confirmer — je paie à la
 * réception » ici. Reprendre le libellé de la fiche produit au dernier écran
 * laissait croire qu'une étape restait à franchir ; « Confirmer » dit que le
 * client termine, sans rien changer à la promesse de paiement à la réception.
 *
 * @return string
 */
function evasions_order_button_text(): string {
	return __( 'Confirmer — je paie à la réception', 'evasions' );
}
add_filter( 'woocommerce_order_button_text', 'evasions_order_button_text' );

/**
 * Frise des trois étapes du tunnel — maquette « Fiche Produit & Tunnel » §2.
 *
 * Présentation pure : l'étape courante se déduit de la page affichée, jamais
 * d'un état de commande calculé. Le thème ne sait rien du panier ici, il sait
 * seulement où se trouve le client.
 *
 * Une `<ol>` plutôt qu'une suite de `<div>` : l'ordre porte le sens, et un
 * lecteur d'écran annonce « 2 sur 3 ». `aria-current="step"` marque l'étape en
 * cours ; les étapes franchies portent une coche dont le sens est déjà dans le
 * texte, d'où l'`aria-hidden` sur le pictogramme.
 *
 * @return void
 */
function evasions_tunnel_steps(): void {
	if ( ! function_exists( 'is_cart' ) ) {
		return;
	}

	// Ordre du parcours ; la clé sert à repérer où l'on est.
	$steps = array(
		'cart'     => __( 'Panier', 'evasions' ),
		'checkout' => __( 'Commande', 'evasions' ),
		'done'     => __( 'Confirmation', 'evasions' ),
	);

	if ( is_cart() ) {
		$current = 'cart';
	} elseif ( is_wc_endpoint_url( 'order-received' ) ) {
		$current = 'done';
	} elseif ( is_checkout() ) {
		$current = 'checkout';
	} else {
		return;
	}

	$position = array_search( $current, array_keys( $steps ), true );
	$index    = 0;

	echo '<nav class="ev-steps" aria-label="' . esc_attr__( 'Étapes de la commande', 'evasions' ) . '">';
	echo '<ol class="ev-steps__list">';

	foreach ( $steps as $key => $label ) {
		$done  = $index < $position;
		$here  = $key === $current;
		$class = 'ev-steps__step';
		$class .= $done ? ' is-done' : '';
		$class .= $here ? ' is-current' : '';

		printf(
			'<li class="%1$s"%2$s><span class="ev-steps__dot" aria-hidden="true">%3$s</span><span class="ev-steps__label">%4$s</span></li>',
			esc_attr( $class ),
			$here ? ' aria-current="step"' : '',
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne, ou chaîne vide.
			$done ? evasions_icon( 'check', 12 ) : '',
			esc_html( $label )
		);

		++$index;
	}

	echo '</ol></nav>';
}
add_action( 'woocommerce_before_cart', 'evasions_tunnel_steps', 5 );
add_action( 'woocommerce_before_checkout_form', 'evasions_tunnel_steps', 5 );
add_action( 'woocommerce_before_thankyou', 'evasions_tunnel_steps', 5 );

/**
 * Nombre d'articles, en tête de la liste du panier.
 *
 * @return void
 */
function evasions_cart_title(): void {
	if ( ! WC()->cart ) {
		return;
	}

	$count = (int) WC()->cart->get_cart_contents_count();

	echo '<h2 class="ev-cart-title">' . esc_html(
		sprintf(
			/* translators: %d: number of items in the cart */
			_n( '%d article dans votre panier', '%d articles dans votre panier', $count, 'evasions' ),
			$count
		)
	) . '</h2>';
}
add_action( 'woocommerce_before_cart_table', 'evasions_cart_title' );

/**
 * Disponibilité de chaque ligne du panier.
 *
 * @param array<string,mixed> $cart_item Cart item.
 * @return void
 */
function evasions_cart_item_stock( $cart_item ): void {
	$product = $cart_item['data'] ?? null;

	if ( $product instanceof WC_Product && $product->is_in_stock() ) {
		echo '<span class="ev-stock ev-stock--small is-in">' . esc_html__( 'En stock', 'evasions' ) . '</span>';
	}
}
add_action( 'woocommerce_after_cart_item_name', 'evasions_cart_item_stock' );

/**
 * Sous le bouton « Passer à la commande » : rappel du mode de paiement.
 *
 * @return void
 */
function evasions_cart_totals_note(): void {
	echo '<p class="ev-cart-note">' . esc_html__( 'Paiement à la livraison', 'evasions' ) . '</p>';

	$benefits = evasions_benefits();
	if ( array() === $benefits ) {
		return;
	}

	echo '<ul class="ev-cart-benefits">';
	foreach ( $benefits as $benefit ) {
		echo '<li><span class="ev-cart-benefits__icon">' . evasions_icon( $benefit['icon'], 16 ) . '</span><span><strong>' . esc_html( $benefit['title'] ) . '</strong> ' . esc_html( $benefit['sub'] ) . '</span></li>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- SVG interne, textes échappés.
	}
	echo '</ul>';
}
add_action( 'woocommerce_after_cart_totals', 'evasions_cart_totals_note' );

/**
 * Barre d'achat fixe sur mobile : total et bouton, toujours à portée du pouce (§1.8 de la spécification UX).
 *
 * Imprimée dans `.cart_totals`, donc rafraîchie avec lui quand la quantité change.
 *
 * @return void
 */
function evasions_cart_mobile_bar(): void {
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}

	echo '<div class="ev-cart-bar"><div><span>' . esc_html__( 'Total', 'evasions' ) . '</span><strong>' . wp_kses_post( WC()->cart->get_total() ) . '</strong></div>';
	echo '<a class="ev-btn ev-btn--primary" href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'Passer à la commande', 'evasions' ) . '</a></div>';
}
add_action( 'woocommerce_after_cart_totals', 'evasions_cart_mobile_bar', 20 );

/**
 * Remplace les ventes croisées natives par une section « Vous pourriez aussi aimer ».
 *
 * WooCommerce les affiche dans deux colonnes au-dessus du récapitulatif. Le
 * design les veut sous la liste, en cartes compactes. Ce sont les produits que
 * le propriétaire a reliés à la main (Produits liés → Ventes croisées) : aucune
 * recommandation n'est déduite.
 *
 * @return void
 */
function evasions_cart_cross_sells(): void {
	if ( ! WC()->cart || WC()->cart->is_empty() ) {
		return;
	}

	$ids      = array_slice( array_map( 'absint', (array) WC()->cart->get_cross_sells() ), 0, 3 );
	$products = array_filter(
		array_map( 'wc_get_product', $ids ),
		static fn( $product ) => $product instanceof WC_Product && $product->is_visible()
	);

	if ( array() === $products ) {
		return;
	}

	echo '<section class="ev-cross" aria-labelledby="ev-cross-title"><h2 id="ev-cross-title">' . esc_html__( 'Vous pourriez aussi aimer', 'evasions' ) . '</h2><div class="ev-grid ev-grid--cross">';
	foreach ( $products as $product ) {
		get_template_part( 'template-parts/product-card-compact', null, array( 'product' => $product ) );
	}
	echo '</div></section>';
}
add_action( 'woocommerce_after_cart', 'evasions_cart_cross_sells' );

/**
 * Retire l'affichage natif des ventes croisées (remplacé ci-dessus).
 *
 * @return void
 */
function evasions_cart_hooks(): void {
	remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );
}
add_action( 'init', 'evasions_cart_hooks' );

/**
 * Champs du checkout : présentation seulement.
 *
 * Le plugin (`Evasions\Core\Tunisia::checkout_fields()`) décide QUELS champs
 * existent, lesquels sont obligatoires ET dans quel ordre (§9.1, §13). Le thème
 * ne touche plus ni à l'ordre ni aux libellés des champs : il n'ajuste qu'un
 * placeholder d'adresse et le libellé des notes de commande, qui sont de la
 * présentation pure.
 *
 * @param array<string,array<string,array<string,mixed>>> $fields Checkout fields.
 * @return array<string,array<string,array<string,mixed>>>
 */
function evasions_checkout_fields( $fields ) {
	if ( isset( $fields['billing']['billing_address_1'] ) ) {
		$fields['billing']['billing_address_1']['placeholder'] = __( 'Numéro, rue, quartier…', 'evasions' );
	}

	if ( isset( $fields['order']['order_comments'] ) ) {
		$fields['order']['order_comments']['label']       = __( 'Informations complémentaires', 'evasions' );
		$fields['order']['order_comments']['placeholder'] = __( 'Étage, code d’accès, point de repère… (facultatif)', 'evasions' );
		$fields['order']['order_comments']['required']    = false;
	}

	return $fields;
}
add_filter( 'woocommerce_checkout_fields', 'evasions_checkout_fields', 30 );

/**
 * Sépare les champs de facturation en deux cartes : « Vos informations » puis « Adresse de livraison ».
 *
 * Les champs sont imprimés à la suite par WooCommerce, sans regroupement. On
 * ouvre la première carte devant le premier champ du tunnel (le téléphone, §9.1),
 * on change de carte devant le gouvernorat, et on referme après le dernier champ
 * (`evasions_checkout_close_card()`).
 *
 * Le téléphone reçoit un préfixe « +216 » purement visuel : il n'est pas envoyé,
 * et le plugin accepte le numéro avec ou sans indicatif.
 *
 * @param string              $field Field HTML.
 * @param string              $key   Field key.
 * @param array<string,mixed> $args  Field arguments.
 * @return string
 */
function evasions_checkout_form_field( $field, $key, $args ) {
	static $opened = false;

	if ( ! is_string( $field ) || ! is_string( $key ) ) {
		return $field;
	}

	$head = '';

	// Le téléphone est le premier champ (§9.1) : la carte « Vos informations » s'ouvre devant lui.
	if ( 'billing_phone' === $key ) {
		$opened = true;
		$head   = '<div class="ev-co-card"><header class="ev-co-card__head"><h2>1. ' . esc_html__( 'Vos informations', 'evasions' ) . '</h2><p>' . esc_html__( 'Nous avons besoin de quelques informations pour vous contacter.', 'evasions' ) . '</p></header><div class="ev-co-card__fields">';

		$field = preg_replace( '/(<input[^>]*type="tel")/', '<span class="ev-phone-prefix" aria-hidden="true">+216</span>$1', $field, 1 ) ?? $field;
		$field = str_replace( 'woocommerce-input-wrapper', 'woocommerce-input-wrapper ev-phone-wrap', $field );
	} elseif ( 'billing_state' === $key && $opened ) {
		$head = '</div></div><div class="ev-co-card"><header class="ev-co-card__head"><h2>2. ' . esc_html__( 'Adresse de livraison', 'evasions' ) . '</h2><p>' . esc_html__( 'Où souhaitez-vous recevoir votre commande ?', 'evasions' ) . '</p></header><div class="ev-co-card__fields">';
	}

	return $head . $field;
}
add_filter( 'woocommerce_form_field', 'evasions_checkout_form_field', 10, 3 );

/**
 * Referme la dernière carte après les champs de facturation.
 *
 * @return void
 */
function evasions_checkout_close_card(): void {
	echo '</div></div>';
}
add_action( 'woocommerce_after_checkout_billing_form', 'evasions_checkout_close_card' );

/**
 * Miniature et nom de chaque article dans le récapitulatif du checkout.
 *
 * @param string              $name      Item name markup.
 * @param array<string,mixed> $cart_item Cart item.
 * @return string
 */
function evasions_checkout_item_name( $name, $cart_item ) {
	if ( ! function_exists( 'is_checkout' ) || ! is_checkout() ) {
		return $name;
	}

	$product = $cart_item['data'] ?? null;

	if ( ! $product instanceof WC_Product ) {
		return $name;
	}

	return '<span class="ev-co-item"><span class="ev-co-item__img">' . $product->get_image( 'woocommerce_gallery_thumbnail' ) . '</span><span class="ev-co-item__name">' . wp_kses_post( $name ) . '</span></span>';
}
add_filter( 'woocommerce_cart_item_name', 'evasions_checkout_item_name', 10, 2 );

/**
 * Titre de la section paiement.
 *
 * @return void
 */
function evasions_checkout_payment_heading(): void {
	echo '<header class="ev-co-pay-head"><h2>3. ' . esc_html__( 'Paiement', 'evasions' ) . '</h2><p>' . esc_html__( 'Vous payez à la réception', 'evasions' ) . '</p></header>';
}
add_action( 'woocommerce_review_order_before_payment', 'evasions_checkout_payment_heading' );

/**
 * Bloc « Une question ? » : WhatsApp ou page Contact, seulement s'ils existent.
 *
 * @return void
 */
function evasions_checkout_help(): void {
	$url = '';

	if ( class_exists( '\Evasions\Core\WhatsApp' ) && '' !== \Evasions\Core\Config::instance()->whatsapp_number() ) {
		$url = \Evasions\Core\WhatsApp::link();
	}

	if ( '' === $url ) {
		$contact = evasions_page_link( 'contact', '' );
		$url     = $contact ? $contact['url'] : '';
	}

	if ( '' === $url ) {
		return;
	}

	echo '<aside class="ev-co-help"><h2>' . esc_html__( 'Une question ?', 'evasions' ) . '</h2><p>' . esc_html__( 'Écrivez-nous avant de commander.', 'evasions' ) . '</p><a class="ev-btn ev-btn--white" href="' . esc_url( $url ) . '">' . esc_html__( 'Nous contacter', 'evasions' ) . '</a></aside>';
}
add_action( 'woocommerce_checkout_after_order_review', 'evasions_checkout_help' );

/**
 * Garde le sélecteur de gouvernorat natif.
 *
 * WooCommerce l'enrichit avec select2 : une liste déroulante en JavaScript, à
 * restyler champ par champ, qui remplace le sélecteur du système. Sur mobile —
 * l'appareil de la grande majorité des clients — le sélecteur natif est plus
 * rapide, mieux adapté au pouce et accessible d'origine. Sans select2, le script
 * du checkout se rabat sur le `<select>` d'origine (il teste la présence de
 * `selectWoo` avant de l'utiliser).
 *
 * @return void
 */
function evasions_native_selects(): void {
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		wp_dequeue_script( 'selectWoo' );
		wp_dequeue_style( 'select2' );
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_native_selects', 100 );

/**
 * Allège le panier et le checkout des scripts inutiles à un COD (§9.1, §17.1).
 *
 * Le budget de ces deux pages est le plus serré du site (§17.1 : ≤ 400 Ko). On y
 * retire ce qui ne sert pas à commander en paiement à la livraison :
 *
 * - `wc-order-attribution` et `sourcebuster` : l'attribution marketing piste la
 *   provenance des visites pour la publicité. La boutique n'en fait pas usage, et
 *   la spec proscrit le script tiers autre que la mesure (§17.4) ; on retire donc
 *   ces scripts (les champs cachés restent sans effet).
 * - `wc-add-to-cart` : on n'ajoute rien au panier depuis le panier ni le checkout.
 * - `wc-cart-fragments` : rafraîchit le mini-panier en Ajax ; le panier est déjà
 *   la page affichée, et l'en-tête du checkout est allégé (sans panier, §9.1).
 *
 * Rien n'est touché sur la boutique ni la fiche produit, où ces scripts servent.
 *
 * @return void
 */
function evasions_lean_cart_checkout(): void {
	if ( ! function_exists( 'is_checkout' ) || ! ( is_checkout() || is_cart() ) ) {
		return;
	}

	foreach ( array( 'wc-order-attribution', 'sourcebuster', 'wc-add-to-cart', 'wc-cart-fragments' ) as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_lean_cart_checkout', 100 );

/**
 * Page de remerciement : bloc « Enregistrez le numéro, c'est nous » (§10).
 *
 * Sur un tunnel COD, un humain rappelle pour confirmer la commande (§21). Si le
 * client n'a pas notre numéro, il prend l'appel pour un démarchage et ne répond
 * pas — la commande tombe. Ce bloc l'invite à enregistrer le numéro tout de
 * suite, juste après avoir commandé, quand il est encore sur la page.
 *
 * Rien n'est inventé (§60) : le bloc n'existe que si un numéro de contact est
 * configuré (WhatsApp, sinon le téléphone du pied de page).
 *
 * @param int $order_id Order identifier.
 * @return void
 */
function evasions_thankyou_save_number( $order_id ): void {
	$wa_number = class_exists( '\Evasions\Core\Config' ) ? \Evasions\Core\Config::instance()->whatsapp_number() : '';
	$contact   = preg_replace( '/\s+/', '', evasions_const( 'EVASIONS_CONTACT_PHONE' ) );
	$number    = '' !== $contact ? $contact : $wa_number;

	if ( '' === $number ) {
		return;
	}

	$wa_link = ( '' !== $wa_number && class_exists( '\Evasions\Core\WhatsApp' ) ) ? \Evasions\Core\WhatsApp::link() : '';
	?>
	<section class="ev-save-number" aria-labelledby="ev-save-number-title">
		<h2 id="ev-save-number-title"><?php esc_html_e( 'Enregistrez le numéro, c’est nous', 'evasions' ); ?></h2>
		<p><?php esc_html_e( 'Nous vous appellerons pour confirmer votre commande et convenir de la livraison. Enregistrez ce numéro pour reconnaître notre appel.', 'evasions' ); ?></p>
		<p class="ev-save-number__num">
			<a href="tel:<?php echo esc_attr( $number ); ?>"><?php echo esc_html( $number ); ?></a>
		</p>
		<?php if ( '' !== $wa_link ) : ?>
			<a class="ev-btn ev-btn--outline" href="<?php echo esc_url( $wa_link ); ?>" target="_blank" rel="noopener nofollow">
				<?php esc_html_e( 'Nous écrire sur WhatsApp', 'evasions' ); ?>
			</a>
		<?php endif; ?>
	</section>
	<?php
}
add_action( 'woocommerce_thankyou', 'evasions_thankyou_save_number', 5 );
