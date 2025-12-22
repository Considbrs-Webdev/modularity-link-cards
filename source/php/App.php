<?php

namespace ModularityBoilerplate;

/**
 * Class App
 * 
 * Main application bootstrap class.
 * Initialize your plugin components here.
 * 
 * @package ModularityBoilerplate
 */
class App
{
    public function __construct()
    {
        // Register module with Modularity
        add_action('init', [$this, 'registerModule']);

        // Example: Add more initializations here
        // new SomeOtherClass();
        // new Admin\Settings();
    }

    /**
     * Register the module with Modularity
     * 
     * @return void
     */
    public function registerModule()
    {
        if (function_exists('modularity_register_module')) {
            modularity_register_module(
                MODULARITYBOILERPLATE_MODULE_PATH,
                'Boilerplate',
            );
        }
    }
}
