<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/public
 */
class Techpremium_Ws_Pro_Public {

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
     * Register the stylesheets for the public-facing side of the site.
     */
    public function enqueue_styles() {
        wp_enqueue_style( 
            $this->plugin_name, 
            plugin_dir_url( __FILE__ ) . 'css/techpremium-ws-pro-public.css', 
            array(), 
            $this->version, 
            'all' 
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     */
    public function enqueue_scripts() {
        wp_enqueue_script( 
            $this->plugin_name, 
            plugin_dir_url( __FILE__ ) . 'js/techpremium-ws-pro-public.js', 
            array( 'jquery' ), 
            $this->version, 
            false 
        );

        // Localize script for analytics tracking
        wp_localize_script( 
            $this->plugin_name, 
            'techpremium_ws_pro_public', 
            array(
                'ajax_url' => admin_url( 'admin-ajax.php' ),
                'nonce' => wp_create_nonce( 'techpremium_ws_pro_public_nonce' ),
                'analytics_enabled' => $this->is_analytics_enabled()
            )
        );
    }

    /**
     * Register shortcodes
     */
    public function register_shortcodes() {
        add_shortcode( 'techpremium_story', array( $this, 'render_story_shortcode' ) );
        add_shortcode( 'techpremium_story_grid', array( $this, 'render_story_grid_shortcode' ) );
        add_shortcode( 'techpremium_story_carousel', array( $this, 'render_story_carousel_shortcode' ) );
    }

    /**
     * Render single story shortcode
     */
    public function render_story_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'id' => '',
            'width' => '360',
            'height' => '640',
            'autoplay' => 'false',
            'controls' => 'true',
            'poster' => ''
        ), $atts );

