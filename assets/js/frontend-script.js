/**
 * WooCommerce Quantity Control - Frontend JavaScript
 */

 (function($) {
    'use strict';

    $(document).ready(function() {
        WC_QC_Frontend.init();
    });

    var WC_QC_Frontend = {
        
        init: function() {
            this.bindEvents();
            this.initQuantityValidation();
        },

        bindEvents: function() {
            // Quantity input validation
            $(document).on('input change', '.qty', this.validateQuantityInput);
            $(document).on('blur', '.qty', this.validateQuantityInput);
            
            // Add to cart button validation
            $(document).on('click', '.single_add_to_cart_button', this.validateAddToCart);
            
            // Cart update validation
            $(document).on('click', '[name="update_cart"]', this.validateCartUpdate);
            
            // Prevent manual input of invalid quantities
            $(document).on('keydown', '.qty', this.preventInvalidInput);
        },

        initQuantityValidation: function() {
            // Add visual indicators to quantity inputs
            $('.qty').each(function() {
                var $input = $(this);
                var min = parseInt($input.attr('min')) || 1;
                var max = parseInt($input.attr('max')) || 999;
                
                // Store limits as data attributes
                $input.data('min-quantity', min);
                $input.data('max-quantity', max);
                
                // Add validation container
                if (!$input.next('.wc-qc-validation-message').length) {
                    $input.after('<div class="wc-qc-validation-message"></div>');
                }
                
                // Validate initial value
                WC_QC_Frontend.validateQuantityInput.call($input[0]);
            });
        },

        validateQuantityInput: function() {
            var $input = $(this);
            var value = parseInt($input.val()) || 0;
            var min = $input.data('min-quantity') || parseInt($input.attr('min')) || 1;
            var max = $input.data('max-quantity') || parseInt($input.attr('max')) || 999;
            var $message = $input.next('.wc-qc-validation-message');
            
            // Clear previous validation
            $input.removeClass('wc-qc-invalid');
            $message.empty().hide();
            
            var isValid = true;
            var message = '';
            
            if (value < min) {
                isValid = false;
                message = wc_qc_frontend.strings.min_error.replace('{min}', min);
                $input.val(min); // Auto-correct to minimum
            } else if (max > 0 && value > max) {
                isValid = false;
                message = wc_qc_frontend.strings.max_error.replace('{max}', max);
                $input.val(max); // Auto-correct to maximum
            }
            
            if (!isValid) {
                $input.addClass('wc-qc-invalid');
                $message.html('<span class="wc-qc-error">' + message + '</span>').show();
                WC_QC_Frontend.showTooltip($input, message);
            }
            
            return isValid;
        },

        validateAddToCart: function(e) {
            var $button = $(this);
            var $form = $button.closest('form.cart');
            var $qtyInput = $form.find('.qty');
            
            if ($qtyInput.length) {
                var isValid = WC_QC_Frontend.validateQuantityInput.call($qtyInput[0]);
                
                if (!isValid) {
                    e.preventDefault();
                    WC_QC_Frontend.showNotice('error', 'Please correct the quantity before adding to cart.');
                    $qtyInput.focus();
                    return false;
                }
            }
            
            return true;
        },

        validateCartUpdate: function(e) {
            var isValid = true;
            
            $('.qty').each(function() {
                if (!WC_QC_Frontend.validateQuantityInput.call(this)) {
                    isValid = false;
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                WC_QC_Frontend.showNotice('error', 'Please correct the quantities before updating cart.');
                return false;
            }
            
            return true;
        },

        preventInvalidInput: function(e) {
            var $input = $(this);
            var min = $input.data('min-quantity') || parseInt($input.attr('min')) || 1;
            var max = $input.data('max-quantity') || parseInt($input.attr('max')) || 999;
            var currentValue = parseInt($input.val()) || min;
            
            // Allow navigation keys
            if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
                // Allow Ctrl+A, Ctrl+C, Ctrl+V, Ctrl+X
                (e.keyCode === 65 && e.ctrlKey === true) ||
                (e.keyCode === 67 && e.ctrlKey === true) ||
                (e.keyCode === 86 && e.ctrlKey === true) ||
                (e.keyCode === 88 && e.ctrlKey === true) ||
                // Allow home, end, left, right, down, up
                (e.keyCode >= 35 && e.keyCode <= 40)) {
                return;
            }
            
            // Ensure that it is a number and stop the keypress
            if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                e.preventDefault();
            }
        },

        showTooltip: function($input, message) {
            // Remove existing tooltips
            $('.wc-qc-tooltip').remove();
            
            var $tooltip = $('<div class="wc-qc-tooltip">' + message + '</div>');
            $('body').append($tooltip);
            
            var inputOffset = $input.offset();
            $tooltip.css({
                position: 'absolute',
                top: inputOffset.top - $tooltip.outerHeight() - 10,
                left: inputOffset.left,
                zIndex: 9999
            });
            
            // Auto-hide tooltip
            setTimeout(function() {
                $tooltip.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 3000);
        },

        showNotice: function(type, message) {
            var noticeClass = type === 'error' ? 'woocommerce-error' : 'woocommerce-message';
            var $notice = $('<div class="woocommerce-notices-wrapper"><div class="' + noticeClass + '">' + message + '</div></div>');
            
            // Remove existing notices
            $('.woocommerce-notices-wrapper').remove();
            
            // Add notice to top of page
            if ($('.woocommerce').length) {
                $('.woocommerce').prepend($notice);
            } else {
                $('body').prepend($notice);
            }
            
            // Scroll to notice
            $('html, body').animate({
                scrollTop: $notice.offset().top - 100
            }, 300);
            
            // Auto-hide after 5 seconds
            setTimeout(function() {
                $notice.fadeOut(300, function() {
                    $(this).remove();
                });
            }, 5000);
        }
    };

    // Add custom CSS for frontend validation
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .wc-qc-invalid {
                border-color: #dc3545 !important;
                box-shadow: 0 0 0 2px rgba(220, 53, 69, 0.2) !important;
            }
            
            .wc-qc-validation-message {
                margin-top: 5px;
                font-size: 12px;
            }
            
            .wc-qc-error {
                color: #dc3545;
                font-weight: 500;
            }
            
            .wc-qc-tooltip {
                background: #333;
                color: #fff;
                padding: 8px 12px;
                border-radius: 4px;
                font-size: 12px;
                max-width: 200px;
                word-wrap: break-word;
                box-shadow: 0 2px 8px rgba(0,0,0,0.3);
            }
            
            .wc-qc-tooltip:after {
                content: '';
                position: absolute;
                top: 100%;
                left: 20px;
                margin-left: -5px;
                border-width: 5px;
                border-style: solid;
                border-color: #333 transparent transparent transparent;
            }
            
            .wc-qc-quantity-message {
                background: #f8f9fa;
                border: 1px solid #e9ecef;
                border-radius: 4px;
                padding: 10px 15px;
                margin: 10px 0;
                font-size: 14px;
                color: #495057;
            }
            
            @media (max-width: 768px) {
                .wc-qc-tooltip {
                    position: fixed !important;
                    top: 20px !important;
                    left: 20px !important;
                    right: 20px !important;
                    width: auto !important;
                    max-width: none !important;
                }
            }
        `)
        .appendTo('head');

})(jQuery);