<?php
/**
 * Icônes SVG en ligne.
 *
 * Tracés repris tels quels du design (viewBox 24, trait 1,6). En ligne plutôt
 * qu'en fichier : zéro requête supplémentaire, et `currentColor` laisse le CSS
 * décider de la couleur.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

/**
 * Tracés des icônes, par nom.
 *
 * Les clés `tag` à `mail` sont celles que propose le plugin pour la barre de
 * promesses : une icône choisie en administration doit exister ici.
 *
 * @return array<string,string>
 */
function evasions_icon_paths(): array {
	return array(
		'truck'  => '<rect x="1.5" y="6" width="12.5" height="9.5" rx="1.5"/><path d="M14 9.2h3.7l3 3v3.3H14z"/><circle cx="5.5" cy="18" r="1.9"/><circle cx="17.5" cy="18" r="1.9"/>',
		'card'   => '<rect x="2" y="5" width="20" height="14" rx="2.5"/><path d="M2 10.2h20"/>',
		'check'  => '<path d="M4 12.6 9.6 18 20 6.4"/>',
		'clock'  => '<circle cx="12" cy="12" r="8.6"/><path d="M12 7.2V12l3.2 2"/>',
		'search' => '<circle cx="11" cy="11" r="6.5"/><path d="M16 16l4.8 4.8"/>',
		'user'   => '<circle cx="12" cy="8" r="3.6"/><path d="M5 20c1.4-3.6 4-5.2 7-5.2s5.6 1.6 7 5.2"/>',
		'bag'    => '<path d="M5 8h14l-1.2 12H6.2z"/><path d="M9 8V6.4a3 3 0 0 1 6 0V8"/>',
		'menu'   => '<path d="M4 7h16M4 12h16M4 17h16"/>',
		'close'  => '<path d="M6 6l12 12M18 6L6 18"/>',
		'arrow'  => '<path d="M5 12h14M13 6l6 6-6 6"/>',
		'tag'    => '<path d="M3.5 12.5V4.5h8l9 9-8 8z"/><circle cx="8" cy="9" r="1.3"/>',
		'shield' => '<path d="M12 3l7.5 3v5.5c0 4.5-3.2 8-7.5 9.5-4.3-1.5-7.5-5-7.5-9.5V6z"/><path d="M8.6 12l2.5 2.5 4.3-4.6"/>',
		'chat'   => '<path d="M4 5h16v11H9l-5 4z"/>',
		'phone'  => '<path d="M6 3.5h3l1.5 4-2 1.3a11 11 0 0 0 6.7 6.7l1.3-2 4 1.5v3a2 2 0 0 1-2.2 2A16.5 16.5 0 0 1 4 5.7 2 2 0 0 1 6 3.5z"/>',
		'pin'    => '<path d="M12 21s6.5-5.6 6.5-11a6.5 6.5 0 0 0-13 0c0 5.4 6.5 11 6.5 11z"/><circle cx="12" cy="10" r="2.3"/>',
		'mail'   => '<rect x="3" y="5.5" width="18" height="13" rx="2"/><path d="M3.5 7l8.5 6 8.5-6"/>',
		// Pictogrammes des articles de CGV (maquette « EVASIONS Pages Infos »).
		'doc'    => '<path d="M7 3h7l4 4v14H7z"/><path d="M14 3v4h4"/><path d="M10 12h5"/><path d="M10 16h5"/>',
		'tent'   => '<path d="M3 20L12 5l9 15z"/><path d="M12 5v15"/><path d="M9 20l3-5 3 5"/>',
		'return' => '<path d="M4 9h11a5 5 0 0 1 0 10H9"/><path d="M8 5L4 9l4 4"/>',
		'lock'   => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/><path d="M12 15v2"/>',
		// Bulle WhatsApp au trait, pour rester dans la famille des autres pictogrammes.
		'whatsapp' => '<path d="M4.2 19.8l1.1-3.6A8.2 8.2 0 1 1 8 18.8z"/><path d="M9.2 8.6c-.3 2.9 3.2 6.4 6.1 6.1l.6-1.5-1.8-.9-.9.8a4 4 0 0 1-2.3-2.3l.8-.9-.9-1.8z"/>',
		'scale'  => '<path d="M12 4v16"/><path d="M7 20h10"/><path d="M5 7h14"/><path d="M5 7l-3 6a3 3 0 0 0 6 0z"/><path d="M19 7l-3 6a3 3 0 0 0 6 0z"/>',
	);
}

