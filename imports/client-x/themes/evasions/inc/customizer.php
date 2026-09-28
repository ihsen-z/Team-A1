<?php
/**
 * Personnalisation de l'accueil et des réseaux — dans le thème (Customizer).
 *
 * Choix d'architecture : la présentation (accroche, bannière, titres de sections,
 * slogan, images de fond, réseaux sociaux) est éditée DEPUIS LE THÈME, via le
 * Customizer natif de WordPress (Apparence → Personnaliser), avec aperçu en
 * direct. Le thème ne dépend d'aucune classe du plugin pour ces réglages : le
 * couplage thème ↔ evasions-core est réduit au minimum. Le plugin garde ce qui
 * doit survivre à un changement de thème (commande COD, checkout, tracking,
 * données produit, pages de contenu, coordonnées).
 *
 * Les valeurs par défaut sont partagées entre le Customizer et les gabarits
 * (`evasions_default()`) : une seule source de vérité.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Valeurs par défaut des réglages du thème.
 *
 * Un champ vide « rien n'est inventé » (§60) reste vide : le bloc n'est alors
 * pas rendu. C'est le cas du 3e argument de réassurance (« Produits
 * sélectionnés et testés »), à confirmer par le propriétaire.
 *
 * @return array<string,mixed>
 */
function evasions_customizer_defaults(): array {
	static $defaults = null;

	if ( null !== $defaults ) {
		return $defaults;
	}

	$defaults = array(
		// Accroche.
		'evasions_hero_title_lead'   => 'Préparez votre prochaine',
		'evasions_hero_title_em'     => 'évasion',
		'evasions_hero_subtitle'     => 'Des accessoires pratiques pour profiter pleinement de la plage, du camping et de vos aventures en plein air.',
		'evasions_hero_cta_primary'  => 'Découvrir la collection',
		'evasions_hero_cta_secondary' => 'Explorer camping & randonnée',
		// Réassurance : 3e argument, vide par défaut (§60).
		'evasions_benefit3_title'    => '',
		'evasions_benefit3_sub'      => '',
		// Bannière.
		'evasions_banner_title'      => 'Des produits pensés pour les vrais aventuriers',
		'evasions_banner_text'       => 'Qualité, confort et praticité pour tous vos usages.',
		'evasions_banner_cta'        => 'Découvrir nos produits',
		// Titres de sections.
		// Le §1.5 refuse « incontournable » : le titre DÉCRIT la section (les produits
		// mis en avant de WooCommerce) au lieu de la vanter (audit REC-03, 28/09/2026).
		'evasions_title_featured'    => 'Nos produits mis en avant',
		'evasions_title_reviews'     => 'Avis de nos clients',
		'evasions_title_universes'   => 'Nos univers',
		// Univers.
		'evasions_plage_sub'         => 'Soleil · Détente · Loisirs',
		'evasions_camping_title'     => 'Le plein d’aventures vous attend',
		'evasions_camping_text'      => 'Des équipements fiables, pratiques et résistants pour profiter de la nature en toute sérénité.',
		// Pied de page.
		'evasions_footer_tagline'    => 'Évasion · Nature · Aventure · Liberté',
		// Page Packs : surtitre et image de la bannière (maquette « Pages Infos »).
		'evasions_packs_over'          => 'Packs Évasion',
		'evasions_img_packs'           => 0,
		// Bandeau « Pourquoi choisir… » (boutique, catégories, univers).
		// Arguments vides = ceux de la réassurance de l'accueil (`evasions_benefits()`).
		'evasions_why_enabled'         => true,
		'evasions_why_title'           => '',
		'evasions_why_color_title'     => '',
		'evasions_why_color_text'      => '',
		'evasions_why_color_icon_bg'   => '',
		'evasions_why_color_icon'      => '',
		'evasions_why_item1_icon'      => 'truck',
		'evasions_why_item1_title'     => '',
		'evasions_why_item1_sub'       => '',
		'evasions_why_item2_icon'      => 'card',
		'evasions_why_item2_title'     => '',
		'evasions_why_item2_sub'       => '',
		'evasions_why_item3_icon'      => 'check',
		'evasions_why_item3_title'     => '',
		'evasions_why_item3_sub'       => '',
		'evasions_why_item4_icon'      => 'clock',
		'evasions_why_item4_title'     => '',
		'evasions_why_item4_sub'       => '',
		'evasions_img_prefooter'       => 0,
		// Communauté (accueil, avant le pied de page) — remplace « Pourquoi choisir ».
		'evasions_community_enabled'        => true,
		'evasions_community_title'          => 'Rejoignez notre communauté',
		'evasions_community_text'           => 'Recevez nos nouveautés, conseils et offres exclusives.',
		'evasions_community_button'         => 'S’inscrire',
		'evasions_community_placeholder'    => 'Votre adresse e-mail',
		'evasions_community_form_action'    => '',
		'evasions_community_color_title'    => '',
		'evasions_community_color_text'     => '',
		'evasions_community_color_panel'    => '',
		'evasions_community_color_btn'      => '',
		'evasions_community_color_btn_text' => '',
		'evasions_img_community'            => 0,
		// Appel à l'aventure (fiche produit, avant le pied de page).
		// Couleurs vides = jetons --ev-* de la feuille de style (§2.1) ; on ne
		// fige une valeur ici que si le propriétaire en choisit une.
		'evasions_cta_enabled'       => true,
		'evasions_cta_title'         => 'Prêt pour votre prochaine aventure ?',
		'evasions_cta_text'          => 'Équipez-vous dès maintenant et partez explorer !',
		'evasions_cta_button'        => 'Voir tous les produits',
		'evasions_cta_url'           => '',
		'evasions_cta_color_title'   => '',
		'evasions_cta_color_text'    => '',
		'evasions_cta_color_btn'     => '',
		'evasions_cta_color_btn_text' => '',
		'evasions_img_cta'           => 0,
		// Images de fond : identifiant de pièce jointe, 0 = image par défaut du thème.
		'evasions_img_hero_desktop'     => 0,
		'evasions_img_hero_mobile'      => 0,
		'evasions_img_banner'           => 0,
		'evasions_img_universe_plage'   => 0,
		'evasions_img_universe_camping' => 0,
		// Réseaux sociaux.
		'evasions_social_facebook'   => '',
		'evasions_social_instagram'  => '',
		'evasions_social_tiktok'     => '',
		'evasions_social_youtube'    => '',
	);

	return $defaults;
}

