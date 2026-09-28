<?php
/**
 * Réglages du thème : fonctionnalités, menus, ressources, structure du catalogue.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Déclare ce que le thème sait faire.
 *
 * @return void
 */
function evasions_setup(): void {
	load_theme_textdomain( 'evasions', get_template_directory() . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);

	// WooCommerce : le thème ne surcharge pas les gabarits, il s'appuie sur ceux du plugin.
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	/*
	 * « wc-product-gallery-slider » est REMIS le 27 septembre 2026, après un
	 * test en conditions réelles par le propriétaire. La décision du
	 * 26 septembre, qui l'avait retiré au titre de l'interdiction du carrousel
	 * (spec §7.3, audit UX-09), est renversée : une décision du propriétaire
	 * prime sur la spec là où elle s'en écarte. Ne pas le retirer à nouveau au
	 * nom du §7.3 sans une nouvelle décision.
	 *
	 * Deux conséquences assumées, à ne pas oublier : l'accès clavier du
	 * carrousel reste à vérifier sur un produit à plusieurs images (A11Y-03,
	 * rouvert), et WooCommerce charge de nouveau flexslider sur la fiche
	 * produit, dont le LCP dépasse déjà le budget §17.1 (PERF-02).
	 */
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary' => __( 'Menu principal', 'evasions' ),
			'footer'  => __( 'Pied de page — liens utiles', 'evasions' ),
		)
	);
}
add_action( 'after_setup_theme', 'evasions_setup' );

/**
 * Version d'un fichier du thème pour l'adresse de ses feuilles et scripts.
 *
 * La version du thème, suivie de la date de modification du fichier : le
 * navigateur recharge un fichier dès qu'il change, sans attendre un changement
 * de numéro de version.
 *
 * La date retenue est la PLUS RÉCENTE des deux — la source et son minifié.
 * Auparavant seule la source comptait, alors que c'est le minifié qui est servi
 * hors SCRIPT_DEBUG (voir evasions_use_minified_assets() plus bas) : relancer
 * build-assets.sh sans toucher la source laissait le `ver=` inchangé, donc un
 * navigateur qui gardait l'ancienne feuille — une correction invisible même en
 * rechargeant (audit ARCH-04). Prendre le maximum plutôt que reproduire ici le
 * choix source/minifié : deux copies d'une même décision finissent toujours par
 * diverger, et une version trop récente ne coûte qu'un rechargement.
 *
 * @param string $path Path relative to the theme root, e.g. 'assets/css/main.css'.
 * @return string
 */
function evasions_asset_version( string $path ): string {
	$dates = array();

	foreach ( array( $path, (string) preg_replace( '/\.(css|js)$/', '.min.$1', $path ) ) as $candidat ) {
		$fichier = get_theme_file_path( $candidat );
		if ( file_exists( $fichier ) ) {
			$dates[] = (int) filemtime( $fichier );
		}
	}

	return array() === $dates ? EVASIONS_THEME_VERSION : EVASIONS_THEME_VERSION . '.' . max( $dates );
}

/**
 * Charge la feuille de styles et le script du thème.
 *
 * Les polices (Manrope, DM Sans) sont servies par le thème lui-même : ses
 * règles `@font-face` sont en tête de `main.css`. Aucune requête vers un
 * service tiers, donc rien qui bloque l'affichage en attendant un autre serveur
 * (§27 du document directeur) et rien à déclarer côté vie privée.
 *
 * @return void
 */
