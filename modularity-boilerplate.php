<?php

/**
 * Plugin Name:       Modularity Boilerplate
 * Plugin URI:        https://github.com/helsingborg-stad/modularity-boilerplate
 * Description:       A boilerplate for creating Modularity modules.
 * Version: 1.0.0
 * Author:            Starter
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-boilerplate
 * Domain Path:       /languages
 */

// Protect against direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYBOILERPLATE_PATH', plugin_dir_path(__FILE__));
define('MODULARITYBOILERPLATE_URL', plugins_url('', __FILE__));
define('MODULARITYBOILERPLATE_MODULE_VIEW_PATH', plugin_dir_path(__FILE__) . 'source/php/Module/views');
define('MODULARITYBOILERPLATE_MODULE_PATH', MODULARITYBOILERPLATE_PATH . 'source/php/Module/');

// Load text domain
add_action('init', function () {
    load_plugin_textdomain('modularity-boilerplate', false, plugin_basename(dirname(__FILE__)) . '/languages');
});

// Autoload from plugin
if (file_exists(MODULARITYBOILERPLATE_PATH . 'vendor/autoload.php')) {
    require_once MODULARITYBOILERPLATE_PATH . 'vendor/autoload.php';
}

// ACF auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-boilerplate');
    $acfExportManager->setExportFolder(MODULARITYBOILERPLATE_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'boilerplate-module' => 'group_boilerplate_module',
    ));
    $acfExportManager->import();
});

// Modularity 3.0 ready - ViewPath for Component library
add_filter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-boilerplate'] = MODULARITYBOILERPLATE_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Start application
new ModularityBoilerplate\App();

