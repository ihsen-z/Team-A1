<?php
/**
 * Services de contenu partagés par les blocs.
 *
 * @package FactoryCore
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

/**
 * Découpe un contenu HTML en sections, à un niveau de titre donné.
 *
 * L'intérêt est qu'aucune donnée n'est dupliquée : l'administrateur écrit une
 * page normale, et le bloc la rejoue en accordéon, en onglets ou en cartes.
 * Une FAQ n'a pas à exister deux fois, une fois dans la page et une fois dans
 * un champ personnalisé.
 *
 * @param string $html Contenu HTML à découper.
 * @param string $tag  Niveau de titre (« h2 », « h3 »…).
 * @return array{intro: string, sections: array<int, array{title: string, html: string}>}
 */
function factory_core_split_by_heading(string $html, string $tag): array {
	$tag = preg_replace('/[^a-z0-9]/', '', strtolower($tag));

	if (! is_string($tag) || ! preg_match('/^h[1-6]$/', $tag)) {
		return array(
			'intro'    => $html,
			'sections' => array(),
		);
	}

	$parts = preg_split(
		'#<' . $tag . '\b[^>]*>(.*?)</' . $tag . '>#is',
		$html,
		-1,
		PREG_SPLIT_DELIM_CAPTURE
	);

	// Moins de trois éléments : aucun titre de ce niveau n'a été trouvé.
	if (! is_array($parts) || count($parts) < 3) {
		return array(
			'intro'    => $html,
			'sections' => array(),
		);
	}

	$intro    = (string) array_shift($parts);
	$sections = array();
	$total    = count($parts);

	// Après le découpage, les éléments vont par deux : titre, puis contenu.
	for ($i = 0; $i + 1 < $total; $i += 2) {
		$title = trim(wp_strip_all_tags((string) $parts[ $i ]));

		if ('' === $title) {
			continue;
		}

		$sections[] = array(
			'title' => $title,
			'html'  => trim((string) $parts[ $i + 1 ]),
		);
	}

	return array(
		'intro'    => trim($intro),
		'sections' => $sections,
	);
}

/**
 * Retire d'un titre une numérotation déjà écrite à la main.
 *
 * « 3. Livraison » et « 3) Livraison » deviennent « Livraison », pour que la
 * numérotation automatique ne produise pas « 3. 3. Livraison ».
 *
 * @param string $title Titre brut.
 * @return string
 */
function factory_core_strip_leading_number(string $title): string {
	$clean = preg_replace('/^\s*\d+\s*[.)–—-]\s*/u', '', $title);

	return is_string($clean) ? $clean : $title;
}
