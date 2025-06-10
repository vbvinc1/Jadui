<?php

/**
 * SEO Integration functionality
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/includes/seo
 */
class Techpremium_Ws_Pro_Seo {

    /**
     * Initialize SEO functionality
     */
    public function init() {
        add_action( 'wp_head', array( $this, 'add_schema_markup' ) );
        add_action( 'wp_head', array( $this, 'add_open_graph_tags' ) );
        add_action( 'wp_head', array( $this, 'add_twitter_cards' ) );

        // Yoast SEO integration
        if ( class_exists( 'WPSEO_Options' ) ) {
            add_filter( 'wpseo_schema_graph', array( $this, 'add_yoast_schema' ), 10, 2 );
        }

        // RankMath integration
        if ( class_exists( 'RankMath' ) ) {
            add_filter( 'rank_math/json_ld', array( $this, 'add_rankmath_schema' ), 10, 2 );
        }

        // Web Stories specific hooks
        add_action( 'web_stories_story_head', array( $this, 'add_story_meta' ) );
        add_filter( 'web_stories_amp_dev_mode', array( $this, 'enable_amp_dev_mode' ) );
    }

    /**
     * Add structured data schema markup
     */
    public function add_schema_markup() {
        if ( ! is_singular( 'web-story' ) ) {
            return;
        }

        global $post;

        $story_meta = get_post_meta( $post->ID, 'techpremium_story_seo', true );
        $story_data = get_post_meta( $post->ID, 'web_stories_story_data', true );

        $schema = $this->generate_story_schema( $post, $story_meta, $story_data );

        echo '<script type="application/ld+json">' . wp_json_encode( $schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>';
    }

    /**
     * Generate story schema markup
     */
    public function generate_story_schema( $post, $story_meta = array(), $story_data = array() ) {
        $schema = array(
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            '@id' => get_permalink( $post->ID ) . '#article',
            'isPartOf' => array(
                '@id' => get_permalink( $post->ID ) . '#webpage'
            ),
            'author' => array(
                '@type' => 'Person',
                'name' => get_the_author_meta( 'display_name', $post->post_author ),
                'url' => get_author_posts_url( $post->post_author )
            ),
            'headline' => get_the_title( $post->ID ),
            'datePublished' => get_the_date( 'c', $post->ID ),
            'dateModified' => get_the_modified_date( 'c', $post->ID ),
            'mainEntityOfPage' => array(
                '@type' => 'WebPage',
                '@id' => get_permalink( $post->ID ) . '#webpage'
            ),
            'publisher' => array(
                '@type' => 'Organization',
                'name' => get_bloginfo( 'name' ),
                'url' => home_url(),
                'logo' => array(
                    '@type' => 'ImageObject',
                    'url' => $this->get_site_logo()
                )
            )
        );

        // Add story-specific data
        if ( ! empty( $story_data ) ) {
            $pages = json_decode( $story_data, true );
            if ( isset( $pages['pages'] ) && is_array( $pages['pages'] ) ) {
                $schema['numberOfPages'] = count( $pages['pages'] );
            }
        }

        // Add featured image
        if ( has_post_thumbnail( $post->ID ) ) {
            $image_id = get_post_thumbnail_id( $post->ID );
            $image_data = wp_get_attachment_image_src( $image_id, 'full' );

            if ( $image_data ) {
                $schema['image'] = array(
                    '@type' => 'ImageObject',
                    'url' => $image_data[0],
                    'width' => $image_data[1],
                    'height' => $image_data[2]
                );
            }
        }

        // Add SEO meta if available
        if ( ! empty( $story_meta ) ) {
            if ( isset( $story_meta['description'] ) ) {
                $schema['description'] = $story_meta['description'];
            }

            if ( isset( $story_meta['keywords'] ) ) {
                $schema['keywords'] = explode( ',', $story_meta['keywords'] );
            }
        }

        return apply_filters( 'techpremium_ws_pro_story_schema', $schema, $post );
    }

    /**
     * Add Open Graph meta tags
     */
    public function add_open_graph_tags() {
        if ( ! is_singular( 'web-story' ) ) {
            return;
        }

        global $post;

        $story_meta = get_post_meta( $post->ID, 'techpremium_story_seo', true );

        echo '<meta property="og:type" content="article" />' . "
";
        echo '<meta property="og:title" content="' . esc_attr( get_the_title( $post->ID ) ) . '" />' . "
";
        echo '<meta property="og:url" content="' . esc_url( get_permalink( $post->ID ) ) . '" />' . "
";
        echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '" />' . "
";

        // Description
        $description = '';
        if ( ! empty( $story_meta['description'] ) ) {
            $description = $story_meta['description'];
        } else {
            $description = get_the_excerpt( $post->ID );
        }

        if ( $description ) {
            echo '<meta property="og:description" content="' . esc_attr( wp_trim_words( $description, 55 ) ) . '" />' . "
";
        }

        // Featured image
        if ( has_post_thumbnail( $post->ID ) ) {
            $image_id = get_post_thumbnail_id( $post->ID );
            $image_data = wp_get_attachment_image_src( $image_id, 'large' );

            if ( $image_data ) {
                echo '<meta property="og:image" content="' . esc_url( $image_data[0] ) . '" />' . "
";
                echo '<meta property="og:image:width" content="' . esc_attr( $image_data[1] ) . '" />' . "
";
                echo '<meta property="og:image:height" content="' . esc_attr( $image_data[2] ) . '" />' . "
";
            }
        }

        // Article specific tags
        echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c', $post->ID ) ) . '" />' . "
";
        echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c', $post->ID ) ) . '" />' . "
";
        echo '<meta property="article:author" content="' . esc_attr( get_the_author_meta( 'display_name', $post->post_author ) ) . '" />' . "
";
    }

    /**
     * Add Twitter Card meta tags
     */
    public function add_twitter_cards() {
        if ( ! is_singular( 'web-story' ) ) {
            return;
        }

        global $post;

        $story_meta = get_post_meta( $post->ID, 'techpremium_story_seo', true );

        echo '<meta name="twitter:card" content="summary_large_image" />' . "
";
        echo '<meta name="twitter:title" content="' . esc_attr( get_the_title( $post->ID ) ) . '" />' . "
";

        // Description
        $description = '';
        if ( ! empty( $story_meta['description'] ) ) {
            $description = $story_meta['description'];
        } else {
            $description = get_the_excerpt( $post->ID );
        }

        if ( $description ) {
            echo '<meta name="twitter:description" content="' . esc_attr( wp_trim_words( $description, 55 ) ) . '" />' . "
";
        }

        // Featured image
        if ( has_post_thumbnail( $post->ID ) ) {
            $image_id = get_post_thumbnail_id( $post->ID );
            $image_data = wp_get_attachment_image_src( $image_id, 'large' );

            if ( $image_data ) {
                echo '<meta name="twitter:image" content="' . esc_url( $image_data[0] ) . '" />' . "
";
            }
        }

        // Site Twitter handle
        $options = get_option( 'techpremium_ws_pro_options', array() );
        if ( isset( $options['social']['twitter_handle'] ) && ! empty( $options['social']['twitter_handle'] ) ) {
            echo '<meta name="twitter:site" content="@' . esc_attr( $options['social']['twitter_handle'] ) . '" />' . "
";
        }
    }

    /**
     * Add story-specific meta tags
     */
    public function add_story_meta() {
        global $post;

        // Google Discover optimization
        echo '<meta name="robots" content="max-image-preview:large" />' . "
";

        // Story format
        echo '<meta name="format-detection" content="telephone=no" />' . "
";

        // Viewport for mobile optimization
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no" />' . "
";

        // Theme color
        $options = get_option( 'techpremium_ws_pro_options', array() );
        $theme_color = isset( $options['branding']['primary_color'] ) ? $options['branding']['primary_color'] : '#007cba';
        echo '<meta name="theme-color" content="' . esc_attr( $theme_color ) . '" />' . "
";

        // Story-specific canonical
        echo '<link rel="canonical" href="' . esc_url( get_permalink( $post->ID ) ) . '" />' . "
";
    }

    /**
     * Add Yoast SEO schema integration
     */
    public function add_yoast_schema( $graph, $context ) {
        if ( ! is_singular( 'web-story' ) ) {
            return $graph;
        }

        global $post;

        $story_schema = $this->generate_story_schema( $post );

        // Add story schema to Yoast graph
        $graph[] = $story_schema;

        return $graph;
    }

    /**
     * Add RankMath schema integration
     */
    public function add_rankmath_schema( $jsonld, $post ) {
        if ( ! is_singular( 'web-story' ) || ! $post ) {
            return $jsonld;
        }

        $story_schema = $this->generate_story_schema( $post );

        // Add story schema to RankMath JSON-LD
        $jsonld[] = $story_schema;

        return $jsonld;
    }

    /**
     * Enable AMP dev mode for debugging
     */
    public function enable_amp_dev_mode( $enabled ) {
        $options = get_option( 'techpremium_ws_pro_options', array() );

        if ( isset( $options['advanced']['amp_dev_mode'] ) ) {
            return $options['advanced']['amp_dev_mode'];
        }

        return $enabled;
    }

    /**
     * Get site logo URL
     */
    private function get_site_logo() {
        $custom_logo_id = get_theme_mod( 'custom_logo' );

        if ( $custom_logo_id ) {
            $logo_data = wp_get_attachment_image_src( $custom_logo_id, 'full' );
            if ( $logo_data ) {
                return $logo_data[0];
            }
        }

        // Fallback to plugin options
        $options = get_option( 'techpremium_ws_pro_options', array() );
        if ( isset( $options['branding']['logo'] ) && ! empty( $options['branding']['logo'] ) ) {
            return $options['branding']['logo'];
        }

        // Final fallback
        return home_url( '/wp-content/uploads/techpremium-stories/default-logo.png' );
    }

    /**
     * Calculate SEO score for a story
     */
    public function calculate_seo_score( $post_id ) {
        $score = 0;
        $max_score = 100;

        $story_meta = get_post_meta( $post_id, 'techpremium_story_seo', true );
        $story_data = get_post_meta( $post_id, 'web_stories_story_data', true );

        // Title (20 points)
        $title = get_the_title( $post_id );
        if ( ! empty( $title ) && strlen( $title ) >= 10 && strlen( $title ) <= 60 ) {
            $score += 20;
        } elseif ( ! empty( $title ) ) {
            $score += 10;
        }

        // Description (20 points)
        if ( ! empty( $story_meta['description'] ) && strlen( $story_meta['description'] ) >= 120 && strlen( $story_meta['description'] ) <= 160 ) {
            $score += 20;
        } elseif ( ! empty( $story_meta['description'] ) ) {
            $score += 10;
        }

        // Featured image (15 points)
        if ( has_post_thumbnail( $post_id ) ) {
            $score += 15;
        }

        // Keywords (15 points)
        if ( ! empty( $story_meta['keywords'] ) ) {
            $keywords = explode( ',', $story_meta['keywords'] );
            if ( count( $keywords ) >= 3 && count( $keywords ) <= 10 ) {
                $score += 15;
            } else {
                $score += 8;
            }
        }

        // Story structure (15 points)
        if ( ! empty( $story_data ) ) {
            $pages = json_decode( $story_data, true );
            if ( isset( $pages['pages'] ) && is_array( $pages['pages'] ) ) {
                $page_count = count( $pages['pages'] );
                if ( $page_count >= 4 && $page_count <= 30 ) {
                    $score += 15;
                } elseif ( $page_count >= 2 ) {
                    $score += 10;
                }
            }
        }

        // Content quality (15 points)
        $content = get_post_field( 'post_content', $post_id );
        if ( ! empty( $content ) && strlen( strip_tags( $content ) ) >= 100 ) {
            $score += 15;
        } elseif ( ! empty( $content ) ) {
            $score += 8;
        }

        return min( $score, $max_score );
    }

    /**
     * Generate SEO suggestions
     */
    public function get_seo_suggestions( $post_id ) {
        $suggestions = array();

        $title = get_the_title( $post_id );
        $story_meta = get_post_meta( $post_id, 'techpremium_story_seo', true );

        // Title suggestions
        if ( empty( $title ) ) {
            $suggestions[] = array(
                'type' => 'error',
                'message' => __( 'Add a compelling title to your story.', 'techpremium-web-stories-pro' )
            );
        } elseif ( strlen( $title ) < 10 ) {
            $suggestions[] = array(
                'type' => 'warning',
                'message' => __( 'Your title is too short. Consider making it more descriptive.', 'techpremium-web-stories-pro' )
            );
        } elseif ( strlen( $title ) > 60 ) {
            $suggestions[] = array(
                'type' => 'warning',
                'message' => __( 'Your title might be too long for search results.', 'techpremium-web-stories-pro' )
            );
        }

        // Description suggestions
        if ( empty( $story_meta['description'] ) ) {
            $suggestions[] = array(
                'type' => 'error',
                'message' => __( 'Add a meta description to improve search visibility.', 'techpremium-web-stories-pro' )
            );
        } elseif ( strlen( $story_meta['description'] ) < 120 ) {
            $suggestions[] = array(
                'type' => 'warning',
                'message' => __( 'Your meta description could be longer and more descriptive.', 'techpremium-web-stories-pro' )
            );
        }

        // Featured image suggestion
        if ( ! has_post_thumbnail( $post_id ) ) {
            $suggestions[] = array(
                'type' => 'warning',
                'message' => __( 'Add a featured image to improve social sharing.', 'techpremium-web-stories-pro' )
            );
        }

        // Keywords suggestion
        if ( empty( $story_meta['keywords'] ) ) {
            $suggestions[] = array(
                'type' => 'info',
                'message' => __( 'Add relevant keywords to help with discoverability.', 'techpremium-web-stories-pro' )
            );
        }

        return $suggestions;
    }
}
