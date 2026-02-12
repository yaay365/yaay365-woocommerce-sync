jQuery(document).ready(function($) {
    'use strict';

    // Test Connection
    $('#yaay365-test-connection').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var $result = $('#yaay365-connection-result, #yaay365-sync-result');
        
        $button.prop('disabled', true).text(yaay365Sync.strings.testing);
        $result.removeClass('success error').addClass('loading').html('<p>Testing connection...</p>').show();

        $.ajax({
            url: yaay365Sync.ajaxUrl,
            type: 'POST',
            data: {
                action: 'yaay365_test_connection',
                nonce: yaay365Sync.nonce
            },
            success: function(response) {
                if (response.success) {
                    $result.removeClass('loading error').addClass('success')
                        .html('<p>' + response.data.message + '</p>');
                } else {
                    $result.removeClass('loading success').addClass('error')
                        .html('<p>' + response.data.message + '</p>');
                }
            },
            error: function() {
                $result.removeClass('loading success').addClass('error')
                    .html('<p>Connection test failed. Please try again.</p>');
            },
            complete: function() {
                $button.prop('disabled', false).text('Test Connection');
            }
        });
    });

    // Manual Sync
    $('#yaay365-manual-sync').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(yaay365Sync.strings.confirmSync)) {
            return;
        }

        var $button = $(this);
        var $result = $('#yaay365-sync-result');
        
        $button.prop('disabled', true).text(yaay365Sync.strings.syncing);
        $result.removeClass('success error').addClass('loading').html('<p>Syncing products...</p>').show();

        $.ajax({
            url: yaay365Sync.ajaxUrl,
            type: 'POST',
            data: {
                action: 'yaay365_manual_sync',
                nonce: yaay365Sync.nonce
            },
            success: function(response) {
                if (response.success) {
                    var message = '<p>' + response.data.message + '</p>';
                    if (response.data.data && response.data.data.errors) {
                        message += '<ul>';
                        response.data.data.errors.forEach(function(error) {
                            message += '<li>' + error.product_name + ': ' + error.error + '</li>';
                        });
                        message += '</ul>';
                    }
                    $result.removeClass('loading error').addClass('success').html(message);
                } else {
                    $result.removeClass('loading success').addClass('error')
                        .html('<p>' + response.data.message + '</p>');
                }
            },
            error: function() {
                $result.removeClass('loading success').addClass('error')
                    .html('<p>Sync failed. Please try again.</p>');
            },
            complete: function() {
                $button.prop('disabled', false).text('Manual Sync (AJAX)');
            }
        });
    });

    // Refresh Logs
    $('#yaay365-refresh-logs').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        $button.prop('disabled', true).text('Loading...');

        $.ajax({
            url: yaay365Sync.ajaxUrl,
            type: 'POST',
            data: {
                action: 'yaay365_view_logs',
                nonce: yaay365Sync.nonce
            },
            success: function(response) {
                if (response.success) {
                    var $logsDisplay = $('#yaay365-logs-display');
                    $logsDisplay.empty();
                    
                    if (response.data.logs.length === 0) {
                        $logsDisplay.html('<p>No logs found.</p>');
                    } else {
                        response.data.logs.forEach(function(log) {
                            var logClass = '';
                            if (log.indexOf('[ERROR]') !== -1) logClass = 'error';
                            else if (log.indexOf('[WARNING]') !== -1) logClass = 'warning';
                            else if (log.indexOf('[SUCCESS]') !== -1) logClass = 'success';
                            
                            $logsDisplay.append('<div class="log-entry ' + logClass + '">' + 
                                escapeHtml(log) + '</div>');
                        });
                    }
                }
            },
            complete: function() {
                $button.prop('disabled', false).text('Refresh Logs');
            }
        });
    });

    // Clear Logs
    $('#yaay365-clear-logs').on('click', function(e) {
        e.preventDefault();
        
        if (!confirm(yaay365Sync.strings.confirmClearLogs)) {
            return;
        }

        var $button = $(this);
        $button.prop('disabled', true).text('Clearing...');

        $.ajax({
            url: yaay365Sync.ajaxUrl,
            type: 'POST',
            data: {
                action: 'yaay365_clear_logs',
                nonce: yaay365Sync.nonce
            },
            success: function(response) {
                if (response.success) {
                    $('#yaay365-logs-display').html('<p>No logs found.</p>');
                    alert(response.data.message);
                }
            },
            complete: function() {
                $button.prop('disabled', false).text('Clear All Logs');
            }
        });
    });

    // Helper function to escape HTML
    function escapeHtml(text) {
        var map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, function(m) { return map[m]; });
    }
});
