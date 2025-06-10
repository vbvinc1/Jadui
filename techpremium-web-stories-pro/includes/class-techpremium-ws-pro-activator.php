<?php

/**
 * Fired during plugin activation
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/includes
 */
class Techpremium_Ws_Pro_Activator {

    /**
     * Plugin activation handler
     */
    public static function activate() {
        global $wpdb;

        // Create custom database tables
        self::create_database_tables();

        // Set default options
        self::set_default_options();

        // Create upload directories
        self::create_upload_directories();

        // Flush rewrite rules
        flush_rewrite_rules();
    }

    /**
     * Create custom database tables
     */
    private static function create_database_tables() {
        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        // Stories table
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            title varchar(255) NOT NULL,
            content longtext NOT NULL,
            html_content longtext,
            story_data longtext,
            template_id varchar(100),
            status varchar(20) DEFAULT 'draft',
            seo_data longtext,
            analytics_data longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            author_id bigint(20) UNSIGNED,
            PRIMARY KEY (id),
            KEY author_id (author_id),
            KEY status (status),
            KEY created_at (created_at)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );

        // Templates table
        $template_table = $wpdb->prefix . 'techpremium_story_templates';

        $template_sql = "CREATE TABLE $template_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            description text,
            template_data longtext NOT NULL,
            preview_image varchar(255),
            category varchar(100),
            is_premium boolean DEFAULT FALSE,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY category (category)
        ) $charset_collate;";

        dbDelta( $template_sql );

        // Insert default templates
        self::insert_default_templates();
    }

    /**
     * Set default plugin options
     */
    private static function set_default_options() {
        $default_options = array(
            'api_key' => '',
            'seo_integration' => array(
                'yoast' => true,
                'rankmath' => true,
                'auto_schema' => true,
                'open_graph' => true,
                'twitter_cards' => true
            ),
            'analytics' => array(
                'google_analytics' => '',
                'facebook_pixel' => '',
                'enable_tracking' => true
            ),
            'branding' => array(
                'logo' => '',
                'primary_color' => '#007cba',
                'secondary_color' => '#0073aa',
                'font_family' => 'Roboto'
            ),
            'advanced' => array(
                'enable_ab_testing' => false,
                'auto_optimize_images' => true,
                'enable_lazy_loading' => true,
                'cache_stories' => true
            )
        );

        add_option( 'techpremium_ws_pro_options', $default_options );
    }

    /**
     * Create upload directories
     */
    private static function create_upload_directories() {
        $upload_dir = wp_upload_dir();
        $plugin_upload_dir = $upload_dir['basedir'] . '/techpremium-stories/';

        if ( ! file_exists( $plugin_upload_dir ) ) {
            wp_mkdir_p( $plugin_upload_dir );
            wp_mkdir_p( $plugin_upload_dir . 'templates/' );
            wp_mkdir_p( $plugin_upload_dir . 'exports/' );
            wp_mkdir_p( $plugin_upload_dir . 'temp/' );
        }

        // Create .htaccess for security
        $htaccess_file = $plugin_upload_dir . '.htaccess';
        if ( ! file_exists( $htaccess_file ) ) {
            $htaccess_content = "Options -Indexes\nDeny from all\n<Files ~ "\.(json|html)$">\nAllow from all\n</Files>";
            file_put_contents( $htaccess_file, $htaccess_content );
        }
    }

    /**
     * Insert default templates
     */
    private static function insert_default_templates() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'techpremium_story_templates';

        $default_templates = array(
            array(
                'name' => 'Tech News Template',
                'description' => 'Modern template for technology news and updates',
                'template_data' => json_encode(array(
                    'pages' => 5,
                    'layout' => 'vertical',
                    'animations' => array('fade', 'slide'),
                    'colors' => array('#007cba', '#0073aa', '#ffffff'),
                    'fonts' => array('Roboto', 'Open Sans')
                )),
                'category' => 'technology',
                'is_premium' => false
            ),
            array(
                'name' => 'AI Tools Showcase',
                'description' => 'Perfect for showcasing AI tools and features',
                'template_data' => json_encode(array(
                    'pages' => 10,
                    'layout' => 'card-based',
                    'animations' => array('zoom', 'fade', 'slide'),
                    'colors' => array('#6c5ce7', '#a29bfe', '#ffffff'),
                    'fonts' => array('Inter', 'Poppins')
                )),
                'category' => 'ai',
                'is_premium' => true
            )
        );

        foreach ( $default_templates as $template ) {
            $wpdb->insert( $table_name, $template );
        }
    }
}
