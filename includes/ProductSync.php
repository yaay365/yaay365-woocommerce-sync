<?php

namespace Yaay365\Sync;

class ProductSync
{
    private $api_client;
    private $logger;

    public function __construct($api_client, $logger)
    {
        $this->api_client = $api_client;
        $this->logger = $logger;
    }

    /**
     * Sync a single product when it's saved
     */
    public function sync_product_on_save($product_id)
    {
        // Check if sync on save is enabled
        if (get_option('yaay365_sync_sync_on_save', 'yes') !== 'yes') {
            return;
        }

        $product = wc_get_product($product_id);
        if (!$product) {
            return;
        }

        // Only sync published products
        if ($product->get_status() !== 'publish') {
            return;
        }

        $formatted_product = $this->format_product($product);
        $this->api_client->sync_products([$formatted_product]);
    }

    /**
     * Sync all products (bulk sync)
     */
    public function bulk_sync_all_products()
    {
        // Get all published products
        $products = wc_get_products([
            'limit' => -1,
            'status' => 'publish'
        ]);

        if (empty($products)) {
            $this->logger->log('No products found to sync', 'warning');
            wp_redirect(add_query_arg(['page' => 'yaay365-sync', 'synced' => '0'], admin_url('admin.php')));
            exit;
        }

        $formatted_products = array_map([$this, 'format_product'], $products);
        $result = $this->api_client->sync_products($formatted_products);

        $redirect_args = [
            'page' => 'yaay365-sync',
            'synced' => $result['success'] ? '1' : '0'
        ];

        if (isset($result['data'])) {
            $redirect_args['created'] = $result['data']['created'] ?? 0;
            $redirect_args['updated'] = $result['data']['updated'] ?? 0;
            $redirect_args['failed'] = $result['data']['failed'] ?? 0;
        }

        wp_redirect(add_query_arg($redirect_args, admin_url('admin.php')));
        exit;
    }

    /**
     * Cron job to sync products
     */
    public function cron_sync_products()
    {
        if (get_option('yaay365_sync_auto_sync', 'no') !== 'yes') {
            return;
        }

        $products = wc_get_products([
            'limit' => -1,
            'status' => 'publish'
        ]);

        if (empty($products)) {
            return;
        }

        $formatted_products = array_map([$this, 'format_product'], $products);
        $this->api_client->sync_products($formatted_products);
    }

    /**
     * Format a WooCommerce product for the API
     */
    public function format_product($product)
    {
        // Get main image
        $image_url = null;
        $image_id = $product->get_image_id();
        if ($image_id) {
            $image_url = wp_get_attachment_url($image_id);
        }

        // Get gallery images
        $images = [];
        $gallery_ids = $product->get_gallery_image_ids();
        if ($image_id) {
            array_unshift($gallery_ids, $image_id);
        }
        foreach ($gallery_ids as $img_id) {
            $img_url = wp_get_attachment_url($img_id);
            if ($img_url) {
                $images[] = ['src' => $img_url];
            }
        }

        // Get categories
        $categories = [];
        $category_terms = get_the_terms($product->get_id(), 'product_cat');
        if ($category_terms && !is_wp_error($category_terms)) {
            foreach ($category_terms as $term) {
                $categories[] = ['name' => $term->name];
            }
        }

        // Get currency
        $currency = get_woocommerce_currency();

        return [
            'name' => $product->get_name(),
            'sku' => $product->get_sku(),
            'description' => $product->get_description() ?: $product->get_short_description(),
            'regular_price' => $product->get_regular_price(),
            'sale_price' => $product->get_sale_price(),
            'stock_quantity' => $product->get_stock_quantity(),
            'status' => $product->get_status(),
            'featured' => $product->get_featured(),
            'images' => $images,
            'categories' => $categories,
            'currency' => $currency
        ];
    }
}
