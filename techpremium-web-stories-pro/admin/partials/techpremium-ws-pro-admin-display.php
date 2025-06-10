<?php
/**
 * Provide a admin area view for the plugin
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/admin/partials
 */

// Prevent direct access
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Get stories from database
global $wpdb;
$stories_table = $wpdb->prefix . 'techpremium_stories';
$stories = $wpdb->get_results( "SELECT * FROM $stories_table ORDER BY created_at DESC LIMIT 20" );
?>

<div class="wrap techpremium-ws-pro-admin">
    <!-- Header -->
    <div class="techpremium-header">
        <h1><?php esc_html_e( 'TechPremium Web Stories Pro', 'techpremium-web-stories-pro' ); ?></h1>
        <p><?php esc_html_e( 'Create, manage, and optimize stunning web stories with advanced features and seamless Google Web Stories integration.', 'techpremium-web-stories-pro' ); ?></p>
    </div>

    <!-- Navigation -->
    <nav class="techpremium-nav-tabs">
        <a href="#stories" class="techpremium-nav-tab active" data-tab="stories">
            <i class="dashicons dashicons-format-gallery"></i>
            <?php esc_html_e( 'All Stories', 'techpremium-web-stories-pro' ); ?>
        </a>
        <a href="#analytics" class="techpremium-nav-tab" data-tab="analytics">
            <i class="dashicons dashicons-chart-bar"></i>
            <?php esc_html_e( 'Analytics', 'techpremium-web-stories-pro' ); ?>
        </a>
        <a href="#templates" class="techpremium-nav-tab" data-tab="templates">
            <i class="dashicons dashicons-layout"></i>
            <?php esc_html_e( 'Templates', 'techpremium-web-stories-pro' ); ?>
        </a>
        <a href="#seo" class="techpremium-nav-tab" data-tab="seo">
            <i class="dashicons dashicons-search"></i>
            <?php esc_html_e( 'SEO', 'techpremium-web-stories-pro' ); ?>
        </a>
    </nav>

    <!-- Notifications area -->
    <div class="techpremium-notifications"></div>

    <!-- Stories Tab -->
    <div id="stories" class="techpremium-tab-content">
        <div class="techpremium-card">
            <div class="techpremium-card-header">
                <div style="display: flex; justify-content: space-between; align-items: center;">
                    <span><?php esc_html_e( 'Your Web Stories', 'techpremium-web-stories-pro' ); ?></span>
                    <a href="<?php echo admin_url( 'admin.php?page=techpremium-ws-pro-add-new' ); ?>" class="techpremium-btn techpremium-btn-primary">
                        <i class="dashicons dashicons-plus"></i>
                        <?php esc_html_e( 'Add New Story', 'techpremium-web-stories-pro' ); ?>
                    </a>
                </div>
            </div>
            <div class="techpremium-card-body">
                <?php if ( empty( $stories ) ) : ?>
                    <div style="text-align: center; padding: 40px;">
                        <i class="dashicons dashicons-format-gallery" style="font-size: 4em; color: #ccc; margin-bottom: 20px;"></i>
                        <h3><?php esc_html_e( 'No stories yet', 'techpremium-web-stories-pro' ); ?></h3>
                        <p><?php esc_html_e( 'Upload your first HTML story or create one from a template.', 'techpremium-web-stories-pro' ); ?></p>
                        <a href="<?php echo admin_url( 'admin.php?page=techpremium-ws-pro-add-new' ); ?>" class="techpremium-btn techpremium-btn-primary">
                            <?php esc_html_e( 'Create Your First Story', 'techpremium-web-stories-pro' ); ?>
                        </a>
                    </div>
                <?php else : ?>
                    <div class="techpremium-stories-grid">
                        <?php foreach ( $stories as $story ) : ?>
                            <div class="techpremium-story-card story-card" data-story-id="<?php echo esc_attr( $story->id ); ?>">
                                <div class="techpremium-story-preview">
                                    <i class="dashicons dashicons-format-gallery"></i>
                                </div>
                                <div class="techpremium-story-info">
                                    <h3 class="techpremium-story-title"><?php echo esc_html( $story->title ); ?></h3>
                                    <div class="techpremium-story-meta">
                                        <span class="story-status"><?php echo esc_html( ucfirst( $story->status ) ); ?></span> • 
                                        <span class="story-date"><?php echo esc_html( mysql2date( 'M j, Y', $story->created_at ) ); ?></span>
                                    </div>
                                    <div class="techpremium-story-actions">
                                        <?php if ( $story->status === 'draft' ) : ?>
                                            <a href="#" class="techpremium-btn techpremium-btn-primary convert-story" data-story-id="<?php echo esc_attr( $story->id ); ?>">
                                                <?php esc_html_e( 'Convert', 'techpremium-web-stories-pro' ); ?>
                                            </a>
                                        <?php endif; ?>
                                        <a href="#" class="techpremium-btn techpremium-btn-secondary edit-story" data-story-id="<?php echo esc_attr( $story->id ); ?>">
                                            <?php esc_html_e( 'Edit', 'techpremium-web-stories-pro' ); ?>
                                        </a>
                                        <a href="#" class="techpremium-btn techpremium-btn-secondary duplicate-story" data-story-id="<?php echo esc_attr( $story->id ); ?>">
                                            <?php esc_html_e( 'Duplicate', 'techpremium-web-stories-pro' ); ?>
                                        </a>
                                        <a href="#" class="techpremium-btn techpremium-btn-danger delete-story" data-story-id="<?php echo esc_attr( $story->id ); ?>">
                                            <?php esc_html_e( 'Delete', 'techpremium-web-stories-pro' ); ?>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <div class="techpremium-card">
            <div class="techpremium-card-header">
                <?php esc_html_e( 'Quick Stats', 'techpremium-web-stories-pro' ); ?>
            </div>
            <div class="techpremium-card-body">
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px;">
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                        <h3 style="margin: 0; color: #007cba; font-size: 2em;"><?php echo count( $stories ); ?></h3>
                        <p style="margin: 5px 0 0 0; color: #666;"><?php esc_html_e( 'Total Stories', 'techpremium-web-stories-pro' ); ?></p>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                        <h3 style="margin: 0; color: #28a745; font-size: 2em;">
                            <?php echo count( array_filter( $stories, function( $story ) { return $story->status === 'published'; } ) ); ?>
                        </h3>
                        <p style="margin: 5px 0 0 0; color: #666;"><?php esc_html_e( 'Published', 'techpremium-web-stories-pro' ); ?></p>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                        <h3 style="margin: 0; color: #ffc107; font-size: 2em;">
                            <?php echo count( array_filter( $stories, function( $story ) { return $story->status === 'draft'; } ) ); ?>
                        </h3>
                        <p style="margin: 5px 0 0 0; color: #666;"><?php esc_html_e( 'Drafts', 'techpremium-web-stories-pro' ); ?></p>
                    </div>
                    <div style="text-align: center; padding: 20px; background: #f8f9fa; border-radius: 8px;">
                        <h3 style="margin: 0; color: #17a2b8; font-size: 2em;">4.8</h3>
                        <p style="margin: 5px 0 0 0; color: #666;"><?php esc_html_e( 'Avg SEO Score', 'techpremium-web-stories-pro' ); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Tab -->
    <div id="analytics" class="techpremium-tab-content" style="display: none;">
        <div class="techpremium-card">
            <div class="techpremium-card-header">
                <?php esc_html_e( 'Story Performance', 'techpremium-web-stories-pro' ); ?>
            </div>
            <div class="techpremium-card-body">
                <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 30px;">
                    <div>
                        <canvas id="views-chart" width="400" height="200"></canvas>
                    </div>
                    <div>
                        <canvas id="engagement-chart" width="200" height="200"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Templates Tab -->
    <div id="templates" class="techpremium-tab-content" style="display: none;">
        <?php include_once 'techpremium-ws-pro-templates-display.php'; ?>
    </div>

    <!-- SEO Tab -->
    <div id="seo" class="techpremium-tab-content" style="display: none;">
        <?php include_once 'techpremium-ws-pro-seo-display.php'; ?>
    </div>
</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
