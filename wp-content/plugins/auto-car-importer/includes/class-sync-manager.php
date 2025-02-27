<?php
/**
 * Sync Manager für Import und Aktualisierung von Fahrzeugdaten
 */
class ACI_Sync_Manager {
    
    /**
     * CSV Processor Instanz
     */
    private $csv_processor;
    
    /**
     * Image Handler Instanz
     */
    private $image_handler;
    
    /**
     * ACF Manager Instanz
     */
    private $acf_manager;
    
    /**
     * Logger Instanz
     */
    private $logger;
    
    /**
     * Statistiken für den aktuellen Sync-Vorgang
     */
    private $stats = array(
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'images_total' => 0,
        'images_new' => 0,
    );
    
    /**
     * Konstruktor
     * 
     * @param ACI_CSV_Processor $csv_processor CSV Processor Instanz
     * @param ACI_Image_Handler $image_handler Image Handler Instanz
     * @param ACI_ACF_Manager $acf_manager ACF Manager Instanz
     * @param ACI_Logger $logger Logger Instanz
     */
    public function __construct($csv_processor, $image_handler, $acf_manager, $logger) {
        $this->csv_processor = $csv_processor;
        $this->image_handler = $image_handler;
        $this->acf_manager = $acf_manager;
        $this->logger = $logger;
    }
    
    /**
     * Verarbeitet eine ZIP-Datei, extrahiert CSV und Bilder, und importiert die Daten
     * 
     * @param string $zip_path Pfad zur ZIP-Datei
     * @param array $options Import-Optionen
     * @return array Statistiken des Import-Vorgangs
     */
    public function process_zip_file($zip_path, $options = array()) {
        $this->logger->log('Verarbeite ZIP-Datei: ' . $zip_path, 'info');
        
        // Debug-Informationen zur ZIP-Datei anzeigen
        $this->debug_zip_contents($zip_path);
        
        // Upload-Verzeichnis vorbereiten
        $upload_dir = wp_upload_dir();
        $extract_dir = $upload_dir['basedir'] . '/aci-temp';
        $csv_extract_dir = $extract_dir . '/csv';
        $images_extract_dir = $extract_dir . '/images';
        
        // Verzeichnisse erstellen, falls sie nicht existieren
        wp_mkdir_p($csv_extract_dir);
        wp_mkdir_p($images_extract_dir);
        
        // CSV-Datei extrahieren, mit Option für Dateinamen
        $csv_filename = isset($options['csv_filename']) ? $options['csv_filename'] : '';
        $csv_path = $this->csv_processor->extract_csv_from_zip($zip_path, $csv_extract_dir, $csv_filename);
        
        if (is_wp_error($csv_path)) {
            $this->logger->log('Fehler beim Extrahieren der CSV-Datei: ' . $csv_path->get_error_message(), 'error');
            $this->stats['errors']++;
            return $this->stats;
        }
        
        $this->logger->log('CSV-Datei gefunden und extrahiert: ' . $csv_path, 'info');
        
        // Bilder extrahieren
        $image_paths = $this->image_handler->extract_images_from_zip($zip_path, $images_extract_dir);
        
        if (is_wp_error($image_paths)) {
            $this->logger->log('Fehler beim Extrahieren der Bilder: ' . $image_paths->get_error_message(), 'error');
            $this->stats['errors']++;
            // Trotzdem mit dem CSV-Import fortfahren
        } else {
            $this->stats['images_total'] = count($image_paths);
            $this->logger->log($this->stats['images_total'] . ' Bilder erfolgreich extrahiert', 'info');
        }
        
        // Bilder zu Fahrzeugen zuordnen
        $image_map = $this->image_handler->map_images_to_cars($image_paths);
        $this->logger->log('Bilder zu ' . count($image_map) . ' Fahrzeugen zugeordnet', 'info');
        
        // CSV-Daten importieren
        $csv_data = $this->csv_processor->process_csv($csv_path, $options['delimiter'] ?? ',', $options['enclosure'] ?? '"');
        
        if (is_wp_error($csv_data)) {
            $this->logger->log('Fehler bei der CSV-Verarbeitung: ' . $csv_data->get_error_message(), 'error');
            $this->stats['errors']++;
            return $this->stats;
        }
        
        $this->logger->log('CSV-Verarbeitung abgeschlossen. ' . count($csv_data) . ' Datensätze gefunden.', 'info');
        
        // Daten validieren
        $validation = $this->csv_processor->validate_car_data($csv_data);
        
        if (is_wp_error($validation)) {
            $this->logger->log('Validierungsfehler: ' . $validation->get_error_message(), 'error');
            $this->stats['errors']++;
            return $this->stats;
        }
        
        $this->stats['total'] = count($csv_data);
        $this->logger->log('Start des Imports von ' . $this->stats['total'] . ' Fahrzeugen mit ' . $this->stats['images_total'] . ' Bildern', 'info');
        
        // CPT Manager initialisieren
        $cpt_manager = new ACI_CPT_Manager();
        
        // Jedes Fahrzeug importieren
        foreach ($csv_data as $car) {
            // Eindeutige Kennung ermitteln (bild_id hat Priorität, dann interne_nummer)
            $car_identifier = !empty($car['bild_id']) ? $car['bild_id'] : $car['interne_nummer'];
            
            // Bilder für dieses Fahrzeug finden
            $car_images = $this->image_handler->get_car_images($car, $image_map);
            
            // Option zum Überspringen von Fahrzeugen ohne Bilder
            if ($options['skip_without_images'] && empty($car_images)) {
                $this->logger->log('Fahrzeug übersprungen (keine Bilder): ' . $car_identifier, 'info');
                $this->stats['skipped']++;
                continue;
            }
            
            // Anreichern der Fahrzeugdaten mit Bildpfaden
            $car['_image_paths'] = $car_images;
            
            // Vorhandenes Fahrzeug suchen
            $existing_car_id = null;
            
            if (!empty($car['bild_id'])) {
                $existing_car_id = $cpt_manager->get_car_by_bild_id($car['bild_id']);
            }
            
            if (!$existing_car_id && !empty($car['interne_nummer'])) {
                $existing_car_id = $cpt_manager->get_car_by_interne_nummer($car['interne_nummer']);
            }
            
            // Entscheiden, ob wir aktualisieren oder neu erstellen
            if ($existing_car_id) {
                if ($options['update_existing']) {
                    $result = $this->update_car($existing_car_id, $car);
                    
                    if (is_wp_error($result)) {
                        $this->logger->log('Fehler beim Aktualisieren des Fahrzeugs ' . $car_identifier . ': ' . $result->get_error_message(), 'error');
                        $this->stats['errors']++;
                    } else {
                        $this->stats['updated']++;
                    }
                } else {
                    $this->logger->log('Fahrzeug übersprungen (bereits vorhanden): ' . $car_identifier, 'info');
                    $this->stats['skipped']++;
                }
            } else {
                $result = $this->create_car($car);
                
                if (is_wp_error($result)) {
                    $this->logger->log('Fehler beim Erstellen des Fahrzeugs ' . $car_identifier . ': ' . $result->get_error_message(), 'error');
                    $this->stats['errors']++;
                } else {
                    $this->stats['created']++;
                }
            }
        }
        
        $this->logger->log('Import abgeschlossen. ' . 
            'Erstellt: ' . $this->stats['created'] . ', ' . 
            'Aktualisiert: ' . $this->stats['updated'] . ', ' . 
            'Übersprungen: ' . $this->stats['skipped'] . ', ' . 
            'Fehler: ' . $this->stats['errors'], 'info');
        
        // Temporäre Dateien aufräumen
        $this->cleanup_temp_files($extract_dir);
        
        return $this->stats;
    }
    
