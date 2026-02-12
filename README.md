# Yaay365 Sync - WooCommerce Product Synchronization Plugin

A WordPress plugin to sync WooCommerce products to the Yaay365 catalogue system via REST API using your Yaay365 account credentials (email and password).

## Features

- ✅ **Automatic Sync** - Products sync automatically when saved/updated
- ✅ **Bulk Sync** - Sync all products with one click
- ✅ **Scheduled Sync** - Hourly automatic synchronization
- ✅ **Connection Testing** - Test API connection before syncing
- ✅ **Comprehensive Logging** - Monitor all sync activities
- ✅ **Error Handling** - Detailed error messages and recovery
- ✅ **Category Mapping** - Automatic category creation and mapping
- ✅ **Image Support** - Sync product images and gallery
- ✅ **Stock Management** - Keep stock levels in sync
- ✅ **Price Sync** - Regular and sale prices

## Requirements

- WordPress 5.8+
- WooCommerce 5.0+
- PHP 7.4+
- Valid Yaay365 account (email and password)

## Installation

1. Upload the plugin folder to `/wp-content/plugins/yaay365-sync/`
2. Activate the plugin through the WordPress 'Plugins' menu
3. Navigate to **Yaay365 Sync > Settings**
4. Configure your email, password, and company ID
5. Test the connection
6. Start syncing!

## Configuration

### API Settings

Navigate to **Yaay365 Sync > Settings** and configure:

- **API URL**: Automatically set to `https://yaay365.com`
- **Email**: Your Yaay365 account email
- **Password**: Your Yaay365 account password
- **Company ID**: Your company ID in the Yaay365 system

### Sync Options

- **Sync on Save**: Automatically sync products when created/updated
- **Auto Sync (Cron)**: Enable hourly automatic synchronization
- **Logging**: Enable/disable activity logging

## Usage

### Manual Sync

1. Go to **Yaay365 Sync** dashboard
2. Click **Sync All Products Now**
3. Wait for the sync to complete
4. Review results and check logs if needed

### Automatic Sync

Enable "Sync on Save" in settings. Products will automatically sync when:
- A new product is created
- An existing product is updated
- Product status changes to "published"

### Scheduled Sync

Enable "Auto Sync" in settings to run hourly synchronization automatically via WordPress cron.

### View Logs

Navigate to **Yaay365 Sync > Logs** to:
- View recent sync activities
- Monitor errors and warnings
- Clear old logs

## API Format

The plugin sends data to your API endpoint (`/api/catalogues/sync`) in this format:

```json
{
  "company_id": 1,
  "auth_email": "user@example.com",
  "auth_password": "your-password",
  "products": [
    {
      "name": "Product Name",
      "sku": "PROD-001",
      "description": "Product description",
      "regular_price": "99.99",
      "sale_price": "79.99",
      "stock_quantity": 100,
      "status": "publish",
      "featured": true,
      "images": [
        {"src": "https://example.com/image.jpg"}
      ],
      "categories": [
        {"name": "Category Name"}
      ],
      "currency": "USD"
    }
  ]
}
```

## Expected API Response

Your API should return:

```json
{
  "success": true,
  "synced": 10,
  "created": 5,
  "updated": 5,
  "failed": 0,
  "errors": []
}
```

## Troubleshooting

### Connection Test Fails

- API URL is automatically set to https://yaay365.com
- Check email and password are correct
- Ensure company ID exists
- Check server can make outbound HTTP requests

### Products Not Syncing

- Ensure "Sync on Save" is enabled
- Check product status is "published"
- Review logs for error messages
- Test API connection

### Cron Not Running

- Verify "Auto Sync" is enabled
- Check WordPress cron is working: `wp cron event list`
- Manually trigger: `wp cron event run yaay365_sync_cron`

## Development

### File Structure

```
yaay365-sync/
├── assets/
│   ├── css/
│   │   └── admin.css
│   └── js/
│       └── admin.js
├── includes/
│   ├── Admin.php
│   ├── ApiClient.php
│   ├── Logger.php
│   ├── Plugin.php
│   └── ProductSync.php
├── templates/
│   ├── admin-logs.php
│   ├── admin-main.php
│   └── admin-settings.php
├── yaay365-sync.php
├── readme.txt
└── README.md
```

### Hooks & Filters

**Actions:**
- `yaay365_sync_before_sync` - Before syncing products
- `yaay365_sync_after_sync` - After syncing products
- `yaay365_sync_product_formatted` - After formatting a product

**Filters:**
- `yaay365_sync_product_data` - Modify product data before sending
- `yaay365_sync_api_headers` - Modify API request headers
- `yaay365_sync_api_timeout` - Change API timeout (default: 60s)

### Extending

```php
// Modify product data before sync
add_filter('yaay365_sync_product_data', function($product_data, $wc_product) {
    // Add custom fields
    $product_data['custom_field'] = get_post_meta($wc_product->get_id(), 'custom_field', true);
    return $product_data;
}, 10, 2);
```

## License

GPL v2 or later

## Support

For support and documentation, visit: https://yaay365.com/support

## Changelog

### 1.0.0
- Initial release
- Core sync functionality
- Admin interface
- Logging system
- Cron support
