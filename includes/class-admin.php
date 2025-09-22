<?php
/**
 * Admin functionality
 */

if (!defined('ABSPATH')) {
    exit;
}

class WC_QC_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_wc_qc_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_wc_qc_get_product_settings', array($this, 'get_product_settings'));
        add_action('wp_ajax_wc_qc_save_product_settings', array($this, 'save_product_settings'));
        add_action('woocommerce_product_options_inventory_product_data', array($this, 'add_product_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_product_fields'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_submenu_page(
            'woocommerce',
            __('Quantity Control', 'wc-quantity-control'),
            __('Quantity Control', 'wc-quantity-control'),
            'manage_woocommerce',
            'wc-quantity-control',
            array($this, 'admin_page')
        );
    }
    
    /**
     * Enqueue admin scripts and styles
     */
    public function enqueue_admin_scripts($hook) {
        if ('woocommerce_page_wc-quantity-control' !== $hook && 'post.php' !== $hook && 'post-new.php' !== $hook) {
            return;
        }
        
        wp_enqueue_style('wc-qc-admin-style', WC_QC_PLUGIN_URL . 'assets/css/admin-style.css', array(), WC_QC_VERSION);
        wp_enqueue_script('wc-qc-admin-script', WC_QC_PLUGIN_URL . 'assets/js/admin-script.js', array('jquery'), WC_QC_VERSION, true);
        
        wp_localize_script('wc-qc-admin-script', 'wc_qc_admin', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wc_qc_admin_nonce'),
            'strings' => array(
                'saving' => __('Saving...', 'wc-quantity-control'),
                'saved' => __('Settings saved!', 'wc-quantity-control'),
                'error' => __('Error saving settings', 'wc-quantity-control'),
            )
        ));
    }
    
    /**
     * Admin page content
     */
    public function admin_page() {
        include WC_QC_PLUGIN_DIR . 'templates/admin-page.php';
    }
    
    /**
     * Save global settings via AJAX
     */
    public function save_settings() {
        check_ajax_referer('wc_qc_admin_nonce', 'nonce');
        
        if (!current_user_can('manage_woocommerce')) {
            wp_die(__('You do not have sufficient permissions to access this page.'));
        }
        
        $settings = array(
            'global_min_quantity' => intval($_POST['global_min_quantity']),
            'global_max_quantity' => intval($_POST['global_max_quantity']),
            'enable_global_limits' => sanitize_text_field($_POST['enable_global_limits']),
            'show_quantity_message' => sanitize_text_field($_POST['show_quantity_message']),
            'quantity_message' => sanitize_text_field($_POST['quantity_message']),
        );
        
        foreach ($settings as $key => $value) {
            update_option('wc_qc_' . $key, $value);
        }
        
        wp_send_json_success(array('message' => __('Settings saved successfully!', 'wc-quantity-control')));
    }
    
    /**
     * Add product-specific fields
     */
    public function add_product_fields() {
        global $post;
        
        echo '<div class="options_group">';
        
        woocommerce_wp_checkbox(array(
            'id' => '_wc_qc_override_global',
            'label' => __('Override Global Limits', 'wc-quantity-control'),
            'description' => __('Enable product-specific quantity limits', 'wc-quantity-control'),
        ));
        
        woocommerce_wp_text_input(array(
            'id' => '_wc_qc_min_quantity',
            'label' => __('Minimum Quantity', 'wc-quantity-control'),
            'type' => 'number',
            'custom_attributes' => array('min' => '1'),
        ));
        
        woocommerce_wp_text_input(array(
            'id' => '_wc_qc_max_quantity',
            'label' => __('Maximum Quantity', 'wc-quantity-control'),
            'type' => 'number',
            'custom_attributes' => array('min' => '1'),
        ));
        
        echo '</div>';
    }
    
    /**
     * Save product-specific fields
     */
    public function save_product_fields($post_id) {
        $override_global = isset($_POST['_wc_qc_override_global']) ? 'yes' : 'no';
        update_post_meta($post_id, '_wc_qc_override_global', $override_global);
        
        if (isset($_POST['_wc_qc_min_quantity'])) {
            update_post_meta($post_id, '_wc_qc_min_quantity', intval($_POST['_wc_qc_min_quantity']));
        }
        
        if (isset($_POST['_wc_qc_max_quantity'])) {
            update_post_meta($post_id, '_wc_qc_max_quantity', intval($_POST['_wc_qc_max_quantity']));
        }
    }
}