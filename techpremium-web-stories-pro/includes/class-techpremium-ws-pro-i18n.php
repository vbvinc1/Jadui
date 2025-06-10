<?php

/**
 * Define the internationalization functionality
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/includes
 */
class Techpremium_Ws_Pro_i18n {

    /**
     * Load the plugin text domain for translation.
     */
    public function load_plugin_textdomain() {
        load_plugin_textdomain(
            'techpremium-web-stories-pro',
            false,
            dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
        );
    }
}