    /**
     * Erstellt einen neuen Sportwagen-Eintrag
     * 
     * @param array $car_data Die Fahrzeugdaten
     * @return int|WP_Error Post-ID bei Erfolg, WP_Error bei Fehler
     */
    private function create_car($car_data) {
        // Titel aus den Daten generieren
        $title = '';
        if (!empty($car_data['marke']) && !empty($car_data['modell'])) {
            $title = $car_data['marke'] . ' ' . $car_data['modell'];
            
            if (!empty($car_data['baujahr'])) {
                $title .= ' (' . $car_data['baujahr'] . ')';
            }
        } else {
            $title = 'Sportwagen ' . (!empty($car_data['interne_nummer']) ? $car_data['interne_nummer'] : uniqid());
        }
        
        // Neues Fahrzeug erstellen
        $post_data = array(
            'post_title'   => $title,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'sportwagen',
        );
        
        // Beschreibung als Inhalt, falls vorhanden
        if (!empty($car_data['bemerkung'])) {
            $post_data['post_content'] = $car_data['bemerkung'];
        }
        
        // Post erstellen
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // ACF-Felder aktualisieren
        $result = $this->acf_manager->update_car_acf_fields($post_id, $car_data);
        
        if (is_wp_error($result)) {
            $this->logger->log('Fehler beim Aktualisieren der ACF-Felder: ' . $result->get_error_message(), 'error');
        }
        
        // Bilder importieren, falls vorhanden
        if (!empty($car_data['_image_paths'])) {
            $attachment_ids = $this->image_handler->import_car_images($post_id, $car_data['_image_paths']);
            $this->stats['images_new'] += count($attachment_ids);
        }
        
        $this->logger->log('Neues Fahrzeug erstellt: ' . $title . ' (ID: ' . $post_id . ')', 'info');
        
        return $post_id;
    }
    
