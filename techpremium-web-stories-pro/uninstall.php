<?php

/**
 * Fired when the plugin is uninstalled.
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
    exit;
}

/**
 * Remove plugin data on uninstall
 */
function techpremium_ws_pro_uninstall() {
    global $wpdb;

    // Get uninstall option
    $options = get_option( 'techpremium_ws_pro_options', array() );
    $remove_data = isset( $options['advanced']['remove_data_on_uninstall'] ) ? 
                   $options['advanced']['remove_data_on_uninstall'] : false;

    if ( ! $remove_data ) {
        return; // User chose to keep data
    }

    // Remove database tables
    $tables = array(
        $wpdb->prefix . 'techpremium_stories',
        $wpdb->prefix . 'techpremium_story_templates',
        $wpdb->prefix . 'techpremium_story_analytics'
    );

    foreach ( $tables as $table ) {
        $wpdb->query( "DROP TABLE IF EXISTS $table" );
    }

    // Remove options
    delete_option( 'techpremium_ws_pro_options' );
    delete_option( 'techpremium_ws_pro_version' );

    // Remove transients
    delete_transient( 'techpremium_ws_pro_templates' );
    delete_transient( 'techpremium_ws_pro_stories' );

    // Remove upload directories
    $upload_dir = wp_upload_dir();
    $plugin_upload_dir = $upload_dir['basedir'] . '/techpremium-stories/';

    if ( is_dir( $plugin_upload_dir ) ) {
        techpremium_ws_pro_remove_directory( $plugin_upload_dir );
    }

    // Clear any cached data
    if ( function_exists( 'wp_cache_flush' ) ) {
        wp_cache_flush();
    }
}

/**
 * Recursively remove directory
 */
function techpremium_ws_pro_remove_directory( $dir ) {
    if ( ! is_dir( $dir ) ) {
        return false;
    }

    $files = array_diff( scandir( $dir ), array( '.', '..' ) );

    foreach ( $files as $file ) {
        $path = $dir . DIRECTORY_SEPARATOR . $file;

        if ( is_dir( $path ) ) {
            techpremium_ws_pro_remove_directory( $path );
        } else {
            unlink( $path );
        }
    }

    return rmdir( $dir );
}

// Execute uninstall
techpremium_ws_pro_uninstall();
