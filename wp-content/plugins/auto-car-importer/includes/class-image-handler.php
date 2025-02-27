<?php
/**
 * Image Handler für die Verwaltung von Fahrzeugbildern
 */
class ACI_Image_Handler {
    
    /**
     * Logger Instanz
     */
    private $logger;
    
    /**
     * Konstruktor
     * 
     * @param ACI_Logger $logger Logger Instanz
     */
    public function __construct($logger) {
        $this->logger = $logger;
    }
    
    /**
     * Bilder aus einer ZIP-Datei extrahieren
     * 
     * @param string $zip_path Pfad zur ZIP-Datei
     * @param string $extract_dir Zielverzeichnis für die extrahierten Dateien
     * @return array|WP_Error Array mit Pfaden zu den extrahierten Bildern oder WP_Error bei Fehler
     */
    public function extract_images_from_zip($zip_path, $extract_dir) {
        if (!file_exists($zip_path)) {
            $this->logger->log('Fehler: ZIP-Datei nicht gefunden: ' . $zip_path, 'error');
            return new WP_Error('file_not_found', __('Die ZIP-Datei wurde nicht gefunden.', 'auto-car-importer'));
        }
        
        // Prüfen, ob das Ziel-Verzeichnis existiert, sonst erstellen
        if (!file_exists($extract_dir)) {
            if (!wp_mkdir_p($extract_dir)) {
                $this->logger->log('Fehler: Zielverzeichnis konnte nicht erstellt werden: ' . $extract_dir, 'error');
                return new WP_Error('dir_create_error', __('Das Zielverzeichnis konnte nicht erstellt werden.', 'auto-car-importer'));
            }
        }
        
        // ZIP-Datei öffnen
        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== true) {
            $this->logger->log('Fehler: ZIP-Datei konnte nicht geöffnet werden: ' . $zip_path, 'error');
            return new WP_Error('zip_open_error', __('Die ZIP-Datei konnte nicht geöffnet werden.', 'auto-car-importer'));
        }
        
