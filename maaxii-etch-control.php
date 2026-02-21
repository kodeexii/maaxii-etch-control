<?php
/**
 * Plugin Name: MaaXII Etch Control
 * Description: Universal remote management and programmatic page builder for Etch (OOP Version).
 * Version: 1.0.18
 * Author: MaaXII Solutions and Services
 * Text Domain: maaxii-etch-control
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Define constants
define( 'MAAXII_ETCH_CONTROL_VERSION', '1.0.18' );
define( 'MAAXII_ETCH_CONTROL_PATH', plugin_dir_path( __FILE__ ) );

// Autoload classes
require_once MAAXII_ETCH_CONTROL_PATH . 'includes/class-maaxii-etch-control.php';
require_once MAAXII_ETCH_CONTROL_PATH . 'includes/class-maaxii-etch-abilities.php';
require_once MAAXII_ETCH_CONTROL_PATH . 'includes/class-maaxii-etch-callbacks.php';

// Initialize Update Checker
if ( file_exists( MAAXII_ETCH_CONTROL_PATH . 'libs/plugin-update-checker/plugin-update-checker.php' ) ) {
    require_once MAAXII_ETCH_CONTROL_PATH . 'libs/plugin-update-checker/plugin-update-checker.php';
    $updateChecker = \YahnisElsts\PluginUpdateChecker\v5\PucFactory::buildUpdateChecker(
        'https://github.com/kodeexii/maaxii-etch-control/',
        __FILE__,
        'maaxii-etch-control'
    );
}

/**
 * Initialize the plugin
 */
function maaxii_etch_control_init() {
    $plugin = new MaaXII_Etch_Control();
    $plugin->run();
}
maaxii_etch_control_init();
