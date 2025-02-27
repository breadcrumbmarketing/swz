<?php
/**
 * CSV Processor für den Import von Fahrzeugdaten
 */
class ACI_CSV_Processor {
    
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
     * CSV-Datei verarbeiten
     * 
     * @param string $file_path Pfad zur CSV-Datei
     * @param string $delimiter CSV-Trennzeichen (Standard: Komma)
     * @param string $enclosure CSV-Textbegrenzungszeichen (Standard: Anführungszeichen)
     * @return array|WP_Error Array mit CSV-Daten oder WP_Error bei Fehler
     */
    public function process_csv($file_path, $delimiter = ',', $enclosure = '"') {
        // Prüfen, ob die Datei existiert
        if (!file_exists($file_path)) {
            $this->logger->log('Fehler: CSV-Datei nicht gefunden: ' . $file_path, 'error');
            return new WP_Error('file_not_found', __('Die CSV-Datei wurde nicht gefunden.', 'auto-car-importer'));
        }
        
        // Datei öffnen
        $file = fopen($file_path, 'r');
        if (!$file) {
            $this->logger->log('Fehler: Datei konnte nicht geöffnet werden: ' . $file_path, 'error');
            return new WP_Error('file_open_error', __('Die CSV-Datei konnte nicht geöffnet werden.', 'auto-car-importer'));
        }
        
        // Header (Spaltennamen) auslesen
        $header = fgetcsv($file, 0, $delimiter, $enclosure);
        if (!$header) {
            fclose($file);
            $this->logger->log('Fehler: CSV-Header konnte nicht gelesen werden', 'error');
            return new WP_Error('header_error', __('Die CSV-Header konnten nicht gelesen werden.', 'auto-car-importer'));
        }
        
        // Header bereinigen
        $header = array_map('trim', $header);
        
        // Daten auslesen
        $data = array();
        $row_number = 1; // Header ist Zeile 1
        
        while (($row = fgetcsv($file, 0, $delimiter, $enclosure)) !== false) {
            $row_number++;
            
            // Überprüfen, ob die Anzahl der Spalten mit der Header-Anzahl übereinstimmt
            if (count($row) !== count($header)) {
                $this->logger->log("Warnung: Zeile $row_number hat eine falsche Spaltenanzahl", 'warning');
                continue;
            }
            
            // Zeilen-Daten in assoziatives Array umwandeln
            $row_data = array();
            foreach ($header as $index => $column_name) {
                if (isset($row[$index])) {
                    $row_data[$column_name] = $row[$index];
                } else {
                    $row_data[$column_name] = '';
                }
            }
            
            // Prüfen, ob Pflichtfelder vorhanden sind
            if (empty($row_data['interne_nummer']) && empty($row_data['bild_id'])) {
                $this->logger->log("Warnung: Zeile $row_number hat weder eine interne Nummer noch eine Bild-ID", 'warning');
                continue;
            }
            
            $data[] = $row_data;
        }
        
        // Datei schließen
        fclose($file);
        
        $this->logger->log('CSV-Verarbeitung abgeschlossen. ' . count($data) . ' Datensätze gefunden.', 'info');
        
        return $data;
    }
    