        // Nach Bilddateien suchen
        $image_paths = array();
        $image_extensions = array('jpg', 'jpeg', 'png', 'gif');
        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($extension, $image_extensions)) {
                // Bilddatei gefunden
                $image_path = $extract_dir . '/' . basename($filename);
                
                // Datei extrahieren
                if ($zip->extractTo($extract_dir, basename($filename))) {
                    $image_paths[] = $image_path;
                } else {
                    $this->logger->log('Warnung: Datei konnte nicht extrahiert werden: ' . $filename, 'warning');
                }
            }
        }
        
        $zip->close();
        
        if (empty($image_paths)) {
            $this->logger->log('Warnung: Keine Bilddateien in der ZIP-Datei gefunden', 'warning');
        } else {
            $this->logger->log(count($image_paths) . ' Bilddateien erfolgreich extrahiert', 'info');
        }
        
        return $image_paths;
    }
    
    /**
     * Zuordnung von Bildern zu Fahrzeugen basierend auf Dateinamen
     * 
     * @param array $image_paths Array mit Pfaden zu den Bildern
     * @return array Assoziatives Array mit Zuordnung ID => Bilder
     */
    public function map_images_to_cars($image_paths) {
        $image_map = array();
        
        foreach ($image_paths as $image_path) {
            $filename = basename($image_path);
            
            // Regulärer Ausdruck für die Bildmuster
            // Format: bild_id_1.jpg oder bild_id_01.jpg
            if (preg_match('/^(.+?)_(\d+)\.(jpg|jpeg|png|gif)$/i', $filename, $matches)) {
                $car_id = $matches[1];
                $image_number = (int)$matches[2];
                
                if (!isset($image_map[$car_id])) {
                    $image_map[$car_id] = array();
                }
                
                $image_map[$car_id][$image_number] = $image_path;
            } else {
                $this->logger->log('Warnung: Dateiname entspricht nicht dem erwarteten Format: ' . $filename, 'warning');
            }
        }
        
        return $image_map;
    }
    
    /**
     * Bilder zu einem Fahrzeug in die WordPress-Mediathek importieren
     * 
     * @param int $post_id Die Post-ID des Fahrzeugs
     * @param array $image_paths Array mit Pfaden zu den Bildern
     * @return array|WP_Error Array mit Attachment-IDs oder WP_Error bei Fehler
     */
    public function import_car_images($post_id, $image_paths) {
        if (empty($image_paths)) {
            return array();
        }
        
        if (!function_exists('media_handle_upload')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
        }
        
        $attachment_ids = array();
        
        // Vorhandene Anhänge abrufen, um Duplikate zu vermeiden
        $existing_attachments = get_posts(array(
            'post_type'      => 'attachment',
            'posts_per_page' => -1,
            'post_parent'    => $post_id,
            'fields'         => 'ids',
        ));
        
        $existing_filenames = array();
        foreach ($existing_attachments as $attachment_id) {
            $filename = basename(get_attached_file($attachment_id));
            $existing_filenames[$filename] = $attachment_id;
        }
        
        foreach ($image_paths as $image_path) {
            $filename = basename($image_path);
            
            // Prüfen, ob das Bild bereits existiert
            if (isset($existing_filenames[$filename])) {
                // Bild existiert bereits, Attachment-ID wiederverwenden
                $attachment_ids[] = $existing_filenames[$filename];
                $this->logger->log("Bild bereits vorhanden, wird wiederverwendet: " . $filename, 'info');
                continue;
            }
            
            // Prüfen, ob die Datei existiert
            if (!file_exists($image_path)) {
                $this->logger->log("Datei nicht gefunden: " . $image_path, 'error');
                continue;
            }
            
            // Datei in die Mediathek hochladen
            $file_array = array(
                'name'     => $filename,
                'tmp_name' => $image_path,
                'error'    => 0,
                'size'     => filesize($image_path),
            );
            
            // Bild in die Mediathek hochladen
            $attachment_id = media_handle_sideload($file_array, $post_id);
            
            if (is_wp_error($attachment_id)) {
                $this->logger->log("Fehler beim Hochladen des Bildes: " . $attachment_id->get_error_message(), 'error');
            } else {
                $attachment_ids[] = $attachment_id;
                $this->logger->log("Bild erfolgreich hochgeladen: " . $filename, 'info');
                
                // Bild-Metadaten aktualisieren
                update_post_meta($attachment_id, '_aci_original_filename', $filename);
            }
        }
        
        // Wenn Bilder vorhanden sind, das erste als Feature Image setzen
        if (!empty($attachment_ids)) {
            set_post_thumbnail($post_id, $attachment_ids[0]);
            $this->logger->log("Feature Image für Fahrzeug ID {$post_id} gesetzt", 'info');
            
            // ACF Bildergalerie-Feld aktualisieren, falls vorhanden
            if (function_exists('update_field') && !empty($attachment_ids)) {
                update_field('bilder_galerie', $attachment_ids, $post_id);
                $this->logger->log("ACF Galerie für Fahrzeug ID {$post_id} aktualisiert", 'info');
            }
        }
        
        return $attachment_ids;
    }
    
    /**
     * Findet die zugehörigen Bilder für ein Fahrzeug
     * 
     * @param array $car_data Die Fahrzeugdaten aus der CSV
     * @param array $image_map Die Zuordnung von IDs zu Bildern
     * @return array Array mit Bildpfaden
     */
    public function get_car_images($car_data, $image_map) {
        $car_images = array();
        
        // Primär nach bild_id suchen, falls vorhanden
        if (!empty($car_data['bild_id']) && isset($image_map[$car_data['bild_id']])) {
            $car_images = $image_map[$car_data['bild_id']];
        } 
        // Alternativ nach interne_nummer suchen
        else if (!empty($car_data['interne_nummer']) && isset($image_map[$car_data['interne_nummer']])) {
            $car_images = $image_map[$car_data['interne_nummer']];
        }
        
        // Nach Bildnummer sortieren
        if (!empty($car_images)) {
            ksort($car_images);
        }
        
        return array_values($car_images); // Indizes zurücksetzen, damit wir ein sequentielles Array zurückgeben
    }
}