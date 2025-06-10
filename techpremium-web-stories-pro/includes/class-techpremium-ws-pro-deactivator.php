<?php

/**
 * Fired during plugin deactivation
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/includes
 */
class Techpremium_Ws_Pro_Deactivator {

    /**
     * Plugin deactivation handler
     */
    public static function deactivate() {
        // Clear scheduled events
        wp_clear_scheduled_hook( 'techpremium_ws_pro_cleanup' );

        // Clear cache
        self::clear_cache();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Clear plugin cache
     */
    private static function clear_cache() {
        // Clear transients
        delete_transient( 'techpremium_ws_pro_templates' );
        delete_transient( 'techpremium_ws_pro_stories' );

        // Clear object cache if available
        if ( function_exists( 'wp_cache_flush' ) ) {
            wp_cache_flush();
        }
    }
}
