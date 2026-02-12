=== Yaay365 Sync ===
Contributors: yaay365
Tags: woocommerce, sync, api, catalogue, products
Requires at least: 5.8
Tested up to: 6.4
Requires PHP: 7.4
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sync WooCommerce products to Yaay365 Catalogue.

== Description ==

Yaay365 Sync is a powerful WordPress plugin that seamlessly synchronizes your WooCommerce products with the Yaay365 Catalogue. Keep your product inventory up-to-date across platforms automatically using your Yaay365 account credentials.

= Features =

* **Automatic Sync on Save** - Products are automatically synced when created or updated
* **Manual Bulk Sync** - Sync all products with a single click
* **Scheduled Auto Sync** - Hourly automatic synchronization via WordPress cron
* **Connection Testing** - Test your API connection before syncing
* **Detailed Logging** - Monitor all sync activities with comprehensive logs
* **Error Handling** - Graceful error handling with detailed error messages
* **Category Support** - Automatically syncs product categories
* **Image Support** - Syncs product images and gallery
* **Stock Management** - Keeps stock levels synchronized
* **Price Support** - Syncs regular and sale prices

= How It Works =

1. Install and activate the plugin
2. Configure your email, password, and company ID in Settings
3. Test the connection
4. Enable automatic sync options or manually sync products
5. Monitor sync activities in the Logs page

= Requirements =

* WordPress 5.8 or higher
* WooCommerce 5.0 or higher
* PHP 7.4 or higher
* Valid Yaay365 account (email and password)

== Installation ==

1. Upload the plugin files to `/wp-content/plugins/yaay365-sync/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to Yaay365 Sync > Settings to configure your email, password, and company ID
4. Test the connection and start syncing!

== Frequently Asked Questions ==

= Do I need a Yaay365 account? =

Yes, you need a valid Yaay365 account with your email and password.

= How often does the automatic sync run? =

When enabled, automatic sync runs hourly via WordPress cron.

= What happens if a product fails to sync? =

Failed products are logged with detailed error messages. You can review them in the Logs page and retry syncing.

= Can I sync specific products only? =

Currently, the plugin syncs all published products. Individual product sync happens automatically when you save a product.

= Is there a limit to how many products I can sync? =

No, the plugin can sync unlimited products. However, syncing large catalogs may take time.

== Screenshots ==

1. Main dashboard showing sync statistics and actions
2. Settings page for API configuration
3. Logs page displaying sync activity
4. Product sync in action

== Changelog ==

= 1.0.0 =
* Initial release
* Automatic sync on product save
* Manual bulk sync
* Hourly cron sync
* Connection testing
* Detailed logging
* Admin dashboard
* Settings management

== Upgrade Notice ==

= 1.0.0 =
Initial release of Yaay365 Sync plugin.

== Support ==

For support, please visit: https://yaay365.com/support

== Privacy Policy ==

This plugin sends product data to your configured Yaay365 API endpoint. Please ensure you have proper data handling agreements in place.
