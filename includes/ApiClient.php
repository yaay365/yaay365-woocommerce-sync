<?php

namespace Yaay365\Sync;

class ApiClient
{
    private $api_url;
    private $sync_endpoint;
    private $public_key;
    private $secret_key;
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
        $this->load_settings();
    }

    private function load_settings()
    {
        $this->api_url = get_option('yaay365_sync_api_url', 'https://api.yaay365.com');

        // Always use the API-key endpoint; self-heal any stale stored value
        $stored_endpoint = get_option('yaay365_sync_sync_endpoint', '/v1/catalogues/sync');
        $legacy_endpoints = ['/api/catalogues/sync', '/catalogues/sync'];
        if (in_array($stored_endpoint, $legacy_endpoints, true)) {
            $stored_endpoint = '/v1/catalogues/sync';
            update_option('yaay365_sync_sync_endpoint', $stored_endpoint);
        }
        $this->sync_endpoint = $stored_endpoint;

        $this->public_key = get_option('yaay365_sync_public_key');
        $this->secret_key = get_option('yaay365_sync_secret_key');
    }

    public function is_configured()
    {
        return !empty($this->api_url) && !empty($this->public_key) && !empty($this->secret_key);
    }

    public function test_connection()
    {
        if (!$this->is_configured()) {
            return [
                'success' => false,
                'message' => __('API is not configured. Please enter your Public Key and Secret Key.', 'yaay365-sync')
            ];
        }

        $endpoint = rtrim($this->api_url, '/') . $this->sync_endpoint;

        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'X-Public-Key'  => $this->public_key,
                'X-Secret-Key'  => $this->secret_key,
            ],
            // Send one minimal product — empty array causes a 500 server-side.
            // Using a fixed SKU means it upserts the same record every time.
            'body'    => json_encode([
                'products' => [
                    [
                        'name'   => '[Yaay365 Connection Test]',
                        'sku'    => 'YAAY365-CONN-TEST',
                        'status' => 'draft',
                    ],
                ],
            ]),
            'timeout' => 15,
        ]);

        if (is_wp_error($response)) {
            return [
                'success' => false,
                'message' => $response->get_error_message()
            ];
        }

        $status_code  = wp_remote_retrieve_response_code($response);
        $body_content = wp_remote_retrieve_body($response);
        $decoded      = json_decode($body_content, true);

        // 2xx  → full success
        // 422  → authenticated but validation rejected our empty payload — still proves keys work
        if ($status_code >= 200 && $status_code < 300) {
            return [
                'success' => true,
                'message' => sprintf(__('Connection successful! Endpoint: %s', 'yaay365-sync'), $endpoint),
            ];
        }

        if ($status_code === 422) {
            return [
                'success' => true,
                'message' => sprintf(
                    __('Connection successful! API keys authenticated. (Endpoint: %s)', 'yaay365-sync'),
                    $endpoint
                ),
            ];
        }

        // Build a useful error message from whatever the server returned.
        if (!empty($decoded['message'])) {
            $error_msg = $decoded['message'];
        } elseif (!empty($decoded['error'])) {
            $error_msg = $decoded['error'];
        } else {
            // Fall back to the raw body (truncated) so the user can see what happened.
            $error_msg = $body_content ? substr(wp_strip_all_tags($body_content), 0, 300) : sprintf('HTTP %d', $status_code);
        }

        // Append errors detail if present (e.g. Laravel validation bag).
        if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
            $detail = [];
            foreach ($decoded['errors'] as $field => $messages) {
                $detail[] = $field . ': ' . (is_array($messages) ? implode(', ', $messages) : $messages);
            }
            $error_msg .= ' — ' . implode(' | ', $detail);
        }

        $error_msg = sprintf('HTTP %d — %s', $status_code, $error_msg);

        return [
            'success' => false,
            'message' => sprintf(__('Connection failed: %s', 'yaay365-sync'), $error_msg),
        ];
    }

    public function sync_products($products)
    {
        if (!$this->is_configured()) {
            $this->logger->log('Sync failed: API not configured', 'error');
            return [
                'success' => false,
                'message' => __('API is not configured.', 'yaay365-sync')
            ];
        }

        $endpoint = rtrim($this->api_url, '/') . $this->sync_endpoint;

        $body = [
            'products' => $products
        ];

        $this->logger->log('Syncing ' . count($products) . ' products to ' . $endpoint, 'info');

        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'X-Public-Key' => $this->public_key,
                'X-Secret-Key' => $this->secret_key,
            ],
            'body'    => json_encode($body),
            'timeout' => 60
        ]);

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            $this->logger->log('Sync failed: ' . $error_message, 'error');
            return [
                'success' => false,
                'message' => $error_message
            ];
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if ($status_code >= 200 && $status_code < 300) {
            $message = sprintf(
                __('Sync completed: %d synced (%d created, %d updated, %d failed)', 'yaay365-sync'),
                $data['synced'] ?? 0,
                $data['created'] ?? 0,
                $data['updated'] ?? 0,
                $data['failed'] ?? 0
            );
            
            $this->logger->log($message, 'success');
            
            if (!empty($data['errors'])) {
                foreach ($data['errors'] as $error) {
                    $this->logger->log(
                        sprintf('Product "%s" failed: %s', $error['product_name'], $error['error']),
                        'warning'
                    );
                }
            }

            return [
                'success' => true,
                'message' => $message,
                'data' => $data
            ];
        }

        $error_message = isset($data['message']) ? $data['message'] : sprintf(__('Sync failed with status code: %d', 'yaay365-sync'), $status_code);
        $this->logger->log('Sync failed: ' . $error_message, 'error');

        return [
            'success' => false,
            'message' => $error_message,
            'data' => $data
        ];
    }
}
