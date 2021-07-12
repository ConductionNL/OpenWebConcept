<?php

/**
 * Gem
 *
 * @package           GemPlugin
 * @author            Open Webconcept
 * @copyright         2021 Open Webconcept
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       Gem
 * Plugin URI:        https://conduction.nl/gem
 * Description:       Dé gemeentenlijke chatbot
 * Version:           0.0.1
 * Requires at least: 5.2
 * Requires PHP:      7.2
 * Author:            Open Webconcept
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
    wp_register_style('owc_gem_general', 'https://virtuele-gemeente-assistent.nl/static/css/widget-v0.11.7.css');
    wp_register_style('owc_gem_local', 'https://mijn.virtuele-gemeente-assistent.nl/demodam/_styling');
    wp_enqueue_style('owc_gem_general');
    wp_enqueue_style('owc_gem_local');

    // <script id="widget-script" src="https://virtuele-gemeente-assistent.nl/static/js/widget-v0.11.7.js" data-municipality="Demodam"></script>

    /*
    wp_register_script( 'owc_gem_webchat', 'https://virtuele-gemeente-assistent.nl/static/js/webchat-v0.11.7.js',[],'0.11.7.js',true );
    wp_register_script( 'owc_gem_widget', 'https://virtuele-gemeente-assistent.nl/static/js/widget-v0.11.7.js',['owc_gem_webchat'],'0.11.7.js',true);
    wp_enqueue_script('owc_gem_webchat');
    wp_enqueue_script('owc_gem_widget');
    */
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