function evasions_assets(): void {
	wp_enqueue_style(
		'evasions',
		get_template_directory_uri() . '/assets/css/main.css',
		array(),
		evasions_asset_version( 'assets/css/main.css' )
	);

	wp_enqueue_script(
		'evasions',
		get_template_directory_uri() . '/assets/js/main.js',
		array(),
		evasions_asset_version( 'assets/js/main.js' ),
		array(
			'in_footer' => true,
			'strategy'  => 'defer',
		)
	);

	/*
	 * Les deux libellés de la galerie produit : le script en a besoin pour
	 * nommer les vignettes qu'il rend atteignables au clavier, et ce sont des
	 * phrases lues par un lecteur d'écran — elles doivent donc passer par la
	 * traduction, comme le reste de la micro-copie. Le `%1$s` et le `%2$s` sont
	 * remplis par le script : rang de l'image, et nombre total.
	 *
	 * `wp_add_inline_script` plutôt qu'une dépendance : rien de nouveau n'est
	 * chargé, la déclaration voyage avec le fichier qu'elle accompagne.
	 */
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_add_inline_script(
			'evasions',
			'window.evasionsGallery = ' . wp_json_encode(
				array(
					/* translators: %1$s: rang de l'image, %2$s: nombre total d'images. */
					'thumb'   => __( 'Image %1$s sur %2$s', 'evasions' ),
					'gallery' => __( 'Images du produit', 'evasions' ),
				)
			) . ';',
			'before'
		);
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_assets' );

/**
 * Sert la version minifiée d'un fichier du thème quand elle existe (production).
 *
 * Plutôt que d'éditer chaque `wp_enqueue_*`, un seul filtre réécrit l'URL des
 * ressources du thème (`assets/css/*.css`, `assets/js/*.js`) vers leur `.min`
 * lorsque le fichier minifié existe et que le mode débogage est éteint. En
 * développement (`SCRIPT_DEBUG`), ou si le `.min` n'a pas été construit, la
 * source est servie telle quelle. Les minifiés se génèrent avec
 * `livraison/build-assets.sh`.
 *
 * @param string $src    URL de la ressource.
 * @param string $handle Identifiant WordPress (inutilisé, requis par le filtre).
 * @return string
 */
function evasions_use_minified_assets( $src, $handle ) {
	unset( $handle );

	if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
		return $src;
	}

	$uri = get_template_directory_uri();

	if ( ! is_string( $src ) || 0 !== strpos( $src, $uri ) ) {
		return $src;
	}

	if ( ! preg_match( '#/assets/(?:css|js)/[^/]+\.(?:css|js)(?:\?|$)#', $src ) || false !== strpos( $src, '.min.' ) ) {
		return $src;
	}

	$min       = preg_replace( '/\.(css|js)(\?|$)/', '.min.$1$2', $src );
	$rel       = strtok( substr( $min, strlen( $uri ) ), '?' );

	return file_exists( get_template_directory() . $rel ) ? $min : $src;
}
add_filter( 'style_loader_src', 'evasions_use_minified_assets', 20, 2 );
add_filter( 'script_loader_src', 'evasions_use_minified_assets', 20, 2 );

/**
 * Précharge les deux polices du premier écran : Manrope (titres) et DM Sans (texte).
 *
 * Sans cela, le navigateur ne découvre les polices qu'après avoir lu la feuille
 * de styles. `crossorigin` est obligatoire pour les polices, même locales.
 * L'italique, rare, n'est pas préchargée.
 *
 * @return void
 */
function evasions_font_preload(): void {
	foreach ( array( 'manrope-latin.woff2', 'dm-sans-latin.woff2' ) as $file ) {
		printf(
			'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "
",
			esc_url( get_template_directory_uri() . '/assets/fonts/' . $file )
		);
	}
}
add_action( 'wp_head', 'evasions_font_preload', 1 );

/**
 * Retire le script et les styles des émojis de WordPress (23 Ko sur chaque page).
 *
 * Les navigateurs récents affichent les émojis eux-mêmes ; ce script ne sert
 * qu'à les remplacer par des images sur de très vieux systèmes.
 *
 * @return void
 */
function evasions_lean_head(): void {
	remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
	remove_action( 'wp_print_styles', 'print_emoji_styles' );
	remove_action( 'wp_enqueue_scripts', 'wp_enqueue_emoji_styles' );
}
add_action( 'init', 'evasions_lean_head' );

