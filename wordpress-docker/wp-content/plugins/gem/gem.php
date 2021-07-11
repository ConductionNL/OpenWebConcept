<?php

/**
 * Waardepapieren
 *
 * @package           WaardenpapierenPlugin
 * @author            Conduction
 * @copyright         2020 Conduction
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Waardepapieren
 * Plugin URI:        https://conduction.nl/waardepapieren
 * Description:       De waardepapieren plugin
 * Version:           1.0.8
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Conduction
 * Author URI:        https://conduction.nl
 * Text Domain:       plugin-slug
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

use OWC\Gem\Autoloader;
use OWC\Gem\Foundation\Plugin;

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
 * Begin execution of the plugin.
 */
function dependency_injection() {
    wp_register_style('owc_gem_general', plugins_url('https://virtuele-gemeente-assistent.nl/static/css/widget-v0.11.7.css');
    wp_register_style('owc_gem_local', plugins_url('https://mijn.virtuele-gemeente-assistent.nl/demodam/_styling');
    wp_enqueue_style('owc_gem_general');
    wp_enqueue_style('owc_gem_local');

    // <script id="widget-script" src="https://virtuele-gemeente-assistent.nl/static/js/widget-v0.11.7.js" data-municipality="Demodam"></script>

    wp_register_script( 'owc_gem_webchat', plugins_url('https://virtuele-gemeente-assistent.nl/static/js/webchat-v0.11.7.js');
    wp_register_script( 'owc_gem_widget', plugins_url('https://virtuele-gemeente-assistent.nl/static/js/widget-v0.11.7.js');
    wp_enqueue_script('owc_gem_webchat');
    wp_enqueue_script('owc_gem_widget');
}

/**
 * Initialise the plugin
 */
add_action('init', function () {

    // Start session on init when there is none.
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Lets load the dependencies
    dependency_injection();

});
