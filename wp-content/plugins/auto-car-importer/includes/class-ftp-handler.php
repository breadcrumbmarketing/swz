<?php
/**
 * FTP Handler für den Auto Car Importer
 */
class ACI_FTP_Handler {
    
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
        
        // AJAX-Aktionen registrieren
        add_action('wp_ajax_aci_test_ftp', array($this, 'ajax_test_ftp'));
        add_action('wp_ajax_aci_refresh_ftp_files', array($this, 'ajax_refresh_ftp_files'));
    }
    
    /**
     * AJAX-Handler für FTP-Verbindungstest
     */
/**
 * AJAX-Handler für FTP-Verbindungstest
 */
public function ajax_test_ftp() {
    // Logging für Debugging
    $this->logger->log('FTP-Test AJAX-Anfrage empfangen', 'info');
    
    // Nonce prüfen
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aci_ajax_nonce')) {
        $this->logger->log('Ungültiger Nonce bei FTP-Test', 'error');
        wp_send_json_error('Sicherheitsprüfung fehlgeschlagen. Bitte laden Sie die Seite neu und versuchen Sie es erneut.');
        return;
    }
    
    // Berechtigungen prüfen
    if (!current_user_can('manage_options')) {
        $this->logger->log('Fehlende Berechtigungen für FTP-Test', 'error');
        wp_send_json_error('Keine Berechtigung');
        return;
    }
    
    // FTP-Einstellungen holen
    $host = isset($_POST['host']) ? sanitize_text_field($_POST['host']) : '';
    $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
    $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
    $path = isset($_POST['path']) ? sanitize_text_field($_POST['path']) : '/';
    
    $this->logger->log('FTP-Test Parameter: Host=' . $host . ', User=' . $username . ', Pfad=' . $path, 'info');
    
    if (empty($host) || empty($username) || empty($password)) {
        $this->logger->log('Unvollständige FTP-Daten bei Test', 'error');
        wp_send_json_error(__('Bitte füllen Sie alle erforderlichen Felder aus.', 'auto-car-importer'));
        return;
    }
    
    // FTP-Verbindung testen mit zusätzlichem Fehler-Handling
    try {
        // FTP-Verbindung herstellen
        $this->logger->log('Versuche FTP-Verbindung herzustellen: ' . $host, 'info');
        
        $conn_id = @ftp_connect($host);
        if (!$conn_id) {
            $this->logger->log('FTP-Verbindung fehlgeschlagen: ' . $host, 'error');
            wp_send_json_error(__('FTP-Verbindung fehlgeschlagen. Bitte überprüfen Sie den Hostnamen.', 'auto-car-importer'));
            return;
        }
        
        // Anmelden
        $this->logger->log('Versuche FTP-Login: ' . $username, 'info');
        $login_result = @ftp_login($conn_id, $username, $password);
        if (!$login_result) {
            ftp_close($conn_id);
            $this->logger->log('FTP-Anmeldung fehlgeschlagen: ' . $username, 'error');
            wp_send_json_error(__('FTP-Anmeldung fehlgeschlagen. Bitte überprüfen Sie Benutzername und Passwort.', 'auto-car-importer'));
            return;
        }
        
        // Passiven Modus aktivieren
        $this->logger->log('Aktiviere passiven Modus', 'info');
        @ftp_pasv($conn_id, true);
        
        // Verzeichnis wechseln
        $this->logger->log('Wechsle in Verzeichnis: ' . $path, 'info');
        if (!@ftp_chdir($conn_id, $path)) {
            ftp_close($conn_id);
            $this->logger->log('FTP-Verzeichnis nicht gefunden: ' . $path, 'error');
            wp_send_json_error(__('Das angegebene FTP-Verzeichnis konnte nicht gefunden werden.', 'auto-car-importer'));
            return;
        }
        
        // Dateien im Verzeichnis auflisten
        $this->logger->log('Liste Dateien im Verzeichnis', 'info');
        $file_list = @ftp_nlist($conn_id, '.');
        
        // FTP-Verbindung schließen
        ftp_close($conn_id);
        
        if ($file_list === false) {
            $this->logger->log('Keine Dateien im FTP-Verzeichnis gefunden: ' . $path, 'warning');
            wp_send_json_success(__('FTP-Verbindung erfolgreich, aber das Verzeichnis ist leer.', 'auto-car-importer'));
            return;
        }
        
        // Erfolg melden
        $this->logger->log('FTP-Verbindung erfolgreich: ' . count($file_list) . ' Datei(en) gefunden', 'info');
        wp_send_json_success(__('FTP-Verbindung erfolgreich! ' . count($file_list) . ' Datei(en) im Verzeichnis gefunden.', 'auto-car-importer'));
        
    } catch (Exception $e) {
        // Allgemeine Fehlerbehandlung
        $this->logger->log('Ausnahme bei FTP-Test: ' . $e->getMessage(), 'error');
        wp_send_json_error(__('Fehler beim Testen der FTP-Verbindung: ' . $e->getMessage(), 'auto-car-importer'));
    }
}
    
    /**
     * AJAX-Handler zum Aktualisieren der FTP-Dateiliste
     */
    public function ajax_refresh_ftp_files() {
        // Nonce prüfen
        check_ajax_referer('aci_ajax_nonce', 'nonce');
        
        // Berechtigungen prüfen
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        // FTP-Einstellungen laden
        $host = get_option('aci_ftp_host', '');
        $username = get_option('aci_ftp_username', '');
        $password = get_option('aci_ftp_password', '');
        $path = get_option('aci_ftp_path', '/');
        
        if (empty($host) || empty($username) || empty($password)) {
            wp_send_json_error(__('FTP-Einstellungen unvollständig', 'auto-car-importer'));
        }
        
        // FTP-Dateien abrufen
        $files = $this->get_ftp_files($host, $username, $password, $path);
        
        if (is_wp_error($files)) {
            wp_send_json_error($files->get_error_message());
        } else {
            wp_send_json_success($files);
        }
    }
    
    /**
     * FTP-Verbindung testen
     * 
     * @param string $host FTP-Host
     * @param string $username FTP-Benutzername
     * @param string $password FTP-Passwort
     * @param string $path FTP-Pfad
     * @return bool|WP_Error True bei Erfolg, WP_Error bei Fehler
     */
    public function test_connection($host, $username, $password, $path) {
        // FTP-Verbindung herstellen
        $this->logger->log('FTP-Verbindung wird getestet: ' . $host, 'info');
        
        $conn_id = @ftp_connect($host);
        if (!$conn_id) {
            $this->logger->log('FTP-Verbindung fehlgeschlagen: ' . $host, 'error');
            return new WP_Error('ftp_connect_error', __('FTP-Verbindung fehlgeschlagen. Bitte überprüfen Sie den Hostnamen.', 'auto-car-importer'));
        }
        
        // Anmelden
        $login_result = @ftp_login($conn_id, $username, $password);
        if (!$login_result) {
            ftp_close($conn_id);
            $this->logger->log('FTP-Anmeldung fehlgeschlagen: ' . $username, 'error');
            return new WP_Error('ftp_login_error', __('FTP-Anmeldung fehlgeschlagen. Bitte überprüfen Sie Benutzername und Passwort.', 'auto-car-importer'));
        }
        
        // Passiven Modus aktivieren
        @ftp_pasv($conn_id, true);
        
        // Verzeichnis wechseln
        if (!@ftp_chdir($conn_id, $path)) {
            ftp_close($conn_id);
            $this->logger->log('FTP-Verzeichnis nicht gefunden: ' . $path, 'error');
            return new WP_Error('ftp_chdir_error', __('Das angegebene FTP-Verzeichnis konnte nicht gefunden werden.', 'auto-car-importer'));
        }
        
        // Dateien im Verzeichnis auflisten
        $file_list = @ftp_nlist($conn_id, '.');
        if ($file_list === false) {
            ftp_close($conn_id);
            $this->logger->log('Keine Dateien im FTP-Verzeichnis gefunden: ' . $path, 'warning');
            return new WP_Error('ftp_nlist_error', __('Keine Dateien im FTP-Verzeichnis gefunden.', 'auto-car-importer'));
        }
        
        // FTP-Verbindung schließen
        ftp_close($conn_id);
        
        // Erfolg melden
        $this->logger->log('FTP-Verbindung erfolgreich hergestellt: ' . $host . $path, 'info');
        return true;
    }
    
    /**
     * ZIP-Dateien im FTP-Verzeichnis abrufen
     * 
     * @param string $host FTP-Host
     * @param string $username FTP-Benutzername
     * @param string $password FTP-Passwort
     * @param string $path FTP-Pfad
     * @return array|WP_Error Array mit Dateien oder WP_Error bei Fehler
     */
    public function get_ftp_files($host, $username, $password, $path) {
        // FTP-Verbindung herstellen
        $conn_id = @ftp_connect($host);
        if (!$conn_id) {
            $this->logger->log('FTP-Verbindung fehlgeschlagen: ' . $host, 'error');
            return new WP_Error('ftp_connect_error', __('FTP-Verbindung fehlgeschlagen.', 'auto-car-importer'));
        }
        
        // Anmelden
        $login_result = @ftp_login($conn_id, $username, $password);
        if (!$login_result) {
            ftp_close($conn_id);
            $this->logger->log('FTP-Anmeldung fehlgeschlagen: ' . $username, 'error');
            return new WP_Error('ftp_login_error', __('FTP-Anmeldung fehlgeschlagen.', 'auto-car-importer'));
        }
        
        // Passiven Modus aktivieren
        @ftp_pasv($conn_id, true);
        
        // Verzeichnis wechseln
        if (!@ftp_chdir($conn_id, $path)) {
            ftp_close($conn_id);
            $this->logger->log('FTP-Verzeichnis nicht gefunden: ' . $path, 'error');
            return new WP_Error('ftp_chdir_error', __('Das angegebene FTP-Verzeichnis konnte nicht gefunden werden.', 'auto-car-importer'));
        }
        
        // Dateien im Verzeichnis auflisten
        $file_list = @ftp_nlist($conn_id, '.');
        if ($file_list === false) {
            ftp_close($conn_id);
            $this->logger->log('Keine Dateien im FTP-Verzeichnis gefunden: ' . $path, 'warning');
            return new WP_Error('ftp_nlist_error', __('Keine Dateien im FTP-Verzeichnis gefunden.', 'auto-car-importer'));
        }
        
        // Nach ZIP-Dateien filtern
        $zip_files = array();
        foreach ($file_list as $file) {
            $file_name = basename($file);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if ($file_ext === 'zip') {
                // Dateigröße abrufen
                $filesize = @ftp_size($conn_id, $file);
                if ($filesize != -1) {
                    $zip_files[] = array(
                        'name' => $file_name,
                        'size' => $filesize,
                        'path' => $file
                    );
                }
            }
        }
        
        // FTP-Verbindung schließen
        ftp_close($conn_id);
        
        return $zip_files;
    }
    
    /**
     * FTP-Datei herunterladen
     * 
     * @param string $host FTP-Host
     * @param string $username FTP-Benutzername
     * @param string $password FTP-Passwort
     * @param string $remote_file Remote-Dateipfad
     * @param string $local_file Lokaler Dateipfad
     * @return bool|WP_Error True bei Erfolg, WP_Error bei Fehler
     */
    public function download_file($host, $username, $password, $remote_file, $local_file) {
        // FTP-Verbindung herstellen
        $conn_id = @ftp_connect($host);
        if (!$conn_id) {
            $this->logger->log('FTP-Verbindung fehlgeschlagen: ' . $host, 'error');
            return new WP_Error('ftp_connect_error', __('FTP-Verbindung fehlgeschlagen.', 'auto-car-importer'));
        }
        
        // Anmelden
        $login_result = @ftp_login($conn_id, $username, $password);
        if (!$login_result) {
            ftp_close($conn_id);
            $this->logger->log('FTP-Anmeldung fehlgeschlagen: ' . $username, 'error');
            return new WP_Error('ftp_login_error', __('FTP-Anmeldung fehlgeschlagen.', 'auto-car-importer'));
        }
        
        // Passiven Modus aktivieren
        @ftp_pasv($conn_id, true);
        
        // Datei herunterladen
        $result = @ftp_get($conn_id, $local_file, $remote_file, FTP_BINARY);
        
        // FTP-Verbindung schließen
        ftp_close($conn_id);
        
        if (!$result) {
            $this->logger->log('Fehler beim Herunterladen der Datei: ' . $remote_file, 'error');
            return new WP_Error('ftp_get_error', __('Fehler beim Herunterladen der Datei.', 'auto-car-importer'));
        }
        
        $this->logger->log('Datei erfolgreich heruntergeladen: ' . $remote_file, 'info');
        return true;
    }
}