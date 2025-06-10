<?php
/**
 * Add new story admin page
 *
 * @since      1.0.0
 * @package    Techpremium_Ws_Pro
 * @subpackage Techpremium_Ws_Pro/admin/partials
 */

// Prevent direct access
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>

<div class="wrap techpremium-ws-pro-admin">
    <div class="techpremium-header">
        <h1><?php esc_html_e( 'Add New Web Story', 'techpremium-web-stories-pro' ); ?></h1>
        <p><?php esc_html_e( 'Upload an HTML file or create a new story from scratch using our advanced builder.', 'techpremium-web-stories-pro' ); ?></p>
    </div>

    <div class="techpremium-notifications"></div>

    <!-- Creation Methods -->
    <div class="techpremium-card">
        <div class="techpremium-card-header">
            <?php esc_html_e( 'Choose Creation Method', 'techpremium-web-stories-pro' ); ?>
        </div>
        <div class="techpremium-card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">

                <!-- HTML Upload -->
                <div class="creation-method active" data-method="upload">
                    <div style="text-align: center; padding: 20px; border: 2px solid #007cba; border-radius: 8px;">
                        <i class="dashicons dashicons-upload" style="font-size: 3em; color: #007cba; margin-bottom: 15px;"></i>
                        <h3><?php esc_html_e( 'Upload HTML File', 'techpremium-web-stories-pro' ); ?></h3>
                        <p><?php esc_html_e( 'Convert your existing HTML/AMP stories into Google Web Stories format.', 'techpremium-web-stories-pro' ); ?></p>
                        <button type="button" class="techpremium-btn techpremium-btn-primary select-method" data-method="upload">
                            <?php esc_html_e( 'Upload HTML', 'techpremium-web-stories-pro' ); ?>
                        </button>
                    </div>
                </div>

                <!-- Template -->
                <div class="creation-method" data-method="template">
                    <div style="text-align: center; padding: 20px; border: 2px solid #ddd; border-radius: 8px;">
                        <i class="dashicons dashicons-layout" style="font-size: 3em; color: #666; margin-bottom: 15px;"></i>
                        <h3><?php esc_html_e( 'Use Template', 'techpremium-web-stories-pro' ); ?></h3>
                        <p><?php esc_html_e( 'Start with one of our professionally designed templates.', 'techpremium-web-stories-pro' ); ?></p>
                        <button type="button" class="techpremium-btn techpremium-btn-secondary select-method" data-method="template">
                            <?php esc_html_e( 'Browse Templates', 'techpremium-web-stories-pro' ); ?>
                        </button>
                    </div>
                </div>

                <!-- Builder -->
                <div class="creation-method" data-method="builder">
                    <div style="text-align: center; padding: 20px; border: 2px solid #ddd; border-radius: 8px;">
                        <i class="dashicons dashicons-edit" style="font-size: 3em; color: #666; margin-bottom: 15px;"></i>
                        <h3><?php esc_html_e( 'Story Builder', 'techpremium-web-stories-pro' ); ?></h3>
                        <p><?php esc_html_e( 'Create a new story from scratch using our drag-and-drop builder.', 'techpremium-web-stories-pro' ); ?></p>
                        <button type="button" class="techpremium-btn techpremium-btn-secondary select-method" data-method="builder">
                            <?php esc_html_e( 'Start Building', 'techpremium-web-stories-pro' ); ?>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Upload Section -->
    <div id="upload-section" class="techpremium-card">
        <div class="techpremium-card-header">
            <?php esc_html_e( 'Upload HTML Story', 'techpremium-web-stories-pro' ); ?>
        </div>
        <div class="techpremium-card-body">
            <form id="upload-form" enctype="multipart/form-data">
                <div class="techpremium-form-group">
                    <label for="story-title" class="techpremium-form-label">
                        <?php esc_html_e( 'Story Title', 'techpremium-web-stories-pro' ); ?>
                    </label>
                    <input type="text" id="story-title" name="story_title" class="techpremium-form-control" placeholder="<?php esc_attr_e( 'Enter story title...', 'techpremium-web-stories-pro' ); ?>">
                </div>

                <div class="techpremium-upload-area">
                    <div class="techpremium-upload-icon">
                        <i class="dashicons dashicons-cloud-upload"></i>
                    </div>
                    <h3><?php esc_html_e( 'Drop your HTML file here', 'techpremium-web-stories-pro' ); ?></h3>
                    <p><?php esc_html_e( 'or click to browse', 'techpremium-web-stories-pro' ); ?></p>
                    <input type="file" id="html-file-input" name="html_file" accept=".html,.htm" style="display: none;">
                    <div class="upload-info">
                        <small><?php esc_html_e( 'Supported formats: HTML, HTM • Max file size: 10MB', 'techpremium-web-stories-pro' ); ?></small>
                    </div>
                </div>

                <div class="techpremium-form-group">
                    <h4><?php esc_html_e( 'Import Options', 'techpremium-web-stories-pro' ); ?></h4>
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 15px; margin-top: 15px;">
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="auto_optimize" checked>
                            <?php esc_html_e( 'Auto-optimize images', 'techpremium-web-stories-pro' ); ?>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="extract_metadata" checked>
                            <?php esc_html_e( 'Extract metadata', 'techpremium-web-stories-pro' ); ?>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="generate_seo" checked>
                            <?php esc_html_e( 'Generate SEO data', 'techpremium-web-stories-pro' ); ?>
                        </label>
                        <label style="display: flex; align-items: center; gap: 10px;">
                            <input type="checkbox" name="create_backup">
                            <?php esc_html_e( 'Create backup', 'techpremium-web-stories-pro' ); ?>
                        </label>
                    </div>
                </div>

                <div style="text-align: center; margin-top: 30px;">
                    <button type="submit" class="techpremium-btn techpremium-btn-primary" disabled>
                        <?php esc_html_e( 'Upload & Convert Story', 'techpremium-web-stories-pro' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Template Section -->
    <div id="template-section" class="techpremium-card" style="display: none;">
        <div class="techpremium-card-header">
            <?php esc_html_e( 'Choose Template', 'techpremium-web-stories-pro' ); ?>
        </div>
        <div class="techpremium-card-body">
            <div class="techpremium-templates-grid">
                <!-- Template cards will be loaded here -->
            </div>
        </div>
    </div>

    <!-- Builder Section -->
    <div id="builder-section" class="techpremium-card" style="display: none;">
        <div class="techpremium-card-header">
            <?php esc_html_e( 'Story Builder', 'techpremium-web-stories-pro' ); ?>
        </div>
        <div class="techpremium-card-body">
            <div class="story-builder">
                <div class="builder-toolbar">
                    <div class="toolbar-section">
                        <h4><?php esc_html_e( 'Pages', 'techpremium-web-stories-pro' ); ?></h4>
                        <button type="button" class="techpremium-btn techpremium-btn-primary add-page">
                            <i class="dashicons dashicons-plus"></i>
                            <?php esc_html_e( 'Add Page', 'techpremium-web-stories-pro' ); ?>
                        </button>
                    </div>
                    <div class="toolbar-section">
                        <h4><?php esc_html_e( 'Elements', 'techpremium-web-stories-pro' ); ?></h4>
                        <div class="element-buttons">
                            <button type="button" class="techpremium-btn techpremium-btn-secondary add-element" data-element-type="text">
                                <i class="dashicons dashicons-text"></i>
                                <?php esc_html_e( 'Text', 'techpremium-web-stories-pro' ); ?>
                            </button>
                            <button type="button" class="techpremium-btn techpremium-btn-secondary add-element" data-element-type="image">
                                <i class="dashicons dashicons-format-image"></i>
                                <?php esc_html_e( 'Image', 'techpremium-web-stories-pro' ); ?>
                            </button>
                            <button type="button" class="techpremium-btn techpremium-btn-secondary add-element" data-element-type="video">
                                <i class="dashicons dashicons-video-alt3"></i>
                                <?php esc_html_e( 'Video', 'techpremium-web-stories-pro' ); ?>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="story-canvas">
                    <div class="story-pages techpremium-sortable">
                        <div class="story-page">
                            <div class="page-header">
                                <span class="page-number">1</span>
                                <h4><?php esc_html_e( 'Cover Page', 'techpremium-web-stories-pro' ); ?></h4>
                                <button type="button" class="delete-page" title="<?php esc_attr_e( 'Delete Page', 'techpremium-web-stories-pro' ); ?>">
                                    <i class="dashicons dashicons-trash"></i>
                                </button>
                            </div>
                            <div class="page-content">
                                <div class="page-background">
                                    <label><?php esc_html_e( 'Background', 'techpremium-web-stories-pro' ); ?></label>
                                    <select class="background-type">
                                        <option value="color"><?php esc_html_e( 'Color', 'techpremium-web-stories-pro' ); ?></option>
                                        <option value="image"><?php esc_html_e( 'Image', 'techpremium-web-stories-pro' ); ?></option>
                                        <option value="video"><?php esc_html_e( 'Video', 'techpremium-web-stories-pro' ); ?></option>
                                    </select>
                                    <input type="color" class="background-color" value="#ffffff">
                                </div>
                                <div class="page-elements">
                                    <!-- Elements will be added here -->
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="builder-actions">
                    <button type="button" class="techpremium-btn techpremium-btn-secondary">
                        <?php esc_html_e( 'Preview', 'techpremium-web-stories-pro' ); ?>
                    </button>
                    <button type="button" class="techpremium-btn techpremium-btn-primary">
                        <?php esc_html_e( 'Save Story', 'techpremium-web-stories-pro' ); ?>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
jQuery(document).ready(function($) {
    // Method selection
    $('.select-method').on('click', function() {
        const method = $(this).data('method');

        // Update active method
        $('.creation-method').removeClass('active');
        $(`.creation-method[data-method="${method}"]`).addClass('active');

        // Show/hide sections
        $('.techpremium-card[id$="-section"]').hide();
        $(`#${method}-section`).show();

        // Update visual styles
        $('.creation-method > div').css('border-color', '#ddd');
        $('.creation-method > div i').css('color', '#666');
        $('.creation-method.active > div').css('border-color', '#007cba');
        $('.creation-method.active > div i').css('color', '#007cba');
    });

    // File input handling
    $('#html-file-input').on('change', function() {
        const file = this.files[0];
        if (file) {
            $('#upload-form button[type="submit"]').prop('disabled', false);
            if (!$('#story-title').val()) {
                $('#story-title').val(file.name.replace(/\.[^/.]+$/, ""));
            }
        }
    });

    // Initialize with upload method
    $('.select-method[data-method="upload"]').click();
});
</script>
