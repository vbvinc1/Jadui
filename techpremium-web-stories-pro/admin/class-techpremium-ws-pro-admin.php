<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/admin
 */
class Techpremium_Ws_Pro_Admin {

    /**
     * The ID of this plugin.
     */
    private $plugin_name;

    /**
     * The version of this plugin.
     */
    private $version;

    /**
     * Initialize the class and set its properties.
     */
    public function __construct( $plugin_name, $version ) {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }

    /**
     * Register the stylesheets for the admin area.
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'css/techpremium-ws-pro-admin.css',
            array(),
            $this->version,
            'all'
        );

        // Add additional admin styles
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style( 'jquery-ui-datepicker' );
    }

    /**
     * Register the JavaScript for the admin area.
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __FILE__ ) . 'js/techpremium-ws-pro-admin.js',
            array( 'jquery', 'wp-color-picker', 'jquery-ui-datepicker', 'jquery-ui-sortable' ),
            $this->version,
            false
        );

        // Localize script for AJAX
        wp_localize_script(
            $this->plugin_name,
            'techpremium_ws_pro_ajax',
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'techpremium_ws_pro_nonce' ),
                'strings' => array(
                    'upload_success' => __( 'Story uploaded successfully!', 'techpremium-web-stories-pro' ),
                    'upload_error' => __( 'Error uploading story. Please try again.', 'techpremium-web-stories-pro' ),
                    'delete_confirm' => __( 'Are you sure you want to delete this story?', 'techpremium-web-stories-pro' ),
                    'processing' => __( 'Processing...', 'techpremium-web-stories-pro' )
                )
            )
        );
    }

    /**
     * Add the plugin admin menu.
     */
    public function add_plugin_admin_menu() {
        add_menu_page(
            __( 'TechPremium Web Stories Pro', 'techpremium-web-stories-pro' ),
            __( 'WS Pro', 'techpremium-web-stories-pro' ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_plugin_admin_page' ),
            'dashicons-format-gallery',
            30
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'All Stories', 'techpremium-web-stories-pro' ),
            __( 'All Stories', 'techpremium-web-stories-pro' ),
            'manage_options',
            $this->plugin_name,
            array( $this, 'display_plugin_admin_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Add New Story', 'techpremium-web-stories-pro' ),
            __( 'Add New', 'techpremium-web-stories-pro' ),
            'manage_options',
            $this->plugin_name . '-add-new',
            array( $this, 'display_add_new_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Templates', 'techpremium-web-stories-pro' ),
            __( 'Templates', 'techpremium-web-stories-pro' ),
            'manage_options',
            $this->plugin_name . '-templates',
            array( $this, 'display_templates_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'SEO Settings', 'techpremium-web-stories-pro' ),
            __( 'SEO Settings', 'techpremium-web-stories-pro' ),
            'manage_options',
            $this->plugin_name . '-seo',
            array( $this, 'display_seo_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Analytics', 'techpremium-web-stories-pro' ),
            __( 'Analytics', 'techpremium-web-stories-pro' ),
            'manage_options',
            $this->plugin_name . '-analytics',
            array( $this, 'display_analytics_page' )
        );

        add_submenu_page(
            $this->plugin_name,
            __( 'Settings', 'techpremium-web-stories-pro' ),
            __( 'Settings', 'techpremium-web-stories-pro' ),
            'manage_options',
            $this->plugin_name . '-settings',
            array( $this, 'display_settings_page' )
        );
    }

    /**
     * Display the main admin page.
     */
    public function display_plugin_admin_page() {
        include_once 'partials/techpremium-ws-pro-admin-display.php';
    }

    /**
     * Display the add new story page.
     */
    public function display_add_new_page() {
        include_once 'partials/techpremium-ws-pro-add-new-display.php';
    }

    /**
     * Display the templates page.
     */
    public function display_templates_page() {
        include_once 'partials/techpremium-ws-pro-templates-display.php';
    }

    /**
     * Display the SEO page.
     */
    public function display_seo_page() {
        include_once 'partials/techpremium-ws-pro-seo-display.php';
    }

    /**
     * Display the analytics page.
     */
    public function display_analytics_page() {
        include_once 'partials/techpremium-ws-pro-analytics-display.php';
    }

    /**
     * Display the settings page.
     */
    public function display_settings_page() {
        include_once 'partials/techpremium-ws-pro-settings-display.php';
    }

    /**
     * Handle options update.
     */
    public function options_update() {
        register_setting( $this->plugin_name, $this->plugin_name, array( $this, 'validate' ) );
    }

    /**
     * Validate options.
     */
    public function validate( $input ) {
        $valid = array();

        // Sanitize text inputs
        if ( isset( $input['api_key'] ) ) {
            $valid['api_key'] = sanitize_text_field( $input['api_key'] );
        }

        // Validate colors
        if ( isset( $input['primary_color'] ) ) {
            $valid['primary_color'] = sanitize_hex_color( $input['primary_color'] );
        }

        return $valid;
    }

    /**
     * Handle HTML file upload.
     */
    public function handle_html_upload() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        // Check user capabilities
        if ( ! current_user_can( 'manage_options' ) ) {
            wp_die( __( 'Insufficient permissions', 'techpremium-web-stories-pro' ) );
        }

        $response = array( 'success' => false );

        if ( ! empty( $_FILES['html_file'] ) ) {
            $uploaded_file = $_FILES['html_file'];

            // Validate file type
            $allowed_types = array( 'text/html', 'text/plain' );
            if ( in_array( $uploaded_file['type'], $allowed_types ) ) {

                $upload_dir = wp_upload_dir();
                $target_dir = $upload_dir['basedir'] . '/techpremium-stories/temp/';
                $target_file = $target_dir . basename( $uploaded_file['name'] );

                if ( move_uploaded_file( $uploaded_file['tmp_name'], $target_file ) ) {
                    $html_content = file_get_contents( $target_file );

                    // Parse and convert HTML to Web Story format
                    $story_data = $this->parse_html_to_story( $html_content );

                    if ( $story_data ) {
                        global $wpdb;
                        $table_name = $wpdb->prefix . 'techpremium_stories';

                        $result = $wpdb->insert(
                            $table_name,
                            array(
                                'title' => sanitize_text_field( $_POST['story_title'] ),
                                'html_content' => $html_content,
                                'story_data' => json_encode( $story_data ),
                                'status' => 'draft',
                                'author_id' => get_current_user_id()
                            )
                        );

                        if ( $result ) {
                            $response['success'] = true;
                            $response['story_id'] = $wpdb->insert_id;
                            $response['message'] = __( 'Story uploaded successfully!', 'techpremium-web-stories-pro' );
                        }
                    }

                    // Clean up temp file
                    unlink( $target_file );
                }
            }
        }

        wp_send_json( $response );
    }

