<?php
if (!defined('ABSPATH')) {
    exit;
}

$logger = \Yaay365\Sync\Plugin::get_instance()->get_logger();
$logs = $logger->get_logs(100);
?>

<div class="wrap yaay365-sync-wrapper">
    <h1><?php echo esc_html(get_admin_page_title()); ?></h1>

    <div class="yaay365-sync-logs-actions">
        <button type="button" id="yaay365-refresh-logs" class="button button-secondary">
            <?php _e('Refresh Logs', 'yaay365-sync'); ?>
        </button>
        <button type="button" id="yaay365-clear-logs" class="button button-secondary">
            <?php _e('Clear All Logs', 'yaay365-sync'); ?>
        </button>
    </div>

    <div class="yaay365-sync-logs-container">
        <h2><?php _e('Recent Logs', 'yaay365-sync'); ?></h2>
        
        <?php if (empty($logs)): ?>
            <p><?php _e('No logs found.', 'yaay365-sync'); ?></p>
        <?php else: ?>
            <div id="yaay365-logs-display" class="yaay365-logs">
                <?php foreach ($logs as $log): ?>
                    <div class="log-entry <?php echo $this->get_log_level_class($log); ?>">
                        <?php echo esc_html($log); ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <style>
        .yaay365-logs {
            background: #fff;
            border: 1px solid #ccd0d4;
            padding: 15px;
            max-height: 600px;
            overflow-y: auto;
            font-family: monospace;
            font-size: 12px;
        }
        .log-entry {
            padding: 5px;
            margin-bottom: 5px;
            border-left: 3px solid #ccc;
            padding-left: 10px;
        }
        .log-entry.error {
            border-left-color: #dc3232;
            background: #fee;
        }
        .log-entry.warning {
            border-left-color: #ffb900;
            background: #fff8e5;
        }
        .log-entry.success {
            border-left-color: #46b450;
            background: #eff9f0;
        }
        .yaay365-sync-logs-actions {
            margin: 20px 0;
        }
    </style>
</div>

<?php
// Helper method to determine log level class
function get_log_level_class($log) {
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
?>
