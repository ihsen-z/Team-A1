<?php
/**
 * Constantes du thème, pour que PHPStan analyse le code hors d'une
 * installation WordPress complète.
 *
 * @package FactoryCore
 */

declare(strict_types=1);

defined('ABSPATH') || define('ABSPATH', __DIR__ . '/');
defined('FACTORY_CORE_DIR') || define('FACTORY_CORE_DIR', dirname(__DIR__, 2) . '/packages/theme-core');
defined('FACTORY_CORE_VERSION') || define('FACTORY_CORE_VERSION', '0.1.0');
