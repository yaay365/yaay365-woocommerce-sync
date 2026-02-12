<?php
if (!defined('ABSPATH')) {
    exit;
}

$product_count = wp_count_posts('product');
$published_products = $product_count->publish;
?>

<div class="wrap yaay365-sync-wrapper">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <?php if (isset($_GET['synced'])): ?>
        <?php if ($_GET['synced'] == '1'): ?>
            <div class="notice notice-success is-dismissible">
                <p>
                    <?php
                    printf(
                        __('Products synced successfully! Created: %d, Updated: %d, Failed: %d', 'yaay365-sync'),
                        isset($_GET['created']) ? intval($_GET['created']) : 0,
                        isset($_GET['updated']) ? intval($_GET['updated']) : 0,
                        isset($_GET['failed']) ? intval($_GET['failed']) : 0
                    );
                    ?>
                </p>
            </div>
        <?php else: ?>
            <div class="notice notice-error is-dismissible">
                <p><?php _e('Sync failed. Please check the logs for more information.', 'yaay365-sync'); ?></p>
            </div>
        <?php endif; ?>
    <?php endif; ?>

    <div class="yaay365-sync-dashboard">
        <div class="yaay365-sync-card">
            <h2><?php _e('Product Statistics', 'yaay365-sync'); ?></h2>
            <div class="yaay365-sync-stats">
                <div class="stat-item">
                    <span class="stat-number"><?php echo esc_html($published_products); ?></span>
                    <span class="stat-label"><?php _e('Published Products', 'yaay365-sync'); ?></span>
                </div>
            </div>
        </div>

        <div class="yaay365-sync-card">
            <h2><?php _e('Sync Actions', 'yaay365-sync'); ?></h2>
            
            <div class="yaay365-sync-actions">
                <button type="button" id="yaay365-test-connection" class="button button-secondary">
                    <?php _e('Test API Connection', 'yaay365-sync'); ?>
                </button>

                <form method="post" action="<?php echo esc_url(admin_url('admin.php?action=yaay365_bulk_sync')); ?>" style="display: inline;">
                    <?php wp_nonce_field('yaay365_bulk_sync'); ?>
                    <button type="submit" class="button button-primary" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to sync all products?', 'yaay365-sync')); ?>')">
                        <?php _e('Sync All Products Now', 'yaay365-sync'); ?>
                    </button>
                </form>

                <button type="button" id="yaay365-manual-sync" class="button button-primary">
                    <?php _e('Manual Sync (AJAX)', 'yaay365-sync'); ?>
                </button>
            </div>

            <div id="yaay365-sync-result" class="yaay365-sync-result" style="display: none;"></div>
        </div>

        <div class="yaay365-sync-card">
            <h2><?php _e('Configuration Status', 'yaay365-sync'); ?></h2>
            <table class="widefat">
                <tbody>
                    <tr>
                        <td><strong><?php _e('API URL:', 'yaay365-sync'); ?></strong></td>
                        <td><?php echo esc_html(get_option('yaay365_sync_api_url', 'https://yaay365.com')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Email:', 'yaay365-sync'); ?></strong></td>
                        <td><?php echo esc_html(get_option('yaay365_sync_auth_email') ?: __('Not configured', 'yaay365-sync')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Company ID:', 'yaay365-sync'); ?></strong></td>
                        <td><?php echo esc_html(get_option('yaay365_sync_company_id') ?: __('Not configured', 'yaay365-sync')); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Sync on Save:', 'yaay365-sync'); ?></strong></td>
                        <td><?php echo get_option('yaay365_sync_sync_on_save', 'yes') === 'yes' ? __('Enabled', 'yaay365-sync') : __('Disabled', 'yaay365-sync'); ?></td>
                    </tr>
                    <tr>
                        <td><strong><?php _e('Auto Sync (Cron):', 'yaay365-sync'); ?></strong></td>
                        <td><?php echo get_option('yaay365_sync_auto_sync', 'no') === 'yes' ? __('Enabled', 'yaay365-sync') : __('Disabled', 'yaay365-sync'); ?></td>
                    </tr>
                </tbody>
            </table>
            <p>
                <a href="<?php echo esc_url(admin_url('admin.php?page=yaay365-sync-settings')); ?>" class="button">
                    <?php _e('Configure Settings', 'yaay365-sync'); ?>
                </a>
            </p>
        </div>

        <div class="yaay365-sync-card">
            <h2><?php _e('Documentation', 'yaay365-sync'); ?></h2>
            <p><?php _e('This plugin syncs your WooCommerce products to the Yaay365 Catalogue.', 'yaay365-sync'); ?></p>
            <ul>
                <li><?php _e('Configure your email, password, and company ID in Settings', 'yaay365-sync'); ?></li>
                <li><?php _e('Enable "Sync on Save" to automatically sync products when they\'re updated', 'yaay365-sync'); ?></li>
                <li><?php _e('Use "Sync All Products" to perform a one-time bulk sync', 'yaay365-sync'); ?></li>
                <li><?php _e('Enable "Auto Sync" for hourly automatic synchronization', 'yaay365-sync'); ?></li>
                <li><?php _e('Check Logs page to monitor sync activities', 'yaay365-sync'); ?></li>
            </ul>
        </div>
    </div>
</div>
