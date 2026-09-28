<?php
/**
 * Thème socle de l'usine à sites.
 *
 * Règle : ce fichier est identique sur tous les sites. Ce qui change d'un
 * client à l'autre vit dans theme.json (généré) et dans le contenu.
 *
 * @package FactoryCore
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

define('FACTORY_CORE_VERSION', '0.1.0');
define('FACTORY_CORE_DIR', get_template_directory());

require_once FACTORY_CORE_DIR . '/inc/setup.php';
require_once FACTORY_CORE_DIR . '/inc/blocks.php';
require_once FACTORY_CORE_DIR . '/inc/security.php';