/**
 * Icônes de marque des réseaux sociaux (§ pied de page).
 *
 * À part : les logos des réseaux sont des glyphes pleins (`fill`), pas des
 * tracés au trait comme le reste du jeu. Chaque SVG est complet et se colore
 * par `currentColor`.
 *
 * @param string $key  Réseau : facebook, instagram, tiktok, youtube.
 * @param int    $size Largeur et hauteur en px.
 * @return string
 */
function evasions_social_icon( string $key, int $size = 18 ): string {
	$icons = array(
		'facebook'  => '<path d="M22 12a10 10 0 1 0-11.6 9.9v-7H7.9V12h2.5V9.8c0-2.5 1.5-3.9 3.7-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.75-1.6 1.5V12h2.7l-.43 2.9h-2.3v7A10 10 0 0 0 22 12z"/>',
		'instagram' => '<path d="M12 2.2c3.2 0 3.6 0 4.85.07 1.17.05 1.8.25 2.23.42.56.22.96.48 1.38.9.42.42.68.82.9 1.38.17.42.37 1.06.42 2.23.06 1.27.07 1.65.07 4.85s0 3.58-.07 4.85c-.05 1.17-.25 1.8-.42 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.17-1.06.37-2.23.42-1.27.06-1.65.07-4.85.07s-3.58 0-4.85-.07c-1.17-.05-1.8-.25-2.23-.42-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.17-.42-.37-1.06-.42-2.23C2.2 15.58 2.2 15.2 2.2 12s0-3.58.07-4.85c.05-1.17.25-1.8.42-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.17 1.06-.37 2.23-.42C8.42 2.2 8.8 2.2 12 2.2zm0 1.8c-3.15 0-3.5 0-4.75.07-.9.04-1.4.2-1.72.32-.43.17-.74.37-1.06.7-.32.32-.52.63-.7 1.06-.13.32-.28.82-.32 1.72C3.4 8.5 3.4 8.85 3.4 12s0 3.5.07 4.75c.04.9.2 1.4.32 1.72.17.43.37.74.7 1.06.32.32.63.52 1.06.7.32.13.82.28 1.72.32 1.25.06 1.6.07 4.75.07s3.5 0 4.75-.07c.9-.04 1.4-.2 1.72-.32.43-.17.74-.37 1.06-.7.32-.32.52-.63.7-1.06.13-.32.28-.82.32-1.72.06-1.25.07-1.6.07-4.75s0-3.5-.07-4.75c-.04-.9-.2-1.4-.32-1.72-.17-.43-.37-.74-.7-1.06-.32-.32-.63-.52-1.06-.7-.32-.13-.82-.28-1.72-.32C15.5 4 15.15 4 12 4zm0 3.06a4.94 4.94 0 1 1 0 9.88 4.94 4.94 0 0 1 0-9.88zm0 1.8a3.14 3.14 0 1 0 0 6.28 3.14 3.14 0 0 0 0-6.28zm5.15-3.24a1.15 1.15 0 1 1 0 2.3 1.15 1.15 0 0 1 0-2.3z"/>',
		'tiktok'    => '<path d="M16.3 3h-2.9v12.1a2.35 2.35 0 1 1-2-2.32V9.8a5.35 5.35 0 1 0 4.9 5.33V9.2a6.15 6.15 0 0 0 3.7 1.23V7.5a3.3 3.3 0 0 1-3.7-3.4V3z"/>',
		'youtube'   => '<path d="M23 12s0-3.2-.4-4.7a2.5 2.5 0 0 0-1.75-1.75C19.3 5.2 12 5.2 12 5.2s-7.3 0-8.85.35A2.5 2.5 0 0 0 1.4 7.3C1 8.8 1 12 1 12s0 3.2.4 4.7a2.5 2.5 0 0 0 1.75 1.75C4.7 18.8 12 18.8 12 18.8s7.3 0 8.85-.35A2.5 2.5 0 0 0 22.6 16.7C23 15.2 23 12 23 12zM9.75 15.3V8.7l5.75 3.3-5.75 3.3z"/>',
	);

	if ( ! isset( $icons[ $key ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="ev-icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true" focusable="false">%2$s</svg>',
		$size,
		$icons[ $key ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- constantes de ce fichier, jamais une entrée.
	);
}

/**
 * Markup d'une icône, ou chaîne vide si le nom est inconnu.
 *
 * @param string $name Icon name.
 * @param int    $size Width and height in px.
 * @return string
 */
function evasions_icon( string $name, int $size = 20 ): string {
	$paths = evasions_icon_paths();

	if ( ! isset( $paths[ $name ] ) ) {
		return '';
	}

	return sprintf(
		'<svg class="ev-icon" width="%1$d" height="%1$d" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">%2$s</svg>',
		$size,
		$paths[ $name ] // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- constantes de ce fichier, jamais une entrée.
	);
}