        if ( empty( $atts['id'] ) ) {
            return '<p>' . __( 'Story ID is required.', 'techpremium-web-stories-pro' ) . '</p>';
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $story = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE id = %d AND status = 'published'",
            $atts['id']
        ) );

        if ( ! $story ) {
            return '<p>' . __( 'Story not found.', 'techpremium-web-stories-pro' ) . '</p>';
        }

        return $this->render_story_embed( $story, $atts );
    }

    /**
     * Render story grid shortcode
     */
    public function render_story_grid_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'columns' => '3',
            'limit' => '6',
            'category' => '',
            'orderby' => 'created_at',
            'order' => 'DESC'
        ), $atts );

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $where_clause = "WHERE status = 'published'";
        $where_values = array();

        if ( ! empty( $atts['category'] ) ) {
            $where_clause .= " AND category = %s";
            $where_values[] = $atts['category'];
        }

        $order_clause = sprintf( 
            "ORDER BY %s %s LIMIT %d",
            sanitize_sql_orderby( $atts['orderby'] ),
            $atts['order'] === 'ASC' ? 'ASC' : 'DESC',
            intval( $atts['limit'] )
        );

        if ( ! empty( $where_values ) ) {
            $stories = $wpdb->get_results( $wpdb->prepare(
                "SELECT * FROM $table_name $where_clause $order_clause",
                ...$where_values
            ) );
        } else {
            $stories = $wpdb->get_results( "SELECT * FROM $table_name $where_clause $order_clause" );
        }

        return $this->render_story_grid( $stories, $atts );
    }

    /**
     * Render story carousel shortcode
     */
    public function render_story_carousel_shortcode( $atts ) {
        $atts = shortcode_atts( array(
            'limit' => '5',
            'autoplay' => 'true',
            'arrows' => 'true',
            'dots' => 'true'
        ), $atts );

        global $wpdb;
        $table_name = $wpdb->prefix . 'techpremium_stories';

        $stories = $wpdb->get_results( $wpdb->prepare(
            "SELECT * FROM $table_name WHERE status = 'published' ORDER BY created_at DESC LIMIT %d",
            intval( $atts['limit'] )
        ) );

        return $this->render_story_carousel( $stories, $atts );
    }

    /**
     * Render story embed
     */
    private function render_story_embed( $story, $atts ) {
        $story_url = $this->get_story_url( $story );

        ob_start();
        ?>
        <div class="techpremium-story-embed" data-story-id="<?php echo esc_attr( $story->id ); ?>">
            <iframe 
                src="<?php echo esc_url( $story_url ); ?>"
                width="<?php echo esc_attr( $atts['width'] ); ?>"
                height="<?php echo esc_attr( $atts['height'] ); ?>"
                frameborder="0"
                allowfullscreen
                <?php if ( $atts['poster'] ) : ?>
                    poster="<?php echo esc_url( $atts['poster'] ); ?>"
                <?php endif; ?>
                loading="lazy">
            </iframe>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render story grid
     */
    private function render_story_grid( $stories, $atts ) {
        if ( empty( $stories ) ) {
            return '<p>' . __( 'No stories found.', 'techpremium-web-stories-pro' ) . '</p>';
        }

        ob_start();
        ?>
        <div class="techpremium-story-grid" data-columns="<?php echo esc_attr( $atts['columns'] ); ?>">
            <?php foreach ( $stories as $story ) : ?>
                <div class="techpremium-story-item">
                    <div class="story-thumbnail">
                        <a href="<?php echo esc_url( $this->get_story_url( $story ) ); ?>" target="_blank">
                            <?php if ( $poster = $this->get_story_poster( $story ) ) : ?>
                                <img src="<?php echo esc_url( $poster ); ?>" alt="<?php echo esc_attr( $story->title ); ?>" loading="lazy">
                            <?php else : ?>
                                <div class="story-placeholder">
                                    <i class="techpremium-icon-story"></i>
                                </div>
                            <?php endif; ?>
                        </a>
                    </div>
                    <div class="story-info">
                        <h3 class="story-title">
                            <a href="<?php echo esc_url( $this->get_story_url( $story ) ); ?>" target="_blank">
                                <?php echo esc_html( $story->title ); ?>
                            </a>
                        </h3>
                        <div class="story-meta">
                            <span class="story-date"><?php echo esc_html( mysql2date( 'M j, Y', $story->created_at ) ); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Render story carousel
     */
    private function render_story_carousel( $stories, $atts ) {
        if ( empty( $stories ) ) {
            return '<p>' . __( 'No stories found.', 'techpremium-web-stories-pro' ) . '</p>';
        }

        wp_enqueue_script( 'techpremium-carousel', plugin_dir_url( __FILE__ ) . 'js/carousel.js', array( 'jquery' ), $this->version, true );

        ob_start();
        ?>
        <div class="techpremium-story-carousel" 
             data-autoplay="<?php echo esc_attr( $atts['autoplay'] ); ?>"
             data-arrows="<?php echo esc_attr( $atts['arrows'] ); ?>"
             data-dots="<?php echo esc_attr( $atts['dots'] ); ?>">

            <div class="carousel-container">
                <div class="carousel-track">
                    <?php foreach ( $stories as $story ) : ?>
                        <div class="carousel-slide">
                            <div class="story-card">
                                <div class="story-image">
                                    <a href="<?php echo esc_url( $this->get_story_url( $story ) ); ?>" target="_blank">
                                        <?php if ( $poster = $this->get_story_poster( $story ) ) : ?>
                                            <img src="<?php echo esc_url( $poster ); ?>" alt="<?php echo esc_attr( $story->title ); ?>" loading="lazy">
                                        <?php else : ?>
                                            <div class="story-placeholder">
                                                <i class="techpremium-icon-story"></i>
                                            </div>
                                        <?php endif; ?>
                                    </a>
                                </div>
                                <div class="story-content">
                                    <h4><?php echo esc_html( $story->title ); ?></h4>
                                    <p><?php echo esc_html( wp_trim_words( strip_tags( $story->content ), 15 ) ); ?></p>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <?php if ( $atts['arrows'] === 'true' ) : ?>
                <button class="carousel-arrow carousel-prev" aria-label="<?php esc_attr_e( 'Previous', 'techpremium-web-stories-pro' ); ?>">
                    <i class="techpremium-icon-arrow-left"></i>
                </button>
                <button class="carousel-arrow carousel-next" aria-label="<?php esc_attr_e( 'Next', 'techpremium-web-stories-pro' ); ?>">
                    <i class="techpremium-icon-arrow-right"></i>
                </button>
            <?php endif; ?>

            <?php if ( $atts['dots'] === 'true' ) : ?>
                <div class="carousel-dots">
                    <?php for ( $i = 0; $i < count( $stories ); $i++ ) : ?>
                        <button class="carousel-dot <?php echo $i === 0 ? 'active' : ''; ?>" data-slide="<?php echo $i; ?>"></button>
                    <?php endfor; ?>
                </div>
            <?php endif; ?>
        </div>
        <?php
        return ob_get_clean();
    }

    /**
     * Get story URL
     */
    private function get_story_url( $story ) {
        // Check if there's an associated Web Stories post
        $web_story_post = get_posts( array(
            'post_type' => 'web-story',
            'meta_key' => 'techpremium_story_id',
            'meta_value' => $story->id,
            'posts_per_page' => 1
        ) );

        if ( ! empty( $web_story_post ) ) {
            return get_permalink( $web_story_post[0]->ID );
        }

        // Fallback to preview URL
        return admin_url( 'admin.php?page=techpremium-ws-pro-preview&story_id=' . $story->id );
    }

    /**
     * Get story poster image
     */
    private function get_story_poster( $story ) {
        // Try to get poster from story data
        if ( ! empty( $story->story_data ) ) {
            $story_data = json_decode( $story->story_data, true );

            if ( isset( $story_data['poster'] ) ) {
                return $story_data['poster'];
            }

            // Try to extract first image from first page
            if ( isset( $story_data['pages'][0]['elements'] ) ) {
                foreach ( $story_data['pages'][0]['elements'] as $element ) {
                    if ( $element['type'] === 'image' && isset( $element['src'] ) ) {
                        return $element['src'];
                    }
                }
            }
        }

        return '';
    }

    /**
     * Check if analytics is enabled
     */
    private function is_analytics_enabled() {
        $options = get_option( 'techpremium_ws_pro_options', array() );
        return isset( $options['analytics']['enable_tracking'] ) ? $options['analytics']['enable_tracking'] : false;
    }

    /**
     * Track story view (AJAX handler)
     */
    public function track_story_view() {
        // Verify nonce
        if ( ! wp_verify_nonce( $_POST['nonce'], 'techpremium_ws_pro_public_nonce' ) ) {
            wp_die( __( 'Security check failed', 'techpremium-web-stories-pro' ) );
        }

        $story_id = intval( $_POST['story_id'] );

        // Update view count
        global $wpdb;
        $analytics_table = $wpdb->prefix . 'techpremium_story_analytics';

        // Check if analytics table exists, create if not
        $this->maybe_create_analytics_table();

        // Insert or update view record
        $today = current_time( 'Y-m-d' );

        $existing = $wpdb->get_row( $wpdb->prepare(
            "SELECT * FROM $analytics_table WHERE story_id = %d AND date = %s",
            $story_id,
            $today
        ) );

        if ( $existing ) {
            $wpdb->update(
                $analytics_table,
                array( 'views' => $existing->views + 1 ),
                array( 'id' => $existing->id )
            );
        } else {
            $wpdb->insert(
                $analytics_table,
                array(
                    'story_id' => $story_id,
                    'date' => $today,
                    'views' => 1,
                    'engagements' => 0
                )
            );
        }

        wp_send_json_success( array( 'tracked' => true ) );
    }

    /**
     * Maybe create analytics table
     */
    private function maybe_create_analytics_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'techpremium_story_analytics';

        if ( $wpdb->get_var( "SHOW TABLES LIKE '$table_name'" ) != $table_name ) {
            $charset_collate = $wpdb->get_charset_collate();

            $sql = "CREATE TABLE $table_name (
                id mediumint(9) NOT NULL AUTO_INCREMENT,
                story_id mediumint(9) NOT NULL,
                date date NOT NULL,
                views int(11) DEFAULT 0,
                engagements int(11) DEFAULT 0,
                completion_rate decimal(5,2) DEFAULT 0.00,
                avg_time_spent int(11) DEFAULT 0,
                PRIMARY KEY (id),
                KEY story_id (story_id),
                KEY date (date),
                UNIQUE KEY story_date (story_id, date)
            ) $charset_collate;";

            require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
            dbDelta( $sql );
        }
    }
}
