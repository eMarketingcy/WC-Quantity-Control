<?php
/**
 * Frontend functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_QC_Frontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_frontend_scripts'));
        add_filter('woocommerce_quantity_input_args', array($this, 'modify_quantity_args'), 10, 2);
        add_action('woocommerce_single_product_summary', array($this, 'display_quantity_message'), 25);
        add_action('wp_footer', array($this, 'add_quantity_validation_script'));
    }
    
    /**
     * Enqueue frontend scripts
     */
    public function enqueue_frontend_scripts() {
        if (is_woocommerce() || is_cart()) {
            wp_enqueue_script('wc-qc-frontend-script', WC_QC_PLUGIN_URL . 'assets/js/frontend-script.js', array('jquery'), WC_QC_VERSION, true);
            
            wp_localize_script('wc-qc-frontend-script', 'wc_qc_frontend', array(
                'strings' => array(
                    'min_error' => __('Minimum quantity is {min}', 'wc-quantity-control'),
                    'max_error' => __('Maximum quantity is {max}', 'wc-quantity-control'),
                    'range_error' => __('Quantity must be between {min} and {max}', 'wc-quantity-control'),
                )
            ));
        }
    }
    
    /**
     * Modify quantity input arguments
     */
    public function modify_quantity_args($args, $product) {
        if (!$product) {
            return $args;
        }
        
        $limits = $this->get_product_quantity_limits($product->get_id());
        
        if ($limits['min'] > 0) {
            $args['min_value'] = $limits['min'];
            $args['input_value'] = max($args['input_value'], $limits['min']);
        }
        
        if ($limits['max'] > 0) {
            $args['max_value'] = $limits['max'];
        }
        
        return $args;
    }
    
    /**
     * Display quantity message on product page
     */
    public function display_quantity_message() {
        global $product;
        
        if (!$product || get_option('wc_qc_show_quantity_message') !== 'yes') {
            return;
        }
        
        $limits = $this->get_product_quantity_limits($product->get_id());
        $message = get_option('wc_qc_quantity_message', 'Quantity must be between {min} and {max}');
        
        if ($limits['min'] > 0 || $limits['max'] > 0) {
            $message = str_replace(array('{min}', '{max}'), array($limits['min'], $limits['max']), $message);
            echo '<div class="wc-qc-quantity-message">' . esc_html($message) . '</div>';
        }
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
            'min' => intval($min),
            'max' => intval($max)
        );
    }
    
    /**
     * Add quantity validation script to footer
     */
    public function add_quantity_validation_script() {
        if (!is_woocommerce() && !is_cart()) {
            return;
        }
        ?>
        <script type="text/javascript">
        jQuery(document).ready(function($) {
            // Add data attributes to quantity inputs for validation
            $('.qty').each(function() {
                var $input = $(this);
                var min = $input.attr('min') || 1;
                var max = $input.attr('max') || 999;
                
                $input.attr('data-min', min);
                $input.attr('data-max', max);
            });
        });
        </script>
        <?php
    }
}