/**
 * Un réglage du thème, avec son défaut partagé.
 *
 * @param string $key Clé du réglage.
 * @return mixed
 */
function evasions_setting( string $key ) {
	$defaults = evasions_customizer_defaults();

	return get_theme_mod( $key, $defaults[ $key ] ?? '' );
}

/**
 * Assainit un nom de pictogramme : seuls ceux du thème sont acceptés.
 *
 * @param string $value Valeur soumise.
 * @return string
 */
function evasions_sanitize_icon( $value ): string {
	return evasions_icon_name( (string) $value );
}

/**
 * Enregistre les sections, réglages et contrôles du Customizer.
 *
 * @param WP_Customize_Manager $wp_customize Gestionnaire du Customizer.
 * @return void
 */
function evasions_customize_register( $wp_customize ): void {
	$defaults = evasions_customizer_defaults();

	$wp_customize->add_panel(
		'evasions',
		array(
			'title'    => __( 'EVASIONS', 'evasions' ),
			'priority' => 30,
		)
	);

	$wp_customize->add_section(
		'evasions_home',
		array(
			'title' => __( 'Accueil — textes', 'evasions' ),
			'panel' => 'evasions',
		)
	);
	$wp_customize->add_section(
		'evasions_home_images',
		array(
			'title'       => __( 'Accueil — images de fond', 'evasions' ),
			'description' => __( 'Un champ vide garde l’image par défaut du thème. Prévoyez des visuels larges et nets (≥ 1200 px).', 'evasions' ),
			'panel'       => 'evasions',
		)
	);
	$wp_customize->add_section(
		'evasions_packs',
		array(
			'title'       => __( 'Page Packs — bannière', 'evasions' ),
			'description' => __( 'Bannière photo en tête de la page Packs. Le titre est celui de la page, la phrase son extrait (Options de la page → Extrait).', 'evasions' ),
			'panel'       => 'evasions',
		)
	);
	$wp_customize->add_section(
		'evasions_why',
		array(
			'title'       => __( 'Boutique — pourquoi choisir', 'evasions' ),
			'description' => __( 'Bandeau photo affiché en bas de la boutique, des catégories et des univers. Un argument sans titre n’est pas affiché ; les quatre vides, le bandeau reprend les arguments de la réassurance de l’accueil. Une couleur vide garde celle du thème.', 'evasions' ),
			'panel'       => 'evasions',
		)
	);
	$wp_customize->add_section(
		'evasions_community',
		array(
			'title'       => __( 'Accueil — communauté', 'evasions' ),
			'description' => __( 'Bandeau d’inscription affiché en bas de l’accueil, des listings et des univers. Un titre et un bouton vides retirent le bandeau. Une couleur vide garde celle du thème.', 'evasions' ),
			'panel'       => 'evasions',
		)
	);
	$wp_customize->add_section(
		'evasions_product_cta',
		array(
			'title'       => __( 'Fiche produit — appel à l’aventure', 'evasions' ),
			'description' => __( 'Bandeau affiché en bas de chaque fiche produit, juste avant le pied de page. Un titre et un bouton vides retirent le bandeau. Une couleur vide garde celle du thème.', 'evasions' ),
			'panel'       => 'evasions',
		)
	);
	$wp_customize->add_section(
		'evasions_social',
		array(
			'title'       => __( 'Réseaux sociaux', 'evasions' ),
			'description' => __( 'Adresses de vos pages. Une adresse vide retire l’icône du pied de page.', 'evasions' ),
			'panel'       => 'evasions',
		)
	);

	// Champs texte simples : clé => [libellé, section].
	$texts = array(
		'evasions_hero_title_lead'    => array( __( 'Accroche — début du titre', 'evasions' ), 'evasions_home' ),
		'evasions_hero_title_em'      => array( __( 'Accroche — mot mis en avant', 'evasions' ), 'evasions_home' ),
		'evasions_hero_cta_primary'   => array( __( 'Accroche — bouton principal', 'evasions' ), 'evasions_home' ),
		'evasions_hero_cta_secondary' => array( __( 'Accroche — bouton secondaire', 'evasions' ), 'evasions_home' ),
		'evasions_benefit3_title'     => array( __( 'Réassurance — 3e argument : titre (ex. « Produits sélectionnés »)', 'evasions' ), 'evasions_home' ),
		'evasions_benefit3_sub'       => array( __( 'Réassurance — 3e argument : sous-titre (ex. « et testés »)', 'evasions' ), 'evasions_home' ),
		'evasions_banner_title'       => array( __( 'Bannière — titre', 'evasions' ), 'evasions_home' ),
		'evasions_banner_cta'         => array( __( 'Bannière — bouton', 'evasions' ), 'evasions_home' ),
		'evasions_title_featured'     => array( __( 'Titre de la section des produits mis en avant', 'evasions' ), 'evasions_home' ),
		'evasions_title_reviews'      => array( __( 'Titre « Avis de nos clients »', 'evasions' ), 'evasions_home' ),
		'evasions_title_universes'    => array( __( 'Titre « Nos univers »', 'evasions' ), 'evasions_home' ),
		'evasions_plage_sub'          => array( __( 'Univers Plage — sous-titre', 'evasions' ), 'evasions_home' ),
		'evasions_camping_title'      => array( __( 'Univers Camping — titre éditorial', 'evasions' ), 'evasions_home' ),
		'evasions_footer_tagline'     => array( __( 'Slogan du pied de page', 'evasions' ), 'evasions_home' ),
		'evasions_packs_over'         => array( __( 'Packs — surtitre de la bannière', 'evasions' ), 'evasions_packs' ),
		'evasions_why_title'          => array( __( 'Pourquoi choisir — titre (vide : « Pourquoi choisir <nom du site> ? »)', 'evasions' ), 'evasions_why' ),
		'evasions_why_item1_title'    => array( __( 'Argument 1 — titre', 'evasions' ), 'evasions_why' ),
		'evasions_why_item1_sub'      => array( __( 'Argument 1 — sous-titre', 'evasions' ), 'evasions_why' ),
		'evasions_why_item2_title'    => array( __( 'Argument 2 — titre', 'evasions' ), 'evasions_why' ),
		'evasions_why_item2_sub'      => array( __( 'Argument 2 — sous-titre', 'evasions' ), 'evasions_why' ),
		'evasions_why_item3_title'    => array( __( 'Argument 3 — titre', 'evasions' ), 'evasions_why' ),
		'evasions_why_item3_sub'      => array( __( 'Argument 3 — sous-titre', 'evasions' ), 'evasions_why' ),
		'evasions_why_item4_title'    => array( __( 'Argument 4 — titre', 'evasions' ), 'evasions_why' ),
		'evasions_why_item4_sub'      => array( __( 'Argument 4 — sous-titre', 'evasions' ), 'evasions_why' ),
		'evasions_community_title'    => array( __( 'Communauté — titre', 'evasions' ), 'evasions_community' ),
		'evasions_community_button'   => array( __( 'Communauté — texte du bouton', 'evasions' ), 'evasions_community' ),
		'evasions_community_placeholder' => array( __( 'Communauté — texte indicatif du champ e-mail', 'evasions' ), 'evasions_community' ),
		'evasions_cta_title'          => array( __( 'Appel à l’aventure — titre', 'evasions' ), 'evasions_product_cta' ),
		'evasions_cta_button'         => array( __( 'Appel à l’aventure — texte du bouton', 'evasions' ), 'evasions_product_cta' ),
	);

	foreach ( $texts as $key => $conf ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_text_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $conf[0],
				'section' => $conf[1],
				'type'    => 'text',
			)
		);
	}

	// Champs multilignes.
	$textareas = array(
		'evasions_hero_subtitle' => __( 'Accroche — sous-titre', 'evasions' ),
		'evasions_banner_text'   => __( 'Bannière — texte', 'evasions' ),
		'evasions_camping_text'  => __( 'Univers Camping — texte éditorial', 'evasions' ),
	);

	foreach ( $textareas as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_textarea_field',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'evasions_home',
				'type'    => 'textarea',
			)
		);
	}

	// Images de fond (contrôle média : identifiant de pièce jointe).
	$images = array(
		'evasions_img_hero_desktop'     => __( 'Accroche — image (desktop)', 'evasions' ),
		'evasions_img_hero_mobile'      => __( 'Accroche — image (mobile)', 'evasions' ),
		'evasions_img_banner'           => __( 'Bannière — image', 'evasions' ),
		'evasions_img_prefooter'        => __( 'Pourquoi choisir — image de fond', 'evasions' ),
		'evasions_img_universe_plage'   => __( 'Univers Plage — image', 'evasions' ),
		'evasions_img_universe_camping' => __( 'Univers Camping — image', 'evasions' ),
	);

	foreach ( $images as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => 0,
				'sanitize_callback' => 'absint',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Media_Control(
				$wp_customize,
				$key,
				array(
					'label'     => $label,
					'section'   => 'evasions_home_images',
					'mime_type' => 'image',
				)
			)
		);
	}

	// Pourquoi choisir : affichage, pictogrammes et couleurs.
	$wp_customize->add_setting(
		'evasions_why_enabled',
		array(
			'default'           => $defaults['evasions_why_enabled'],
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'evasions_why_enabled',
		array(
			'label'   => __( 'Afficher le bandeau sur la boutique et les catégories', 'evasions' ),
			'section' => 'evasions_why',
			'type'    => 'checkbox',
		)
	);

	// Pictogrammes : liste fermée, celle de `evasions_icon_paths()`.
	$icon_choices = array();
	foreach ( array_keys( evasions_icon_paths() ) as $icon_name ) {
		$icon_choices[ $icon_name ] = $icon_name;
	}

	for ( $i = 1; $i <= 4; $i++ ) {
		$key = 'evasions_why_item' . $i . '_icon';

		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'evasions_sanitize_icon',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				/* translators: %d: rank of the argument in the band */
				'label'   => sprintf( __( 'Argument %d — pictogramme', 'evasions' ), $i ),
				'section' => 'evasions_why',
				'type'    => 'select',
				'choices' => $icon_choices,
			)
		);
	}

	// Couleurs : un champ vide laisse le jeton --ev-* du thème s'appliquer.
	$why_colors = array(
		'evasions_why_color_title'   => __( 'Couleur du titre', 'evasions' ),
		'evasions_why_color_text'    => __( 'Couleur du texte des arguments', 'evasions' ),
		'evasions_why_color_icon_bg' => __( 'Couleur de la pastille des pictogrammes', 'evasions' ),
		'evasions_why_color_icon'    => __( 'Couleur des pictogrammes', 'evasions' ),
	);

	foreach ( $why_colors as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$key,
				array(
					'label'   => $label,
					'section' => 'evasions_why',
				)
			)
		);
	}

	// Communauté : affichage, texte long, lien, couleurs et image.
	$wp_customize->add_setting(
		'evasions_community_enabled',
		array(
			'default'           => $defaults['evasions_community_enabled'],
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'evasions_community_enabled',
		array(
			'label'   => __( 'Afficher le bandeau sur l’accueil', 'evasions' ),
			'section' => 'evasions_community',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'evasions_community_text',
		array(
			'default'           => $defaults['evasions_community_text'],
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'evasions_community_text',
		array(
			'label'   => __( 'Communauté — sous-titre', 'evasions' ),
			'section' => 'evasions_community',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'evasions_community_form_action',
		array(
			'default'           => $defaults['evasions_community_form_action'],
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'evasions_community_form_action',
		array(
			'label'       => __( 'Communauté — adresse du formulaire', 'evasions' ),
			'description' => __( 'Adresse d’action fournie par votre outil d’e-mailing (Mailchimp, Brevo…). Tant qu’elle est vide, le champ s’affiche mais n’envoie nulle part. À défaut, la constante EVASIONS_NEWSLETTER_FORM_ACTION est utilisée.', 'evasions' ),
			'section'     => 'evasions_community',
			'type'        => 'url',
		)
	);

	// Couleurs : un champ vide laisse le jeton --ev-* du thème s'appliquer.
	$community_colors = array(
		'evasions_community_color_title'    => __( 'Couleur du titre', 'evasions' ),
		'evasions_community_color_text'     => __( 'Couleur du sous-titre', 'evasions' ),
		'evasions_community_color_panel'    => __( 'Couleur du panneau', 'evasions' ),
		'evasions_community_color_btn'      => __( 'Couleur du bouton', 'evasions' ),
		'evasions_community_color_btn_text' => __( 'Couleur du texte du bouton', 'evasions' ),
	);

	foreach ( $community_colors as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$key,
				array(
					'label'   => $label,
					'section' => 'evasions_community',
				)
			)
		);
	}

	$wp_customize->add_setting(
		'evasions_img_packs',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'evasions_img_packs',
			array(
				'label'       => __( 'Packs — image de la bannière', 'evasions' ),
				'description' => __( 'Visuel large et net (≥ 1400 px). Vide : l’image par défaut du thème.', 'evasions' ),
				'section'     => 'evasions_packs',
				'mime_type'   => 'image',
			)
		)
	);

	$wp_customize->add_setting(
		'evasions_img_community',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'evasions_img_community',
			array(
				'label'       => __( 'Communauté — image', 'evasions' ),
				'description' => __( 'Visuel large et net (≥ 1000 px). Vide : l’image par défaut du thème.', 'evasions' ),
				'section'     => 'evasions_community',
				'mime_type'   => 'image',
			)
		)
	);

	// Appel à l'aventure : texte long, lien, couleurs et image.
	// Ces réglages vivent dans le thème : ils ne décrivent que la présentation
	// d'un bandeau, aucune donnée métier (couplage thème → plugin nul).
	$wp_customize->add_setting(
		'evasions_cta_enabled',
		array(
			'default'           => $defaults['evasions_cta_enabled'],
			'sanitize_callback' => 'wp_validate_boolean',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'evasions_cta_enabled',
		array(
			'label'   => __( 'Afficher le bandeau sur les fiches produit', 'evasions' ),
			'section' => 'evasions_product_cta',
			'type'    => 'checkbox',
		)
	);

	$wp_customize->add_setting(
		'evasions_cta_text',
		array(
			'default'           => $defaults['evasions_cta_text'],
			'sanitize_callback' => 'sanitize_textarea_field',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'evasions_cta_text',
		array(
			'label'   => __( 'Appel à l’aventure — sous-titre', 'evasions' ),
			'section' => 'evasions_product_cta',
			'type'    => 'textarea',
		)
	);

	$wp_customize->add_setting(
		'evasions_cta_url',
		array(
			'default'           => $defaults['evasions_cta_url'],
			'sanitize_callback' => 'esc_url_raw',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		'evasions_cta_url',
		array(
			'label'       => __( 'Appel à l’aventure — lien du bouton', 'evasions' ),
			'description' => __( 'Vide : le bouton mène à la boutique.', 'evasions' ),
			'section'     => 'evasions_product_cta',
			'type'        => 'url',
		)
	);

	// Couleurs : un champ vide laisse le jeton --ev-* du thème s'appliquer.
	$colors = array(
		'evasions_cta_color_title'    => __( 'Couleur du titre', 'evasions' ),
		'evasions_cta_color_text'     => __( 'Couleur du sous-titre', 'evasions' ),
		'evasions_cta_color_btn'      => __( 'Couleur du bouton', 'evasions' ),
		'evasions_cta_color_btn_text' => __( 'Couleur du texte du bouton', 'evasions' ),
	);

	foreach ( $colors as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => $defaults[ $key ],
				'sanitize_callback' => 'sanitize_hex_color',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			new WP_Customize_Color_Control(
				$wp_customize,
				$key,
				array(
					'label'   => $label,
					'section' => 'evasions_product_cta',
				)
			)
		);
	}

	$wp_customize->add_setting(
		'evasions_img_cta',
		array(
			'default'           => 0,
			'sanitize_callback' => 'absint',
			'transport'         => 'refresh',
		)
	);
	$wp_customize->add_control(
		new WP_Customize_Media_Control(
			$wp_customize,
			'evasions_img_cta',
			array(
				'label'       => __( 'Appel à l’aventure — image de fond', 'evasions' ),
				'description' => __( 'Visuel large et net (≥ 2000 px). Vide : l’image par défaut du thème.', 'evasions' ),
				'section'     => 'evasions_product_cta',
				'mime_type'   => 'image',
			)
		)
	);

	// Réseaux sociaux (URL).
	$socials = array(
		'evasions_social_facebook'  => __( 'Facebook', 'evasions' ),
		'evasions_social_instagram' => __( 'Instagram', 'evasions' ),
		'evasions_social_tiktok'    => __( 'TikTok', 'evasions' ),
		'evasions_social_youtube'   => __( 'YouTube', 'evasions' ),
	);

	foreach ( $socials as $key => $label ) {
		$wp_customize->add_setting(
			$key,
			array(
				'default'           => '',
				'sanitize_callback' => 'esc_url_raw',
				'transport'         => 'refresh',
			)
		);
		$wp_customize->add_control(
			$key,
			array(
				'label'   => $label,
				'section' => 'evasions_social',
				'type'    => 'url',
			)
		);
	}
}
add_action( 'customize_register', 'evasions_customize_register' );
