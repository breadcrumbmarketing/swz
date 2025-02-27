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
        
        foreach ($data as $index => $car) {
            $row_number = $index + 2; // +2 wegen Header-Zeile und 0-basiertem Index
            
            // Prüfen, ob mindestens eine der Identifikationsnummern vorhanden ist
            if (empty($car['interne_nummer']) && empty($car['bild_id'])) {
                $validation_errors[] = sprintf(
                    __('Zeile %d: Weder interne_nummer noch bild_id ist angegeben.', 'auto-car-importer'),
                    $row_number
                );
            }
        }
        
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
     * @return string|WP_Error Pfad zur extrahierten CSV-Datei oder WP_Error bei Fehler
     */
    public function extract_csv_from_zip($zip_path, $extract_dir) {
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
        
        // Nach CSV-Dateien suchen
        $csv_path = null;
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            if (pathinfo($filename, PATHINFO_EXTENSION) === 'csv') {
                // CSV-Datei gefunden
                $csv_path = $extract_dir . '/' . basename($filename);
                
                // Datei extrahieren
                if (!$zip->extractTo($extract_dir, basename($filename))) {
                    $zip->close();
                    $this->logger->log('Fehler: Datei konnte nicht extrahiert werden: ' . $filename, 'error');
                    return new WP_Error('extract_error', __('Die CSV-Datei konnte nicht extrahiert werden.', 'auto-car-importer'));
                }
                
                break;
            }
        }
        
        $zip->close();
        
        if ($csv_path === null) {
            $this->logger->log('Fehler: Keine CSV-Datei in der ZIP-Datei gefunden', 'error');
            return new WP_Error('no_csv_found', __('Keine CSV-Datei in der ZIP-Datei gefunden.', 'auto-car-importer'));
        }
        
        $this->logger->log('CSV-Datei erfolgreich extrahiert: ' . $csv_path, 'info');
        
        return $csv_path;
    }
}