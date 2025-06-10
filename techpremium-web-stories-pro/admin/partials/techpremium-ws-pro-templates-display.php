<?php
/**
 * Templates admin page
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/admin/partials
 */

// Prevent direct access
if ( ! defined( 'WPINC' ) ) {
    die;
}

// Get templates from database
global $wpdb;
$templates_table = $wpdb->prefix . 'techpremium_story_templates';
$templates = $wpdb->get_results( "SELECT * FROM $templates_table ORDER BY created_at DESC" );
?>

<div class="techpremium-card">
    <div class="techpremium-card-header">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <span><?php esc_html_e( 'Story Templates', 'techpremium-web-stories-pro' ); ?></span>
            <button type="button" class="techpremium-btn techpremium-btn-primary" id="add-template-btn">
                <i class="dashicons dashicons-plus"></i>
                <?php esc_html_e( 'Create Template', 'techpremium-web-stories-pro' ); ?>
            </button>
        </div>
    </div>
    <div class="techpremium-card-body">
        <!-- Template Filters -->
        <div class="template-filters" style="margin-bottom: 20px;">
            <button type="button" class="techpremium-btn techpremium-btn-secondary filter-btn active" data-filter="all">
                <?php esc_html_e( 'All', 'techpremium-web-stories-pro' ); ?>
            </button>
            <button type="button" class="techpremium-btn techpremium-btn-secondary filter-btn" data-filter="technology">
                <?php esc_html_e( 'Technology', 'techpremium-web-stories-pro' ); ?>
            </button>
            <button type="button" class="techpremium-btn techpremium-btn-secondary filter-btn" data-filter="ai">
                <?php esc_html_e( 'AI', 'techpremium-web-stories-pro' ); ?>
            </button>
            <button type="button" class="techpremium-btn techpremium-btn-secondary filter-btn" data-filter="business">
                <?php esc_html_e( 'Business', 'techpremium-web-stories-pro' ); ?>
            </button>
            <button type="button" class="techpremium-btn techpremium-btn-secondary filter-btn" data-filter="premium">
                <?php esc_html_e( 'Premium', 'techpremium-web-stories-pro' ); ?>
            </button>
        </div>

        <!-- Templates Grid -->
        <div class="techpremium-templates-grid">
            <?php foreach ( $templates as $template ) : ?>
                <div class="techpremium-template-card" data-category="<?php echo esc_attr( $template->category ); ?>" data-premium="<?php echo $template->is_premium ? '1' : '0'; ?>">
                    <div class="techpremium-template-preview">
                        <?php if ( $template->preview_image ) : ?>
                            <img src="<?php echo esc_url( $template->preview_image ); ?>" alt="<?php echo esc_attr( $template->name ); ?>">
                        <?php else : ?>
                            <i class="dashicons dashicons-format-gallery"></i>
                            <span><?php echo esc_html( $template->name ); ?></span>
                        <?php endif; ?>
                        <?php if ( $template->is_premium ) : ?>
                            <span class="premium-badge"><?php esc_html_e( 'Premium', 'techpremium-web-stories-pro' ); ?></span>
                        <?php endif; ?>
                    </div>
                    <div class="techpremium-template-info">
                        <h4 class="techpremium-template-name"><?php echo esc_html( $template->name ); ?></h4>
                        <p class="techpremium-template-desc"><?php echo esc_html( $template->description ); ?></p>
                        <div class="template-actions">
                            <button type="button" class="techpremium-btn techpremium-btn-primary use-template" data-template-id="<?php echo esc_attr( $template->id ); ?>">
                                <?php esc_html_e( 'Use Template', 'techpremium-web-stories-pro' ); ?>
                            </button>
                            <button type="button" class="techpremium-btn techpremium-btn-secondary preview-template" data-template-id="<?php echo esc_attr( $template->id ); ?>">
                                <?php esc_html_e( 'Preview', 'techpremium-web-stories-pro' ); ?>
                            </button>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <?php if ( empty( $templates ) ) : ?>
            <div style="text-align: center; padding: 40px; color: #666;">
                <i class="dashicons dashicons-layout" style="font-size: 4em; margin-bottom: 20px; display: block;"></i>
                <h3><?php esc_html_e( 'No templates yet', 'techpremium-web-stories-pro' ); ?></h3>
                <p><?php esc_html_e( 'Create your first template or install default templates.', 'techpremium-web-stories-pro' ); ?></p>
                <button type="button" class="techpremium-btn techpremium-btn-primary" id="install-default-templates">
                    <?php esc_html_e( 'Install Default Templates', 'techpremium-web-stories-pro' ); ?>
                </button>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.techpremium-template-card {
    position: relative;
    cursor: pointer;
}

.techpremium-template-preview {
    position: relative;
    overflow: hidden;
}

.premium-badge {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #ffc107;
    color: #000;
    padding: 4px 8px;
    border-radius: 4px;
    font-size: 0.8em;
    font-weight: bold;
}

.template-actions {
    display: flex;
    gap: 10px;
    margin-top: 10px;
}

.template-filters .filter-btn.active {
    background: #007cba;
    color: white;
}
</style>

<script>
jQuery(document).ready(function($) {
    // Template filtering
    $('.filter-btn').on('click', function() {
        const filter = $(this).data('filter');

        $('.filter-btn').removeClass('active');
        $(this).addClass('active');

        if (filter === 'all') {
            $('.techpremium-template-card').show();
        } else if (filter === 'premium') {
            $('.techpremium-template-card').hide();
            $('.techpremium-template-card[data-premium="1"]').show();
        } else {
            $('.techpremium-template-card').hide();
            $('.techpremium-template-card[data-category="' + filter + '"]').show();
        }
    });

    // Install default templates
    $('#install-default-templates').on('click', function() {
        $.ajax({
            url: techpremium_ws_pro_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'install_default_templates',
                nonce: techpremium_ws_pro_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    location.reload();
                }
            }
        });
    });
});
</script>
