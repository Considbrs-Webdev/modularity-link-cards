<?php

namespace ModularityLinkCards;

use ModularityLinkCards\Helper\CacheBust;
use ModularityLinkCards\AcfFields\ColorThemeField;

/**
 * Class App
 * 
 * Main application bootstrap class.
 * Initialize your plugin components here.
 * 
 * @package ModularityLinkCards
 */
class App
{
    public function __construct()
    {
        // Register module with Modularity
        add_action('init', [$this, 'registerModule']);

        // Enqueue styles
        add_action('wp_enqueue_scripts', [$this, 'enqueueStyles']);

        // Block / Gutenberg editor — match frontend module appearance
        add_action('enqueue_block_editor_assets', [$this, 'addEditorStyles']);

        // Register custom ACF field type
        add_action('acf/include_field_types', [$this, 'registerAcfFields']);
    }

    /**
     * Register custom ACF field types
     *
     * @return void
     */
    public function registerAcfFields(): void
    {
        new ColorThemeField();
    }

    /**
     * Enqueue styles
     * 
     * @return void
     */
    public function enqueueStyles(): void
    {
        $styleFile = CacheBust::name('css/modularity-link-cards.css');

        if ($styleFile) {
            wp_enqueue_style(
                'modularity-link-cards',
                MODULARITYLINKCARDS_URL . '/assets/dist/' . $styleFile,
                [],
                null
            );
        }
    }

    /**
     * Enqueue the same built CSS in the block editor as on the frontend.
     */
    public function addEditorStyles(): void
    {
        $styleFile = CacheBust::name('css/modularity-link-cards.css');

        if ($styleFile) {
            wp_enqueue_style(
                'modularity-link-cards',
                MODULARITYLINKCARDS_URL . '/assets/dist/' . $styleFile,
                [],
                null
            );
        }
    }

    /**
     * Register the module with Modularity
     * 
     * @return void
     */
    public function registerModule(): void
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITYLINKCARDS_MODULE_PATH,
                'LinkCards',
            );
        }
    }
}
