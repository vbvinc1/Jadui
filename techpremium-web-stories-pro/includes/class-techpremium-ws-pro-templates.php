<?php

/**
 * Template system functionality
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/includes
 */
class Techpremium_Ws_Pro_Templates {

    /**
     * Initialize template system
     */
    public function init() {
        add_action( 'wp_ajax_load_templates', array( $this, 'load_templates' ) );
        add_action( 'wp_ajax_create_from_template', array( $this, 'create_from_template' ) );
        add_action( 'wp_ajax_save_template', array( $this, 'save_template' ) );
        add_action( 'wp_ajax_delete_template', array( $this, 'delete_template' ) );
    }

    /**
     * Load templates via AJAX
     */
    public function load_templates() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_story_templates';

        $category = sanitize_text_field( $_POST['category'] ?? '' );
        $is_premium = isset( $_POST['premium'] ) ? intval( $_POST['premium'] ) : null;

        $where_conditions = array( '1=1' );
        $where_values = array();

        if ( ! empty( $category ) ) {
            $where_conditions[] = 'category = %s';
            $where_values[] = $category;
        }

        if ( $is_premium !== null ) {
            $where_conditions[] = 'is_premium = %d';
            $where_values[] = $is_premium;
        }

        $where_clause = implode( ' AND ', $where_conditions );

        if ( ! empty( $where_values ) ) {
            $templates = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_name WHERE $where_clause ORDER BY created_at DESC",
                ...$where_values
            ) );
        } else {
            $templates = $wpdb->get_results( "SELECT * FROM $table_name ORDER BY created_at DESC" );
        }

        $response = array(
            'success' => true,
            'templates' => $templates
        );

        wp_send_json( $response );
    }

    /**
     * Create story from template
     */
    public function create_from_template() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $template_id = intval( $_POST['template_id'] );
        $story_title = sanitize_text_field( $_POST['story_title'] ?? '' );

        global $wpdb;
        $templates_table = $wpdb->prefix . 'techpremium_story_templates';
        $stories_table = $wpdb->prefix . 'techpremium_stories';

        // Get template
        $template = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $templates_table WHERE id = %d",
            $template_id
        ) );

        if ( ! $template ) {
            wp_send_json_error( __( 'Template not found', 'techpremium-web-stories-pro' ) );
        }

        // Create story from template
        $story_data = array(
            'title' => $story_title ?: ( $template->name . ' - Copy' ),
            'story_data' => $template->template_data,
            'template_id' => $template_id,
            'status' => 'draft',
            'author_id' => get_current_user_id()
        );

        $result = $wpdb->insert( $stories_table, $story_data );

        if ( $result ) {
            wp_send_json_success( array(
                'story_id' => $wpdb->insert_id,
                'message' => __( 'Story created from template successfully!', 'techpremium-web-stories-pro' )
            ) );
        } else {
            wp_send_json_error( __( 'Failed to create story from template', 'techpremium-web-stories-pro' ) );
        }
    }

    /**
     * Save custom template
     */
    public function save_template() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $template_data = array(
            'name' => sanitize_text_field( $_POST['name'] ),
            'description' => sanitize_textarea_field( $_POST['description'] ),
            'template_data' => wp_json_encode( $_POST['template_data'] ),
            'category' => sanitize_text_field( $_POST['category'] ),
            'is_premium' => false
        );

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_story_templates';

        $result = $wpdb->insert( $table_name, $template_data );

        if ( $result ) {
            wp_send_json_success( array(
                'template_id' => $wpdb->insert_id,
                'message' => __( 'Template saved successfully!', 'techpremium-web-stories-pro' )
            ) );
        } else {
            wp_send_json_error( __( 'Failed to save template', 'techpremium-web-stories-pro' ) );
        }
    }

    /**
     * Delete template
     */
    public function delete_template() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $template_id = intval( $_POST['template_id'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_story_templates';

        $result = $wpdb->delete( $table_name, array( 'id' => $template_id ) );

        if ( $result ) {
            wp_send_json_success( __( 'Template deleted successfully', 'techpremium-web-stories-pro' ) );
        } else {
            wp_send_json_error( __( 'Failed to delete template', 'techpremium-web-stories-pro' ) );
        }
    }

    /**
     * Get predefined templates
     */
    public function get_predefined_templates() {
        return array(
            array(
                'name' => 'Tech News',
                'description' => 'Perfect for technology news and updates',
                'category' => 'technology',
                'template_data' => array(
                    'pages' => array(
                        array(
                            'id' => 'cover',
                            'background' => array( 'type' => 'gradient', 'colors' => array( '#007cba', '#0073aa' ) ),
                            'elements' => array(
                                array( 'type' => 'text', 'content' => 'Tech News', 'style' => array( 'fontSize' => '48px', 'color' => '#ffffff', 'textAlign' => 'center' ) )
                            )
                        ),
                        array(
                            'id' => 'content-1',
                            'background' => array( 'type' => 'color', 'color' => '#ffffff' ),
                            'elements' => array(
                                array( 'type' => 'text', 'content' => 'Latest Technology Updates', 'style' => array( 'fontSize' => '32px', 'color' => '#333333' ) ),
                                array( 'type' => 'text', 'content' => 'Stay updated with the latest trends in technology', 'style' => array( 'fontSize' => '18px', 'color' => '#666666' ) )
                            )
                        )
                    )
                ),
                'preview_image' => '',
                'is_premium' => false
            ),
            array(
                'name' => 'AI Showcase',
                'description' => 'Showcase AI tools and technologies',
                'category' => 'ai',
                'template_data' => array(
                    'pages' => array(
                        array(
                            'id' => 'cover',
                            'background' => array( 'type' => 'gradient', 'colors' => array( '#6c5ce7', '#a29bfe' ) ),
                            'elements' => array(
                                array( 'type' => 'text', 'content' => 'AI Tools', 'style' => array( 'fontSize' => '48px', 'color' => '#ffffff', 'textAlign' => 'center' ) )
                            )
                        )
                    )
                ),
                'preview_image' => '',
                'is_premium' => true
            ),
            array(
                'name' => 'Product Launch',
                'description' => 'Perfect for product announcements',
                'category' => 'business',
                'template_data' => array(
                    'pages' => array(
                        array(
                            'id' => 'cover',
                            'background' => array( 'type' => 'gradient', 'colors' => array( '#28a745', '#20c997' ) ),
                            'elements' => array(
                                array( 'type' => 'text', 'content' => 'New Product', 'style' => array( 'fontSize' => '48px', 'color' => '#ffffff' ) )
                            )
                        )
                    )
                ),
                'preview_image' => '',
                'is_premium' => false
            )
        );
    }

    /**
     * Install predefined templates
     */
    public function install_predefined_templates() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_story_templates';

        $templates = $this->get_predefined_templates();

        foreach ( $templates as $template ) {
            // Check if template already exists
            $existing = $wpdb->get_var( $wpdb->prepare(
                "SELECT id FROM $table_name WHERE name = %s",
                $template['name']
            ) );

            if ( ! $existing ) {
                $wpdb->insert( $table_name, array(
                    'name' => $template['name'],
                    'description' => $template['description'],
                    'template_data' => wp_json_encode( $template['template_data'] ),
                    'category' => $template['category'],
                    'preview_image' => $template['preview_image'],
                    'is_premium' => $template['is_premium']
                ) );
            }
        }
    }
}
