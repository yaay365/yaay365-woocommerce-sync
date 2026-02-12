<?php
if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap yaay365-sync-wrapper">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <form method="post" action="options.php">
        <?php
        settings_fields('yaay365_sync_settings');
        do_settings_sections('yaay365_sync_settings');
        ?>

        <table class="form-table">
            <tr>
                <th scope="row">
                    <label for="yaay365_sync_api_url"><?php _e('API URL', 'yaay365-sync'); ?></label>
                </th>
                <td>
                    <input type="url" 
                           id="yaay365_sync_api_url" 
                           name="yaay365_sync_api_url" 
                           value="<?php echo esc_attr(get_option('yaay365_sync_api_url', 'https://yaay365.com')); ?>" 
                           class="regular-text" 
                           placeholder="https://yaay365.com">
                    <p class="description"><?php _e('The base URL of your Yaay365 API (e.g., https://yaay365.com or https://api.yaay365.com)', 'yaay365-sync'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="yaay365_sync_sync_endpoint"><?php _e('Sync Endpoint Path', 'yaay365-sync'); ?></label>
                </th>
                <td>
                    <input type="text" 
                           id="yaay365_sync_sync_endpoint" 
                           name="yaay365_sync_sync_endpoint" 
                           value="<?php echo esc_attr(get_option('yaay365_sync_sync_endpoint', '/api/catalogues/sync')); ?>" 
                           class="regular-text" 
                           placeholder="/api/catalogues/sync">
                    <p class="description"><?php _e('The endpoint path for syncing. Try: /api/catalogues/sync or /catalogues/sync', 'yaay365-sync'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="yaay365_sync_auth_email"><?php _e('Email', 'yaay365-sync'); ?></label>
                </th>
                <td>
                    <input type="email" 
                           id="yaay365_sync_auth_email" 
                           name="yaay365_sync_auth_email" 
                           value="<?php echo esc_attr(get_option('yaay365_sync_auth_email')); ?>" 
                           class="regular-text">
                    <p class="description"><?php _e('Your Yaay365 account email', 'yaay365-sync'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="yaay365_sync_auth_password"><?php _e('Password', 'yaay365-sync'); ?></label>
                </th>
                <td>
                    <input type="password" 
                           id="yaay365_sync_auth_password" 
                           name="yaay365_sync_auth_password" 
                           value="<?php echo esc_attr(get_option('yaay365_sync_auth_password')); ?>" 
                           class="regular-text">
                    <p class="description"><?php _e('Your Yaay365 account password', 'yaay365-sync'); ?></p>
                </td>
            </tr>

            <tr>
                <th scope="row">
                    <label for="yaay365_sync_company_id"><?php _e('Company ID', 'yaay365-sync'); ?></label>
                </th>
                <td>
                    <input type="number" 
                           id="yaay365_sync_company_id" 
                           name="yaay365_sync_company_id" 
                           value="<?php echo esc_attr(get_option('yaay365_sync_company_id')); ?>" 
                           class="small-text" 
                           min="1">
                    <p class="description"><?php _e('Your company ID in the Yaay365 system', 'yaay365-sync'); ?></p>
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

    <hr>

    <h2><?php _e('Connection Test', 'yaay365-sync'); ?></h2>
    <p><?php _e('Save your settings first, then test the connection to verify your credentials.', 'yaay365-sync'); ?></p>
    <button type="button" id="yaay365-test-connection" class="button button-secondary">
        <?php _e('Test Connection', 'yaay365-sync'); ?>
    </button>
    <div id="yaay365-connection-result" style="margin-top: 15px;"></div>
</div>
