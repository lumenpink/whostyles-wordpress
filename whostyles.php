<?php
/**
 * Plugin Name: Whostyles
 * Plugin URI:  https://github.com/whostyles/whostyles-wordpress
 * Description: Native support for Whostyles V1 (Hash Protocol) typography and cosmetic syndication.
 * Version:     0.1.0
 * Author:      Whostyles Contributors
 * License:     GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: whostyles
 * Domain Path: /languages
 * Requires PHP: 7.4
 *
 * @package Whostyles\WordPress
 */

declare(strict_types=1);

namespace Whostyles\WordPress;

// Prevent direct file access.
if (!defined('ABSPATH')) {
    exit;
}

// Load Composer autoloader if it exists.
$autoloader = __DIR__ . '/vendor/autoload.php';
if (file_exists($autoloader)) {
    require_once $autoloader;
}

/**
 * Bootstraps the Whostyles plugin.
 */
add_action('plugins_loaded', function (): void {
    Plugin::getInstance()->initialize();
});