    /**
     * Convert HTML to Web Story format.
     */
    private function parse_html_to_story( $html_content ) {
        // Initialize DOMDocument
        $dom = new DOMDocument();
        libxml_use_internal_errors( true );
        $dom->loadHTML( $html_content );
        libxml_clear_errors();

        $story_data = array(
            'pages' => array(),
            'metadata' => array(),
            'settings' => array()
        );

        // Extract pages from HTML
        $xpath = new DOMXPath( $dom );
        $pages = $xpath->query( '//amp-story-page' );

        if ( $pages->length > 0 ) {
            foreach ( $pages as $page ) {
                $page_data = $this->extract_page_data( $page );
                $story_data['pages'][] = $page_data;
            }
        } else {
            // If not AMP format, create pages from content sections
            $story_data['pages'] = $this->create_pages_from_html( $dom );
        }

        return $story_data;
    }

    /**
     * Extract page data from AMP story page.
     */
    private function extract_page_data( $page_element ) {
        $page_data = array(
            'id' => $page_element->getAttribute( 'id' ),
            'background' => array(),
            'layers' => array()
        );

        // Extract background
        $background = $page_element->getElementsByTagName( 'amp-story-grid-layer' )->item( 0 );
        if ( $background ) {
            $page_data['background'] = $this->extract_background_data( $background );
        }

        // Extract layers
        $layers = $page_element->getElementsByTagName( 'amp-story-grid-layer' );
        foreach ( $layers as $layer ) {
            $layer_data = $this->extract_layer_data( $layer );
            $page_data['layers'][] = $layer_data;
        }

        return $page_data;
    }

    /**
     * Extract background data.
     */
    private function extract_background_data( $background_element ) {
        return array(
            'type' => 'color',
            'color' => '#ffffff',
            'image' => '',
            'video' => ''
        );
    }

