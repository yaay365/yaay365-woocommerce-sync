<?php
if (!defined('ABSPATH')) {
    exit;
}

$api_client = \Yaay365\Sync\Plugin::get_instance()->get_api_client();
$partner_info = $api_client->get_partner_info();
$deals = (isset($partner_info['success']) && $partner_info['success'] && !empty($partner_info['deals'])) ? $partner_info['deals'] : [];
$selected_deal = get_option('yaay365_sync_company_deal');
?>

<div class="wrap yaay365-sync-wrapper">
    <h1 style="display:none;"><?php echo esc_html(get_admin_page_title()); ?></h1>

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

    <div class="yaay365-settings-panel">
        <form method="post" action="options.php">
        <?php
        settings_fields('yaay365_sync_settings');
        do_settings_sections('yaay365_sync_settings');
        ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="yaay365_sync_public_key"><?php _e('Public Key', 'yaay365-sync'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="yaay365_sync_public_key" 
                           name="yaay365_sync_public_key" 
                           value="<?php echo esc_attr(get_option('yaay365_sync_public_key')); ?>" 
                           class="regular-text"
                           autocomplete="off">
                    <p class="description"><?php _e('Your Yaay365 API public key (X-Public-Key)', 'yaay365-sync'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="yaay365_sync_secret_key"><?php _e('Secret Key', 'yaay365-sync'); ?></label>
                </th>
                <td>
                    <input type="password" 
                           id="yaay365_sync_secret_key" 
                           name="yaay365_sync_secret_key" 
                           value="<?php echo esc_attr(get_option('yaay365_sync_secret_key')); ?>" 
                           class="regular-text"
                           autocomplete="off">
                    <p class="description"><?php _e('Your Yaay365 API secret key (X-Secret-Key). This is shown only once when generated.', 'yaay365-sync'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="yaay365_sync_company_deal"><?php _e('Related Company Deal', 'yaay365-sync'); ?></label>
                </th>
                <td>
                    <?php if (!$api_client->is_configured()): ?>
                        <p class="description" style="color: #d63638; font-weight: 500;">
                            <?php _e('Please configure and save your API keys first to load company deals.', 'yaay365-sync'); ?>
                        </p>
                    <?php elseif (empty($deals)): ?>
                        <p class="description" style="color: #646970;">
                            <?php _e('No active deals found for your company. Please create a deal in your partner dashboard first.', 'yaay365-sync'); ?>
                        </p>
                    <?php else: ?>
                        <select id="yaay365_sync_company_deal" name="yaay365_sync_company_deal" class="regular-text">
                            <option value=""><?php _e('-- Select Related Deal --', 'yaay365-sync'); ?></option>
                            <?php foreach ($deals as $deal): ?>
                                <option value="<?php echo esc_attr($deal['id']); ?>" <?php selected($selected_deal, $deal['id']); ?>>
                                    <?php echo esc_html($deal['deal_title']); ?> 
                                    (<?php echo esc_html($deal['discount_value']); ?> <?php echo esc_html($deal['discount_type'] === 'percentage' ? '%' : ($partner_info['currency'] ?? 'KES')); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <p class="description"><?php _e('Choose the related company deal that customers will use with this product catalogue sync.', 'yaay365-sync'); ?></p>
                    <?php endif; ?>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Sync Options', 'yaay365-sync'); ?></th>
                <td>
                    <fieldset>
                        <label>
                            <input type="checkbox" 
                                   name="yaay365_sync_sync_on_save" 
                                   value="yes" 
                                   <?php checked(get_option('yaay365_sync_sync_on_save', 'yes'), 'yes'); ?>>
                            <?php _e('Sync products automatically when saved/updated', 'yaay365-sync'); ?>
                        </label>
                        <br><br>
                        <label>
                            <input type="checkbox" 
                                   name="yaay365_sync_auto_sync" 
                                   value="yes" 
                                   <?php checked(get_option('yaay365_sync_auto_sync', 'no'), 'yes'); ?>>
                            <?php _e('Enable automatic hourly sync (cron)', 'yaay365-sync'); ?>
                        </label>
                    </fieldset>
                </td>
            </tr>

            <tr>
                <th scope="row"><?php _e('Logging', 'yaay365-sync'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" 
                               name="yaay365_sync_log_enabled" 
                               value="yes" 
                               <?php checked(get_option('yaay365_sync_log_enabled', 'yes'), 'yes'); ?>>
                        <?php _e('Enable logging', 'yaay365-sync'); ?>
                    </label>
                    <p class="description"><?php _e('Log sync activities for debugging and monitoring', 'yaay365-sync'); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(); ?>
        </form>

        <hr style="margin: 30px 0; border: none; border-top: 1px solid var(--yaay-border);">

        <h2><?php _e('Connection Test', 'yaay365-sync'); ?></h2>
        <p class="description" style="margin-bottom: 20px;"><?php _e('Save your settings first, then test the connection to verify your credentials live.', 'yaay365-sync'); ?></p>
        
        <button type="button" id="yaay365-test-connection" class="button button-secondary button-hero">
            <span class="dashicons dashicons-yes-alt"></span> <?php _e('Test Connection Now', 'yaay365-sync'); ?>
        </button>
        <div id="yaay365-connection-result"></div>
    </div>
</div>