    /**
 * Prüfen, ob die CSV Daten gültige Sportwagen-Daten enthalten
 * 
 * @param array $data Die CSV-Daten
 * @return bool|WP_Error True wenn gültig, WP_Error bei Fehler
 */
public function validate_car_data($data) {
    if (!is_array($data) || empty($data)) {
        return new WP_Error('invalid_data', __('Keine gültigen CSV-Daten vorhanden.', 'auto-car-importer'));
    }
    
    $required_fields = array('interne_nummer');
    $validation_errors = array();
    
    // Allgemeine Struktur der Daten prüfen
    foreach ($data as $index => $car) {
        $row_number = $index + 2; // +2 wegen Header-Zeile und 0-basiertem Index
        
        // Prüfen, ob mindestens eine der Identifikationsnummern vorhanden ist
        if (empty($car['interne_nummer']) && empty($car['bild_id'])) {
            $validation_errors[] = sprintf(
                __('Zeile %d: Weder interne_nummer noch bild_id ist angegeben.', 'auto-car-importer'),
                $row_number
            );
            continue;
        }
        
        // Pflichtfelder prüfen
        foreach ($required_fields as $field) {
            if (!isset($car[$field]) || $car[$field] === '') {
                $validation_errors[] = sprintf(
                    __('Zeile %d: Pflichtfeld "%s" fehlt oder ist leer.', 'auto-car-importer'),
                    $row_number,
                    $field
                );
            }
        }
        
        // Gültigkeit der Daten prüfen
        if (isset($car['kilometer']) && !empty($car['kilometer']) && !is_numeric($car['kilometer'])) {
            $validation_errors[] = sprintf(
                __('Zeile %d: Kilometerwert "%s" ist keine gültige Zahl.', 'auto-car-importer'),
                $row_number,
                $car['kilometer']
            );
        }
        
        if (isset($car['preis']) && !empty($car['preis']) && !is_numeric($car['preis'])) {
            $validation_errors[] = sprintf(
                __('Zeile %d: Preiswert "%s" ist keine gültige Zahl.', 'auto-car-importer'),
                $row_number,
                $car['preis']
            );
        }
        
        if (isset($car['leistung']) && !empty($car['leistung']) && !is_numeric($car['leistung'])) {
            $validation_errors[] = sprintf(
                __('Zeile %d: Leistungswert "%s" ist keine gültige Zahl.', 'auto-car-importer'),
                $row_number,
                $car['leistung']
            );
        }
        
        // Boolean-Felder prüfen
        $boolean_fields = array('mwst', 'oldtimer', 'beschaedigtes_fahrzeug', 'metallic', 'jahreswagen', 'neufahrzeug');
        foreach ($boolean_fields as $field) {
            if (isset($car[$field]) && !empty($car[$field]) && !in_array($car[$field], array('0', '1', ''), true)) {
                $validation_errors[] = sprintf(
                    __('Zeile %d: Feld "%s" muss 0 oder 1 sein, ist aber "%s".', 'auto-car-importer'),
                    $row_number,
                    $field,
                    $car[$field]
                );
            }
        }
    }
    
    // Wenn Fehler gefunden wurden, diese zurückgeben
    if (!empty($validation_errors)) {
        $error_message = implode("\n", $validation_errors);
        $this->logger->log('Validierungsfehler: ' . $error_message, 'error');
        return new WP_Error('validation_error', $error_message);
    }
    
    return true;
} 
 /**
 * CSV-Datei aus einer ZIP-Datei extrahieren
 * 
 * @param string $zip_path Pfad zur ZIP-Datei
 * @param string $extract_dir Zielverzeichnis für die extrahierten Dateien
 * @param string $csv_filename Optional: Name der CSV-Datei im ZIP-Archiv
 * @return string|WP_Error Pfad zur extrahierten CSV-Datei oder WP_Error bei Fehler
 */
public function extract_csv_from_zip($zip_path, $extract_dir, $csv_filename = '') {
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
    
    // Wenn ein Dateiname angegeben wurde, direkt nach dieser Datei suchen
    if (!empty($csv_filename)) {
        $this->logger->log('Suche nach angegebener CSV-Datei: ' . $csv_filename, 'info');
        
        // Prüfen, ob die Datei mit exaktem Namen existiert
        if ($zip->locateName($csv_filename) !== false) {
            $csv_file = $csv_filename;
        } 
        // Prüfen, ob die Datei in einem Unterverzeichnis existiert
        else {
            $found = false;
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                if (basename($filename) === $csv_filename) {
                    $csv_file = $filename;
                    $found = true;
                    break;
                }
            }
            
            if (!$found) {
                $zip->close();
                $this->logger->log('Fehler: Angegebene CSV-Datei nicht gefunden: ' . $csv_filename, 'error');
                return new WP_Error('csv_not_found', __('Die angegebene CSV-Datei wurde nicht im ZIP-Archiv gefunden.', 'auto-car-importer'));
            }
        }
    } 
    // Ansonsten nach allen CSV-Dateien suchen
    else {
        $csv_files = array();
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if ($ext === 'csv') {
                $csv_files[] = $filename;
            }
        }
        
        // Keine CSV-Datei gefunden
        if (empty($csv_files)) {
            $file_list = array();
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $file_list[] = $zip->getNameIndex($i);
            }
            $zip->close();
            $this->logger->log('Fehler: Keine CSV-Datei in der ZIP-Datei gefunden. Enthaltene Dateien: ' . implode(', ', $file_list), 'error');
            return new WP_Error('no_csv_found', __('Keine CSV-Datei in der ZIP-Datei gefunden.', 'auto-car-importer'));
        }
        
        // Wenn mehrere CSV-Dateien gefunden wurden, nehmen wir die erste
        $csv_file = $csv_files[0];
        if (count($csv_files) > 1) {
            $this->logger->log('Hinweis: Mehrere CSV-Dateien gefunden (' . implode(', ', $csv_files) . '), verwende: ' . $csv_file, 'info');
        }
    }
    
    // Vollständigen Dateinamen extrahieren (inklusive Unterverzeichnis)
    $csv_filename = basename($csv_file);
    $csv_path = $extract_dir . '/' . $csv_filename;
    
    // Datei extrahieren
    if (!$zip->extractTo($extract_dir, $csv_file)) {
        $zip->close();
        $this->logger->log('Fehler: Datei konnte nicht extrahiert werden: ' . $csv_file, 'error');
        return new WP_Error('extract_error', __('Die CSV-Datei konnte nicht extrahiert werden.', 'auto-car-importer'));
    }
    
    $zip->close();
    
    $this->logger->log('CSV-Datei erfolgreich extrahiert: ' . $csv_path, 'info');
    return $csv_path;
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

}