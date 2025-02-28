<?php
/**
 * Sportwagen Image Processor Class
 * 
 * Processes images for Sportwagen import
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class Sportwagen_Image_Processor {
    
    /**
     * Base directory for images
     */
    private $base_dir;
    
    /**
     * Set base directory
     */
    public function set_base_dir($dir) {
        $this->base_dir = trailingslashit($dir);
    }
    
    /**
     * Import images for a vehicle
     */
    public function import_images($post_id, $vehicle_data) {
        if (empty($this->base_dir)) {
            throw new Exception('Base directory not set');
        }
        
        // Determine image identifier (bild_id or interne_nummer)
        $image_id = !empty($vehicle_data['bild_id']) ? $vehicle_data['bild_id'] : $vehicle_data['interne_nummer'];
        
        // Find images
        $images = $this->find_images($image_id);
        
        if (empty($images)) {
            return array(
                'status' => 'warning',
                'message' => 'Keine Bilder für ' . $image_id . ' gefunden'
            );
        }
        
        // Process images
        $attachment_ids = array();
        
        foreach ($images as $image) {
            $attachment_id = $this->upload_to_media_library($image, $post_id);
            if ($attachment_id) {
                $attachment_ids[] = $attachment_id;
            }
        }
        
        if (empty($attachment_ids)) {
            return array(
                'status' => 'error',
                'message' => 'Fehler beim Importieren der Bilder'
            );
        }
        
        // Set featured image
        set_post_thumbnail($post_id, $attachment_ids[0]);
        
        // Save all images to ACF gallery field if exists
        if (function_exists('update_field')) {
            update_field('fahrzeug_bilder', $attachment_ids, $post_id);
        }
        
        return array(
            'status' => 'success',
            'count' => count($attachment_ids),
            'attachment_ids' => $attachment_ids
        );
    }
    
    /**
     * Find images for a vehicle
     */
    private function find_images($image_id) {
        $images = array();
        
        // Häufigste Benennungsmuster prüfen
        $patterns = array(
            // Standard Muster: ID_1.jpg, ID_2.jpg, etc.
            $this->base_dir . $image_id . '_*.jpg',
            
            // Muster mit führenden Nullen: ID_01.jpg, ID_02.jpg, etc.
            $this->base_dir . $image_id . '_??.jpg',
            
            // Weitere mögliche Muster je nach Kundenerfordernissen
            $this->base_dir . $image_id . '*.jpg',  // ID gefolgt von beliebigen Zeichen
            $this->base_dir . '*' . $image_id . '*.jpg', // ID irgendwo im Dateinamen
            
            // Unterstütze auch andere Bildformate
            $this->base_dir . $image_id . '_*.jpeg',
            $this->base_dir . $image_id . '_*.png',
            $this->base_dir . $image_id . '_*.gif'
        );
        
        foreach ($patterns as $pattern) {
            $found_images = glob($pattern);
            if (!empty($found_images)) {
                $images = array_merge($images, $found_images);
            }
        }
        
        // Entferne doppelte Einträge
        $images = array_unique($images);
        
        return $images;
    }
    
    /**
     * Upload image to media library
     */
    private function upload_to_media_library($image_path, $post_id) {
        // Check if file exists
        if (!file_exists($image_path)) {
            return false;
        }
        
        $filename = basename($image_path);
        $upload_dir = wp_upload_dir();
        
        // Copy image to uploads directory
        $new_file_path = $upload_dir['path'] . '/' . $filename;
        copy($image_path, $new_file_path);
        
        // Set file information
        $wp_filetype = wp_check_filetype($filename, null);
        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_title' => sanitize_file_name($filename),
            'post_content' => '',
            'post_status' => 'inherit'
        );
        
        // Insert image into media library
        $attachment_id = wp_insert_attachment($attachment, $new_file_path, $post_id);
        
        if (!is_wp_error($attachment_id)) {
            // Generate metadata for the image
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_data = wp_generate_attachment_metadata($attachment_id, $new_file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_data);
            
            return $attachment_id;
        }
        
        return false;
    }}