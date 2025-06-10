/*!
 * TechPremium Web Stories Pro Public JavaScript
 * Version: 1.0.0
 */

(function($) {
    'use strict';

    /**
     * Main public object
     */
    const TechPremiumWSProPublic = {

        init: function() {
            this.initCarousels();
            this.initAnalytics();
            this.bindEvents();
        },

        /**
         * Initialize carousels
         */
        initCarousels: function() {
            $('.techpremium-story-carousel').each(function() {
                const $carousel = $(this);
                const autoplay = $carousel.data('autoplay') === 'true';
                const arrows = $carousel.data('arrows') === 'true';
                const dots = $carousel.data('dots') === 'true';

                new TechPremiumCarousel($carousel, {
                    autoplay: autoplay,
                    arrows: arrows,
                    dots: dots
                });
            });
        },

        /**
         * Initialize analytics tracking
         */
        initAnalytics: function() {
            if (!techpremium_ws_pro_public.analytics_enabled) {
                return;
            }

            // Track story views
            $('.techpremium-story-embed iframe').on('load', function() {
                const storyId = $(this).closest('.techpremium-story-embed').data('story-id');
                if (storyId) {
                    TechPremiumWSProPublic.trackStoryView(storyId);
                }
            });

            // Track carousel interactions
            $('.techpremium-story-carousel').on('slide-change', function(e, slideIndex) {
                console.log('Carousel slide changed:', slideIndex);
                // Track carousel interaction
            });
        },

        /**
         * Bind events
         */
        bindEvents: function() {
            // Handle story clicks
            $(document).on('click', '.story-thumbnail a, .story-title a', function(e) {
                const storyId = $(this).closest('[data-story-id]').data('story-id');
                if (storyId) {
                    TechPremiumWSProPublic.trackStoryClick(storyId);
                }
            });
        },

        /**
         * Track story view
         */
        trackStoryView: function(storyId) {
            $.ajax({
                url: techpremium_ws_pro_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'track_story_view',
                    nonce: techpremium_ws_pro_public.nonce,
                    story_id: storyId
                },
                success: function(response) {
                    console.log('Story view tracked:', storyId);
                }
            });
        },

        /**
         * Track story click
         */
        trackStoryClick: function(storyId) {
            $.ajax({
                url: techpremium_ws_pro_public.ajax_url,
                type: 'POST',
                data: {
                    action: 'track_story_click',
                    nonce: techpremium_ws_pro_public.nonce,
                    story_id: storyId
                }
            });
        }
    };

    /**
     * Carousel class
     */
    class TechPremiumCarousel {
        constructor($element, options) {
            this.$carousel = $element;
            this.options = $.extend({
                autoplay: true,
                autoplaySpeed: 5000,
                arrows: true,
                dots: true,
                slidesToShow: 1,
                slidesToScroll: 1
            }, options);

            this.currentSlide = 0;
            this.slideCount = this.$carousel.find('.carousel-slide').length;
            this.isPlaying = this.options.autoplay;

            this.init();
        }

        init() {
            this.setupCarousel();
            this.bindEvents();

            if (this.options.autoplay) {
                this.startAutoplay();
            }
        }

        setupCarousel() {
            const slideWidth = this.$carousel.find('.carousel-slide').first().outerWidth(true);
            this.$carousel.find('.carousel-track').css('width', slideWidth * this.slideCount);

            // Update dots
            if (this.options.dots) {
                this.updateDots();
            }
        }

        bindEvents() {
            const self = this;

            // Arrow navigation
            this.$carousel.on('click', '.carousel-prev', function(e) {
                e.preventDefault();
                self.prevSlide();
            });

            this.$carousel.on('click', '.carousel-next', function(e) {
                e.preventDefault();
                self.nextSlide();
            });

            // Dot navigation
            this.$carousel.on('click', '.carousel-dot', function(e) {
                e.preventDefault();
                const slideIndex = $(this).data('slide');
                self.goToSlide(slideIndex);
            });

            // Pause on hover
            this.$carousel.on('mouseenter', function() {
                self.pauseAutoplay();
            });

            this.$carousel.on('mouseleave', function() {
                if (self.options.autoplay) {
                    self.startAutoplay();
                }
            });

            // Touch/swipe support
            this.addTouchSupport();
        }

        addTouchSupport() {
            let startX = 0;
            let currentX = 0;
            let isDragging = false;

            this.$carousel.on('touchstart', (e) => {
                startX = e.originalEvent.touches[0].clientX;
                isDragging = true;
                this.pauseAutoplay();
            });

            this.$carousel.on('touchmove', (e) => {
                if (!isDragging) return;
                currentX = e.originalEvent.touches[0].clientX;
            });

            this.$carousel.on('touchend', () => {
                if (!isDragging) return;
                isDragging = false;

                const diffX = startX - currentX;

                if (Math.abs(diffX) > 50) {
                    if (diffX > 0) {
                        this.nextSlide();
                    } else {
                        this.prevSlide();
                    }
                }

                if (this.options.autoplay) {
                    this.startAutoplay();
                }
            });
        }

        nextSlide() {
            this.currentSlide = (this.currentSlide + 1) % this.slideCount;
            this.updateSlide();
        }

        prevSlide() {
            this.currentSlide = (this.currentSlide - 1 + this.slideCount) % this.slideCount;
            this.updateSlide();
        }

        goToSlide(index) {
            this.currentSlide = index;
            this.updateSlide();
        }

        updateSlide() {
            const slideWidth = this.$carousel.find('.carousel-slide').first().outerWidth(true);
            const translateX = -this.currentSlide * slideWidth;

            this.$carousel.find('.carousel-track').css('transform', `translateX(${translateX}px)`);

            if (this.options.dots) {
                this.updateDots();
            }

            // Trigger custom event
            this.$carousel.trigger('slide-change', [this.currentSlide]);
        }

        updateDots() {
            this.$carousel.find('.carousel-dot').removeClass('active');
            this.$carousel.find('.carousel-dot').eq(this.currentSlide).addClass('active');
        }

        startAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
            }

            this.autoplayInterval = setInterval(() => {
                this.nextSlide();
            }, this.options.autoplaySpeed);

            this.isPlaying = true;
        }

        pauseAutoplay() {
            if (this.autoplayInterval) {
                clearInterval(this.autoplayInterval);
                this.autoplayInterval = null;
            }

            this.isPlaying = false;
        }

        destroy() {
            this.pauseAutoplay();
            this.$carousel.off('.carousel');
        }
    }

    /**
     * Lazy loading for story images
     */
    const lazyLoadImages = function() {
        const images = document.querySelectorAll('img[loading="lazy"]');

        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries, observer) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.src = img.dataset.src || img.src;
                        img.classList.remove('lazy');
                        imageObserver.unobserve(img);
                    }
                });
            });

            images.forEach(img => imageObserver.observe(img));
        } else {
            // Fallback for older browsers
            images.forEach(img => {
                img.src = img.dataset.src || img.src;
            });
        }
    };

    /**
     * Initialize when document is ready
     */
    $(document).ready(function() {
        TechPremiumWSProPublic.init();
        lazyLoadImages();
    });

    // Make carousel class globally available
    window.TechPremiumCarousel = TechPremiumCarousel;

})(jQuery);