/**
 * Allège l'accueil : ni « ajouter au panier » ni fragments de panier (§6.2).
 *
 * Les cartes produit de l'accueil sont sans bouton et entièrement cliquables
 * (décision de design §6.2) : le script `wc-add-to-cart` n'y sert à rien, et
 * `wc-cart-fragments` recharge le mini-panier en Ajax à chaque page — deux poids
 * morts sur la page dont le budget compte le plus (§17.1). Le compteur du panier
 * dans l'en-tête est rendu côté serveur, il n'a pas besoin des fragments.
 * WooCommerce enregistre ces scripts globalement ; on les retire sur l'accueil
 * seulement, sans toucher à la boutique, la fiche produit ou le panier.
 *
 * S'y ajoute `woocommerce` (audit PERF-05). Retirer les deux scripts ci-dessus
 * ne suffisait pas : `woocommerce.min.js` reste chargé partout, et il tire
 * derrière lui jQuery, jQuery Migrate, blockUI et js-cookie — dont jQuery, que
 * WordPress enregistre dans le `<head>` SANS `defer`, donc bloquant pour le
 * rendu. Or `woocommerce.min.js` ne sert qu'aux écrans de la boutique : envoi
 * du tri de la boucle, calculateur de livraison du panier, bandeau de boutique
 * fermé, champs quantité de WooCommerce. L'accueil (`front-page.php`) n'a
 * aucun des trois : ses cartes sont sans bouton (§6.2), son champ quantité
 * n'existe pas, son tri non plus. Le thème n'utilise pas jQuery
 * (`assets/js/main.js` est sans dépendance) et evasions-core non plus sur cet
 * écran : son seul code jQuery écoute `added_to_cart`, un événement que seul un
 * bouton d'ajout Ajax déclenche, et il se garde déjà par `window.jQuery`.
 *
 * @return void
 */
