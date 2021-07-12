<?php

/**
 * ZGW
 *
 * @package           ZGWPlugin
 * @author            Open Webconcept
 * @copyright         2021 Open Webconcept
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       ZGW
 * Plugin URI:        https://conduction.nl/ZGW
 * Description:       Plugin voor het weergeven van aan een BSN of KVK gerelateerde zaken
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Open Webconcept
 * Author URI:        https://conduction.nl
 * Text Domain:       plugin-slug
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

use OWC\ZGW\Autoloader;
use OWC\ZGW\Foundation\Plugin;

/**
 * If this file is called directly, abort.
 */
if (!defined('WPINC')) {
    die;
}

/**
 * Manual loaded file: the autoloader.
 */
require_once __DIR__ . '/autoloader.php';
$autoloader = new Autoloader();

/**
 * Begin execution of the plugin.
 */
$plugin = (new Plugin(__DIR__))->boot();


/**
 * Initialise the plugin
 */
add_action('init', function () {

    // Start session on init when there is none.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

});