    /**
     * Extract layer data.
     */
    private function extract_layer_data( $layer_element ) {
        return array(
            'template' => $layer_element->getAttribute( 'template' ),
            'elements' => $this->extract_elements( $layer_element )
        );
    }

    /**
     * Extract elements from layer.
     */
    private function extract_elements( $layer_element ) {
        $elements = array();

        // Extract text elements
        $text_elements = $layer_element->getElementsByTagName( 'h1' );
        foreach ( $text_elements as $element ) {
            $elements[] = array(
                'type' => 'text',
                'content' => $element->textContent,
                'style' => $this->extract_element_style( $element )
            );
        }

        // Extract image elements
        $img_elements = $layer_element->getElementsByTagName( 'amp-img' );
        foreach ( $img_elements as $element ) {
            $elements[] = array(
                'type' => 'image',
                'src' => $element->getAttribute( 'src' ),
                'alt' => $element->getAttribute( 'alt' ),
                'style' => $this->extract_element_style( $element )
            );
        }

        return $elements;
    }

    /**
     * Extract element style.
     */
    private function extract_element_style( $element ) {
        return array(
            'width' => $element->getAttribute( 'width' ),
            'height' => $element->getAttribute( 'height' ),
            'x' => '0',
            'y' => '0'
        );
    }

    /**
     * Create pages from regular HTML.
     */
    private function create_pages_from_html( $dom ) {
        $pages = array();

        // Simple conversion: create one page with the HTML content
        $page = array(
            'id' => 'page-1',
            'background' => array(
                'type' => 'color',
                'color' => '#ffffff'
            ),
            'layers' => array(
                array(
                    'template' => 'vertical',
                    'elements' => array(
                        array(
                            'type' => 'text',
                            'content' => $dom->textContent,
                            'style' => array(
                                'font-size' => '18px',
                                'color' => '#000000'
                            )
                        )
                    )
                )
            )
        );

        $pages[] = $page;
        return $pages;
    }

    /**
     * Convert story to Google Web Stories format.
     */
    public function convert_html_to_story() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $story_id = intval( $_POST['story_id'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $story = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $story_id ) );

        if ( $story ) {
            // Create Web Story post
            $post_data = array(
                'post_title' => $story->title,
                'post_content' => $story->html_content,
                'post_status' => 'draft',
                'post_type' => 'web-story',
                'meta_input' => array(
                    'web_stories_story_data' => $story->story_data,
                    'techpremium_story_id' => $story_id
                )
            );

            $post_id = wp_insert_post( $post_data );

            if ( $post_id ) {
                // Update story record
                $wpdb->update(
                    $table_name,
                    array( 'status' => 'published' ),
                    array( 'id' => $story_id )
                );

                wp_send_json_success( array(
                    'post_id' => $post_id,
                    'edit_url' => admin_url( 'post.php?post=' . $post_id . '&action=edit' )
                ) );
            }
        }

        wp_send_json_error( __( 'Failed to convert story', 'techpremium-web-stories-pro' ) );
    }

    /**
     * Delete story.
     */
    public function delete_story() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $story_id = intval( $_POST['story_id'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $result = $wpdb->delete( $table_name, array( 'id' => $story_id ) );

        if ( $result ) {
            wp_send_json_success( __( 'Story deleted successfully', 'techpremium-web-stories-pro' ) );
        } else {
            wp_send_json_error( __( 'Failed to delete story', 'techpremium-web-stories-pro' ) );
        }
    }

    /**
     * Duplicate story.
     */
    public function duplicate_story() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $story_id = intval( $_POST['story_id'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $story = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $story_id ) );

        if ( $story ) {
            $new_story_data = array(
                'title' => $story->title . ' (Copy)',
                'content' => $story->content,
                'html_content' => $story->html_content,
                'story_data' => $story->story_data,
                'template_id' => $story->template_id,
                'status' => 'draft',
                'author_id' => get_current_user_id()
            );

            $result = $wpdb->insert( $table_name, $new_story_data );

            if ( $result ) {
                wp_send_json_success( array(
                    'new_story_id' => $wpdb->insert_id,
                    'message' => __( 'Story duplicated successfully', 'techpremium-web-stories-pro' )
                ) );
            }
        }

        wp_send_json_error( __( 'Failed to duplicate story', 'techpremium-web-stories-pro' ) );
    }
}