function evasions_lean_home_assets(): void {
	if ( is_front_page() || is_home() ) {
		wp_dequeue_script( 'wc-add-to-cart' );
		wp_dequeue_script( 'wc-cart-fragments' );
		wp_dequeue_script( 'woocommerce' );
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_lean_home_assets', 99 );

/**
 * Un script déclare-t-il jQuery dans ses dépendances, directement ou non ?
 *
 * Sert de preuve, à l'exécution, avant de retirer jQuery : plutôt que parier
 * sur ce que contient la file d'attente, on la lit. `$seen` coupe une éventuelle
 * dépendance circulaire (WordPress n'en produit pas, mais une extension tierce
 * mal déclarée ferait boucler la récursion).
 *
 * @param string     $handle  Identifiant du script.
 * @param WP_Scripts $scripts File d'attente des scripts.
 * @param array<string,bool> $seen Identifiants déjà visités.
 * @return bool
 */
function evasions_script_needs_jquery( string $handle, WP_Scripts $scripts, array $seen = array() ): bool {
	if ( isset( $seen[ $handle ] ) || ! isset( $scripts->registered[ $handle ] ) ) {
		return false;
	}

	$seen[ $handle ] = true;

	foreach ( (array) $scripts->registered[ $handle ]->deps as $dep ) {
		if ( in_array( $dep, array( 'jquery', 'jquery-core', 'jquery-migrate' ), true ) ) {
			return true;
		}

		if ( evasions_script_needs_jquery( (string) $dep, $scripts, $seen ) ) {
			return true;
		}
	}

	return false;
}

/**
 * Filet de sécurité : sur l'accueil, retire jQuery s'il n'y reste plus personne pour s'en servir.
 *
 * `evasions_lean_home_assets()` retire `woocommerce`, et WordPress n'imprime
 * pas une dépendance dont plus aucun script en file ne dépend : dans le cas
 * normal, jQuery part tout seul et cette fonction ne fait rien. Elle sert au
 * cas où une extension met `jquery` en file d'attente directement — ce qui le
 * ferait revenir, bloquant, dans le `<head>` (audit PERF-05).
 *
 * On ne retire jQuery qu'après avoir prouvé deux choses sur la page rendue :
 * aucun script encore en file ne le déclare en dépendance, et aucun code en
 * ligne n'est accroché à jQuery lui-même (`wp_add_inline_script`). Si l'une des
 * deux échoue, on ne touche à rien : une page un peu plus lourde vaut mieux
 * qu'une page cassée.
 *
 * @return void
 */
function evasions_home_drop_jquery(): void {
	if ( ! is_front_page() && ! is_home() ) {
		return;
	}

	$scripts = wp_scripts();
	$jquery  = array( 'jquery', 'jquery-core', 'jquery-migrate' );

	foreach ( $scripts->queue as $handle ) {
		if ( in_array( $handle, $jquery, true ) ) {
			continue;
		}

		if ( evasions_script_needs_jquery( (string) $handle, $scripts ) ) {
			return;
		}
	}

	foreach ( $jquery as $handle ) {
		if ( ! empty( $scripts->get_data( $handle, 'before' ) ) || ! empty( $scripts->get_data( $handle, 'after' ) ) ) {
			return;
		}
	}

	foreach ( $jquery as $handle ) {
		wp_dequeue_script( $handle );
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_home_drop_jquery', 100 );

/**
 * URL d'un fichier du dossier `assets/img`.
 *
 * @param string $file File name inside assets/img.
 * @return string
 */
function evasions_img( string $file ): string {
	return get_template_directory_uri() . '/assets/img/' . $file;
}

/**
 * URL de la variante AVIF d'un WebP, si elle a été générée.
 *
 * L'AVIF est produit par `livraison/build-images.sh`. On vérifie sa présence
 * sur le disque : sans build, on retombe proprement sur le WebP (§17).
 *
 * @param string $webp_file Nom du fichier WebP (ex. « hero-mobile.webp »).
 * @return string URL de l'AVIF, ou chaîne vide s'il n'existe pas.
 */
function evasions_img_avif( string $webp_file ): string {
	$avif = preg_replace( '/\.webp$/', '.avif', $webp_file );

	if ( null === $avif || $avif === $webp_file ) {
		return '';
	}

	return file_exists( get_template_directory() . '/assets/img/' . $avif ) ? evasions_img( $avif ) : '';
}

/**
 * Balise <picture> AVIF + repli WebP pour une image du thème (§17).
 *
 * Sert l'AVIF quand il existe, le WebP sinon. `$args` accepte class (sur la
 * <picture>), img_class, et les attributs d'image usuels (width, height, alt,
 * loading, decoding, sizes, fetchpriority). L'alt vaut « » (décoratif) par défaut.
 *
 * @param string              $webp_file Nom du fichier WebP.
 * @param array<string,mixed> $args      Options et attributs.
 * @return string
 */
function evasions_picture( string $webp_file, array $args = array() ): string {
	$webp = evasions_img( $webp_file );
	$avif = evasions_img_avif( $webp_file );

	$class     = isset( $args['class'] ) ? ' class="' . esc_attr( (string) $args['class'] ) . '"' : '';
	$img_class = isset( $args['img_class'] ) ? ' class="' . esc_attr( (string) $args['img_class'] ) . '"' : '';

	$attrs = ' alt="' . esc_attr( (string) ( $args['alt'] ?? '' ) ) . '"';
	foreach ( array( 'width', 'height', 'loading', 'decoding', 'sizes', 'fetchpriority' ) as $key ) {
		if ( isset( $args[ $key ] ) ) {
			$attrs .= ' ' . $key . '="' . esc_attr( (string) $args[ $key ] ) . '"';
		}
	}

	$html = '<picture' . $class . '>';
	if ( '' !== $avif ) {
		$html .= '<source type="image/avif" srcset="' . esc_url( $avif ) . '">';
	}
	$html .= '<img' . $img_class . ' src="' . esc_url( $webp ) . '"' . $attrs . '>';
	$html .= '</picture>';

	return $html;
}

/**
 * Image d'une section : le visuel téléversé en administration s'il existe,
 * sinon l'image par défaut du thème (AVIF + repli WebP).
 *
 * Une image téléversée est rendue par `wp_get_attachment_image()`, qui fournit
 * son propre `srcset` responsive. L'image par défaut passe par
 * `evasions_picture()` (AVIF + WebP).
 *
 * @param string              $key         Clé de section (ex. « banner », « hero_desktop »).
 * @param string              $default_webp Fichier WebP par défaut du thème.
 * @param array<string,mixed> $args        Options : img_class, width, height, loading, decoding, sizes, fetchpriority, alt, size.
 * @return string
 */
function evasions_section_image( string $key, string $default_webp, array $args = array() ): string {
	$id = evasions_section_image_id( $key );

	if ( $id > 0 ) {
		$attr = array( 'alt' => (string) ( $args['alt'] ?? '' ) );
		foreach ( array( 'loading', 'decoding', 'sizes', 'fetchpriority' ) as $k ) {
			if ( isset( $args[ $k ] ) ) {
				$attr[ $k ] = (string) $args[ $k ];
			}
		}
		if ( isset( $args['img_class'] ) ) {
			$attr['class'] = (string) $args['img_class'];
		}

		return wp_get_attachment_image( $id, (string) ( $args['size'] ?? 'large' ), false, $attr );
	}

	return evasions_picture( $default_webp, $args );
}

/**
 * Identifiant validé de l'image d'une section, réglée dans le Customizer, ou 0.
 *
 * @param string $key hero_desktop | hero_mobile | banner | community | cta | universe_plage | universe_camping.
 * @return int
 */
function evasions_section_image_id( string $key ): int {
	$id = absint( evasions_setting( 'evasions_img_' . $key ) );

	return ( $id > 0 && wp_attachment_is_image( $id ) ) ? $id : 0;
}

/**
 * Rang de la carte en cours dans la boucle d'un listing, en commençant à 1.
 *
 * Vit ici, avec les autres décisions de chargement d'images, parce qu'il ne
 * sert qu'à cela : reconnaître la PREMIÈRE carte, seule image du premier écran,
 * à qui revient `fetchpriority="high"` (§17.1, audit PERF-03). Les cartes
 * suivantes restent en `lazy`.
 *
 * Le thème remplace `content-product.php` (voir `evasions_listing_item_template()`
 * dans `inc/listing.php`) : le compteur de boucle de WooCommerce est tenu par
 * des gabarits que ce remplacement contourne, le thème ne peut donc pas s'y
 * fier. Il tient le sien, qui ne dépend que de son propre gabarit.
 *
 * Il n'est pas remis à zéro d'une boucle à l'autre, et c'est voulu : une page
 * ne doit désigner qu'une seule image prioritaire, même si elle affiche deux
 * listings.
 *
 * @return int
 */
function evasions_listing_item_rank(): int {
	static $rank = 0;

	++$rank;

	return $rank;
}

/**
 * Point de rupture du hero, en pixels : au-dessus, la photo desktop.
 *
 * Écrit une seule fois parce que trois endroits doivent dire la même chose —
 * le `media` des `<source>`, celui du préchargement et le `@media` de la
 * feuille de styles. Deux copies d'un nombre finissent par diverger, et ici
 * diverger veut dire télécharger deux photos au lieu d'une.
 *
 * @return int
 */
function evasions_hero_breakpoint(): int {
	return 900;
}

/**
 * Les images du hero, dans l'ordre où le navigateur doit les examiner.
 *
 * Le gabarit (`template-parts/hero.php`) en fait des `<source>` et un `<img>`,
 * le préchargement du `<head>` en tire le premier candidat de chaque écran
 * (audit PERF-02). Les deux lisent la MÊME liste : un préchargement qui ne
 * désigne pas exactement l'image retenue par `<picture>` en télécharge une
 * deuxième pour rien.
 *
 * Une image téléversée dans le Customizer remplace le visuel du thème et n'a
 * pas de variante AVIF : son entrée n'a donc pas de `type`.
 *
 * Le dernier élément est toujours celui de l'`<img>` de repli (mobile).
 *
 * @return array<int,array{url:string,type:string,media:string}>
 */
function evasions_hero_media(): array {
	$desktop = '(min-width: ' . evasions_hero_breakpoint() . 'px)';
	$sources = array();

	$desktop_id  = evasions_section_image_id( 'hero_desktop' );
	$desktop_url = $desktop_id > 0 ? (string) wp_get_attachment_image_url( $desktop_id, 'full' ) : '';

	if ( '' !== $desktop_url ) {
		$sources[] = array(
			'url'    => $desktop_url,
			'type'   => '',
			'media'  => $desktop,
			'srcset' => (string) wp_get_attachment_image_srcset( $desktop_id, 'full' ),
			'sizes'  => '100vw',
		);
	} else {
		$avif = evasions_img_avif( 'hero-desktop.webp' );

		if ( '' !== $avif ) {
			$sources[] = array(
				'url'   => $avif,
				'type'  => 'image/avif',
				'media' => $desktop,
			);
		}

		$sources[] = array(
			'url'   => evasions_img( 'hero-desktop.webp' ),
			'type'  => 'image/webp',
			'media' => $desktop,
		);
	}

	$mobile_id  = evasions_section_image_id( 'hero_mobile' );
	$mobile_url = $mobile_id > 0 ? (string) wp_get_attachment_image_url( $mobile_id, 'full' ) : '';

	if ( '' === $mobile_url ) {
		$avif = evasions_img_avif( 'hero-mobile.webp' );

		if ( '' !== $avif ) {
			$sources[] = array(
				'url'   => $avif,
				'type'  => 'image/avif',
				'media' => '',
			);
		}
	}

	$sources[] = array(
		'url'    => '' !== $mobile_url ? $mobile_url : evasions_img( 'hero-mobile.webp' ),
		'type'   => '',
		'media'  => '',
		'srcset' => $mobile_id > 0 ? (string) wp_get_attachment_image_srcset( $mobile_id, 'full' ) : '',
		'sizes'  => $mobile_id > 0 ? '100vw' : '',
	);

	/*
	 * Toutes les entrées portent les mêmes cinq clés, pour que le gabarit et le
	 * préchargement lisent sans rien tester. Les visuels livrés avec le thème
	 * n'ont pas de sous-tailles, donc pas de srcset : ils sont déjà dimensionnés.
	 *
	 * Pourquoi un srcset sur les photos TÉLÉVERSÉES (28/09/2026, audit PERF-02) :
	 * `wp_get_attachment_image_url( $id, 'full' )` sert le fichier entier. Mesuré
	 * sur la pile : 1 440 px de large et 461 Ko envoyés à un téléphone de 390 px,
	 * alors que la sous-taille 768 px existait déjà dans la médiathèque. C'est la
	 * part du LCP que le préchargement ne pouvait pas rattraper — on ne découvre
	 * pas plus vite une image trop lourde. Le §17 demande d'ailleurs un srcset et
	 * 1 400 px au maximum ; une photo en haute définition, comme celles annoncées
	 * pour l'ouverture, aggraverait encore l'écart.
	 */
	foreach ( $sources as $index => $source ) {
		$sources[ $index ] = $source + array(
			'srcset' => '',
			'sizes'  => '',
		);
	}

	return $sources;
}

/**
 * Précharge l'image d'ouverture de l'accueil, en tête du `<head>` (audit PERF-02).
 *
 * Le LCP de l'accueil est dominé par le délai de DÉCOUVERTE de l'image, pas par
 * son poids (mesure du 28 septembre 2026 : 59 % du LCP passés à attendre que la
 * requête parte, 3 % à la télécharger). L'image du hero est la plus grande du
 * premier écran — `.ev-hero` fait 420 px de haut et la photo la remplit en
 * `object-fit: cover` —, elle porte déjà `fetchpriority="high"`, mais elle
 * n'est découverte qu'après l'en-tête du document, ses feuilles de styles et
 * ses scripts. Ce `<link>` la fait découvrir en premier, avant même les polices
 * (`evasions_font_preload`, priorité 1), d'où la priorité 0.
 *
 * Deux garde-fous contre le double téléchargement :
 *
 * - un seul candidat par écran, le premier — c'est celui que `<picture>` retient
 *   d'un navigateur moderne ; un navigateur sans AVIF ignore le préchargement
 *   (`type` non pris en charge) et retombe sur le `<source>` WebP, sans requête
 *   perdue ;
 * - le `media` du candidat mobile est l'exact complément de celui du desktop
 *   (`899.98px` contre `900px`), sinon les deux préchargements se déclencheraient
 *   ensemble sur grand écran.
 *
 * @return void
 */
function evasions_hero_preload(): void {
	// Seul `front-page.php` rend le hero ; ailleurs, ce préchargement téléchargerait une image jamais affichée.
	if ( ! is_front_page() ) {
		return;
	}

	$mobile = '(max-width: ' . number_format( evasions_hero_breakpoint() - 0.02, 2, '.', '' ) . 'px)';
	$vus    = array();

	foreach ( evasions_hero_media() as $source ) {
		$media = '' !== $source['media'] ? $source['media'] : $mobile;

		if ( isset( $vus[ $media ] ) ) {
			continue;
		}

		$vus[ $media ] = true;

		/*
		 * `imagesrcset` et `imagesizes` reprennent mot pour mot ce que le
		 * gabarit met sur son `<source>` : le navigateur doit choisir le même
		 * fichier des deux côtés, sinon il en télécharge deux. `href` reste le
		 * repli pour les navigateurs qui ignorent `imagesrcset`.
		 */
		$responsive = '';
		if ( '' !== $source['srcset'] ) {
			$responsive = ' imagesrcset="' . esc_attr( $source['srcset'] ) . '"';

			if ( '' !== $source['sizes'] ) {
				$responsive .= ' imagesizes="' . esc_attr( $source['sizes'] ) . '"';
			}
		}

		printf(
			'<link rel="preload" as="image" href="%1$s" media="%2$s"%3$s%4$s fetchpriority="high">' . "\n",
			esc_url( $source['url'] ),
			esc_attr( $media ),
			'' !== $source['type'] ? ' type="' . esc_attr( $source['type'] ) . '"' : '', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attribut construit et échappé juste au-dessus.
			$responsive // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- attributs construits et échappés juste au-dessus.
		);
	}
}
add_action( 'wp_head', 'evasions_hero_preload', 0 );

/**
 * Retire les feuilles de style « mise en page » de WooCommerce.
 *
 * `woocommerce-layout`, `woocommerce-smallscreen` et `woocommerce-general`
 * flottent la galerie à 48 %, colorent les boutons en violet et dessinent des
 * onglets à l'ancienne : le design dit autre chose sur chacun de ces points, et
 * les combattre par des sélecteurs plus spécifiques est fragile. Le thème
 * fournit à la place `woocommerce-base.css`, court, qui couvre ce dont les
 * écrans WooCommerce ont besoin (notices, champs, tableaux, étoiles).
 *
 * Les styles des blocs WooCommerce (`wc-blocks-*`) ne sont pas touchés : le
 * checkout par blocs continue de s'afficher comme prévu.
 *
 * @param array<string,array<string,mixed>> $styles WooCommerce stylesheets.
 * @return array<string,array<string,mixed>>
 */
function evasions_woocommerce_styles( $styles ) {
	unset( $styles['woocommerce-general'], $styles['woocommerce-layout'], $styles['woocommerce-smallscreen'] );

	return $styles;
}
add_filter( 'woocommerce_enqueue_styles', 'evasions_woocommerce_styles' );

/**
 * Charge la feuille de base sur les pages WooCommerce.
 *
 * @return void
 */
function evasions_woocommerce_base_style(): void {
	if ( function_exists( 'is_woocommerce' ) && ( is_woocommerce() || is_cart() || is_checkout() || is_account_page() ) ) {
		wp_enqueue_style(
			'evasions-woocommerce',
			get_template_directory_uri() . '/assets/css/woocommerce-base.css',
			array( 'evasions' ),
			evasions_asset_version( 'assets/css/woocommerce-base.css' )
		);
	}
}
add_action( 'wp_enqueue_scripts', 'evasions_woocommerce_base_style', 25 );
