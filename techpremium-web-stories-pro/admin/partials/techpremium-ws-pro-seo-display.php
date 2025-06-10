<?php
/**
 * SEO Settings admin page
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/admin/partials
 */

// Prevent direct access
if ( ! defined( 'WPINC' ) ) {
    die;
}

$options = get_option( 'techpremium_ws_pro_options', array() );
$seo_options = $options['seo_integration'] ?? array();
?>

<div class="techpremium-card">
    <div class="techpremium-card-header">
        <?php esc_html_e( 'SEO Integration Settings', 'techpremium-web-stories-pro' ); ?>
    </div>
    <div class="techpremium-card-body">
        <form id="seo-settings-form">
            <!-- Plugin Integrations -->
            <div class="techpremium-seo-section">
                <h3><?php esc_html_e( 'Plugin Integrations', 'techpremium-web-stories-pro' ); ?></h3>

                <div class="techpremium-form-group">
                    <label class="techpremium-form-label">
                        <div class="techpremium-toggle">
                            <input type="checkbox" name="seo_integration[yoast]" <?php checked( $seo_options['yoast'] ?? true ); ?>>
                            <span class="techpremium-toggle-slider"></span>
                        </div>
                        <?php esc_html_e( 'Yoast SEO Integration', 'techpremium-web-stories-pro' ); ?>
                        <?php if ( ! class_exists( 'WPSEO_Options' ) ) : ?>
                            <span style="color: #dc3545; font-size: 0.9em;">(<?php esc_html_e( 'Plugin not detected', 'techpremium-web-stories-pro' ); ?>)</span>
                        <?php endif; ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Automatically integrate with Yoast SEO for enhanced schema markup.', 'techpremium-web-stories-pro' ); ?></p>
                </div>

                <div class="techpremium-form-group">
                    <label class="techpremium-form-label">
                        <div class="techpremium-toggle">
                            <input type="checkbox" name="seo_integration[rankmath]" <?php checked( $seo_options['rankmath'] ?? true ); ?>>
                            <span class="techpremium-toggle-slider"></span>
                        </div>
                        <?php esc_html_e( 'RankMath SEO Integration', 'techpremium-web-stories-pro' ); ?>
                        <?php if ( ! class_exists( 'RankMath' ) ) : ?>
                            <span style="color: #dc3545; font-size: 0.9em;">(<?php esc_html_e( 'Plugin not detected', 'techpremium-web-stories-pro' ); ?>)</span>
                        <?php endif; ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Automatically integrate with RankMath for enhanced schema markup.', 'techpremium-web-stories-pro' ); ?></p>
                </div>
            </div>

            <!-- Schema Markup -->
            <div class="techpremium-seo-section">
                <h3><?php esc_html_e( 'Schema Markup', 'techpremium-web-stories-pro' ); ?></h3>

                <div class="techpremium-form-group">
                    <label class="techpremium-form-label">
                        <div class="techpremium-toggle">
                            <input type="checkbox" name="seo_integration[auto_schema]" <?php checked( $seo_options['auto_schema'] ?? true ); ?>>
                            <span class="techpremium-toggle-slider"></span>
                        </div>
                        <?php esc_html_e( 'Auto-generate Schema Markup', 'techpremium-web-stories-pro' ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Automatically generate structured data for better search engine understanding.', 'techpremium-web-stories-pro' ); ?></p>
                </div>
            </div>

            <!-- Social Media -->
            <div class="techpremium-seo-section">
                <h3><?php esc_html_e( 'Social Media Optimization', 'techpremium-web-stories-pro' ); ?></h3>

                <div class="techpremium-form-group">
                    <label class="techpremium-form-label">
                        <div class="techpremium-toggle">
                            <input type="checkbox" name="seo_integration[open_graph]" <?php checked( $seo_options['open_graph'] ?? true ); ?>>
                            <span class="techpremium-toggle-slider"></span>
                        </div>
                        <?php esc_html_e( 'Open Graph Tags', 'techpremium-web-stories-pro' ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Add Open Graph meta tags for better Facebook and LinkedIn sharing.', 'techpremium-web-stories-pro' ); ?></p>
                </div>

                <div class="techpremium-form-group">
                    <label class="techpremium-form-label">
                        <div class="techpremium-toggle">
                            <input type="checkbox" name="seo_integration[twitter_cards]" <?php checked( $seo_options['twitter_cards'] ?? true ); ?>>
                            <span class="techpremium-toggle-slider"></span>
                        </div>
                        <?php esc_html_e( 'Twitter Cards', 'techpremium-web-stories-pro' ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Add Twitter Card meta tags for enhanced Twitter sharing.', 'techpremium-web-stories-pro' ); ?></p>
                </div>
            </div>

            <!-- Google Discover -->
            <div class="techpremium-seo-section">
                <h3><?php esc_html_e( 'Google Discover Optimization', 'techpremium-web-stories-pro' ); ?></h3>

                <div class="techpremium-form-group">
                    <label class="techpremium-form-label">
                        <div class="techpremium-toggle">
                            <input type="checkbox" name="seo_integration[google_discover]" <?php checked( $seo_options['google_discover'] ?? true ); ?>>
                            <span class="techpremium-toggle-slider"></span>
                        </div>
                        <?php esc_html_e( 'Google Discover Optimization', 'techpremium-web-stories-pro' ); ?>
                    </label>
                    <p class="description"><?php esc_html_e( 'Optimize stories for Google Discover feed inclusion.', 'techpremium-web-stories-pro' ); ?></p>
                </div>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="techpremium-btn techpremium-btn-primary save-settings">
                    <?php esc_html_e( 'Save SEO Settings', 'techpremium-web-stories-pro' ); ?>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- SEO Tools -->
<div class="techpremium-card">
    <div class="techpremium-card-header">
        <?php esc_html_e( 'SEO Tools', 'techpremium-web-stories-pro' ); ?>
    </div>
    <div class="techpremium-card-body">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 20px;">
            <div class="seo-tool">
                <h4><?php esc_html_e( 'Schema Generator', 'techpremium-web-stories-pro' ); ?></h4>
                <p><?php esc_html_e( 'Generate custom schema markup for your stories.', 'techpremium-web-stories-pro' ); ?></p>
                <button type="button" class="techpremium-btn techpremium-btn-secondary" id="schema-generator">
                    <?php esc_html_e( 'Open Generator', 'techpremium-web-stories-pro' ); ?>
                </button>
            </div>

            <div class="seo-tool">
                <h4><?php esc_html_e( 'SEO Analyzer', 'techpremium-web-stories-pro' ); ?></h4>
                <p><?php esc_html_e( 'Analyze and optimize your stories for better SEO.', 'techpremium-web-stories-pro' ); ?></p>
                <button type="button" class="techpremium-btn techpremium-btn-secondary" id="seo-analyzer">
                    <?php esc_html_e( 'Run Analysis', 'techpremium-web-stories-pro' ); ?>
                </button>
            </div>

            <div class="seo-tool">
                <h4><?php esc_html_e( 'Keyword Research', 'techpremium-web-stories-pro' ); ?></h4>
                <p><?php esc_html_e( 'Find relevant keywords for your stories.', 'techpremium-web-stories-pro' ); ?></p>
                <button type="button" class="techpremium-btn techpremium-btn-secondary" id="keyword-research">
                    <?php esc_html_e( 'Research Keywords', 'techpremium-web-stories-pro' ); ?>
                </button>
            </div>
        </div>
    </div>
</div>
