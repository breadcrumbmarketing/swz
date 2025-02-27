<?php
/**
 * Plugin Name: AJAX Debug Tool
 * Description: Ein einfaches Tool zum Debuggen von AJAX-Aktionen
 * Version: 1.0.0
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// AJAX-Debug-Seite im WordPress-Admin hinzufügen
add_action('admin_menu', 'ajax_debug_add_page');

function ajax_debug_add_page() {
    add_management_page(
        'AJAX Debug',
        'AJAX Debug',
        'manage_options',
        'ajax-debug',
        'ajax_debug_render_page'
    );
}

function ajax_debug_render_page() {
    global $wp_filter;
    ?>
    <div class="wrap">
        <h1>AJAX Debug Tool</h1>
        
        <h2>Registrierte AJAX-Aktionen</h2>
        
        <div class="ajax-debug-filters">
            <label for="ajax-search">Suche:</label>
            <input type="text" id="ajax-search" placeholder="aci_test_ftp">
        </div>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th width="250">AJAX-Aktion</th>
                    <th>Callback</th>
                    <th width="80">Priorität</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Admin AJAX-Aktionen durchsuchen
                $found = false;
                foreach ($wp_filter as $tag => $callbacks) {
                    if (strpos($tag, 'wp_ajax_') === 0) {
                        $action = str_replace('wp_ajax_', '', $tag);
                        
                        foreach ($callbacks as $priority => $callback_group) {
                            foreach ($callback_group as $callback) {
                                $found = true;
                                $callback_name = '';
                                
                                if (is_string($callback['function'])) {
                                    $callback_name = $callback['function'];
                                } elseif (is_array($callback['function'])) {
                                    if (is_object($callback['function'][0])) {
                                        $class_name = get_class($callback['function'][0]);
                                        $callback_name = $class_name . '->' . $callback['function'][1];
                                    } else {
                                        $callback_name = $callback['function'][0] . '::' . $callback['function'][1];
                                    }
                                }
                                
                                echo '<tr class="ajax-action" data-action="' . esc_attr($action) . '">';
                                echo '<td>' . esc_html($action) . '</td>';
                                echo '<td>' . esc_html($callback_name) . '</td>';
                                echo '<td>' . esc_html($priority) . '</td>';
                                echo '</tr>';
                            }
                        }
                    }
                }
                
                if (!$found) {
                    echo '<tr><td colspan="3">Keine AJAX-Aktionen gefunden.</td></tr>';
                }
                ?>
            </tbody>
        </table>
        
        <h2>AJAX-Test</h2>
        <p>Hier kannst du einen AJAX-Aufruf testen:</p>
        
        <div class="ajax-test-form">
            <div class="form-row">
                <label for="ajax-action">AJAX-Aktion:</label>
                <input type="text" id="ajax-action" value="aci_test_ftp">
            </div>
            
            <div class="form-row">
                <label for="ajax-data">Daten (JSON):</label>
                <textarea id="ajax-data" rows="5">{"nonce":"<?php echo wp_create_nonce('ajax-debug-nonce'); ?>","host":"example.com","username":"user","password":"pass","path":"/"}</textarea>
            </div>
            
            <div class="form-row">
                <button id="send-ajax" class="button button-primary">AJAX-Anfrage senden</button>
            </div>
        </div>
        
        <div id="ajax-result" style="margin-top: 20px;"></div>
    </div>
    
    <style>
    .form-row {
        margin-bottom: 15px;
    }
    .form-row label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
    .form-row input[type="text"],
    .form-row textarea {
        width: 100%;
        max-width: 600px;
    }
    </style>
    
    <script>
    jQuery(document).ready(function($) {
        // Filter für AJAX-Aktionen
        $('#ajax-search').on('keyup', function() {
            var search = $(this).val().toLowerCase();
            
            $('.ajax-action').each(function() {
                var action = $(this).data('action').toLowerCase();
                if (action.indexOf(search) > -1) {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        });
        
        // AJAX-Test-Button
        $('#send-ajax').on('click', function() {
            var action = $('#ajax-action').val();
            var jsonData = $('#ajax-data').val();
            
            try {
                var data = JSON.parse(jsonData);
                data.action = action;
                
                $('#ajax-result').html('<div class="notice notice-info inline"><p>Anfrage wird gesendet...</p></div>');
                
                $.ajax({
                    url: ajaxurl,
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        var html = '<div class="notice notice-success inline"><p>Antwort erhalten:</p></div>';
                        html += '<pre>' + JSON.stringify(response, null, 2) + '</pre>';
                        $('#ajax-result').html(html);
                    },
                    error: function(xhr, status, error) {
                        var html = '<div class="notice notice-error inline"><p>Fehler: ' + error + '</p></div>';
                        html += '<pre>' + xhr.responseText + '</pre>';
                        $('#ajax-result').html(html);
                    }
                });
            } catch (e) {
                $('#ajax-result').html('<div class="notice notice-error inline"><p>Ungültiges JSON: ' + e.message + '</p></div>');
            }
        });
    });
    </script>
    <?php
}