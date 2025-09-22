/**
 * WooCommerce Quantity Control - Admin JavaScript
 */

 (function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize admin functionality
        WC_QC_Admin.init();
    });

    var WC_QC_Admin = {
        
        init: function() {
            this.bindEvents();
            this.initTooltips();
        },

        bindEvents: function() {
            // Settings form submission
            $('#wc-qc-settings-form').on('submit', this.saveSettings);
            
            // Real-time validation
            $('#global_min_quantity, #global_max_quantity').on('input', this.validateQuantityInputs);
            
            // Toggle dependencies
            $('input[name="enable_global_limits"]').on('change', this.toggleGlobalLimits);
            $('input[name="show_quantity_message"]').on('change', this.toggleQuantityMessage);
        },

        saveSettings: function(e) {
            e.preventDefault();
            
            var $form = $(this);
            var $button = $form.find('.wc-qc-btn-primary');
            var formData = $form.serialize();
            
            // Show loading state
            $button.addClass('loading').prop('disabled', true);
            
            // Remove any existing messages
            $('.wc-qc-message').remove();
            
            $.ajax({
                url: wc_qc_admin.ajax_url,
                type: 'POST',
                data: formData + '&action=wc_qc_save_settings',
                success: function(response) {
                    if (response.success) {
                        WC_QC_Admin.showMessage('success', response.data.message);
                        WC_QC_Admin.animateSuccess($button);
                    } else {
                        WC_QC_Admin.showMessage('error', response.data.message || wc_qc_admin.strings.error);
                    }
                },
                error: function() {
                    WC_QC_Admin.showMessage('error', wc_qc_admin.strings.error);
                },
                complete: function() {
                    $button.removeClass('loading').prop('disabled', false);
                }
            });
        },

        validateQuantityInputs: function() {
            var $minInput = $('#global_min_quantity');
            var $maxInput = $('#global_max_quantity');
            var minVal = parseInt($minInput.val()) || 1;
            var maxVal = parseInt($maxInput.val()) || 999;
            
            // Remove existing validation classes
            $minInput.removeClass('wc-qc-input-error');
            $maxInput.removeClass('wc-qc-input-error');
            $('.wc-qc-validation-error').remove();
            
            // Validate minimum quantity
            if (minVal < 1) {
                WC_QC_Admin.showFieldError($minInput, 'Minimum quantity must be at least 1');
                return false;
            }
            
            // Validate maximum quantity
            if (maxVal < minVal) {
                WC_QC_Admin.showFieldError($maxInput, 'Maximum quantity must be greater than minimum quantity');
                return false;
            }
            
            return true;
        },

        toggleGlobalLimits: function() {
            var $checkbox = $(this);
            var $quantityInputs = $('#global_min_quantity, #global_max_quantity').closest('.wc-qc-form-group');
            
            if ($checkbox.is(':checked')) {
                $quantityInputs.slideDown(300);
            } else {
                $quantityInputs.slideUp(300);
            }
        },

        toggleQuantityMessage: function() {
            var $checkbox = $(this);
            var $messageInput = $('#quantity_message').closest('.wc-qc-form-group');
            
            if ($checkbox.is(':checked')) {
                $messageInput.slideDown(300);
            } else {
                $messageInput.slideUp(300);
            }
        },

        showMessage: function(type, message) {
            var messageClass = type === 'success' ? 'wc-qc-message-success' : 'wc-qc-message-error';
            var $message = $('<div class="wc-qc-message ' + messageClass + '">' + message + '</div>');
            
            $('.wc-qc-form').prepend($message);
            
            // Scroll to message
            $('html, body').animate({
                scrollTop: $message.offset().top - 100
            }, 300);
            
            // Auto-hide success messages
            if (type === 'success') {
                setTimeout(function() {
                    $message.fadeOut(300, function() {
                        $(this).remove();
                    });
                }, 3000);
            }
        },

        showFieldError: function($field, message) {
            $field.addClass('wc-qc-input-error');
            
            var $error = $('<span class="wc-qc-validation-error" style="color: #dc3545; font-size: 12px; display: block; margin-top: 4px;">' + message + '</span>');
            $field.after($error);
        },

        animateSuccess: function($button) {
            var originalText = $button.find('.wc-qc-btn-text').text();
            
            $button.find('.wc-qc-btn-text').text('✓ Saved!');
            $button.css('background', '#10b981');
            
            setTimeout(function() {
                $button.find('.wc-qc-btn-text').text(originalText);
                $button.css('background', '');
            }, 2000);
        },

        initTooltips: function() {
            // Add tooltips for help text
            $('.wc-qc-help-text').each(function() {
                var $helpText = $(this);
                var $input = $helpText.prev('.wc-qc-input');
                
                $input.on('focus', function() {
                    $helpText.css('color', '#0073aa');
                }).on('blur', function() {
                    $helpText.css('color', '');
                });
            });
        }
    };

    // Add custom CSS for validation errors
    $('<style>')
        .prop('type', 'text/css')
        .html('.wc-qc-input-error { border-color: #dc3545 !important; box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1) !important; }')
        .appendTo('head');

})(jQuery);