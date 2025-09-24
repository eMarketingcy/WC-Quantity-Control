<?php
/**
 * Admin class for WooCommerce Quantity Control (Steps/Packages + ∞ Max)
 */

if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! class_exists( 'WC_QC_Admin', false ) ) {

	class WC_QC_Admin {
		private static $bootstrapped = false;

		const MENU_SLUG  = 'wc-quantity-control';
		const CAPABILITY = 'manage_woocommerce';

		protected $plugin_dir;
		protected $templates_dir;
		protected $assets_url;

		public function __construct() {
			if ( self::$bootstrapped ) return;
			self::$bootstrapped = true;

			$includes_dir       = trailingslashit( dirname( __FILE__ ) );
			$this->plugin_dir   = trailingslashit( dirname( $includes_dir ) );
			$this->templates_dir= $this->plugin_dir . 'templates/';
			$this->assets_url   = trailingslashit( plugins_url( '../assets', __FILE__ ) );

			add_action( 'admin_menu',               [ $this, 'add_menu' ] );
			add_action( 'admin_enqueue_scripts',    [ $this, 'enqueue_admin_assets' ] );
			add_action( 'wp_ajax_wc_qc_admin_save', [ $this, 'ajax_save_settings' ] );
			
       // add_action('admin_menu', array($this, 'add_admin_menu'));
       // add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        //add_action('wp_ajax_wc_qc_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_wc_qc_get_product_settings', array($this, 'get_product_settings'));
        add_action('wp_ajax_wc_qc_save_product_settings', array($this, 'save_product_settings'));
        add_action('woocommerce_product_options_inventory_product_data', array($this, 'add_product_fields'));
        add_action('woocommerce_process_product_meta', array($this, 'save_product_fields'));
    
			
			
		}

		public function add_menu() {
			add_submenu_page(
				'woocommerce',
				__( 'Quantity Control', 'wc-quantity-control' ),
				__( 'Quantity Control', 'wc-quantity-control' ),
				self::CAPABILITY,
				self::MENU_SLUG,
				[ $this, 'render_page' ]
			);
		}

		public function enqueue_admin_assets( $hook ) {
			if ( $hook !== 'woocommerce_page_' . self::MENU_SLUG ) return;

			wp_enqueue_style( 'wc-qc-admin', $this->assets_url . 'css/admin-style.css', [], '3.0.0' );
			wp_enqueue_script( 'wc-qc-admin', $this->assets_url . 'js/admin-script.js', ['jquery'], '3.0.0', true );

			wp_localize_script( 'wc-qc-admin', 'WCQC', [
				'i18n' => [
					'saved'  => __( 'Settings saved.', 'wc-quantity-control' ),
					'failed' => __( 'Save failed.', 'wc-quantity-control' ),
					'error'  => __( 'Network error. Please try again.', 'wc-quantity-control' ),
				],
			] );
		}

		public function render_page() {
			if ( ! current_user_can( self::CAPABILITY ) ) {
				wp_die( esc_html__( 'You do not have permission to access this page.', 'wc-quantity-control' ) );
			}

			$settings = [
				// Global limits
				'enable_global' => get_option( 'wc_qc_enable_global_limits', 'yes' ) === 'yes',
				'no_max'        => get_option( 'wc_qc_no_max', 'no' ) === 'yes',
				'min'           => (int) get_option( 'wc_qc_global_min_quantity', 1 ),
				'max'           => (int) get_option( 'wc_qc_global_max_quantity', 999 ),
				// Messaging
				'show_message'  => get_option( 'wc_qc_show_quantity_message', 'yes' ) === 'yes',
				'message'       => (string) get_option( 'wc_qc_quantity_message', 'Quantity must be between {min} and {max}.' ),
				// Steps / Packages
				'enable_step'   => get_option( 'wc_qc_enable_step', 'no' ) === 'yes',
				'step'          => (int) get_option( 'wc_qc_global_step', 1 ),
				'enable_order_step' => get_option( 'wc_qc_enable_order_step', 'no' ) === 'yes',
				'order_step'        => (int) get_option( 'wc_qc_order_step', 0 ),
				'enable_packages'=> get_option( 'wc_qc_enable_packages', 'no' ) === 'yes',
				'packages'      => (string) get_option( 'wc_qc_allowed_packages', '' ),
			];

			$template = $this->templates_dir . 'admin-page.php';
			if ( file_exists( $template ) ) {
				include $template;
				return;
			}

			// Minimal fallback
			$nonce = wp_create_nonce( 'wc_qc_admin_save' ); ?>
			<div class="wrap">
				<h1><?php esc_html_e( 'Quantity Control', 'wc-quantity-control' ); ?></h1>
				<form id="wc-qc-admin-form">
					<input type="hidden" name="action" value="wc_qc_admin_save">
					<input type="hidden" name="nonce" value="<?php echo esc_attr( $nonce ); ?>">
					<p><label><input type="checkbox" name="enable_global" <?php checked($settings['enable_global']); ?>> <?php esc_html_e('Enable global limits','wc-quantity-control');?></label></p>
					<p><label><input type="checkbox" name="no_max" <?php checked($settings['no_max']); ?>> <?php esc_html_e('No maximum (infinite)','wc-quantity-control');?></label></p>
					<p><?php esc_html_e('Min','wc-quantity-control');?> <input type="number" name="min" min="1" value="<?php echo esc_attr($settings['min']); ?>"> &nbsp;
					   <?php esc_html_e('Max','wc-quantity-control');?> <input type="number" name="max" min="1" value="<?php echo esc_attr($settings['max']); ?>"></p>
					<hr>
					<p><label><input type="checkbox" name="show_message" <?php checked($settings['show_message']); ?>> <?php esc_html_e('Show message','wc-quantity-control');?></label></p>
					<p><textarea name="message" rows="3" class="large-text"><?php echo esc_textarea($settings['message']); ?></textarea></p>
					<hr>
					<p><label><input type="checkbox" name="enable_step" <?php checked($settings['enable_step']); ?>> <?php esc_html_e('Enable per-item step','wc-quantity-control');?></label>
					<input type="number" name="step" min="1" value="<?php echo esc_attr(max(1,$settings['step'])); ?>" style="width:90px"></p>
					<p><label><input type="checkbox" name="enable_order_step" <?php checked($settings['enable_order_step']); ?>> <?php esc_html_e('Enable cart-level (order) step','wc-quantity-control');?></label>
					<input type="number" name="order_step" min="1" value="<?php echo esc_attr(max(0,$settings['order_step'])); ?>" style="width:90px"></p>
					<p><label><input type="checkbox" name="enable_packages" <?php checked($settings['enable_packages']); ?>> <?php esc_html_e('Enable fixed package sizes (comma-separated)','wc-quantity-control');?></label></p>
					<p><input type="text" name="packages" value="<?php echo esc_attr($settings['packages']); ?>" class="regular-text" placeholder="100,250,500"></p>
					<p><button class="button button-primary" id="wc-qc-save"><?php esc_html_e('Save changes','wc-quantity-control');?></button></p>
				</form>
			</div>
			<?php
		}

		public function ajax_save_settings() {
			if ( ! current_user_can( self::CAPABILITY ) ) {
				wp_send_json_error( __( 'Permission denied.', 'wc-quantity-control' ), 403 );
			}
			check_ajax_referer( 'wc_qc_admin_save', 'nonce' );

			// Global limits
			$enable_global = isset($_POST['enable_global']) && $_POST['enable_global']==='yes' ? 'yes':'no';
			$no_max        = isset($_POST['no_max']) && $_POST['no_max']==='yes' ? 'yes':'no';
			$min = isset($_POST['min']) ? max(1, intval($_POST['min'])) : 1;
			$max = isset($_POST['max']) ? max(1, intval($_POST['max'])) : 999;
			if ( $no_max === 'yes' ) { /* keep max saved, but validator ignores it */ }

			// Messaging
			$show_message = isset($_POST['show_message']) && $_POST['show_message']==='yes' ? 'yes':'no';
			$message = isset($_POST['message']) ? wp_kses_post( wp_unslash($_POST['message']) ) : 'Quantity must be between {min} and {max}.';

			// Steps / Packages
			$enable_step = isset($_POST['enable_step']) && $_POST['enable_step']==='yes' ? 'yes':'no';
			$step = isset($_POST['step']) ? max(1, intval($_POST['step'])) : 1;

			$enable_order_step = isset($_POST['enable_order_step']) && $_POST['enable_order_step']==='yes' ? 'yes':'no';
			$order_step = isset($_POST['order_step']) ? max(1, intval($_POST['order_step'])) : 0;

			$enable_packages = isset($_POST['enable_packages']) && $_POST['enable_packages']==='yes' ? 'yes':'no';
			$packages_raw = isset($_POST['packages']) ? sanitize_text_field( wp_unslash($_POST['packages']) ) : '';
			$packages_arr = [];
			if ( $packages_raw !== '' ) {
				foreach ( explode(',', $packages_raw) as $q ) {
					$q = (int) trim($q);
					if ($q > 0) $packages_arr[$q] = $q;
				}
			}
			$packages_clean = implode(',', array_values($packages_arr));

			// Save
			update_option( 'wc_qc_enable_global_limits', $enable_global );
			update_option( 'wc_qc_no_max', $no_max );
			update_option( 'wc_qc_global_min_quantity', $min );
			update_option( 'wc_qc_global_max_quantity', $max );

			update_option( 'wc_qc_show_quantity_message', $show_message );
			update_option( 'wc_qc_quantity_message', $message );

			update_option( 'wc_qc_enable_step', $enable_step );
			update_option( 'wc_qc_global_step', $step );

			update_option( 'wc_qc_enable_order_step', $enable_order_step );
			update_option( 'wc_qc_order_step', $order_step );

			update_option( 'wc_qc_enable_packages', $enable_packages );
			update_option( 'wc_qc_allowed_packages', $packages_clean );

			wp_send_json_success([
				'min'=>$min,'max'=>$max,'step'=>$step,'order_step'=>$order_step,'packages'=>$packages_clean
			]);
		}
		
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
}
