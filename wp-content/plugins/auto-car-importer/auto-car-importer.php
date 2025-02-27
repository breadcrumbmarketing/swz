<?php
/**
 * Plugin Name: Auto Car Importer
 * Plugin URI: 
 * Description: Importiert Fahrzeugdaten aus CSV-Dateien und verknüpft zugehörige Bilder
 * Version: 1.0.0
 * Author: Hamy Vosugh
 * Text Domain: auto-car-importer
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Plugin-Konstanten definieren
define('ACI_VERSION', '1.0.0');
define('ACI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ACI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ACI_PLUGIN_BASENAME', plugin_basename(__FILE__));

// Klassen einbinden
require_once ACI_PLUGIN_DIR . 'includes/class-admin.php';
require_once ACI_PLUGIN_DIR . 'includes/class-cpt-manager.php';
require_once ACI_PLUGIN_DIR . 'includes/class-csv-processor.php';
require_once ACI_PLUGIN_DIR . 'includes/class-image-handler.php';
require_once ACI_PLUGIN_DIR . 'includes/class-sync-manager.php';
require_once ACI_PLUGIN_DIR . 'includes/class-logger.php';
require_once ACI_PLUGIN_DIR . 'includes/class-ftp-handler.php';
require_once ACI_PLUGIN_DIR . 'includes/class-acf-manager.php';

/**
 * Hauptklasse für das Plugin
 */
class Auto_Car_Importer {
    
    /**
     * Klassenmember
     */
    private $admin;
    private $cpt_manager;
    private $csv_processor;
    private $image_handler;
    private $sync_manager;
    private $logger;
    private $ftp_handler;
    private $acf_manager;
    
    /**
     * Konstruktor
     */
    public function __construct() {
        // Initialisierung Plugin
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * Plugin initialisieren
     */
    public function init() {
        // Logger initialisieren (als erstes, damit alle anderen Klassen ihn nutzen können)
        $this->logger = new ACI_Logger();
        
        // Custom Post Type Manager initialisieren
        $this->cpt_manager = new ACI_CPT_Manager();
        
        // ACF Manager initialisieren
        $this->acf_manager = new ACI_ACF_Manager($this->logger);
        
        // FTP-Handler initialisieren
        $this->ftp_handler = new ACI_FTP_Handler($this->logger);
        
        // Admin-Bereich initialisieren
        if (is_admin()) {
            $this->admin = new ACI_Admin($this->logger);
        }
        
        // CSV-Processor initialisieren
        $this->csv_processor = new ACI_CSV_Processor($this->logger);
        
        // Image-Handler initialisieren
        $this->image_handler = new ACI_Image_Handler($this->logger);
        
        // Sync-Manager initialisieren
        $this->sync_manager = new ACI_Sync_Manager(
            $this->csv_processor, 
            $this->image_handler,
            $this->acf_manager,
            $this->logger
        );
        
        // Hooks für die Aktivierung und Deaktivierung des Plugins registrieren
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * Plugin aktivieren
     */
    public function activate() {
        // Custom Post Type registrieren
        $this->cpt_manager->register_post_type();
        
        // Rollen und Berechtigungen aktualisieren
        $this->update_roles_and_capabilities();
        
        // Dateisystem initialisieren (Upload-Verzeichnis erstellen)
        $this->init_filesystem();
        
        // Rewrite-Regeln aktualisieren
        flush_rewrite_rules();
    }
    
    /**
     * Plugin deaktivieren
     */
    public function deactivate() {
        // Rewrite-Regeln aktualisieren
        flush_rewrite_rules();
    }
    
    /**
     * Rollen und Berechtigungen aktualisieren
     */
    private function update_roles_and_capabilities() {
        // Administrator-Rolle
        $admin_role = get_role('administrator');
        $admin_role->add_cap('manage_car_importer');
    }
    
    /**
     * Dateisystem initialisieren
     */
    private function init_filesystem() {
        // Upload-Verzeichnis für temporäre Dateien erstellen
        $upload_dir = wp_upload_dir();
        $aci_dir = $upload_dir['basedir'] . '/aci-temp';
        
        if (!file_exists($aci_dir)) {
            wp_mkdir_p($aci_dir);
        }
        
        // .htaccess-Datei erstellen zum Schutz des Verzeichnisses
        if (file_exists($aci_dir) && !file_exists($aci_dir . '/.htaccess')) {
            $htaccess_content = "Deny from all\n";
            @file_put_contents($aci_dir . '/.htaccess', $htaccess_content);
        }
    }
}

// Plugin-Instanz starten
$auto_car_importer = new Auto_Car_Importer();