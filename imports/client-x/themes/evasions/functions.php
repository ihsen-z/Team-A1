<?php
/**
 * Thème EVASIONS — point d'entrée.
 *
 * Séparation posée par le document directeur (§26) : le thème s'occupe de la
 * présentation, le plugin EVASIONS Core de la logique métier. Ce fichier ne
 * fait donc que charger des morceaux, dans un ordre explicite, sans autoloader.
 *
 * Le thème doit rester utilisable si le plugin est désactivé : chaque appel à
 * une classe du plugin est protégé par un `class_exists()` (voir `inc/content.php`).
 * Il perd alors ses promesses éditables, pas sa mise en page.
 *
 * @package Evasions\Theme
 */

defined( 'ABSPATH' ) || exit;

define( 'EVASIONS_THEME_VERSION', '0.1.0' );

require_once __DIR__ . '/inc/icons.php';
require_once __DIR__ . '/inc/setup.php';
require_once __DIR__ . '/inc/customizer.php';
require_once __DIR__ . '/inc/content.php';
require_once __DIR__ . '/inc/woocommerce.php';
require_once __DIR__ . '/inc/product.php';
require_once __DIR__ . '/inc/cart-checkout.php';
require_once __DIR__ . '/inc/listing.php';
require_once __DIR__ . '/inc/universe.php';
require_once __DIR__ . '/inc/pages.php';
require_once __DIR__ . '/inc/info-pages.php';
require_once __DIR__ . '/inc/brand.php';
