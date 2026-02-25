<?php
/**
 * Plugin Name: Yaay365 Sync
 * Plugin URI: https://yaay365.com
 * Description: Syncs WooCommerce products to Yaay365 Catalogue.
 * Version: 1.0.5
 * Author: Yaay365
 * Author URI: https://yaay365.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: yaay365-sync
 * Domain Path: /languages
 * Requires at least: 5.8
 * Requires PHP: 7.4
 * WC requires at least: 5.0
 * WC tested up to: 8.5
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('YAAY365_SYNC_VERSION', '1.0.5');
define('YAAY365_SYNC_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('YAAY365_SYNC_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YAAY365_SYNC_PLUGIN_FILE', __FILE__);
define('YAAY365_SYNC_API_URL', 'https://api.yaay365.com');

// Check if WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>' . __('Yaay365 Sync requires WooCommerce to be installed and active.', 'yaay365-sync') . '</p></div>';
    });
    return;
}

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'Yaay365\\Sync\\';
    $base_dir = YAAY365_SYNC_PLUGIN_DIR . 'includes/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Migrate old settings to new format
function yaay365_sync_migrate() {
    $migrated_version = get_option('yaay365_sync_migrated_version', '0');

    // Migration: switch from email/password/company_id to API key auth
    if (version_compare($migrated_version, '1.0.5', '<')) {
        // Force the endpoint to the API-key authenticated route
        $old_endpoints = ['/api/catalogues/sync', '/catalogues/sync', '/v1/catalogues/sync'];
        update_option('yaay365_sync_sync_endpoint', '/v1/catalogues/sync');
        update_option('yaay365_sync_api_url', 'https://api.yaay365.com');

        // Ensure new keys exist (won't overwrite if already set)
        add_option('yaay365_sync_public_key', '');
        add_option('yaay365_sync_secret_key', '');

        update_option('yaay365_sync_migrated_version', '1.0.5');
    }
}

// Initialize the plugin
function yaay365_sync_init() {
    yaay365_sync_migrate();
    $plugin = Yaay365\Sync\Plugin::get_instance();
}
add_action('plugins_loaded', 'yaay365_sync_init');

// Activation hook
register_activation_hook(__FILE__, function() {
    // Create options with default values
    add_option('yaay365_sync_api_url', 'https://api.yaay365.com');
    add_option('yaay365_sync_sync_endpoint', '/v1/catalogues/sync');
    add_option('yaay365_sync_public_key', '');
    add_option('yaay365_sync_secret_key', '');
    add_option('yaay365_sync_auto_sync', 'no');
    add_option('yaay365_sync_sync_on_save', 'yes');
    add_option('yaay365_sync_log_enabled', 'yes');
    
    // Schedule cron job if auto sync is enabled
    if (!wp_next_scheduled('yaay365_sync_cron')) {
        wp_schedule_event(time(), 'hourly', 'yaay365_sync_cron');
    }
});

// Deactivation hook
register_deactivation_hook(__FILE__, function() {
    wp_clear_scheduled_hook('yaay365_sync_cron');
});
