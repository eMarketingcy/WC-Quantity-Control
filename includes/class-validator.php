<?php
/**
 * Validation functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_QC_Validator {
    
    public function __construct() {
        add_filter('woocommerce_add_to_cart_validation', array($this, 'validate_add_to_cart'), 10, 3);
        add_action('woocommerce_check_cart_items', array($this, 'validate_cart_items'));
        add_filter('woocommerce_update_cart_validation', array($this, 'validate_cart_update'), 10, 4);
    }
    
    /**
     * Validate add to cart
     */
    public function validate_add_to_cart($passed, $product_id, $quantity) {
        $limits = $this->get_product_quantity_limits($product_id);
        
        if ($quantity < $limits['min']) {
            wc_add_notice(
                sprintf(__('Minimum quantity for this product is %d', 'wc-quantity-control'), $limits['min']),
                'error'
            );
            return false;
        }
        
        if ($limits['max'] > 0 && $quantity > $limits['max']) {
            wc_add_notice(
                sprintf(__('Maximum quantity for this product is %d', 'wc-quantity-control'), $limits['max']),
                'error'
            );
            return false;
        }
        
        return $passed;
    }
    
    /**
     * Validate cart items
     */
    public function validate_cart_items() {
        foreach (WC()->cart->get_cart() as $cart_item_key => $cart_item) {
            $product_id = $cart_item['product_id'];
            $quantity = $cart_item['quantity'];
            $limits = $this->get_product_quantity_limits($product_id);
            
            if ($quantity < $limits['min']) {
                wc_add_notice(
                    sprintf(__('Minimum quantity for "%s" is %d', 'wc-quantity-control'), 
                        $cart_item['data']->get_name(), 
                        $limits['min']
                    ),
                    'error'
                );
            }
            
            if ($limits['max'] > 0 && $quantity > $limits['max']) {
                wc_add_notice(
                    sprintf(__('Maximum quantity for "%s" is %d', 'wc-quantity-control'), 
                        $cart_item['data']->get_name(), 
                        $limits['max']
                    ),
                    'error'
                );
            }
        }
    }
    
    /**
     * Validate cart update
     */
    public function validate_cart_update($passed, $cart_item_key, $values, $quantity) {
        $product_id = $values['product_id'];
        $limits = $this->get_product_quantity_limits($product_id);
        
        if ($quantity < $limits['min']) {
            wc_add_notice(
                sprintf(__('Minimum quantity for this product is %d', 'wc-quantity-control'), $limits['min']),
                'error'
            );
            return false;
        }
        
        if ($limits['max'] > 0 && $quantity > $limits['max']) {
            wc_add_notice(
                sprintf(__('Maximum quantity for this product is %d', 'wc-quantity-control'), $limits['max']),
                'error'
            );
            return false;
        }
        
        return $passed;
    }
    
    /**
     * Get quantity limits for a product
     */
    private function get_product_quantity_limits($product_id) {
        $override_global = get_post_meta($product_id, '_wc_qc_override_global', true);
        
        if ($override_global === 'yes') {
            $min = get_post_meta($product_id, '_wc_qc_min_quantity', true);
            $max = get_post_meta($product_id, '_wc_qc_max_quantity', true);
        } else {
            $min = get_option('wc_qc_global_min_quantity', 1);
            $max = get_option('wc_qc_global_max_quantity', 999);
        }
        
        return array(
            'min' => intval($min) ?: 1,
            'max' => intval($max) ?: 999
        );
    }
}