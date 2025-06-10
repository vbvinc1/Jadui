<?php

/**
 * Story management functionality
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/includes
 */
class Techpremium_Ws_Pro_Story_Manager {

    /**
     * Initialize story manager
     */
    public function init() {
        add_action( 'wp_ajax_bulk_action_stories', array( $this, 'handle_bulk_actions' ) );
        add_action( 'wp_ajax_export_story', array( $this, 'export_story' ) );
        add_action( 'wp_ajax_import_story', array( $this, 'import_story' ) );
        add_action( 'wp_ajax_optimize_story', array( $this, 'optimize_story' ) );
        add_action( 'wp_ajax_ab_test_story', array( $this, 'setup_ab_test' ) );
    }

    /**
     * Handle bulk actions on stories
     */
    public function handle_bulk_actions() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $action = sanitize_text_field( $_POST['action_type'] );
        $story_ids = array_map( 'intval', $_POST['story_ids'] );

        if ( empty( $story_ids ) ) {
            wp_send_json_error( __( 'No stories selected', 'techpremium-web-stories-pro' ) );
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        switch ( $action ) {
            case 'delete':
                $result = $this->bulk_delete_stories( $story_ids );
                break;

            case 'publish':
                $result = $this->bulk_update_status( $story_ids, 'published' );
                break;

            case 'draft':
                $result = $this->bulk_update_status( $story_ids, 'draft' );
                break;

            case 'optimize':
                $result = $this->bulk_optimize_stories( $story_ids );
                break;

            default:
                wp_send_json_error( __( 'Invalid action', 'techpremium-web-stories-pro' ) );
        }

        if ( $result ) {
            wp_send_json_success( array(
                'message' => sprintf( __( 'Action completed for %d stories', 'techpremium-web-stories-pro' ), count( $story_ids ) )
            ) );
        } else {
            wp_send_json_error( __( 'Action failed', 'techpremium-web-stories-pro' ) );
        }
    }

    /**
     * Bulk delete stories
     */
    private function bulk_delete_stories( $story_ids ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $placeholders = implode( ',', array_fill( 0, count( $story_ids ), '%d' ) );

        return $wpdb->query( $wpdb->prepare(
            "DELETE FROM $table_name WHERE id IN ($placeholders)",
            ...$story_ids
        ) );
    }

    /**
     * Bulk update story status
     */
    private function bulk_update_status( $story_ids, $status ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $placeholders = implode( ',', array_fill( 0, count( $story_ids ), '%d' ) );

        return $wpdb->query( $wpdb->prepare(
            "UPDATE $table_name SET status = %s WHERE id IN ($placeholders)",
            $status,
            ...$story_ids
        ) );
    }

    /**
     * Bulk optimize stories
     */
    private function bulk_optimize_stories( $story_ids ) {
        $success_count = 0;

        foreach ( $story_ids as $story_id ) {
            if ( $this->optimize_single_story( $story_id ) ) {
                $success_count++;
            }
        }

        return $success_count > 0;
    }

