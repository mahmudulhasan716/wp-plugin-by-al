<?php
/**
 * Plugin Name: wp-plugin-by-al
 * Plugin URI: https://yourwebsite.com
 * Description: Adds Business License ID field to Dokan vendor registration and management
 * Version: 1.0.0
 * Author: Mamun
 * Author URI: https://yourwebsite.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: dokan-business-license
 * Domain Path: /languages
 * Requires at least: 5.0
 * Tested up to: 6.4
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 * WooCommerce: true
 * Dokan: true
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('DOKAN_BUSINESS_LICENSE_VERSION', '1.0.0');
define('DOKAN_BUSINESS_LICENSE_PLUGIN_FILE', __FILE__);
define('DOKAN_BUSINESS_LICENSE_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('DOKAN_BUSINESS_LICENSE_PLUGIN_URL', plugin_dir_url(__FILE__));

// Initialize the plugin
require_once DOKAN_BUSINESS_LICENSE_PLUGIN_DIR . 'includes/WpPluginByAl.php';

new WpPluginByAl();