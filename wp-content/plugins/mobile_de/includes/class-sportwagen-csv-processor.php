<?php
/**
 * Sportwagen CSV Processor Class
 * 
 * Processes CSV files for Sportwagen import
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Sportwagen_CSV_Processor {
    
    /**
     * Field mapper instance
     */
    private $field_mapper;
    
    /**
     * CSV file path
     */
    private $csv_file;
    
    /**
     * Import statistics
     */
    private $stats = array(
        'processed' => 0,
        'created' => 0,
        'updated' => 0,
        'errors' => array()
    );
    
    /**
     * Constructor
     */
    public function __construct($field_mapper) {
        $this->field_mapper = $field_mapper;
    }
    
    /**
     * Set CSV file
     */
    public function set_csv_file($file_path) {
        $this->csv_file = $file_path;
    }
    
    /**
     * Process CSV file
     */
    public function process($update_existing = true) {
        if (!file_exists($this->csv_file)) {
            throw new Exception('CSV-Datei nicht gefunden');
        }
        
        $handle = fopen($this->csv_file, 'r');
        if (!$handle) {
            throw new Exception('Fehler beim Öffnen der CSV-Datei');
        }
        
        // Reset statistics
        $this->stats = array(
            'processed' => 0,
            'created' => 0,
            'updated' => 0,
            'errors' => array()
        );
        
        // Skip header row if present
        $header = fgetcsv($handle, 0, ';');
        
        while (($data = fgetcsv($handle, 0, ';')) !== false) {
            $this->stats['processed']++;
            
            try {
                $result = $this->process_row($data, $update_existing);
                
                if ($result['action'] === 'created') {
                    $this->stats['created']++;
                } elseif ($result['action'] === 'updated') {
                    $this->stats['updated']++;
                }
            } catch (Exception $e) {
                $this->stats['errors'][] = 'Zeile ' . $this->stats['processed'] . ': ' . $e->getMessage();
            }
        }
        
        fclose($handle);
        
        return $this->stats;
    }
    
    /**
     * Process a single CSV row
     */
    private function process_row($data, $update_existing) {
        // Get internal number (column B)
        $interne_nummer_idx = $this->field_mapper->column_to_index('B');
        $interne_nummer = isset($data[$interne_nummer_idx]) ? sanitize_text_field($data[$interne_nummer_idx]) : '';
        
        if (empty($interne_nummer)) {
            throw new Exception('Interne Nummer fehlt');
        }
        
        // Check if vehicle already exists
        $existing_posts = get_posts(array(
            'post_type' => 'sportwagen',
            'meta_key' => 'interne_nummer',
            'meta_value' => $interne_nummer,
            'posts_per_page' => 1
        ));
        
        $post_id = 0;
        $action = 'created';
        
        if (!empty($existing_posts) && $update_existing) {
            $post_id = $existing_posts[0]->ID;
            $action = 'updated';
        }
        
        // Create title and content for the post
        $marke_idx = $this->field_mapper->column_to_index('D');
        $modell_idx = $this->field_mapper->column_to_index('E');
        
        $marke = isset($data[$marke_idx]) ? sanitize_text_field($data[$marke_idx]) : '';
        $modell = isset($data[$modell_idx]) ? sanitize_text_field($data[$modell_idx]) : '';
        $title = $marke . ' ' . $modell . ' (' . $interne_nummer . ')';
        
        // Get description/remarks (column Z)
        $bemerkung_idx = $this->field_mapper->column_to_index('Z');
        $content = isset($data[$bemerkung_idx]) ? sanitize_textarea_field($data[$bemerkung_idx]) : '';
        
        // Create or update post
        if ($post_id === 0) {
            $post_id = wp_insert_post(array(
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => 'publish',
                'post_type' => 'sportwagen'
            ));
            
            if (is_wp_error($post_id)) {
                throw new Exception('Fehler beim Erstellen des Fahrzeugs: ' . $post_id->get_error_message());
            }
        } else {
            wp_update_post(array(
                'ID' => $post_id,
                'post_title' => $title,
                'post_content' => $content
            ));
        }
        
        // Update ACF fields
        $field_map = $this->field_mapper->get_field_map();
        
        foreach ($field_map as $column => $field_name) {
            $column_idx = $this->field_mapper->column_to_index($column);
            
            if (isset($data[$column_idx]) && $data[$column_idx] !== '') {
                $value = $this->field_mapper->format_field_value($field_name, $data[$column_idx]);
                update_field($field_name, $value, $post_id);
            }
        }
        
        return array(
            'post_id' => $post_id,
            'action' => $action,
            'interne_nummer' => $interne_nummer,
            'bild_id' => isset($data[$this->field_mapper->column_to_index('AA')]) ? $data[$this->field_mapper->column_to_index('AA')] : ''
        );
    }
    
    /**
     * Get import statistics
     */
    public function get_stats() {
        return $this->stats;
    }
}