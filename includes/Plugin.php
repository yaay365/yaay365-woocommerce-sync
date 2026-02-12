<?php

namespace Yaay365\Sync;

class Plugin
{
    private static $instance = null;
    
    private $api_client;
    private $product_sync;
    private $admin;
    private $logger;

    public static function get_instance()
    {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        $this->logger = new Logger();
        $this->api_client = new ApiClient($this->logger);
        $this->product_sync = new ProductSync($this->api_client, $this->logger);
        $this->admin = new Admin($this->product_sync, $this->logger);

        $this->init_hooks();
    }

    private function init_hooks()
    {
        // Admin hooks
        add_action('admin_menu', [$this->admin, 'add_menu_pages']);
        add_action('admin_init', [$this->admin, 'register_settings']);
        add_action('admin_enqueue_scripts', [$this->admin, 'enqueue_scripts']);

        // Product sync hooks
        add_action('woocommerce_update_product', [$this->product_sync, 'sync_product_on_save'], 10, 1);
        add_action('woocommerce_new_product', [$this->product_sync, 'sync_product_on_save'], 10, 1);
        
        // Bulk sync action
        add_action('admin_action_yaay365_bulk_sync', [$this->product_sync, 'bulk_sync_all_products']);
        
        // Cron job for auto sync
        add_action('yaay365_sync_cron', [$this->product_sync, 'cron_sync_products']);
        
        // AJAX handlers
        add_action('wp_ajax_yaay365_test_connection', [$this->admin, 'ajax_test_connection']);
        add_action('wp_ajax_yaay365_manual_sync', [$this->admin, 'ajax_manual_sync']);
        add_action('wp_ajax_yaay365_view_logs', [$this->admin, 'ajax_view_logs']);
        add_action('wp_ajax_yaay365_clear_logs', [$this->admin, 'ajax_clear_logs']);
    }

    public function get_api_client()
    {
        return $this->api_client;
    }

    public function get_product_sync()
    {
        return $this->product_sync;
    }

    public function get_logger()
    {
        return $this->logger;
    }
}
