/*!
 * TechPremium Web Stories Pro Admin JavaScript
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Main admin object
     */
    const TechPremiumWSPro = {

        init: function() {
            this.bindEvents();
            this.initUploader();
            this.initColorPickers();
            this.initSortables();
            this.initTooltips();
            this.initAnalytics();
        },

        /**
         * Bind all events
         */
        bindEvents: function() {
            // Navigation tabs
            $(document).on('click', '.techpremium-nav-tab', this.handleTabClick);

            // Story actions
            $(document).on('click', '.convert-story', this.convertStory);
            $(document).on('click', '.delete-story', this.deleteStory);
            $(document).on('click', '.duplicate-story', this.duplicateStory);
            $(document).on('click', '.edit-story', this.editStory);

            // Template actions
            $(document).on('click', '.use-template', this.useTemplate);
            $(document).on('click', '.preview-template', this.previewTemplate);

            // Settings
            $(document).on('change', '.techpremium-toggle input', this.handleToggleChange);
            $(document).on('click', '.save-settings', this.saveSettings);

            // Story builder
            $(document).on('click', '.add-page', this.addPage);
            $(document).on('click', '.delete-page', this.deletePage);
            $(document).on('click', '.add-element', this.addElement);

            // SEO optimization
            $(document).on('click', '.optimize-seo', this.optimizeSEO);
            $(document).on('click', '.generate-schema', this.generateSchema);
        },

        /**
         * Handle tab navigation
         */
        handleTabClick: function(e) {
            e.preventDefault();

            const $tab = $(this);
            const targetTab = $tab.data('tab');

            // Update active states
            $('.techpremium-nav-tab').removeClass('active');
            $tab.addClass('active');

            // Show/hide content
            $('.techpremium-tab-content').hide();
            $('#' + targetTab).show().addClass('techpremium-fade-in');

            // Update URL hash
            window.location.hash = targetTab;
        },

        /**
         * Initialize file uploader
         */
        initUploader: function() {
            const $uploadArea = $('.techpremium-upload-area');

            if ($uploadArea.length === 0) return;

            // Drag and drop
            $uploadArea.on('dragover', function(e) {
                e.preventDefault();
                $(this).addClass('dragover');
            });

            $uploadArea.on('dragleave', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');
            });

            $uploadArea.on('drop', function(e) {
                e.preventDefault();
                $(this).removeClass('dragover');

                const files = e.originalEvent.dataTransfer.files;
                if (files.length > 0) {
                    TechPremiumWSPro.handleFileUpload(files[0]);
                }
            });

            // Click to upload
            $uploadArea.on('click', function() {
                $('#html-file-input').click();
            });

            $('#html-file-input').on('change', function() {
                const file = this.files[0];
                if (file) {
                    TechPremiumWSPro.handleFileUpload(file);
                }
            });
        },

        /**
         * Handle file upload
         */
        handleFileUpload: function(file) {
            // Validate file type
            if (!file.name.match(/\.(html|htm)$/)) {
                this.showNotification('Please select an HTML file.', 'error');
                return;
            }

            // Validate file size (max 10MB)
            if (file.size > 10 * 1024 * 1024) {
                this.showNotification('File size must be less than 10MB.', 'error');
                return;
            }

            const formData = new FormData();
            formData.append('action', 'upload_html_story');
            formData.append('nonce', techpremium_ws_pro_ajax.nonce);
            formData.append('html_file', file);
            formData.append('story_title', $('#story-title').val() || file.name.replace(/\.[^/.]+$/, ""));

            // Show progress
            this.showProgress('Uploading story...');

            $.ajax({
                url: techpremium_ws_pro_ajax.ajax_url,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    TechPremiumWSPro.hideProgress();

                    if (response.success) {
                        TechPremiumWSPro.showNotification(response.message, 'success');
                        TechPremiumWSPro.addStoryToList(response.data);
                    } else {
                        TechPremiumWSPro.showNotification(response.data || 'Upload failed', 'error');
                    }
                },
                error: function() {
                    TechPremiumWSPro.hideProgress();
                    TechPremiumWSPro.showNotification('Network error occurred', 'error');
                }
            });
        },

        /**
         * Convert story to Web Stories format
         */
        convertStory: function(e) {
            e.preventDefault();

            const storyId = $(this).data('story-id');

            if (!confirm(techpremium_ws_pro_ajax.strings.convert_confirm)) {
                return;
            }

            TechPremiumWSPro.showProgress('Converting story...');

            $.ajax({
                url: techpremium_ws_pro_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'convert_story',
                    nonce: techpremium_ws_pro_ajax.nonce,
                    story_id: storyId
                },
                success: function(response) {
                    TechPremiumWSPro.hideProgress();

                    if (response.success) {
                        TechPremiumWSPro.showNotification('Story converted successfully!', 'success');

                        // Redirect to edit page
                        if (response.data.edit_url) {
                            window.open(response.data.edit_url, '_blank');
                        }

                        // Update story status in list
                        $(`.story-card[data-story-id="${storyId}"]`).find('.story-status').text('Published');
                    } else {
                        TechPremiumWSPro.showNotification(response.data || 'Conversion failed', 'error');
                    }
                },
                error: function() {
                    TechPremiumWSPro.hideProgress();
                    TechPremiumWSPro.showNotification('Network error occurred', 'error');
                }
            });
        },

        /**
         * Delete story
         */
        deleteStory: function(e) {
            e.preventDefault();

            const storyId = $(this).data('story-id');

            if (!confirm(techpremium_ws_pro_ajax.strings.delete_confirm)) {
                return;
            }

            $.ajax({
                url: techpremium_ws_pro_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'delete_story',
                    nonce: techpremium_ws_pro_ajax.nonce,
                    story_id: storyId
                },
                success: function(response) {
                    if (response.success) {
                        $(`.story-card[data-story-id="${storyId}"]`).fadeOut(300, function() {
                            $(this).remove();
                        });
                        TechPremiumWSPro.showNotification('Story deleted successfully', 'success');
                    } else {
                        TechPremiumWSPro.showNotification(response.data || 'Delete failed', 'error');
                    }
                },
                error: function() {
                    TechPremiumWSPro.showNotification('Network error occurred', 'error');
                }
            });
        },

        /**
         * Duplicate story
         */
        duplicateStory: function(e) {
            e.preventDefault();

            const storyId = $(this).data('story-id');

            $.ajax({
                url: techpremium_ws_pro_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'duplicate_story',
                    nonce: techpremium_ws_pro_ajax.nonce,
                    story_id: storyId
                },
                success: function(response) {
                    if (response.success) {
                        TechPremiumWSPro.showNotification(response.data.message, 'success');
                        location.reload(); // Refresh to show duplicated story
                    } else {
                        TechPremiumWSPro.showNotification(response.data || 'Duplicate failed', 'error');
                    }
                },
                error: function() {
                    TechPremiumWSPro.showNotification('Network error occurred', 'error');
                }
            });
        },

        /**
         * Edit story
         */
        editStory: function(e) {
            e.preventDefault();

            const storyId = $(this).data('story-id');
            const editUrl = admin_url + 'admin.php?page=techpremium-ws-pro-edit&story_id=' + storyId;

            window.location.href = editUrl;
        },

        /**
         * Use template
         */
        useTemplate: function(e) {
            e.preventDefault();

            const templateId = $(this).data('template-id');
            const createUrl = admin_url + 'admin.php?page=techpremium-ws-pro-add-new&template_id=' + templateId;

            window.location.href = createUrl;
        },

        /**
         * Preview template
         */
        previewTemplate: function(e) {
            e.preventDefault();

            const templateId = $(this).data('template-id');

            // Create modal for template preview
            const modal = $(`
                <div class="techpremium-modal">
                    <div class="techpremium-modal-content">
                        <div class="techpremium-modal-header">
                            <h3>Template Preview</h3>
                            <span class="techpremium-modal-close">&times;</span>
                        </div>
                        <div class="techpremium-modal-body">
                            <div class="techpremium-spinner"></div>
                        </div>
                    </div>
                </div>
            `);

            $('body').append(modal);
            modal.fadeIn(300);

            // Load template preview
            setTimeout(function() {
                modal.find('.techpremium-modal-body').html(`
                    <div class="template-preview-frame">
                        <p>Template preview would be loaded here.</p>
                        <p>Template ID: ${templateId}</p>
                    </div>
                `);
            }, 1000);

            // Close modal
            modal.on('click', '.techpremium-modal-close, .techpremium-modal', function(e) {
                if (e.target === this) {
                    modal.fadeOut(300, function() {
                        modal.remove();
                    });
                }
            });
        },

        /**
         * Handle toggle changes
         */
        handleToggleChange: function() {
            const $toggle = $(this);
            const setting = $toggle.data('setting');
            const value = $toggle.is(':checked');

            // Auto-save setting
            TechPremiumWSPro.saveSetting(setting, value);
        },

        /**
         * Save individual setting
         */
        saveSetting: function(setting, value) {
            $.ajax({
                url: techpremium_ws_pro_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'save_setting',
                    nonce: techpremium_ws_pro_ajax.nonce,
                    setting: setting,
                    value: value
                },
                success: function(response) {
                    if (!response.success) {
                        TechPremiumWSPro.showNotification('Failed to save setting', 'error');
                    }
                }
            });
        },

        /**
         * Save all settings
         */
        saveSettings: function(e) {
            e.preventDefault();

            const $form = $(this).closest('form');
            const formData = $form.serialize();

            TechPremiumWSPro.showProgress('Saving settings...');

            $.ajax({
                url: techpremium_ws_pro_ajax.ajax_url,
                type: 'POST',
                data: formData + '&action=save_all_settings&nonce=' + techpremium_ws_pro_ajax.nonce,
                success: function(response) {
                    TechPremiumWSPro.hideProgress();

                    if (response.success) {
                        TechPremiumWSPro.showNotification('Settings saved successfully!', 'success');
                    } else {
                        TechPremiumWSPro.showNotification(response.data || 'Save failed', 'error');
                    }
                },
                error: function() {
                    TechPremiumWSPro.hideProgress();
                    TechPremiumWSPro.showNotification('Network error occurred', 'error');
                }
            });
        },

        /**
         * Initialize color pickers
         */
        initColorPickers: function() {
            $('.color-picker').wpColorPicker({
                change: function(event, ui) {
                    const setting = $(this).data('setting');
                    if (setting) {
                        TechPremiumWSPro.saveSetting(setting, ui.color.toString());
                    }
                }
            });
        },

        /**
         * Initialize sortables
         */
        initSortables: function() {
            $('.techpremium-sortable').sortable({
                handle: '.sort-handle',
                axis: 'y',
                update: function(event, ui) {
                    const order = $(this).sortable('toArray');
                    TechPremiumWSPro.saveSetting('page_order', order);
                }
            });
        },

        /**
         * Initialize tooltips
         */
        initTooltips: function() {
            $('[data-tooltip]').tooltip({
                position: { my: "center bottom-20", at: "center top" }
            });
        },

        /**
         * Initialize analytics
         */
        initAnalytics: function() {
            if (typeof Chart === 'undefined') return;

            // Views chart
            const viewsCtx = document.getElementById('views-chart');
            if (viewsCtx) {
                new Chart(viewsCtx, {
                    type: 'line',
                    data: {
                        labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                        datasets: [{
                            label: 'Views',
                            data: [12, 19, 3, 5, 2, 3, 20],
                            borderColor: '#007cba',
                            backgroundColor: 'rgba(0, 124, 186, 0.1)',
                            tension: 0.4
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'top',
                            }
                        }
                    }
                });
            }

            // Engagement chart
            const engagementCtx = document.getElementById('engagement-chart');
            if (engagementCtx) {
                new Chart(engagementCtx, {
                    type: 'doughnut',
                    data: {
                        labels: ['Completed', 'Partial', 'Bounced'],
                        datasets: [{
                            data: [300, 150, 50],
                            backgroundColor: ['#28a745', '#ffc107', '#dc3545']
                        }]
                    },
                    options: {
                        responsive: true,
                        plugins: {
                            legend: {
                                position: 'bottom',
                            }
                        }
                    }
                });
            }
        },

        /**
         * Story builder methods
         */
        addPage: function(e) {
            e.preventDefault();

            const pageTemplate = $('.page-template').html();
            const pageCount = $('.story-page').length + 1;
            const newPage = $(pageTemplate.replace(/{{PAGE_NUMBER}}/g, pageCount));

            $('.story-pages').append(newPage);
            newPage.addClass('techpremium-fade-in');
        },

        deletePage: function(e) {
            e.preventDefault();

            if ($('.story-page').length <= 1) {
                TechPremiumWSPro.showNotification('Story must have at least one page', 'warning');
                return;
            }

            $(this).closest('.story-page').fadeOut(300, function() {
                $(this).remove();
                TechPremiumWSPro.updatePageNumbers();
            });
        },

        addElement: function(e) {
            e.preventDefault();

            const elementType = $(this).data('element-type');
            const $page = $(this).closest('.story-page');
            const elementTemplate = $(`.element-template[data-type="${elementType}"]`).html();

            if (elementTemplate) {
                const newElement = $(elementTemplate);
                $page.find('.page-elements').append(newElement);
                newElement.addClass('techpremium-fade-in');
            }
        },

        updatePageNumbers: function() {
            $('.story-page').each(function(index) {
                $(this).find('.page-number').text(index + 1);
            });
        },

        /**
         * SEO optimization methods
         */
        optimizeSEO: function(e) {
            e.preventDefault();

            const storyId = $(this).data('story-id');

            TechPremiumWSPro.showProgress('Optimizing SEO...');

            $.ajax({
                url: techpremium_ws_pro_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'optimize_story_seo',
                    nonce: techpremium_ws_pro_ajax.nonce,
                    story_id: storyId
                },
                success: function(response) {
                    TechPremiumWSPro.hideProgress();

                    if (response.success) {
                        TechPremiumWSPro.showNotification('SEO optimization completed!', 'success');

                        // Update SEO score display
                        if (response.data.seo_score) {
                            $('.seo-score').text(response.data.seo_score + '%');
                            $('.seo-progress-bar').css('width', response.data.seo_score + '%');
                        }
                    } else {
                        TechPremiumWSPro.showNotification(response.data || 'SEO optimization failed', 'error');
                    }
                },
                error: function() {
                    TechPremiumWSPro.hideProgress();
                    TechPremiumWSPro.showNotification('Network error occurred', 'error');
                }
            });
        },

        generateSchema: function(e) {
            e.preventDefault();

            const storyId = $(this).data('story-id');

            $.ajax({
                url: techpremium_ws_pro_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'generate_story_schema',
                    nonce: techpremium_ws_pro_ajax.nonce,
                    story_id: storyId
                },
                success: function(response) {
                    if (response.success) {
                        $('#schema-output').val(JSON.stringify(response.data.schema, null, 2));
                        TechPremiumWSPro.showNotification('Schema generated successfully!', 'success');
                    } else {
                        TechPremiumWSPro.showNotification(response.data || 'Schema generation failed', 'error');
                    }
                },
                error: function() {
                    TechPremiumWSPro.showNotification('Network error occurred', 'error');
                }
            });
        },

        /**
         * Utility methods
         */
        showNotification: function(message, type) {
            const notification = $(`
                <div class="techpremium-notification ${type}">
                    ${message}
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text">Dismiss this notice.</span>
                    </button>
                </div>
            `);

            $('.techpremium-notifications').prepend(notification);
            notification.addClass('techpremium-fade-in');

            // Auto-dismiss after 5 seconds
            setTimeout(function() {
                notification.fadeOut(300, function() {
                    notification.remove();
                });
            }, 5000);

            // Manual dismiss
            notification.on('click', '.notice-dismiss', function() {
                notification.fadeOut(300, function() {
                    notification.remove();
                });
            });
        },

        showProgress: function(message) {
            const progress = $(`
                <div class="techpremium-progress-overlay">
                    <div class="techpremium-progress-content">
                        <div class="techpremium-spinner"></div>
                        <p>${message}</p>
                    </div>
                </div>
            `);

            $('body').append(progress);
            progress.fadeIn(300);
        },

        hideProgress: function() {
            $('.techpremium-progress-overlay').fadeOut(300, function() {
                $(this).remove();
            });
        },

        addStoryToList: function(storyData) {
            const storyCard = $(`
                <div class="techpremium-story-card story-card" data-story-id="${storyData.story_id}">
                    <div class="techpremium-story-preview">
                        <i class="dashicons dashicons-format-gallery"></i>
                    </div>
                    <div class="techpremium-story-info">
                        <h3 class="techpremium-story-title">${storyData.title}</h3>
                        <div class="techpremium-story-meta">
                            <span class="story-status">Draft</span> • 
                            <span class="story-date">Just now</span>
                        </div>
                        <div class="techpremium-story-actions">
                            <a href="#" class="techpremium-btn techpremium-btn-primary convert-story" data-story-id="${storyData.story_id}">Convert</a>
                            <a href="#" class="techpremium-btn techpremium-btn-secondary edit-story" data-story-id="${storyData.story_id}">Edit</a>
                            <a href="#" class="techpremium-btn techpremium-btn-danger delete-story" data-story-id="${storyData.story_id}">Delete</a>
                        </div>
                    </div>
                </div>
            `);

            $('.techpremium-stories-grid').prepend(storyCard);
            storyCard.addClass('techpremium-fade-in');
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        TechPremiumWSPro.init();

        // Handle initial tab from URL hash
        if (window.location.hash) {
            const targetTab = window.location.hash.substring(1);
            $(`.techpremium-nav-tab[data-tab="${targetTab}"]`).click();
        }
    });

})(jQuery);
