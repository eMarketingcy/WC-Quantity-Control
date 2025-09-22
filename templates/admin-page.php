<?php
/**
 * Admin page template
 */

if (!defined('ABSPATH')) {
    exit;
}

// Get current settings
$global_min = get_option('wc_qc_global_min_quantity', 1);
$global_max = get_option('wc_qc_global_max_quantity', 999);
$enable_global = get_option('wc_qc_enable_global_limits', 'yes');
$show_message = get_option('wc_qc_show_quantity_message', 'yes');
$quantity_message = get_option('wc_qc_quantity_message', 'Quantity must be between {min} and {max}');
?>

<div class="wc-qc-admin-wrapper">
    <div class="wc-qc-header">
        <h1><?php _e('Quantity Control Settings', 'wc-quantity-control'); ?></h1>
        <p class="description"><?php _e('Configure minimum and maximum order quantities for your WooCommerce store.', 'wc-quantity-control'); ?></p>
    </div>

    <div class="wc-qc-content">
        <div class="wc-qc-card">
            <div class="wc-qc-card-header">
                <h2><?php _e('Global Settings', 'wc-quantity-control'); ?></h2>
                <p><?php _e('These settings apply to all products unless overridden at the product level.', 'wc-quantity-control'); ?></p>
            </div>
            
            <form id="wc-qc-settings-form" class="wc-qc-form">
                <?php wp_nonce_field('wc_qc_admin_nonce', 'nonce'); ?>
                
                <div class="wc-qc-form-group">
                    <label class="wc-qc-toggle">
                        <input type="checkbox" name="enable_global_limits" value="yes" <?php checked($enable_global, 'yes'); ?>>
                        <span class="wc-qc-toggle-slider"></span>
                        <span class="wc-qc-toggle-label"><?php _e('Enable Global Quantity Limits', 'wc-quantity-control'); ?></span>
                    </label>
                </div>

                <div class="wc-qc-form-row">
                    <div class="wc-qc-form-group">
                        <label for="global_min_quantity"><?php _e('Global Minimum Quantity', 'wc-quantity-control'); ?></label>
                        <input type="number" id="global_min_quantity" name="global_min_quantity" value="<?php echo esc_attr($global_min); ?>" min="1" class="wc-qc-input">
                        <span class="wc-qc-help-text"><?php _e('Minimum quantity customers must order', 'wc-quantity-control'); ?></span>
                    </div>
                    
                    <div class="wc-qc-form-group">
                        <label for="global_max_quantity"><?php _e('Global Maximum Quantity', 'wc-quantity-control'); ?></label>
                        <input type="number" id="global_max_quantity" name="global_max_quantity" value="<?php echo esc_attr($global_max); ?>" min="1" class="wc-qc-input">
                        <span class="wc-qc-help-text"><?php _e('Maximum quantity customers can order', 'wc-quantity-control'); ?></span>
                    </div>
                </div>

                <div class="wc-qc-form-group">
                    <label class="wc-qc-toggle">
                        <input type="checkbox" name="show_quantity_message" value="yes" <?php checked($show_message, 'yes'); ?>>
                        <span class="wc-qc-toggle-slider"></span>
                        <span class="wc-qc-toggle-label"><?php _e('Show Quantity Message on Product Pages', 'wc-quantity-control'); ?></span>
                    </label>
                </div>

                <div class="wc-qc-form-group">
                    <label for="quantity_message"><?php _e('Quantity Message', 'wc-quantity-control'); ?></label>
                    <input type="text" id="quantity_message" name="quantity_message" value="<?php echo esc_attr($quantity_message); ?>" class="wc-qc-input">
                    <span class="wc-qc-help-text"><?php _e('Use {min} and {max} as placeholders for the actual values', 'wc-quantity-control'); ?></span>
                </div>

                <div class="wc-qc-form-actions">
                    <button type="submit" class="wc-qc-btn wc-qc-btn-primary">
                        <span class="wc-qc-btn-text"><?php _e('Save Settings', 'wc-quantity-control'); ?></span>
                        <span class="wc-qc-btn-loading" style="display: none;"><?php _e('Saving...', 'wc-quantity-control'); ?></span>
                    </button>
                </div>
            </form>
        </div>

        <div class="wc-qc-card">
            <div class="wc-qc-card-header">
                <h2><?php _e('Product-Specific Settings', 'wc-quantity-control'); ?></h2>
                <p><?php _e('Override global settings for individual products by editing them in the product inventory tab.', 'wc-quantity-control'); ?></p>
            </div>
            
            <div class="wc-qc-info-box">
                <div class="wc-qc-info-icon">ℹ️</div>
                <div class="wc-qc-info-content">
                    <h3><?php _e('How to set product-specific limits:', 'wc-quantity-control'); ?></h3>
                    <ol>
                        <li><?php _e('Go to Products → All Products', 'wc-quantity-control'); ?></li>
                        <li><?php _e('Edit any product', 'wc-quantity-control'); ?></li>
                        <li><?php _e('Navigate to the Inventory tab', 'wc-quantity-control'); ?></li>
                        <li><?php _e('Check "Override Global Limits" and set your custom values', 'wc-quantity-control'); ?></li>
                    </ol>
                </div>
            </div>
        </div>

        <div class="wc-qc-card">
            <div class="wc-qc-card-header">
                <h2><?php _e('Plugin Status', 'wc-quantity-control'); ?></h2>
            </div>
            
            <div class="wc-qc-status-grid">
                <div class="wc-qc-status-item">
                    <div class="wc-qc-status-icon wc-qc-status-active">✓</div>
                    <div class="wc-qc-status-content">
                        <h3><?php _e('Plugin Active', 'wc-quantity-control'); ?></h3>
                        <p><?php _e('Quantity controls are working properly', 'wc-quantity-control'); ?></p>
                    </div>
                </div>
                
                <div class="wc-qc-status-item">
                    <div class="wc-qc-status-icon wc-qc-status-active">✓</div>
                    <div class="wc-qc-status-content">
                        <h3><?php _e('WooCommerce Compatible', 'wc-quantity-control'); ?></h3>
                        <p><?php printf(__('Version %s detected', 'wc-quantity-control'), WC()->version); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>