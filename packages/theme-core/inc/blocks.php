<?php
/**
 * Enregistrement de la bibliothèque de blocs maison.
 *
 * Les blocs sont copiés dans le thème par le pipeline (étape « compose »).
 * Chacun a son block.json : rien n'est enregistré à la main.
 *
 * @package FactoryCore
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action('init', 'factory_core_register_blocks');
/**
 * Enregistre chaque bloc présent dans /blocks.
 */
function factory_core_register_blocks(): void {
	$blocks_dir = FACTORY_CORE_DIR . '/blocks';

	if (! is_dir($blocks_dir)) {
		return;
	}

	foreach ((array) glob($blocks_dir . '/*', GLOB_ONLYDIR) as $block_path) {
		if (is_string($block_path) && file_exists($block_path . '/block.json')) {
			register_block_type($block_path);
		}
	}
}

add_filter('allowed_block_types_all', 'factory_core_allowed_blocks', 10, 2);
/**
 * Restreint les blocs disponibles à ceux du preset du site.
 *
 * C'est le garde-fou qui empêche un site de dériver hors du système : ce que
 * le preset n'autorise pas n'apparaît pas dans l'éditeur, pour le client aussi.
 *
 * @param bool|string[]           $allowed Blocs autorisés, ou true pour tous.
 * @param WP_Block_Editor_Context $context Contexte de l'éditeur.
 * @return bool|string[]
 */
function factory_core_allowed_blocks($allowed, $context) {
	unset($context);

	$preset = get_option('factory_blocks_allowed');

	if (! is_array($preset) || array() === $preset) {
		return $allowed;
	}

	return array_values(array_map('strval', $preset));
}
