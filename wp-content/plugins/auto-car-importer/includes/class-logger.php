<?php
/**
 * Logger für den Auto Car Importer
 */
class ACI_Logger {
    
    /**
     * Log-Einträge
     */
    private $logs = array();
    
    /**
     * Maximale Anzahl an Log-Einträgen
     */
    private $max_logs = 1000;
    
    /**
     * Konstruktor
     */
    public function __construct() {
        // Logs aus der Datenbank laden
        $this->load_logs();
    }
    
    /**
     * Lade bestehende Logs aus der Datenbank
     */
    private function load_logs() {
        $saved_logs = get_option('aci_logs', array());
        
        if (is_array($saved_logs)) {
            $this->logs = $saved_logs;
        }
    }
    
    /**
     * Speichert die Logs in der Datenbank
     */
    private function save_logs() {
        // Auf maximale Anzahl beschränken
        if (count($this->logs) > $this->max_logs) {
            $this->logs = array_slice($this->logs, -$this->max_logs);
        }
        
        update_option('aci_logs', $this->logs);
    }
    
    /**
     * Fügt einen Log-Eintrag hinzu
     * 
     * @param string $message Die Log-Nachricht
     * @param string $level Log-Level (info, warning, error)
     * @param array $context Zusätzliche Kontextinformationen
     */
    public function log($message, $level = 'info', $context = array()) {
        $log_entry = array(
            'time'    => current_time('mysql'),
            'message' => $message,
            'level'   => $level,
            'context' => $context,
        );
        
        $this->logs[] = $log_entry;
        
        // Logs speichern
        $this->save_logs();
        
        // Bei Fehlern zusätzlich in die PHP-Fehlerprotokollierung schreiben
        if ($level === 'error') {
            error_log('[Auto Car Importer] ' . $message);
        }
    }
    
    /**
     * Gibt alle Log-Einträge zurück
     * 
     * @param int $limit Maximale Anzahl der zurückzugebenden Einträge
     * @param string $level Optional: Nur Einträge eines bestimmten Levels zurückgeben
     * @return array Log-Einträge
     */
    public function get_logs($limit = 100, $level = '') {
        // Nach Level filtern, falls angegeben
        if (!empty($level)) {
            $filtered_logs = array_filter($this->logs, function($log) use ($level) {
                return $log['level'] === $level;
            });
        } else {
            $filtered_logs = $this->logs;
        }
        
        // Neueste Einträge zuerst
        $sorted_logs = array_reverse($filtered_logs);
        
        // Auf gewünschte Anzahl begrenzen
        return array_slice($sorted_logs, 0, $limit);
    }
    
    /**
     * Löscht alle Log-Einträge
     */
    public function clear_logs() {
        $this->logs = array();
        $this->save_logs();
    }
    
    /**
     * Erstellt eine CSV-Datei mit den Log-Einträgen
     * 
     * @return string|WP_Error Pfad zur CSV-Datei oder WP_Error bei Fehler
     */
    public function export_logs_to_csv() {
        $upload_dir = wp_upload_dir();
        $csv_file = $upload_dir['basedir'] . '/aci-logs-' . date('Y-m-d-H-i-s') . '.csv';
        
        $fp = fopen($csv_file, 'w');
        
        if (!$fp) {
            return new WP_Error('file_error', __('Konnte CSV-Datei nicht erstellen', 'auto-car-importer'));
        }
        
        // CSV-Header schreiben
        fputcsv($fp, array('Zeit', 'Level', 'Nachricht', 'Kontext'));
        
        // Log-Einträge schreiben
        foreach ($this->logs as $log) {
            fputcsv($fp, array(
                $log['time'],
                $log['level'],
                $log['message'],
                !empty($log['context']) ? json_encode($log['context']) : '',
            ));
        }
        
        fclose($fp);
        
        return $csv_file;
    }
}