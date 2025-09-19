<?php
/**
 * Main plugin class for Dokan Business License Manager
 *
 * @package Dokan_Business_License
 * @since 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

require_once DOKAN_BUSINESS_LICENSE_PLUGIN_DIR . 'includes/BusinessLicense.php';

class WpPluginByAl {

    /**
     * Plugin instance
     *
     * @var Dokan_Business_License
     */
    private static $instance = null;

    /**
     * Get plugin instance
     *
     * @return Dokan_Business_License
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
    public function __construct() {
        $this->init_hooks();
        $this->load_dependencies();
    }

    /**
     * Initialize hooks
     */
    private function init_hooks() {
        // Initialize after WordPress is fully loaded
        add_action('init', array($this, 'init'), 20);
        add_action('plugins_loaded', array($this, 'init_classes'), 20);
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_action('admin_enqueue_scripts', array($this, 'admin_enqueue_scripts'));
        
        // Activation/Deactivation hooks
        register_activation_hook(DOKAN_BUSINESS_LICENSE_PLUGIN_FILE, array($this, 'activate'));
        register_deactivation_hook(DOKAN_BUSINESS_LICENSE_PLUGIN_FILE, array($this, 'deactivate'));
    }

    public function init_classes() {
        new BusinessLicense();
    }

    /**
     * Load plugin dependencies
     */
    private function load_dependencies() {
        // No additional dependencies needed for basic functionality
    }

    /**
     * Initialize plugin
     */
    public function init() {
        // Load text domain
        load_plugin_textdomain('dokan-business-license', false, dirname(plugin_basename(DOKAN_BUSINESS_LICENSE_PLUGIN_FILE)) . '/languages');
        
        // Initialize frontend and admin classes
        // Classes will be loaded when needed
        
        // Localize script for AJAX
        add_action('wp_enqueue_scripts', array($this, 'localize_scripts'));
    }

    /**
     * Enqueue frontend scripts and styles
     */
    public function enqueue_scripts() {
        if (dokan_is_store_page() || dokan_is_store_review_page() || dokan_is_seller_dashboard()) {
            wp_enqueue_style(
                'dokan-business-license-style',
                DOKAN_BUSINESS_LICENSE_PLUGIN_URL . 'assets/css/frontend.css',
                array(),
                DOKAN_BUSINESS_LICENSE_VERSION
            );
        }
    }

    /**
     * Enqueue admin scripts and styles
     */
    public function admin_enqueue_scripts() {
        wp_enqueue_style(
            'dokan-business-license-admin-style',
            DOKAN_BUSINESS_LICENSE_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            DOKAN_BUSINESS_LICENSE_VERSION
        );
    }

    /**
     * Localize frontend scripts (safe no-op if no script handle exists)
     */
    public function localize_scripts() {
        // Add localization here when a frontend script handle is available.
        // Example (when you register/enqueue a JS file):
        // wp_localize_script('your-handle', 'DokanBL', array('ajaxUrl' => admin_url('admin-ajax.php')));
    }

    /**
     * Plugin activation
     */
    public function activate() {
    }

    /**
     * Plugin deactivation
     */
    public function deactivate() {
        // Clean up if needed
    }

}

