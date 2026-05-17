<?php
if (!defined('ABSPATH')) {
    exit;
}

$logger = \Yaay365\Sync\Plugin::get_instance()->get_logger();
$logs = $logger->get_logs(100);

// Helper function to determine log level class
if (!function_exists('yaay365_get_log_level_class')) {
    function yaay365_get_log_level_class($log)
    {
        if (stripos($log, '[ERROR]') !== false) {
            return 'error';
        }
        if (stripos($log, '[WARNING]') !== false) {
            return 'warning';
        }
        if (stripos($log, '[SUCCESS]') !== false) {
            return 'success';
        }
        return '';
    }
}
?>

<div class="wrap yaay365-sync-wrapper">
    <h1 style="display:none;"><?php echo esc_html(get_admin_page_title()); ?></h1>

    <!-- Unified Brand Navbar -->
    <div class="yaay365-top-navbar">
        <div class="yaay365-navbar-left">
            <img src="<?php echo esc_url(YAAY365_SYNC_PLUGIN_URL . 'assets/images/logo.png'); ?>" alt="Yaay365 Logo"
                class="yaay365-navbar-logo">
            <span class="yaay365-navbar-title"><?php _e('YAAY365 Sync', 'yaay365-sync'); ?></span>
        </div>
        <div class="yaay365-navbar-nav">
            <a href="<?php echo esc_url(admin_url('admin.php?page=yaay365-sync')); ?>"
                class="nav-item <?php echo (!isset($_GET['page']) || $_GET['page'] === 'yaay365-sync') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-dashboard"></span> <?php _e('Dashboard', 'yaay365-sync'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=yaay365-sync-settings')); ?>"
                class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] === 'yaay365-sync-settings') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-admin-generic"></span> <?php _e('Settings', 'yaay365-sync'); ?>
            </a>
            <a href="<?php echo esc_url(admin_url('admin.php?page=yaay365-sync-logs')); ?>"
                class="nav-item <?php echo (isset($_GET['page']) && $_GET['page'] === 'yaay365-sync-logs') ? 'active' : ''; ?>">
                <span class="dashicons dashicons-database"></span> <?php _e('Logs', 'yaay365-sync'); ?>
            </a>
        </div>
    </div>

    <div class="yaay365-logs-container">
        <div
            style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 15px;">
            <h2 style="margin: 0; font-size: 18px;"><?php _e('Recent Integration Logs', 'yaay365-sync'); ?></h2>

            <div class="yaay365-action-buttons" style="margin: 0; display: flex; gap: 10px;">
                <button type="button" id="yaay365-refresh-logs" class="button button-primary">
                    <span class="dashicons dashicons-update" style="margin-top: 4px;"></span>
                    <?php _e('Refresh Logs', 'yaay365-sync'); ?>
                </button>
                <button type="button" id="yaay365-clear-logs" class="button button-secondary">
                    <span class="dashicons dashicons-trash" style="margin-top: 4px;"></span>
                    <?php _e('Clear All Logs', 'yaay365-sync'); ?>
                </button>
            </div>
        </div>

        <?php if (empty($logs)): ?>
            <div class="yaay365-empty-state">
                <span class="dashicons dashicons-database"></span>
                <p><?php _e('No sync logs recorded yet.', 'yaay365-sync'); ?></p>
            </div>
        <?php else: ?>
            <div id="yaay365-logs-display" class="yaay365-logs">
                <?php foreach ($logs as $log): ?>
                    <div class="log-entry <?php echo yaay365_get_log_level_class($log); ?>">
                        <?php echo esc_html($log); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>