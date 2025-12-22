<?php

/**
 * Plugin Name:       Modularity LinkCards
 * Plugin URI:        https://github.com/helsingborg-stad/modularity-link-cards
 * Description:       A link-cards for creating Modularity modules.
 * Version: 1.0.0
 * Author:            Starter
 * Author URI:        https://github.com/helsingborg-stad
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       modularity-link-cards
 * Domain Path:       /languages
 */

// Protect against direct file access
if (! defined('WPINC')) {
    die;
}

define('MODULARITYLINKCARDS_PATH', plugin_dir_path(__FILE__));
define('MODULARITYLINKCARDS_URL', plugins_url('', __FILE__));
define('MODULARITYLINKCARDS_MODULE_VIEW_PATH', plugin_dir_path(__FILE__) . 'source/php/Module/views');
define('MODULARITYLINKCARDS_MODULE_PATH', MODULARITYLINKCARDS_PATH . 'source/php/Module/');

// Load text domain
add_action('init', function () {
    load_plugin_textdomain('modularity-link-cards', false, plugin_basename(dirname(__FILE__)) . '/languages');
});

// Autoload from plugin
if (file_exists(MODULARITYLINKCARDS_PATH . 'vendor/autoload.php')) {
    require_once MODULARITYLINKCARDS_PATH . 'vendor/autoload.php';
}

// ACF auto import and export
add_action('acf/init', function () {
    $acfExportManager = new \AcfExportManager\AcfExportManager();
    $acfExportManager->setTextdomain('modularity-link-cards');
    $acfExportManager->setExportFolder(MODULARITYLINKCARDS_PATH . 'source/php/AcfFields/');
    $acfExportManager->autoExport(array(
        'link-cards-module' => 'group_67892a1b3c4d5e6f',
    ));
    $acfExportManager->import();
});

// Modularity 3.0 ready - ViewPath for Component library
add_filter('/Modularity/externalViewPath', function ($arr) {
    $arr['mod-link-cards'] = MODULARITYLINKCARDS_MODULE_VIEW_PATH;
    return $arr;
}, 10, 3);

// Start application
new ModularityLinkCards\App();

