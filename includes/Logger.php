<?php

namespace Yaay365\Sync;

class Logger
{
    private $log_file;

    public function __construct()
    {
        $upload_dir = wp_upload_dir();
        $log_dir = $upload_dir['basedir'] . '/yaay365-sync-logs';
        
        if (!file_exists($log_dir)) {
            wp_mkdir_p($log_dir);
        }

        $this->log_file = $log_dir . '/sync-log.txt';
    }

    public function log($message, $level = 'info')
    {
        if (get_option('yaay365_sync_log_enabled', 'yes') !== 'yes') {
            return;
        }

        $timestamp = current_time('Y-m-d H:i:s');
        $log_entry = sprintf("[%s] [%s] %s\n", $timestamp, strtoupper($level), $message);

        file_put_contents($this->log_file, $log_entry, FILE_APPEND);
    }

    public function get_logs($limit = 100)
    {
        if (!file_exists($this->log_file)) {
            return [];
        }

        $lines = file($this->log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        
        if ($lines === false) {
            return [];
        }

        // Get last N lines
        $lines = array_slice($lines, -$limit);
        
        // Reverse to show newest first
        return array_reverse($lines);
    }

    public function clear_logs()
    {
        if (file_exists($this->log_file)) {
            file_put_contents($this->log_file, '');
        }
    }
}
