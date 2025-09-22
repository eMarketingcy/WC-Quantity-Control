<?php
/**
 * Plugin Name: WooCommerce Quantity Control
 * Plugin URI: https://emarketing.cy
 * Description: Control minimum and maximum order quantities for WooCommerce products with a modern admin interface.
 * Version: 1.0.0
 * Author: eMarketing Cyprus
 * License: GPL v2 or later
 * Text Domain: wc-quantity-control
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.8
 * WC requires at least: 5.0
 * WC tested up to: 8.0
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('WC_QC_VERSION', '1.0.0');
define('WC_QC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WC_QC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WC_QC_PLUGIN_BASENAME', plugin_basename(__FILE__));

/**
 * Main plugin class
 */
class WC_Quantity_Control {
    
    /**
     * Single instance of the class
     */
    private static $instance = null;
    
    /**
     * Get single instance
     */
    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        add_action('plugins_loaded', array($this, 'init'));
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Initialize plugin
     */
    public function init() {
        // Check if WooCommerce is active
        if (!class_exists('WooCommerce')) {
            add_action('admin_notices', array($this, 'woocommerce_missing_notice'));
            return;
        }
        
        // Load plugin files
        $this->load_files();
        
        // Initialize classes
        if (is_admin()) {
            new WC_QC_Admin();
        }
        new WC_QC_Frontend();
        new WC_QC_Validator();
        
        // Load textdomain
        load_plugin_textdomain('wc-quantity-control', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    /**
     * Load required files
     */
    private function load_files() {
        require_once WC_QC_PLUGIN_DIR . 'includes/class-admin.php';
        require_once WC_QC_PLUGIN_DIR . 'includes/class-frontend.php';
        require_once WC_QC_PLUGIN_DIR . 'includes/class-validator.php';
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Set default options
        $default_options = array(
            'global_min_quantity' => 1,
            'global_max_quantity' => 999,
            'enable_global_limits' => 'yes',
            'show_quantity_message' => 'yes',
            'quantity_message' => 'Quantity must be between {min} and {max}',
        );
        
        foreach ($default_options as $key => $value) {
            if (get_option('wc_qc_' . $key) === false) {
                update_option('wc_qc_' . $key, $value);
            }
        }
    }
    
    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }
    
    /**
     * WooCommerce missing notice
     */
    public function woocommerce_missing_notice() {
        echo '<div class="error"><p><strong>' . __('WooCommerce Quantity Control', 'wc-quantity-control') . '</strong> ' . __('requires WooCommerce to be installed and active.', 'wc-quantity-control') . '</p></div>';
    }
}

// Initialize plugin
WC_Quantity_Control::get_instance();