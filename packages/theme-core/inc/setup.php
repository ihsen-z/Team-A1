<?php
/**
 * Réglages du thème et chargement des styles.
 *
 * @package FactoryCore
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

add_action('after_setup_theme', 'factory_core_setup');
/**
 * Déclare les fonctionnalités du thème.
 */
function factory_core_setup(): void {
	add_theme_support('wp-block-styles');
	add_theme_support('responsive-embeds');
	add_theme_support('editor-styles');
	add_theme_support('post-thumbnails');
	add_theme_support('title-tag');
	add_theme_support('html5', array( 'search-form', 'gallery', 'caption', 'style', 'script' ));

	// Les tailles d'image sont fixées ici, pas par client : la cohérence de la
	// médiathèque est ce qui garde les pages rapides.
	add_image_size('factory-card', 640, 420, true);
	add_image_size('factory-hero', 1600, 900, true);

	register_nav_menus(
		array(
			'primaire' => __('Menu principal', 'factory-core'),
			'legal'    => __('Menu légal', 'factory-core'),
		)
	);

	load_theme_textdomain('factory-core', FACTORY_CORE_DIR . '/languages');
}

add_action('wp_enqueue_scripts', 'factory_core_assets');
/**
 * Charge la feuille de style du thème.
 */
function factory_core_assets(): void {
	wp_enqueue_style(
		'factory-core',
		get_stylesheet_uri(),
		array(),
		FACTORY_CORE_VERSION
	);
}

add_filter('wp_lazy_loading_enabled', '__return_true');

add_action('init', 'factory_core_trim_head');
/**
 * Retire du <head> ce qui ne sert à aucun de nos sites.
 */
function factory_core_trim_head(): void {
	remove_action('wp_head', 'wp_generator');
	remove_action('wp_head', 'wlwmanifest_link');
	remove_action('wp_head', 'rsd_link');
	remove_action('wp_head', 'wp_shortlink_wp_head');
}
