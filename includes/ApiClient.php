<?php

namespace Yaay365\Sync;

class ApiClient
{
    private $api_url;
    private $sync_endpoint;
    private $auth_email;
    private $auth_password;
    private $company_id;
    private $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
        $this->load_settings();
    }

    private function load_settings()
    {
        $this->api_url = get_option('yaay365_sync_api_url', 'https://yaay365.com');
        $this->sync_endpoint = get_option('yaay365_sync_sync_endpoint', '/api/catalogues/sync');
        $this->auth_email = get_option('yaay365_sync_auth_email');
        $this->auth_password = get_option('yaay365_sync_auth_password');
        $this->company_id = get_option('yaay365_sync_company_id');
    }

    public function is_configured()
    {
        return !empty($this->api_url) && !empty($this->auth_email) && !empty($this->auth_password) && !empty($this->company_id);
    }

    public function test_connection()
    {
        if (!$this->is_configured()) {
            return [
                'success' => false,
                'message' => __('API is not configured. Please enter Email, Password, and Company ID.', 'yaay365-sync')
            ];
        }

        // Try multiple endpoint variations
        $endpoints_to_try = [
            rtrim($this->api_url, '/') . $this->sync_endpoint,
            rtrim($this->api_url, '/') . '/catalogues/sync',
            rtrim($this->api_url, '/') . '/api/catalogues/sync',
        ];
        
        // Remove duplicates
        $endpoints_to_try = array_unique($endpoints_to_try);
        
        $test_results = [];
        
        foreach ($endpoints_to_try as $endpoint) {
            // Send a minimal valid product so Laravel validation passes (avoids 422)
            $response = wp_remote_post($endpoint, [
                'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
                'body' => json_encode([
                    'company_id' => (int) $this->company_id,
                    'auth_email' => $this->auth_email,
                    'auth_password' => $this->auth_password,
                    'products' => [
                        [
                            'name' => 'Connection Test Product',
                            'sku' => 'YAAY365-CONN-TEST'
                        ]
                    ]
                ]),
                'timeout' => 10
            ]);

            if (is_wp_error($response)) {
                $test_results[] = [
                    'endpoint' => $endpoint,
                    'status' => 'error',
                    'message' => $response->get_error_message()
                ];
                continue;
            }

            $status_code = wp_remote_retrieve_response_code($response);
            $body_content = wp_remote_retrieve_body($response);
            $decoded = json_decode($body_content, true);
            
            if ($status_code >= 200 && $status_code < 300) {
                // Success! Update the endpoint setting
                update_option('yaay365_sync_sync_endpoint', str_replace(rtrim($this->api_url, '/'), '', $endpoint));
                
                return [
                    'success' => true,
                    'message' => sprintf(__('Connection successful! Using endpoint: %s', 'yaay365-sync'), $endpoint)
                ];
            }
            
            $error_msg = isset($decoded['message']) ? $decoded['message'] : 'Unknown error';
            $test_results[] = [
                'endpoint' => $endpoint,
                'status' => $status_code,
                'message' => $error_msg
            ];
        }

        // All endpoints failed, return detailed info
        $error_message = __('All endpoints failed. Tested:', 'yaay365-sync') . '<br>';
        foreach ($test_results as $result) {
            $error_message .= sprintf(
                '<br>• %s → %s: %s',
                $result['endpoint'],
                $result['status'],
                $result['message']
            );
        }
        
        $error_message .= '<br><br>' . __('Please check: 1) Laravel route is in routes/api.php or routes/web.php, 2) Run "php artisan route:clear" on server, 3) Check CORS settings', 'yaay365-sync');

        return [
            'success' => false,
            'message' => $error_message
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
            'company_id' => (int) $this->company_id,
            'auth_email' => $this->auth_email,
            'auth_password' => $this->auth_password,
            'products' => $products
        ];

        $this->logger->log('Syncing ' . count($products) . ' products to ' . $endpoint, 'info');

        $response = wp_remote_post($endpoint, [
            'headers' => ['Content-Type' => 'application/json', 'Accept' => 'application/json'],
            'body' => json_encode($body),
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
