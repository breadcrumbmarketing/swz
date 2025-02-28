<?php
/**
 * Plugin Name: Sportwagen Importer
 * Description: Modular Importer für Sportwagen Daten aus CSV und Bilder aus ZIP-Datei
 * Version: 1.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Include required files
require_once plugin_dir_path(__FILE__) . 'includes/class-sportwagen-admin.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-sportwagen-csv-processor.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-sportwagen-image-processor.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-sportwagen-field-mapper.php';
require_once plugin_dir_path(__FILE__) . 'includes/class-sportwagen-importer.php';

/**
 * Main plugin class
 */
class Sportwagen_Importer_Plugin {
    
    /**
     * Plugin instance
     */
    private static $instance = null;
    
    /**
     * Return plugin instance
     */
    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Constructor
     */
    private function __construct() {
        // Initialize components
        $this->init();
        
        // Register activation hook
        register_activation_hook(__FILE__, array($this, 'activate'));
    }
    
    /**
     * Initialize plugin components
     */
    private function init() {
        // Initialize admin UI
        new Sportwagen_Admin();
        
        // Initialize importer
        $field_mapper = new Sportwagen_Field_Mapper();
        $csv_processor = new Sportwagen_CSV_Processor($field_mapper);
        $image_processor = new Sportwagen_Image_Processor();
        
        new Sportwagen_Importer($csv_processor, $image_processor);
    }
    
    /**
     * Plugin activation
     */
    public function activate() {
        // Create directories for assets if they don't exist
        $this->create_assets_directories();
        
        // Create default assets
        $this->create_default_assets();
        
        // Clear permalinks
        flush_rewrite_rules();
    }
    
    /**
     * Create assets directories
     */
    private function create_assets_directories() {
        // CSS directory
        $css_dir = plugin_dir_path(__FILE__) . 'assets/css';
        if (!file_exists($css_dir)) {
            wp_mkdir_p($css_dir);
        }
        
        // JS directory
        $js_dir = plugin_dir_path(__FILE__) . 'assets/js';
        if (!file_exists($js_dir)) {
            wp_mkdir_p($js_dir);
        }
        
        // Includes directory
        $includes_dir = plugin_dir_path(__FILE__) . 'includes';
        if (!file_exists($includes_dir)) {
            wp_mkdir_p($includes_dir);
        }
    }
    
    /**
     * Create default assets files
     */
    private function create_default_assets() {
        // CSS file
        $css_file = plugin_dir_path(__FILE__) . 'assets/css/importer.css';
        if (!file_exists($css_file)) {
            $css_content = $this->get_default_css();
            file_put_contents($css_file, $css_content);
        }
        
        // JS file
        $js_file = plugin_dir_path(__FILE__) . 'assets/js/importer.js';
        if (!file_exists($js_file)) {
            $js_content = $this->get_default_js();
            file_put_contents($js_file, $js_content);
        }
    }
    
    /**
     * Get default CSS content
     */
    private function get_default_css() {
        return <<<CSS
.sportwagen-importer-card {
    background: #fff;
    padding: 20px;
    margin-top: 20px;
    border-radius: 3px;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

.sportwagen-progress-bar {
    height: 20px;
    background-color: #f0f0f0;
    border-radius: 10px;
    margin: 20px 0;
    overflow: hidden;
}

.sportwagen-progress-bar-fill {
    height: 100%;
    background-color: #0073aa;
    width: 0;
    transition: width 0.3s ease;
}

.sportwagen-options-section {
    margin: 20px 0;
    padding: 15px;
    background: #f9f9f9;
    border-left: 4px solid #0073aa;
}

#sportwagen-import-results {
    margin-top: 20px;
    padding: 15px;
    background: #f9f9f9;
    border-left: 4px solid #46b450;
}

.sportwagen-error-log {
    margin-top: 10px;
    padding: 10px;
    background: #fbeaea;
    border-left: 4px solid #dc3232;
}
CSS;
    }
    
    /**
     * Get default JS content
     */
    private function get_default_js() {
        return <<<JS
jQuery(document).ready(function($) {
    $('#sportwagen-import-form').on('submit', function(e) {
        e.preventDefault();
        
        var formData = new FormData(this);
        formData.append('action', 'sportwagen_import');
        formData.append('nonce', SportwagenImporter.nonce);
        formData.append('update_existing', $('#update_existing').prop('checked'));
        formData.append('import_images', $('#import_images').prop('checked'));
        
        // UI aktualisieren
        $('#submit-import').prop('disabled', true);
        $('#sportwagen-import-progress').show();
        $('.sportwagen-progress-bar-fill').css('width', '0%');
        $('#sportwagen-import-results').hide();
        
        // AJAX-Anfrage senden
        $.ajax({
            url: SportwagenImporter.ajax_url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            xhr: function() {
                var xhr = new window.XMLHttpRequest();
                xhr.upload.addEventListener('progress', function(evt) {
                    if (evt.lengthComputable) {
                        var percentComplete = evt.loaded / evt.total * 100;
                        $('.sportwagen-progress-bar-fill').css('width', percentComplete + '%');
                    }
                }, false);
                return xhr;
            },
            success: function(response) {
                $('#submit-import').prop('disabled', false);
                
                if (response.success) {
                    var data = response.data;
                    var resultHtml = '<p>Import abgeschlossen!</p>';
                    resultHtml += '<ul>';
                    resultHtml += '<li>Verarbeitete Einträge: ' + data.processed + '</li>';
                    resultHtml += '<li>Neue Einträge erstellt: ' + data.created + '</li>';
                    resultHtml += '<li>Bestehende Einträge aktualisiert: ' + data.updated + '</li>';
                    resultHtml += '</ul>';
                    
                    if (data.error_count > 0) {
                        resultHtml += '<div class="sportwagen-error-log"><h4>Fehler (' + data.error_count + '):</h4><ul>';
                        $.each(data.errors, function(i, error) {
                            resultHtml += '<li>' + error + '</li>';
                        });
                        resultHtml += '</ul></div>';
                    }
                    
                    $('#sportwagen-results-content').html(resultHtml);
                    $('#sportwagen-import-results').show();
                    
                    // Fortschrittsbalken auf 100%
                    $('.sportwagen-progress-bar-fill').css('width', '100%');
                    $('.sportwagen-progress-status').text('Import abgeschlossen');
                } else {
                    $('#sportwagen-results-content').html('<div class="sportwagen-error-log"><p>Fehler beim Import: ' + response.data + '</p></div>');
                    $('#sportwagen-import-results').show();
                    $('.sportwagen-progress-status').text('Import fehlgeschlagen');
                }
            },
            error: function(xhr, status, error) {
                $('#submit-import').prop('disabled', false);
                $('#sportwagen-results-content').html('<div class="sportwagen-error-log"><p>Fehler beim Import: ' + error + '</p></div>');
                $('#sportwagen-import-results').show();
                $('.sportwagen-progress-status').text('Import fehlgeschlagen');
            }
        });
    });
});
JS;
    }
}

// Initialize plugin
Sportwagen_Importer_Plugin::get_instance();