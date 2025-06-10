<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/includes
 */
class Techpremium_Ws_Pro {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Techpremium_Ws_Pro_Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected $loader;

    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected $plugin_name;

    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected $version;

    /**
     * Define the core functionality of the plugin.
     */
    public function __construct() {
        if ( defined( 'TECHPREMIUM_WS_PRO_VERSION' ) ) {
            $this->version = TECHPREMIUM_WS_PRO_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        $this->plugin_name = 'techpremium-ws-pro';

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     */
    private function load_dependencies() {

        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-techpremium-ws-pro-loader.php';

        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-techpremium-ws-pro-i18n.php';

        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-techpremium-ws-pro-admin.php';

        /**
         * The class responsible for defining all actions that occur in the public-facing
         * side of the site.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-techpremium-ws-pro-public.php';

        /**
         * SEO Integration classes
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/seo/class-techpremium-ws-pro-seo.php';

        /**
         * REST API classes
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/api/class-techpremium-ws-pro-api.php';

        /**
         * Template system
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-techpremium-ws-pro-templates.php';

        /**
         * Story management system
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-techpremium-ws-pro-story-manager.php';

        $this->loader = new Techpremium_Ws_Pro_Loader();
    }

    /**
     * Define the locale for this plugin for internationalization.
     */
    private function set_locale() {
        $plugin_i18n = new Techpremium_Ws_Pro_i18n();
        $this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );
    }

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     */
    private function define_admin_hooks() {
        $plugin_admin = new Techpremium_Ws_Pro_Admin( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action( 'admin_menu', $plugin_admin, 'add_plugin_admin_menu' );
        $this->loader->add_action( 'admin_init', $plugin_admin, 'options_update' );

        // AJAX handlers
        $this->loader->add_action( 'wp_ajax_upload_html_story', $plugin_admin, 'handle_html_upload' );
        $this->loader->add_action( 'wp_ajax_convert_story', $plugin_admin, 'convert_html_to_story' );
        $this->loader->add_action( 'wp_ajax_delete_story', $plugin_admin, 'delete_story' );
        $this->loader->add_action( 'wp_ajax_duplicate_story', $plugin_admin, 'duplicate_story' );

        // REST API
        $api = new Techpremium_Ws_Pro_Api();
        $this->loader->add_action( 'rest_api_init', $api, 'register_routes' );

        // SEO Integration
        $seo = new Techpremium_Ws_Pro_Seo();
        $this->loader->add_action( 'init', $seo, 'init' );
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     */
    private function define_public_hooks() {
        $plugin_public = new Techpremium_Ws_Pro_Public( $this->get_plugin_name(), $this->get_version() );

        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

        // Shortcodes
        $this->loader->add_action( 'init', $plugin_public, 'register_shortcodes' );

        // Template system
        $templates = new Techpremium_Ws_Pro_Templates();
        $this->loader->add_action( 'init', $templates, 'init' );
    }

    /**
     * Run the loader to execute all of the hooks with WordPress.
     */
    public function run() {
        $this->loader->run();
    }

    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     */
    public function get_plugin_name() {
        return $this->plugin_name;
    }

    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     */
    public function get_loader() {
        return $this->loader;
    }

    /**
     * Retrieve the version number of the plugin.
     */
    public function get_version() {
        return $this->version;
    }
}
