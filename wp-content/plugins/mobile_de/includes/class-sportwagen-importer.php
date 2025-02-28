<?php
/**
 * Sportwagen Importer Class
 * 
 * Main class for importing Sportwagen data
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Sportwagen_Importer {
    
    /**
     * CSV processor instance
     */
    private $csv_processor;
    
    /**
     * Image processor instance
     */
    private $image_processor;
    
    /**
     * Temporary directory
     */
    private $temp_dir;
    
    /**
     * Constructor
     */
    public function __construct($csv_processor, $image_processor) {
        $this->csv_processor = $csv_processor;
        $this->image_processor = $image_processor;
        
        // Register AJAX handler
        add_action('wp_ajax_sportwagen_import', array($this, 'handle_import'));
    }
    
    /**
     * Handle import AJAX request
     */
    public function handle_import() {
        // Verify nonce
        check_ajax_referer('sportwagen_import_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung zum Importieren von Daten');
        }
        
        // Check if file was uploaded
        if (empty($_FILES['import_file'])) {
            wp_send_json_error('Keine Datei hochgeladen');
        }
        
        // Process the ZIP file
        try {
            $results = $this->process_import_file($_FILES['import_file']);
            wp_send_json_success($results);
        } catch (Exception $e) {
            wp_send_json_error($e->getMessage());
        }
    }
    
    /**
     * Process the import file
     */
    private function process_import_file($file) {
        // Get import options
        $update_existing = isset($_POST['update_existing']) && $_POST['update_existing'] === 'true';
        $import_images = isset($_POST['import_images']) && $_POST['import_images'] === 'true';
        
        // Handle the uploaded file
        $upload = wp_handle_upload($file, array('test_form' => false));
        
        if (isset($upload['error'])) {
            throw new Exception('Fehler beim Hochladen: ' . $upload['error']);
        }
        
        // Create temporary directory
        $this->temp_dir = get_temp_dir() . 'sportwagen_import_' . time() . '/';
        if (!wp_mkdir_p($this->temp_dir)) {
            throw new Exception('Fehler beim Erstellen des temporären Verzeichnisses');
        }
        
        // Extract ZIP file
        $zip = new ZipArchive();
        if ($zip->open($upload['file']) !== true) {
            throw new Exception('Fehler beim Öffnen der ZIP-Datei');
        }
        
        $zip->extractTo($this->temp_dir);
        $zip->close();
        
        // Suche rekursiv nach CSV-Dateien (auch in Unterordnern)
        $csv_files = $this->find_files_recursive($this->temp_dir, '*.csv');
        
        if (empty($csv_files)) {
            throw new Exception('Keine CSV-Datei in der ZIP gefunden');
        }
        
        // Set CSV file for processor
        $this->csv_processor->set_csv_file($csv_files[0]);
        
        // Process CSV data
        $csv_results = $this->csv_processor->process($update_existing);
        
        // Process images if required
        if ($import_images && isset($csv_results['vehicles'])) {
            // Finde alle Bildordner in der ZIP
            $image_dirs = array();
            
            // Zuerst das Hauptverzeichnis prüfen
            $jpg_files = glob($this->temp_dir . '*.jpg');
            if (!empty($jpg_files)) {
                $image_dirs[] = $this->temp_dir;
            }
            
            // Dann Unterordner prüfen
            $dirs = glob($this->temp_dir . '*', GLOB_ONLYDIR);
            foreach ($dirs as $dir) {
                $jpg_files = glob($dir . '/*.jpg');
                if (!empty($jpg_files)) {
                    $image_dirs[] = $dir . '/';
                }
            }
            
            // Verarbeite Bilder für jedes Fahrzeug
            $images_imported = 0;
            
            foreach ($csv_results['vehicles'] as $vehicle) {
                // Für jedes Verzeichnis mit Bildern prüfen, ob passende Bilder vorhanden sind
                foreach ($image_dirs as $dir) {
                    $this->image_processor->set_base_dir($dir);
                    $image_result = $this->image_processor->import_images($vehicle['post_id'], $vehicle);
                    
                    if ($image_result['status'] === 'success') {
                        $images_imported += $image_result['count'];
                        break; // Breche ab, wenn Bilder gefunden wurden
                    }
                }
            }
            
            $csv_results['images_imported'] = $images_imported;
        }
        
        // Cleanup temporary directory
        $this->cleanup_temp_dir();
        
        // Prepare results
        return array(
            'processed' => $csv_results['processed'],
            'created' => $csv_results['created'],
            'updated' => $csv_results['updated'],
            'images_imported' => isset($csv_results['images_imported']) ? $csv_results['images_imported'] : 0,
            'errors' => $csv_results['errors'],
            'error_count' => count($csv_results['errors'])
        );
    }
    
    /**
     * Find files recursively using a pattern
     */
    private function find_files_recursive($dir, $pattern) {
        $files = glob($dir . $pattern);
        
        // Suche in Unterordnern
        $dirs = glob($dir . '*', GLOB_ONLYDIR);
        foreach ($dirs as $subdir) {
            $subdir_files = $this->find_files_recursive($subdir . '/', $pattern);
            $files = array_merge($files, $subdir_files);
        }
        
        return $files;
    }
    
    /**
     * Clean up temporary directory
     */
    private function cleanup_temp_dir() {
        if (is_dir($this->temp_dir)) {
            $files = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($this->temp_dir, RecursiveDirectoryIterator::SKIP_DOTS),
                RecursiveIteratorIterator::CHILD_FIRST
            );
            
            foreach ($files as $file) {
                if ($file->isDir()) {
                    rmdir($file->getRealPath());
                } else {
                    unlink($file->getRealPath());
                }
            }
            
            rmdir($this->temp_dir);
        }
    }
}