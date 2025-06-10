<?php

/**
 * REST API functionality
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/includes/api
 */
class Techpremium_Ws_Pro_Api {

    /**
     * Register REST API routes
     */
    public function register_routes() {
        $namespace = 'techpremium-ws-pro/v1';

        // Stories endpoints
        register_rest_route( $namespace, '/stories', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_stories' ),
                'permission_callback' => array( $this, 'check_permissions' )
            ),
            array(
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => array( $this, 'create_story' ),
                'permission_callback' => array( $this, 'check_permissions' ),
                'args' => array(
                    'title' => array(
                        'required' => true,
                        'type' => 'string',
                        'sanitize_callback' => 'sanitize_text_field'
                    ),
                    'content' => array(
                        'required' => false,
                        'type' => 'string'
                    )
                )
            )
        ) );

        register_rest_route( $namespace, '/stories/(?P<id>\d+)', array(
            array(
                'methods' => WP_REST_Server::READABLE,
                'callback' => array( $this, 'get_story' ),
                'permission_callback' => array( $this, 'check_permissions' )
            ),
            array(
                'methods' => WP_REST_Server::EDITABLE,
                'callback' => array( $this, 'update_story' ),
                'permission_callback' => array( $this, 'check_permissions' )
            ),
            array(
                'methods' => WP_REST_Server::DELETABLE,
                'callback' => array( $this, 'delete_story' ),
                'permission_callback' => array( $this, 'check_permissions' )
            )
        ) );

        // Templates endpoints
        register_rest_route( $namespace, '/templates', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'get_templates' ),
            'permission_callback' => array( $this, 'check_permissions' )
        ) );

        register_rest_route( $namespace, '/templates/(?P<id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'get_template' ),
            'permission_callback' => array( $this, 'check_permissions' )
        ) );

        // Analytics endpoints
        register_rest_route( $namespace, '/analytics/(?P<story_id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'get_story_analytics' ),
            'permission_callback' => array( $this, 'check_permissions' )
        ) );

        // SEO endpoints
        register_rest_route( $namespace, '/seo/analyze/(?P<story_id>\d+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'analyze_story_seo' ),
            'permission_callback' => array( $this, 'check_permissions' )
        ) );

        // Conversion endpoints
        register_rest_route( $namespace, '/convert/html', array(
            'methods' => WP_REST_Server::CREATABLE,
            'callback' => array( $this, 'convert_html_to_story' ),
            'permission_callback' => array( $this, 'check_permissions' ),
            'args' => array(
                'html_content' => array(
                    'required' => true,
                    'type' => 'string'
                ),
                'title' => array(
                    'required' => false,
                    'type' => 'string'
                )
            )
        ) );

        // Export endpoints
        register_rest_route( $namespace, '/export/(?P<story_id>\d+)/(?P<format>\w+)', array(
            'methods' => WP_REST_Server::READABLE,
            'callback' => array( $this, 'export_story' ),
            'permission_callback' => array( $this, 'check_permissions' )
        ) );
    }

    /**
     * Check API permissions
     */
    public function check_permissions() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Get all stories
     */
    public function get_stories( $request ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'techpremium_stories';
        $per_page = $request->get_param( 'per_page' ) ?: 10;
        $page = $request->get_param( 'page' ) ?: 1;
        $offset = ( $page - 1 ) * $per_page;

        $stories = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT %d OFFSET %d",
            $per_page,
            $offset
        ) );

        $total = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name" );

        $response = array(
            'stories' => $stories,
            'total' => intval( $total ),
            'pages' => ceil( $total / $per_page ),
            'current_page' => $page
        );

        return rest_ensure_response( $response );
    }

    /**
     * Get single story
     */
    public function get_story( $request ) {
        global $wpdb;

        $story_id = $request['id'];
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $story = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $story_id
        ) );

        if ( ! $story ) {
            return new WP_Error( 'story_not_found', __( 'Story not found', 'techpremium-web-stories-pro' ), array( 'status' => 404 ) );
        }

        // Add parsed story data
        if ( ! empty( $story->story_data ) ) {
            $story->parsed_data = json_decode( $story->story_data, true );
        }

        return rest_ensure_response( $story );
    }

    /**
     * Create new story
     */
    public function create_story( $request ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'techpremium_stories';

        $story_data = array(
            'title' => sanitize_text_field( $request['title'] ),
            'content' => wp_kses_post( $request['content'] ?: '' ),
            'status' => 'draft',
            'author_id' => get_current_user_id()
        );

        $result = $wpdb->insert( $table_name, $story_data );

        if ( $result === false ) {
            return new WP_Error( 'story_creation_failed', __( 'Failed to create story', 'techpremium-web-stories-pro' ), array( 'status' => 500 ) );
        }

        $story_id = $wpdb->insert_id;
        $story = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $story_id ) );

        return rest_ensure_response( $story );
    }

    /**
     * Update story
     */
    public function update_story( $request ) {
        global $wpdb;

        $story_id = $request['id'];
        $table_name = $wpdb->prefix . 'techpremium_stories';

        // Check if story exists
        $existing_story = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $story_id ) );

        if ( ! $existing_story ) {
            return new WP_Error( 'story_not_found', __( 'Story not found', 'techpremium-web-stories-pro' ), array( 'status' => 404 ) );
        }

        $update_data = array();

        if ( isset( $request['title'] ) ) {
            $update_data['title'] = sanitize_text_field( $request['title'] );
        }

        if ( isset( $request['content'] ) ) {
            $update_data['content'] = wp_kses_post( $request['content'] );
        }

        if ( isset( $request['story_data'] ) ) {
            $update_data['story_data'] = wp_json_encode( $request['story_data'] );
        }

        if ( isset( $request['status'] ) ) {
            $update_data['status'] = sanitize_text_field( $request['status'] );
        }

        if ( ! empty( $update_data ) ) {
            $result = $wpdb->update( $table_name, $update_data, array( 'id' => $story_id ) );

            if ( $result === false ) {
                return new WP_Error( 'story_update_failed', __( 'Failed to update story', 'techpremium-web-stories-pro' ), array( 'status' => 500 ) );
            }
        }

        $updated_story = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $story_id ) );

        return rest_ensure_response( $updated_story );
    }

    /**
     * Delete story
     */
    public function delete_story( $request ) {
        global $wpdb;

        $story_id = $request['id'];
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $result = $wpdb->delete( $table_name, array( 'id' => $story_id ) );

        if ( $result === false ) {
            return new WP_Error( 'story_deletion_failed', __( 'Failed to delete story', 'techpremium-web-stories-pro' ), array( 'status' => 500 ) );
        }

        return rest_ensure_response( array( 'deleted' => true ) );
    }

    /**
     * Get templates
     */
    public function get_templates( $request ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'techpremium_story_templates';

        $category = $request->get_param( 'category' );
        $is_premium = $request->get_param( 'premium' );

        $where_conditions = array();
        $where_values = array();

        if ( $category ) {
            $where_conditions[] = 'category = %s';
            $where_values[] = $category;
        }

        if ( $is_premium !== null ) {
            $where_conditions[] = 'is_premium = %d';
            $where_values[] = $is_premium ? 1 : 0;
        }

        $where_clause = '';
        if ( ! empty( $where_conditions ) ) {
            $where_clause = 'WHERE ' . implode( ' AND ', $where_conditions );
        }

        $query = "SELECT * FROM $table_name $where_clause ORDER BY created_at DESC";

        if ( ! empty( $where_values ) ) {
            $templates = $wpdb->get_results( $wpdb->prepare( $query, $where_values ) );
        } else {
            $templates = $wpdb->get_results( $query );
        }

        return rest_ensure_response( $templates );
    }

    /**
     * Get single template
     */
    public function get_template( $request ) {
        global $wpdb;

        $template_id = $request['id'];
        $table_name = $wpdb->prefix . 'techpremium_story_templates';

        $template = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $template_id
        ) );

        if ( ! $template ) {
            return new WP_Error( 'template_not_found', __( 'Template not found', 'techpremium-web-stories-pro' ), array( 'status' => 404 ) );
        }

        // Parse template data
        if ( ! empty( $template->template_data ) ) {
            $template->parsed_data = json_decode( $template->template_data, true );
        }

        return rest_ensure_response( $template );
    }

    /**
     * Get story analytics
     */
    public function get_story_analytics( $request ) {
        $story_id = $request['story_id'];

        // Mock analytics data - in a real implementation, this would come from
        // Google Analytics, Facebook Analytics, or custom tracking
        $analytics = array(
            'views' => array(
                'total' => 1250,
                'today' => 45,
                'week' => 312,
                'month' => 892
            ),
            'engagement' => array(
                'completion_rate' => 78.5,
                'average_time' => 28.4,
                'bounce_rate' => 21.5,
                'pages_per_session' => 4.2
            ),
            'traffic_sources' => array(
                'google' => 45.2,
                'direct' => 28.1,
                'social' => 18.7,
                'referral' => 8.0
            ),
            'demographics' => array(
                'age_groups' => array(
                    '18-24' => 22.3,
                    '25-34' => 34.5,
                    '35-44' => 28.1,
                    '45-54' => 12.1,
                    '55+' => 3.0
                ),
                'devices' => array(
                    'mobile' => 68.4,
                    'desktop' => 24.1,
                    'tablet' => 7.5
                )
            ),
            'performance' => array(
                'load_time' => 1.2,
                'first_paint' => 0.8,
                'interactive' => 2.1,
                'seo_score' => 87
            )
        );

        return rest_ensure_response( $analytics );
    }

    /**
     * Analyze story SEO
     */
    public function analyze_story_seo( $request ) {
        $story_id = $request['story_id'];

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $story = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $story_id ) );

        if ( ! $story ) {
            return new WP_Error( 'story_not_found', __( 'Story not found', 'techpremium-web-stories-pro' ), array( 'status' => 404 ) );
        }

        $seo = new Techpremium_Ws_Pro_Seo();
        $score = $seo->calculate_seo_score( $story_id );
        $suggestions = $seo->get_seo_suggestions( $story_id );

        $analysis = array(
            'score' => $score,
            'grade' => $this->get_seo_grade( $score ),
            'suggestions' => $suggestions,
            'checks' => array(
                'title_length' => $this->check_title_length( $story->title ),
                'meta_description' => $this->check_meta_description( $story_id ),
                'featured_image' => $this->check_featured_image( $story_id ),
                'content_length' => $this->check_content_length( $story->content ),
                'keywords' => $this->check_keywords( $story_id )
            )
        );

        return rest_ensure_response( $analysis );
    }

    /**
     * Convert HTML to story format
     */
    public function convert_html_to_story( $request ) {
        $html_content = $request['html_content'];
        $title = $request['title'] ?: 'Untitled Story';

        // Use the same HTML parsing logic from the admin class
        $admin = new Techpremium_Ws_Pro_Admin( 'techpremium-ws-pro', '1.0.0' );
        $story_data = $admin->parse_html_to_story( $html_content );

        if ( ! $story_data ) {
            return new WP_Error( 'conversion_failed', __( 'Failed to convert HTML to story format', 'techpremium-web-stories-pro' ), array( 'status' => 400 ) );
        }

        return rest_ensure_response( array(
            'success' => true,
            'story_data' => $story_data,
            'message' => __( 'HTML converted successfully', 'techpremium-web-stories-pro' )
        ) );
    }

    /**
     * Export story in different formats
     */
    public function export_story( $request ) {
        $story_id = $request['story_id'];
        $format = $request['format'];

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $story = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $table_name WHERE id = %d", $story_id ) );

        if ( ! $story ) {
            return new WP_Error( 'story_not_found', __( 'Story not found', 'techpremium-web-stories-pro' ), array( 'status' => 404 ) );
        }

        switch ( $format ) {
            case 'json':
                $export_data = array(
                    'title' => $story->title,
                    'content' => $story->content,
                    'story_data' => json_decode( $story->story_data, true ),
                    'exported_at' => current_time( 'mysql' )
                );
                break;

            case 'html':
                $export_data = $story->html_content ?: $story->content;
                break;

            case 'amp':
                $export_data = $this->convert_to_amp_format( $story );
                break;

            default:
                return new WP_Error( 'invalid_format', __( 'Invalid export format', 'techpremium-web-stories-pro' ), array( 'status' => 400 ) );
        }

        return rest_ensure_response( array(
            'format' => $format,
            'data' => $export_data,
            'filename' => sanitize_file_name( $story->title ) . '.' . $format
        ) );
    }

    /**
     * Helper methods
     */
    private function get_seo_grade( $score ) {
        if ( $score >= 90 ) return 'A+';
        if ( $score >= 80 ) return 'A';
        if ( $score >= 70 ) return 'B+';
        if ( $score >= 60 ) return 'B';
        if ( $score >= 50 ) return 'C+';
        if ( $score >= 40 ) return 'C';
        return 'F';
    }

    private function check_title_length( $title ) {
        $length = strlen( $title );
        return array(
            'status' => ( $length >= 10 && $length <= 60 ) ? 'good' : 'warning',
            'length' => $length,
            'message' => ( $length >= 10 && $length <= 60 ) ? 
                __( 'Title length is optimal', 'techpremium-web-stories-pro' ) :
                __( 'Title should be 10-60 characters', 'techpremium-web-stories-pro' )
        );
    }

    private function check_meta_description( $story_id ) {
        $meta = get_post_meta( $story_id, 'techpremium_story_seo', true );
        $description = isset( $meta['description'] ) ? $meta['description'] : '';
        $length = strlen( $description );

        return array(
            'status' => ( $length >= 120 && $length <= 160 ) ? 'good' : 'warning',
            'length' => $length,
            'message' => ( $length >= 120 && $length <= 160 ) ? 
                __( 'Meta description length is optimal', 'techpremium-web-stories-pro' ) :
                __( 'Meta description should be 120-160 characters', 'techpremium-web-stories-pro' )
        );
    }

    private function check_featured_image( $story_id ) {
        $has_image = has_post_thumbnail( $story_id );

        return array(
            'status' => $has_image ? 'good' : 'warning',
            'message' => $has_image ? 
                __( 'Featured image is set', 'techpremium-web-stories-pro' ) :
                __( 'Add a featured image', 'techpremium-web-stories-pro' )
        );
    }

    private function check_content_length( $content ) {
        $length = strlen( strip_tags( $content ) );

        return array(
            'status' => ( $length >= 100 ) ? 'good' : 'warning',
            'length' => $length,
            'message' => ( $length >= 100 ) ? 
                __( 'Content length is sufficient', 'techpremium-web-stories-pro' ) :
                __( 'Add more content to improve SEO', 'techpremium-web-stories-pro' )
        );
    }

    private function check_keywords( $story_id ) {
        $meta = get_post_meta( $story_id, 'techpremium_story_seo', true );
        $keywords = isset( $meta['keywords'] ) ? $meta['keywords'] : '';
        $keyword_count = empty( $keywords ) ? 0 : count( explode( ',', $keywords ) );

        return array(
            'status' => ( $keyword_count >= 3 && $keyword_count <= 10 ) ? 'good' : 'warning',
            'count' => $keyword_count,
            'message' => ( $keyword_count >= 3 && $keyword_count <= 10 ) ? 
                __( 'Keywords are well optimized', 'techpremium-web-stories-pro' ) :
                __( 'Add 3-10 relevant keywords', 'techpremium-web-stories-pro' )
        );
    }

    private function convert_to_amp_format( $story ) {
        // Convert story to AMP format
        $amp_html = '<!DOCTYPE html>
<html ⚡>
<head>
    <meta charset="utf-8">
    <title>' . esc_html( $story->title ) . '</title>
    <link rel="canonical" href="#">
    <meta name="viewport" content="width=device-width,minimum-scale=1,initial-scale=1">
    <script async src="https://cdn.ampproject.org/v0.js"></script>
    <script async custom-element="amp-story" src="https://cdn.ampproject.org/v0/amp-story-1.0.js"></script>
    <style amp-boilerplate>body{-webkit-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-moz-animation:-amp-start 8s steps(1,end) 0s 1 normal both;-ms-animation:-amp-start 8s steps(1,end) 0s 1 normal both;animation:-amp-start 8s steps(1,end) 0s 1 normal both}@-webkit-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-moz-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-ms-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@-o-keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}@keyframes -amp-start{from{visibility:hidden}to{visibility:visible}}</style><noscript><style amp-boilerplate>body{-webkit-animation:none;-moz-animation:none;-ms-animation:none;animation:none}</style></noscript>
</head>
<body>
    <amp-story standalone 
               title="' . esc_attr( $story->title ) . '"
               publisher="' . esc_attr( get_bloginfo( 'name' ) ) . '"
               publisher-logo-src="' . esc_url( get_site_icon_url() ) . '"
               poster-portrait-src="' . esc_url( get_the_post_thumbnail_url( $story->id, 'full' ) ) . '">

        <!-- Story pages will be generated here based on story_data -->

    </amp-story>
</body>
</html>';

        return $amp_html;
    }
}
