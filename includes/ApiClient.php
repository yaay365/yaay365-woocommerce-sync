<?php

namespace Yaay365\Sync;

class ApiClient
{
    private $api_url;
    private $sync_endpoint;
    private $public_key;
    private $secret_key;
    private $logger;
    private $ssl_verify;

    public function __construct($logger)
    {
        $this->logger = $logger;
        $this->load_settings();
    }

    private function load_settings()
    {
        $site_url = site_url();
        $is_local = false;

        $local_indicators = ['.test', '.local', 'localhost', '127.0.0.1'];
        foreach ($local_indicators as $indicator) {
            if (strpos($site_url, $indicator) !== false) {
                $is_local = true;
                break;
            }
        }

        $this->api_url = 'https://api.yaay365.com';

        if ($is_local) {
            $this->ssl_verify = false;
        } else {
            $this->ssl_verify = true;
        }

        $this->sync_endpoint = '/v1/partner/catalogues/sync';

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
            'sslverify' => $this->ssl_verify,
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

        $deal_id = get_option('yaay365_sync_company_deal');
        if (!empty($deal_id)) {
            $body['deal_id'] = intval($deal_id);
        }

        $this->logger->log('Syncing ' . count($products) . ' products to ' . $endpoint, 'info');

        $response = wp_remote_post($endpoint, [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
                'X-Public-Key' => $this->public_key,
                'X-Secret-Key' => $this->secret_key,
            ],
            'sslverify' => $this->ssl_verify,
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

    /**
     * Retrieve company/partner details and statistics.
     */
    public function get_partner_info()
    {
        if (!$this->is_configured()) {
            return [
                'success' => false,
                'message' => __('API is not configured. Please enter your Public Key and Secret Key.', 'yaay365-sync')
            ];
        }

        $endpoint = rtrim($this->api_url, '/') . '/v1/partner/info';

        $response = wp_remote_get($endpoint, [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json',
                'X-Public-Key'  => $this->public_key,
                'X-Secret-Key'  => $this->secret_key,
            ],
            'sslverify' => $this->ssl_verify,
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

        if ($status_code >= 200 && $status_code < 300) {
            return $decoded;
        }

        $error_msg = !empty($decoded['message']) ? $decoded['message'] : sprintf('HTTP %d', $status_code);
        return [
            'success' => false,
            'message' => sprintf(__('Failed to retrieve company info: %s', 'yaay365-sync'), $error_msg)
        ];
    }
}
