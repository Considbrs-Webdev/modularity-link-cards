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

        // Iframed block canvas. enqueue_block_editor_assets never reaches the preview.
        add_action('enqueue_block_assets', [$this, 'addEditorStyles']);

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
        $this->enqueueStylesheet();
    }

    /**
     * Enqueue the module stylesheet inside the block editor iframe.
     *
     * @return void
     */
    public function addEditorStyles(): void
    {
        if (!is_admin()) {
            return;
        }

        $this->enqueueStylesheet();
    }

    /**
     * Built stylesheet URL, or an empty string when the Vite manifest has no entry.
     */
    private function stylesheetUrl(): string
    {
        $styleFile = CacheBust::name('css/modularity-link-cards.css');
        if (!$styleFile) {
            return '';
        }

        return MODULARITYLINKCARDS_URL . '/assets/dist/' . $styleFile;
    }

    /**
     * Enqueue the built module stylesheet.
     *
     * @return void
     */
    private function enqueueStylesheet(): void
    {
        $url = $this->stylesheetUrl();
        if ($url === '') {
            return;
        }

        wp_enqueue_style('modularity-link-cards', $url, [], null);
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
