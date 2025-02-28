<?php
/**
 * Sportwagen Admin Class
 * 
 * Handles the admin interface for the importer
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Sportwagen_Admin {
    
    /**
     * Constructor
     */
    public function __construct() {
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register scripts and styles
        add_action('admin_enqueue_scripts', array($this, 'enqueue_scripts'));
    }
    
    /**
     * Add admin menu
     */
    public function add_admin_menu() {
        add_menu_page(
            'Sportwagen Importer',
            'Sportwagen Importer',
            'manage_options',
            'sportwagen-importer',
            array($this, 'render_admin_page'),
            'dashicons-car',
            30
        );
    }
    
    /**
     * Enqueue scripts and styles
     */
    public function enqueue_scripts($hook) {
        if ($hook != 'toplevel_page_sportwagen-importer') {
            return;
        }
        
        wp_enqueue_style('sportwagen-importer-css', plugin_dir_url(dirname(__FILE__)) . 'assets/css/importer.css', array(), '1.0.0');
        wp_enqueue_script('sportwagen-importer-js', plugin_dir_url(dirname(__FILE__)) . 'assets/js/importer.js', array('jquery'), '1.0.0', true);
        wp_localize_script('sportwagen-importer-js', 'SportwagenImporter', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sportwagen_import_nonce')
        ));
    }
    
    /**
     * Render admin page
     */
    public function render_admin_page() {
        ?>
        <div class="wrap">
            <h1>Sportwagen Importer</h1>
            
            <div class="sportwagen-importer-card">
                <h2>Daten importieren</h2>
                <p>Bitte wählen Sie eine ZIP-Datei aus, die CSV-Daten und Bilder enthält.</p>
                
                <form id="sportwagen-import-form" method="post" enctype="multipart/form-data">
                    <input type="file" name="import_file" id="import_file" accept=".zip" required>
                    <p class="description">Die ZIP-Datei sollte eine CSV-Datei mit Fahrzeugdaten und Bilder enthalten.</p>
                    
                    <div class="sportwagen-options-section">
                        <h3>Import-Optionen</h3>
                        <label>
                            <input type="checkbox" name="update_existing" id="update_existing" checked>
                            Bestehende Fahrzeuge aktualisieren (basierend auf interner Nummer)
                        </label>
                        <br>
                        <label>
                            <input type="checkbox" name="import_images" id="import_images" checked>
                            Bilder importieren
                        </label>
                    </div>
                    
                    <button type="submit" id="submit-import" class="button button-primary">Import starten</button>
                </form>
                
                <div id="sportwagen-import-progress" style="display:none;">
                    <div class="sportwagen-progress-bar">
                        <div class="sportwagen-progress-bar-fill"></div>
                    </div>
                    <p class="sportwagen-progress-status">Import wird verarbeitet...</p>
                </div>
                
                <div id="sportwagen-import-results" style="display:none;">
                    <h3>Import Ergebnisse</h3>
                    <div id="sportwagen-results-content"></div>
                </div>
            </div>
        </div>
        <?php
    }
}