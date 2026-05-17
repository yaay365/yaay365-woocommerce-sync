<?php
if (!defined('ABSPATH')) {
    exit;
}

$product_count = wp_count_posts('product');
$published_products = $product_count->publish;

$api_client = \Yaay365\Sync\Plugin::get_instance()->get_api_client();
$partner_info = $api_client->get_partner_info();
$is_connected = isset($partner_info['success']) && $partner_info['success'];
?>

<div class="wrap yaay365-sync-wrapper">
    <h1 class="wp-heading-inline" style="display:none;"><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Unified Brand Navbar -->
    <div class="yaay365-top-navbar">
        <div class="yaay365-navbar-left">
            <img src="<?php echo esc_url(YAAY365_SYNC_PLUGIN_URL . 'assets/images/logo.png'); ?>" alt="Yaay365 Logo" class="yaay365-navbar-logo">
            <span class="yaay365-navbar-title"><?php _e('YAAY365 Sync', 'yaay365-sync'); ?></span>
        </div>
        <div class="yaay365-navbar-nav">
            <a href="<?php echo esc_url(admin_url('admin.php?page=yaay365-sync')); ?>" class="nav-item <?php echo (!isset($_GET['page']) || $_GET['page'] === 'yaay365-sync') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-dashboard"></span> <?php _e('Dashboard', 'yaay365-sync'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=yaay365-sync-settings')); ?>" class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] === 'yaay365-sync-settings') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-admin-generic"></span> <?php _e('Settings', 'yaay365-sync'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=yaay365-sync-logs')); ?>" class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] === 'yaay365-sync-logs') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-database"></span> <?php _e('Logs', 'yaay365-sync'); ?>
            </a>
        </div>
    </div>

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

    <!-- Premium Brand Header -->
    <div class="yaay365-brand-header">
        <div class="yaay365-header-content">
            <span class="yaay365-header-badge"><?php _e('WooCommerce Partner Sync', 'yaay365-sync'); ?></span>
            <?php if ($is_connected): ?>
                <h1 class="yaay365-company-title"><?php echo esc_html($partner_info['company_name']); ?></h1>
                <p class="yaay365-company-subtitle">
                    <?php if (!empty($partner_info['trading_name'])): ?>
                        <strong><?php _e('Trading as:', 'yaay365-sync'); ?></strong> <?php echo esc_html($partner_info['trading_name']); ?> | 
                    <?php endif; ?>
                    <strong><?php _e('Status:', 'yaay365-sync'); ?></strong> 
                    <span class="yaay365-status-badge active">
                        <?php echo esc_html(ucfirst($partner_info['status'])); ?>
                    </span>
                </p>
            <?php else: ?>
                <h1 class="yaay365-company-title"><?php _e('Yaay365 Integration Dashboard', 'yaay365-sync'); ?></h1>
                <p class="yaay365-company-subtitle"><?php _e('Establish a premium sync between your WooCommerce store and the Yaay365 global catalogue.', 'yaay365-sync'); ?></p>
            <?php endif; ?>
        </div>
        <?php if ($is_connected && !empty($partner_info['website'])): ?>
            <div class="yaay365-header-actions">
                <a href="<?php echo esc_url($partner_info['website']); ?>" target="_blank" class="yaay365-btn-secondary">
                    <span class="dashicons dashicons-external"></span> <?php _e('Visit Website', 'yaay365-sync'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>

    <!-- Stats Grid -->
    <div class="yaay365-stats-grid">
        <div class="yaay365-stat-card product-card">
            <div class="stat-icon-wrapper">
                <span class="dashicons dashicons-admin-plugins"></span>
            </div>
            <div class="stat-details">
                <span class="stat-number"><?php echo esc_html($published_products); ?></span>
                <span class="stat-label"><?php _e('WooCommerce Products', 'yaay365-sync'); ?></span>
            </div>
        </div>

        <div class="yaay365-stat-card synced-card">
            <div class="stat-icon-wrapper">
                <span class="dashicons dashicons-cloud-upload"></span>
            </div>
            <div class="stat-details">
                <span class="stat-number"><?php echo esc_html($is_connected ? $partner_info['stats']['catalogues_count'] : 0); ?></span>
                <span class="stat-label"><?php _e('Synced Products', 'yaay365-sync'); ?></span>
            </div>
        </div>

        <div class="yaay365-stat-card branches-card">
            <div class="stat-icon-wrapper">
                <span class="dashicons dashicons-store"></span>
            </div>
            <div class="stat-details">
                <span class="stat-number"><?php echo esc_html($is_connected ? $partner_info['stats']['branches_count'] : 0); ?></span>
                <span class="stat-label"><?php _e('Company Branches', 'yaay365-sync'); ?></span>
            </div>
        </div>

        <div class="yaay365-stat-card deals-card">
            <div class="stat-icon-wrapper">
                <span class="dashicons dashicons-tag"></span>
            </div>
            <div class="stat-details">
                <span class="stat-number"><?php echo esc_html($is_connected ? $partner_info['stats']['deals_count'] : 0); ?></span>
                <span class="stat-label"><?php _e('Active Deals', 'yaay365-sync'); ?></span>
            </div>
        </div>

        <div class="yaay365-stat-card orders-card">
            <div class="stat-icon-wrapper">
                <span class="dashicons dashicons-cart"></span>
            </div>
            <div class="stat-details">
                <span class="stat-number"><?php echo esc_html($is_connected ? $partner_info['stats']['orders_count'] : 0); ?></span>
                <span class="stat-label"><?php _e('Partner Orders', 'yaay365-sync'); ?></span>
            </div>
        </div>
    </div>

    <div class="yaay365-dashboard-body">
        <div class="yaay365-main-content">
            <!-- Sync Actions -->
            <div class="yaay365-premium-card">
                <div class="card-header">
                    <h3><span class="dashicons dashicons-update"></span> <?php _e('Sync Control Center', 'yaay365-sync'); ?></h3>
                </div>
                <div class="card-body">
                    <p class="card-desc"><?php _e('Perform manual synchronizations or test the connection live with the Yaay365 API gateway.', 'yaay365-sync'); ?></p>
                    
                    <div class="yaay365-action-buttons">
                        <form method="post" action="<?php echo esc_url(admin_url('admin.php?action=yaay365_bulk_sync')); ?>" style="display: inline;">
                            <?php wp_nonce_field('yaay365_bulk_sync'); ?>
                            <button type="submit" class="button button-primary button-hero" onclick="return confirm('<?php echo esc_js(__('Are you sure you want to sync all products?', 'yaay365-sync')); ?>')">
                                <span class="dashicons dashicons-cloud-upload"></span> <?php _e('Sync All Products Now', 'yaay365-sync'); ?>
                            </button>
                        </form>
                        
                        <button type="button" id="yaay365-manual-sync" class="button button-secondary button-hero">
                            <span class="dashicons dashicons-update"></span> <?php _e('Manual Sync (AJAX)', 'yaay365-sync'); ?>
                        </button>
                        
                        <button type="button" id="yaay365-test-connection" class="button button-secondary button-hero">
                            <span class="dashicons dashicons-yes-alt"></span> <?php _e('Test Connection', 'yaay365-sync'); ?>
                        </button>
                    </div>

                    <div id="yaay365-sync-result" class="yaay365-sync-result" style="display: none;"></div>
                </div>
            </div>

            <!-- Branches List -->
            <div class="yaay365-premium-card">
                <div class="card-header">
                    <h3><span class="dashicons dashicons-store"></span> <?php _e('Company Branches & Outlets', 'yaay365-sync'); ?></h3>
                </div>
                <div class="card-body">
                    <?php if (!$is_connected): ?>
                        <div class="yaay365-empty-state">
                            <span class="dashicons dashicons-warning"></span>
                            <p><?php _e('Please connect your account under Settings to fetch company branch outlets.', 'yaay365-sync'); ?></p>
                        </div>
                    <?php elseif (empty($partner_info['branches'])): ?>
                        <div class="yaay365-empty-state">
                            <span class="dashicons dashicons-info"></span>
                            <p><?php _e('No branch outlets registered. Register your branches in the merchant portal to view them here.', 'yaay365-sync'); ?></p>
                        </div>
                    <?php else: ?>
                        <div class="yaay365-table-responsive">
                            <table class="yaay365-data-table">
                                <thead>
                                    <tr>
                                        <th><?php _e('Branch Name', 'yaay365-sync'); ?></th>
                                        <th><?php _e('Contact Details', 'yaay365-sync'); ?></th>
                                        <th><?php _e('Address', 'yaay365-sync'); ?></th>
                                        <th><?php _e('Type', 'yaay365-sync'); ?></th>
                                        <th><?php _e('Status', 'yaay365-sync'); ?></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($partner_info['branches'] as $branch): ?>
                                        <tr>
                                            <td>
                                                <strong class="branch-title"><?php echo esc_html($branch['branch_name']); ?></strong>
                                            </td>
                                            <td>
                                                <span class="branch-info-item"><span class="dashicons dashicons-email"></span> <?php echo esc_html($branch['email'] ?: 'N/A'); ?></span>
                                                <br>
                                                <span class="branch-info-item"><span class="dashicons dashicons-phone"></span> <?php echo esc_html($branch['phone'] ?: 'N/A'); ?></span>
                                            </td>
                                            <td>
                                                <?php echo esc_html($branch['address'] ?: 'N/A'); ?>, 
                                                <?php echo esc_html($branch['state_county'] ?: 'N/A'); ?>, 
                                                <?php echo esc_html($branch['country'] ?: 'N/A'); ?>
                                            </td>
                                            <td>
                                                <?php if (!empty($branch['is_head_office']) && $branch['is_head_office']): ?>
                                                    <span class="yaay365-badge badge-purple"><?php _e('Head Office', 'yaay365-sync'); ?></span>
                                                <?php else: ?>
                                                    <span class="yaay365-badge badge-blue"><?php _e('Branch', 'yaay365-sync'); ?></span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php if ($branch['status'] === 'active'): ?>
                                                    <span class="yaay365-badge badge-green"><?php _e('Active', 'yaay365-sync'); ?></span>
                                                <?php else: ?>
                                                    <span class="yaay365-badge badge-gray"><?php echo esc_html(ucfirst($branch['status'])); ?></span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="yaay365-sidebar">
            <!-- Connection Status -->
            <div class="yaay365-sidebar-card">
                <h3><?php _e('Integration Info', 'yaay365-sync'); ?></h3>
                <div class="integration-details">
                    <div class="detail-row">
                        <span class="label"><?php _e('Connection Status:', 'yaay365-sync'); ?></span>
                        <span class="val">
                            <?php if ($is_connected): ?>
                                <span class="badge-dot dot-green"></span> <?php _e('Connected', 'yaay365-sync'); ?>
                            <?php else: ?>
                                <span class="badge-dot dot-red"></span> <?php _e('Disconnected', 'yaay365-sync'); ?>
                            <?php endif; ?>
                        </span>
                    </div>
                    <div class="detail-row">
                        <span class="label"><?php _e('Public Key:', 'yaay365-sync'); ?></span>
                        <span class="val font-mono">
                            <?php
                            $pk = get_option('yaay365_sync_public_key');
                            echo $pk ? esc_html(substr($pk, 0, 8) . '••••') : esc_html(__('Not configured', 'yaay365-sync'));
                            ?>
                        </span>
                    </div>
                    <?php if ($is_connected): ?>
                        <div class="detail-row">
                            <span class="label"><?php _e('Related Deal ID:', 'yaay365-sync'); ?></span>
                            <span class="val font-mono">
                                <?php
                                $deal_id = get_option('yaay365_sync_company_deal');
                                echo $deal_id ? esc_html($deal_id) : esc_html(__('None chosen', 'yaay365-sync'));
                                ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    <div class="detail-row">
                        <span class="label"><?php _e('Sync on Save:', 'yaay365-sync'); ?></span>
                        <span class="val"><?php echo get_option('yaay365_sync_sync_on_save', 'yes') === 'yes' ? __('Enabled', 'yaay365-sync') : __('Disabled', 'yaay365-sync'); ?></span>
                    </div>
                    <div class="detail-row">
                        <span class="label"><?php _e('Hourly Cron Sync:', 'yaay365-sync'); ?></span>
                        <span class="val"><?php echo get_option('yaay365_sync_auto_sync', 'no') === 'yes' ? __('Enabled', 'yaay365-sync') : __('Disabled', 'yaay365-sync'); ?></span>
                    </div>
                </div>
                <div class="card-footer-action">
                    <a href="<?php echo esc_url(admin_url('admin.php?page=yaay365-sync-settings')); ?>" class="button">
                        <span class="dashicons dashicons-admin-generic"></span> <?php _e('Configure Settings', 'yaay365-sync'); ?>
                    </a>
                </div>
            </div>

            <!-- Developer Tools & Documentation -->
            <div class="yaay365-sidebar-card info-card">
                <h3><?php _e('Plugin Quick Guide', 'yaay365-sync'); ?></h3>
                <ul class="yaay365-guide-list">
                    <li><span class="dashicons dashicons-yes"></span> <?php _e('Set your Public and Secret Keys in the Settings page.', 'yaay365-sync'); ?></li>
                    <li><span class="dashicons dashicons-yes"></span> <?php _e('Save your keys to dynamically populate related deals dropdown.', 'yaay365-sync'); ?></li>
                    <li><span class="dashicons dashicons-yes"></span> <?php _e('Select a related deal and hit save to connect transactions.', 'yaay365-sync'); ?></li>
                    <li><span class="dashicons dashicons-yes"></span> <?php _e('Check the Logs page to debug or trace connection calls.', 'yaay365-sync'); ?></li>
                </ul>
            </div>
        </div>
    </div>
</div>