    /**
     * Export story
     */
    public function export_story() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $story_id = intval( $_POST['story_id'] );
        $format = sanitize_text_field( $_POST['format'] );

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $story = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $story_id
        ) );

        if ( ! $story ) {
            wp_send_json_error( __( 'Story not found', 'techpremium-web-stories-pro' ) );
        }

        $export_data = $this->prepare_export_data( $story, $format );

        wp_send_json_success( array(
            'data' => $export_data,
            'filename' => sanitize_file_name( $story->title ) . '.' . $format,
            'content_type' => $this->get_content_type( $format )
        ) );
    }

    /**
     * Import story
     */
    public function import_story() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        if ( empty( $_FILES['import_file'] ) ) {
            wp_send_json_error( __( 'No file uploaded', 'techpremium-web-stories-pro' ) );
        }

        $file = $_FILES['import_file'];
        $file_content = file_get_contents( $file['tmp_name'] );

        // Detect file format and parse
        $file_extension = pathinfo( $file['name'], PATHINFO_EXTENSION );

        switch ( $file_extension ) {
            case 'json':
                $story_data = json_decode( $file_content, true );
                break;

            case 'html':
            case 'htm':
                $story_data = $this->parse_html_import( $file_content );
                break;

            default:
                wp_send_json_error( __( 'Unsupported file format', 'techpremium-web-stories-pro' ) );
        }

        if ( ! $story_data ) {
            wp_send_json_error( __( 'Failed to parse import file', 'techpremium-web-stories-pro' ) );
        }

        // Create story from imported data
        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $insert_data = array(
            'title' => sanitize_text_field( $story_data['title'] ?? 'Imported Story' ),
            'content' => wp_kses_post( $story_data['content'] ?? '' ),
            'story_data' => wp_json_encode( $story_data['story_data'] ?? array() ),
            'status' => 'draft',
            'author_id' => get_current_user_id()
        );

        $result = $wpdb->insert( $table_name, $insert_data );

        if ( $result ) {
            wp_send_json_success( array(
                'story_id' => $wpdb->insert_id,
                'message' => __( 'Story imported successfully!', 'techpremium-web-stories-pro' )
            ) );
        } else {
            wp_send_json_error( __( 'Failed to import story', 'techpremium-web-stories-pro' ) );
        }
    }

    /**
     * Optimize single story
     */
    public function optimize_story() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $story_id = intval( $_POST['story_id'] );

        if ( $this->optimize_single_story( $story_id ) ) {
            wp_send_json_success( __( 'Story optimized successfully!', 'techpremium-web-stories-pro' ) );
        } else {
            wp_send_json_error( __( 'Failed to optimize story', 'techpremium-web-stories-pro' ) );
        }
    }

    /**
     * Setup A/B testing
     */
    public function setup_ab_test() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $story_id = intval( $_POST['story_id'] );
        $test_config = $_POST['test_config'];

        // Create A/B test configuration
        $ab_test_data = array(
            'original_story_id' => $story_id,
            'test_type' => sanitize_text_field( $test_config['type'] ),
            'variations' => array_map( 'sanitize_text_field', $test_config['variations'] ),
            'traffic_split' => intval( $test_config['traffic_split'] ),
            'duration' => intval( $test_config['duration'] ),
            'metrics' => array_map( 'sanitize_text_field', $test_config['metrics'] ),
            'status' => 'active',
            'created_at' => current_time( 'mysql' )
        );

        update_post_meta( $story_id, 'techpremium_ab_test', $ab_test_data );

        wp_send_json_success( __( 'A/B test setup successfully!', 'techpremium-web-stories-pro' ) );
    }

    /**
     * Helper methods
     */
    private function optimize_single_story( $story_id ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $story = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d",
            $story_id
        ) );

        if ( ! $story ) {
            return false;
        }

        $optimizations = array();

        // Image optimization
        if ( ! empty( $story->story_data ) ) {
            $story_data = json_decode( $story->story_data, true );
            $story_data = $this->optimize_images_in_story_data( $story_data );
            $optimizations['story_data'] = wp_json_encode( $story_data );
        }

        // SEO optimization
        $seo_data = $this->generate_seo_optimization( $story );
        if ( $seo_data ) {
            $optimizations['seo_data'] = wp_json_encode( $seo_data );
        }

        // Performance optimization
        $optimizations['updated_at'] = current_time( 'mysql' );

        if ( ! empty( $optimizations ) ) {
            return $wpdb->update( $table_name, $optimizations, array( 'id' => $story_id ) );
        }

        return true;
    }

    private function optimize_images_in_story_data( $story_data ) {
        if ( ! isset( $story_data['pages'] ) || ! is_array( $story_data['pages'] ) ) {
            return $story_data;
        }

        foreach ( $story_data['pages'] as &$page ) {
            if ( isset( $page['elements'] ) && is_array( $page['elements'] ) ) {
                foreach ( $page['elements'] as &$element ) {
                    if ( $element['type'] === 'image' && isset( $element['src'] ) ) {
                        // Optimize image URL (add WebP support, compression, etc.)
                        $element['src'] = $this->optimize_image_url( $element['src'] );
                    }
                }
            }
        }

        return $story_data;
    }

    private function optimize_image_url( $image_url ) {
        // Add image optimization parameters
        $optimized_url = add_query_arg( array(
            'quality' => 85,
            'format' => 'webp',
            'auto' => 'compress'
        ), $image_url );

        return $optimized_url;
    }

    private function generate_seo_optimization( $story ) {
        $seo_data = array();

        // Auto-generate meta description if missing
        if ( empty( $story->seo_data ) ) {
            $content = strip_tags( $story->content );
            $seo_data['description'] = wp_trim_words( $content, 25, '...' );
        }

        // Auto-generate keywords based on content
        $keywords = $this->extract_keywords_from_content( $story->content );
        if ( ! empty( $keywords ) ) {
            $seo_data['keywords'] = implode( ', ', array_slice( $keywords, 0, 10 ) );
        }

        return $seo_data;
    }

    private function extract_keywords_from_content( $content ) {
        // Simple keyword extraction
        $text = strtolower( strip_tags( $content ) );
        $words = str_word_count( $text, 1 );

        // Remove common stop words
        $stop_words = array( 'the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'could', 'should' );
        $words = array_diff( $words, $stop_words );

        // Count word frequency
        $word_count = array_count_values( $words );
        arsort( $word_count );

        // Return top keywords
        return array_keys( array_slice( $word_count, 0, 15, true ) );
    }

    private function prepare_export_data( $story, $format ) {
        switch ( $format ) {
            case 'json':
                return array(
                    'title' => $story->title,
                    'content' => $story->content,
                    'story_data' => json_decode( $story->story_data, true ),
                    'seo_data' => json_decode( $story->seo_data, true ),
                    'exported_at' => current_time( 'mysql' ),
                    'version' => TECHPREMIUM_WS_PRO_VERSION
                );

            case 'html':
                return $story->html_content ?: $this->generate_html_from_story_data( $story );

            case 'amp':
                return $this->generate_amp_from_story( $story );

            default:
                return $story->content;
        }
    }

    private function get_content_type( $format ) {
        $types = array(
            'json' => 'application/json',
            'html' => 'text/html',
            'amp' => 'text/html',
            'txt' => 'text/plain'
        );

        return $types[ $format ] ?? 'application/octet-stream';
    }

    private function parse_html_import( $html_content ) {
        // Use the same parsing logic as in the admin class
        $admin = new Techpremium_Ws_Pro_Admin( 'techpremium-ws-pro', '1.0.0' );
        return $admin->parse_html_to_story( $html_content );
    }

    private function generate_html_from_story_data( $story ) {
        // Generate HTML from story data
        $html = '<html><head><title>' . esc_html( $story->title ) . '</title></head><body>';

        if ( ! empty( $story->story_data ) ) {
            $story_data = json_decode( $story->story_data, true );

            if ( isset( $story_data['pages'] ) && is_array( $story_data['pages'] ) ) {
                foreach ( $story_data['pages'] as $page ) {
                    $html .= '<div class="story-page">';

                    if ( isset( $page['elements'] ) && is_array( $page['elements'] ) ) {
                        foreach ( $page['elements'] as $element ) {
                            if ( $element['type'] === 'text' ) {
                                $html .= '<p>' . esc_html( $element['content'] ) . '</p>';
                            } elseif ( $element['type'] === 'image' ) {
                                $html .= '<img src="' . esc_url( $element['src'] ) . '" alt="' . esc_attr( $element['alt'] ?? '' ) . '">';
                            }
                        }
                    }

                    $html .= '</div>';
                }
            }
        } else {
            $html .= wp_kses_post( $story->content );
        }

        $html .= '</body></html>';

        return $html;
    }

    private function generate_amp_from_story( $story ) {
        // Generate AMP version
        return $this->generate_html_from_story_data( $story ); // Simplified for now
    }
}
