<?php

namespace Yaay365\Sync;

class Admin
{
    private $product_sync;
    private $logger;

    public function __construct($product_sync, $logger)
    {
        $this->product_sync = $product_sync;
        $this->logger = $logger;
    }

    public function add_menu_pages()
    {
        add_menu_page(
            __('Yaay365 Sync', 'yaay365-sync'),
            __('Yaay365 Sync', 'yaay365-sync'),
            'manage_options',
            'yaay365-sync',
            [$this, 'render_main_page'],
            'dashicons-update',
            56
        );

        add_submenu_page(
            'yaay365-sync',
            __('Settings', 'yaay365-sync'),
            __('Settings', 'yaay365-sync'),
            'manage_options',
            'yaay365-sync-settings',
            [$this, 'render_settings_page']
        );

        add_submenu_page(
            'yaay365-sync',
            __('Logs', 'yaay365-sync'),
            __('Logs', 'yaay365-sync'),
            'manage_options',
            'yaay365-sync-logs',
            [$this, 'render_logs_page']
        );
    }

    public function register_settings()
    {
        register_setting('yaay365_sync_settings', 'yaay365_sync_api_url');
        register_setting('yaay365_sync_settings', 'yaay365_sync_sync_endpoint');
        register_setting('yaay365_sync_settings', 'yaay365_sync_auth_email');
        register_setting('yaay365_sync_settings', 'yaay365_sync_auth_password');
        register_setting('yaay365_sync_settings', 'yaay365_sync_company_id');
        register_setting('yaay365_sync_settings', 'yaay365_sync_auto_sync');
        register_setting('yaay365_sync_settings', 'yaay365_sync_sync_on_save');
        register_setting('yaay365_sync_settings', 'yaay365_sync_log_enabled');
    }

    public function enqueue_scripts($hook)
    {
        if (strpos($hook, 'yaay365-sync') === false) {
            return;
        }

        wp_enqueue_style(
            'yaay365-sync-admin',
            YAAY365_SYNC_PLUGIN_URL . 'assets/css/admin.css',
            [],
            YAAY365_SYNC_VERSION
        );

        wp_enqueue_script(
            'yaay365-sync-admin',
            YAAY365_SYNC_PLUGIN_URL . 'assets/js/admin.js',
            ['jquery'],
            YAAY365_SYNC_VERSION,
            true
        );

        wp_localize_script('yaay365-sync-admin', 'yaay365Sync', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yaay365_sync_nonce'),
            'strings' => [
                'testing' => __('Testing connection...', 'yaay365-sync'),
                'syncing' => __('Syncing products...', 'yaay365-sync'),
                'confirmSync' => __('Are you sure you want to sync all products?', 'yaay365-sync'),
                'confirmClearLogs' => __('Are you sure you want to clear all logs?', 'yaay365-sync'),
            ]
        ]);
    }

    public function render_main_page()
    {
        include YAAY365_SYNC_PLUGIN_DIR . 'templates/admin-main.php';
    }

    public function render_settings_page()
    {
        include YAAY365_SYNC_PLUGIN_DIR . 'templates/admin-settings.php';
    }

    public function render_logs_page()
    {
        include YAAY365_SYNC_PLUGIN_DIR . 'templates/admin-logs.php';
    }

    public function ajax_test_connection()
    {
        check_ajax_referer('yaay365_sync_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'yaay365-sync')]);
        }

        $api_client = Plugin::get_instance()->get_api_client();
        $result = $api_client->test_connection();

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    public function ajax_manual_sync()
    {
        check_ajax_referer('yaay365_sync_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'yaay365-sync')]);
        }

        $products = wc_get_products([
            'limit' => -1,
            'status' => 'publish'
        ]);

        if (empty($products)) {
            wp_send_json_error(['message' => __('No products found to sync', 'yaay365-sync')]);
        }

        $formatted_products = array_map([$this->product_sync, 'format_product'], $products);
        $api_client = Plugin::get_instance()->get_api_client();
        $result = $api_client->sync_products($formatted_products);

        if ($result['success']) {
            wp_send_json_success($result);
        } else {
            wp_send_json_error($result);
        }
    }

    public function ajax_view_logs()
    {
        check_ajax_referer('yaay365_sync_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'yaay365-sync')]);
        }

        $logs = $this->logger->get_logs(100);
        wp_send_json_success(['logs' => $logs]);
    }

    public function ajax_clear_logs()
    {
        check_ajax_referer('yaay365_sync_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_send_json_error(['message' => __('Unauthorized', 'yaay365-sync')]);
        }

        $this->logger->clear_logs();
        wp_send_json_success(['message' => __('Logs cleared successfully', 'yaay365-sync')]);
    }
}
