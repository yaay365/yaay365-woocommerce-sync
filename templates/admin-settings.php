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
                           value="<?php echo esc_attr(get_option('yaay365_sync_api_url', 'https://api.yaay365.com')); ?>" 
                           class="regular-text" 
                           placeholder="https://api.yaay365.com">
                    <p class="description"><?php _e('The base URL of the Yaay365 API (default: https://api.yaay365.com)', 'yaay365-sync'); ?></p>
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
                           value="<?php echo esc_attr(get_option('yaay365_sync_sync_endpoint', '/v1/catalogues/sync')); ?>" 
                           class="regular-text" 
                           placeholder="/v1/catalogues/sync">
                    <p class="description"><?php _e('The endpoint path for syncing (default: /v1/catalogues/sync)', 'yaay365-sync'); ?></p>
                </td>
            </tr>

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