    /**
     * Aktualisiert einen vorhandenen Sportwagen-Eintrag
     * 
     * @param int $post_id Die Post-ID des zu aktualisierenden Fahrzeugs
     * @param array $car_data Die neuen Fahrzeugdaten
     * @return int|WP_Error Post-ID bei Erfolg, WP_Error bei Fehler
     */
    private function update_car($post_id, $car_data) {
        // Titel aus den Daten generieren
        $title = '';
        if (!empty($car_data['marke']) && !empty($car_data['modell'])) {
            $title = $car_data['marke'] . ' ' . $car_data['modell'];
            
            if (!empty($car_data['baujahr'])) {
                $title .= ' (' . $car_data['baujahr'] . ')';
            }
        } else {
            // Bestehenden Titel beibehalten
            $post = get_post($post_id);
            $title = $post->post_title;
        }
        
        // Fahrzeugdaten aktualisieren
        $post_data = array(
            'ID'           => $post_id,
            'post_title'   => $title,
        );
        
        // Beschreibung als Inhalt, falls vorhanden
        if (!empty($car_data['bemerkung'])) {
            $post_data['post_content'] = $car_data['bemerkung'];
        }
        
        // Post aktualisieren
        $result = wp_update_post($post_data, true);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // ACF-Felder aktualisieren
        $result = $this->acf_manager->update_car_acf_fields($post_id, $car_data);
        
        if (is_wp_error($result)) {
            $this->logger->log('Fehler beim Aktualisieren der ACF-Felder: ' . $result->get_error_message(), 'error');
        }
        
        // Bilder importieren, falls vorhanden
        if (!empty($car_data['_image_paths'])) {
            $attachment_ids = $this->image_handler->import_car_images($post_id, $car_data['_image_paths']);
            $this->stats['images_new'] += count($attachment_ids);
        }
        
        $this->logger->log('Fahrzeug aktualisiert: ' . $title . ' (ID: ' . $post_id . ')', 'info');
        
        return $post_id;
    }
    
    /**
     * Den Inhalt einer ZIP-Datei protokollieren
     * 
     * @param string $zip_path Pfad zur ZIP-Datei
     * @return void
     */
    private function debug_zip_contents($zip_path) {
        if (!file_exists($zip_path)) {
            $this->logger->log('Fehler: ZIP-Datei für Debug nicht gefunden: ' . $zip_path, 'error');
            return;
        }
        
        try {
            $zip = new ZipArchive();
            if ($zip->open($zip_path) === TRUE) {
                $this->logger->log('=== ZIP-Datei Inhalt Debug ===', 'info');
                $this->logger->log('ZIP-Datei: ' . $zip_path, 'info');
                $this->logger->log('Anzahl Dateien: ' . $zip->numFiles, 'info');
                
                // Alle Dateien auflisten
                $files_by_type = array();
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (!isset($files_by_type[$ext])) {
                        $files_by_type[$ext] = array();
                    }
                    
                    $files_by_type[$ext][] = $filename;
                }
                
                // Nach Dateityp gruppiert loggen
                foreach ($files_by_type as $ext => $files) {
                    $this->logger->log('Typ ".' . $ext . '": ' . count($files) . ' Dateien', 'info');
                    // Maximal 10 Dateien pro Typ anzeigen, um das Log nicht zu überfüllen
                    $sample_files = array_slice($files, 0, 10);
                    $this->logger->log('Beispiele: ' . implode(', ', $sample_files), 'info');
                }
                
                $zip->close();
                $this->logger->log('=== Ende ZIP-Datei Debug ===', 'info');
            } else {
                $this->logger->log('Konnte ZIP-Datei für Debug nicht öffnen: ' . $zip_path, 'error');
            }
        } catch (Exception $e) {
            $this->logger->log('Fehler beim Debug der ZIP-Datei: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Räumt temporäre Dateien auf
     * 
     * @param string $dir Das zu bereinigende Verzeichnis
     */
    private function cleanup_temp_files($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        // Verzeichnisinhalt auslesen
        $files = glob($dir . '/{,.}*', GLOB_BRACE);
        
        foreach ($files as $file) {
            $basename = basename($file);
            
            // '.' und '..' überspringen
            if ($basename === '.' || $basename === '..') {
                continue;
            }
            
            if (is_dir($file)) {
                // Unterverzeichnis rekursiv löschen
                $this->cleanup_temp_files($file);
                @rmdir($file);
            } else {
                // .htaccess nicht löschen
                if ($basename !== '.htaccess') {
                    @unlink($file);
                }
            }
        }
    }
}