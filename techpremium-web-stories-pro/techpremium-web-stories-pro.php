<?php
/**
 * Plugin Name:       TechPremium Web Stories Pro
 * Plugin URI:        https://techpremium.me/plugins/web-stories-pro
 * Description:       Advanced WordPress plugin for creating, managing, and optimizing Google Web Stories with premium features, SEO integration, and professional templates.
 * Version:           1.0.0
 * Author:            TechPremium
 * Author URI:        https://techpremium.me
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       techpremium-web-stories-pro
 * Domain Path:       /languages
 * Requires at least: 5.0
 * Tested up to:      6.4
 * Requires PHP:      7.4
 * Network:           false
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}

/**
 * Plugin version.
 */
define( 'TECHPREMIUM_WS_PRO_VERSION', '1.0.0' );

/**
 * Plugin path and URL constants
 */
define( 'TECHPREMIUM_WS_PRO_PATH', plugin_dir_path( __FILE__ ) );
define( 'TECHPREMIUM_WS_PRO_URL', plugin_dir_url( __FILE__ ) );
define( 'TECHPREMIUM_WS_PRO_BASENAME', plugin_basename( __FILE__ ) );

/**
 * The code that runs during plugin activation.
 */
function activate_techpremium_ws_pro() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-techpremium-ws-pro-activator.php';
    Techpremium_Ws_Pro_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_techpremium_ws_pro() {
    require_once plugin_dir_path( __FILE__ ) . 'includes/class-techpremium-ws-pro-deactivator.php';
    Techpremium_Ws_Pro_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_techpremium_ws_pro' );
register_deactivation_hook( __FILE__, 'deactivate_techpremium_ws_pro' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-techpremium-ws-pro.php';

/**
 * Begins execution of the plugin.
 */
function run_techpremium_ws_pro() {
    $plugin = new Techpremium_Ws_Pro();
    $plugin->run();
}

/**
 * Check if Google Web Stories plugin is active
 */
function techpremium_ws_pro_check_dependencies() {
    if ( ! is_plugin_active( 'web-stories/web-stories.php' ) ) {
        add_action( 'admin_notices', 'techpremium_ws_pro_dependency_notice' );
        return false;
    }
    return true;
}

/**
 * Display dependency notice
 */
function techpremium_ws_pro_dependency_notice() {
    ?>
    <div class="notice notice-warning is-dismissible">
        <p>
            <?php esc_html_e( 'TechPremium Web Stories Pro requires the Google Web Stories plugin to be installed and activated.', 'techpremium-web-stories-pro' ); ?>
            <a href="<?php echo esc_url( admin_url( 'plugin-install.php?s=web+stories&tab=search&type=term' ) ); ?>">
                <?php esc_html_e( 'Install Web Stories', 'techpremium-web-stories-pro' ); ?>
            </a>
        </p>
    </div>
    <?php
}

/**
 * Initialize the plugin
 */
add_action( 'plugins_loaded', function() {
    if ( techpremium_ws_pro_check_dependencies() ) {
        run_techpremium_ws_pro();
    }
} );

/**
 * Add settings link to plugin page
 */
function techpremium_ws_pro_add_settings_link( $links ) {
    $settings_link = '<a href="' . admin_url( 'admin.php?page=techpremium-ws-pro' ) . '">' . __( 'Settings', 'techpremium-web-stories-pro' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}
add_filter( 'plugin_action_links_' . TECHPREMIUM_WS_PRO_BASENAME, 'techpremium_ws_pro_add_settings_link' );
