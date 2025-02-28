<?php
/**
 * CSV Processor برای ایمپورت داده‌های خودرو
 */
class ACI_CSV_Processor {
    
    /**
     * نمونه Logger
     */
    private $logger;
    
    /**
     * سازنده
     * 
     * @param ACI_Logger $logger نمونه Logger
     */
    public function __construct($logger) {
        $this->logger = $logger;
    }
    
    /**
     * فایل CSV را پردازش کنید
     * 
     * @param string $file_path مسیر به فایل CSV
     * @param string $delimiter جداکننده CSV (پیش‌فرض: سمی‌کولن)
     * @param string $enclosure محصورکننده متن CSV (پیش‌فرض: نقل قول)
     * @return array|WP_Error آرایه‌ای از داده‌های CSV یا WP_Error در صورت خطا
     */
    public function process_csv($file_path, $delimiter = ';', $enclosure = '"') {
        // بررسی کنید که آیا فایل وجود دارد
        if (!file_exists($file_path)) {
            $this->logger->log('خطا: فایل CSV پیدا نشد: ' . $file_path, 'error');
            return new WP_Error('file_not_found', __('فایل CSV پیدا نشد.', 'auto-car-importer'));
        }
        
        // فایل را باز کنید
        $file = fopen($file_path, 'r');
        if (!$file) {
            $this->logger->log('خطا: نمی‌توان فایل را باز کرد: ' . $file_path, 'error');
            return new WP_Error('file_open_error', __('نمی‌توان فایل CSV را باز کرد.', 'auto-car-importer'));
        }
        
        // هدر (نام ستون‌ها) را بخوانید
        $header = fgetcsv($file, 0, $delimiter, $enclosure);
        if (!$header) {
            fclose($file);
            $this->logger->log('خطا: نمی‌توان هدر CSV را خواند', 'error');
            return new WP_Error('header_error', __('نمی‌توان هدرهای CSV را خواند.', 'auto-car-importer'));
        }
        
        // هدر را تمیز کنید
        $header = array_map('trim', $header);
        
        // داده‌ها را بخوانید
        $data = array();
        $row_number = 1; // هدر سطر 1 است
        
        while (($row = fgetcsv($file, 0, $delimiter, $enclosure)) !== false) {
            $row_number++;
            
            // بررسی کنید که آیا تعداد ستون‌ها با تعداد هدر مطابقت دارد
            if (count($row) !== count($header)) {
                $this->logger->log("هشدار: سطر $row_number تعداد ستون اشتباهی دارد", 'warning');
                continue;
            }
            
            // داده‌های سطر را به آرایه انجمنی تبدیل کنید
            $row_data = array();
            foreach ($header as $index => $column_name) {
                if (isset($row[$index])) {
                    $row_data[$column_name] = $row[$index];
                } else {
                    $row_data[$column_name] = '';
                }
            }
            
            // بررسی کنید که آیا فیلدهای الزامی وجود دارند
            if (empty($row_data['interne_nummer']) && empty($row_data['bild_id'])) {
                $this->logger->log("هشدار: سطر $row_number نه شماره داخلی و نه ID تصویری دارد", 'warning');
                continue;
            }
            
            $data[] = $row_data;
        }
        
        // فایل را ببندید
        fclose($file);
        
        $this->logger->log('پردازش CSV تکمیل شد. ' . count($data) . ' رکورد پیدا شد.', 'info');
        
        return $data;
    }
    
    /**
     * بررسی کنید که آیا داده‌های CSV حاوی داده‌های معتبر Sportwagen هستند
     * 
     * @param array $data داده‌های CSV
     * @return bool|WP_Error True اگر معتبر است، WP_Error در صورت خطا
     */
    public function validate_car_data($data) {
        if (!is_array($data) || empty($data)) {
            return new WP_Error('invalid_data', __('داده‌های CSV معتبر وجود ندارد.', 'auto-car-importer'));
        }
        
        $required_fields = array('interne_nummer');
        $validation_errors = array();
        
        // ساختار کلی داده‌ها را بررسی کنید
        foreach ($data as $index => $car) {
            $row_number = $index + 2; // +2 به دلیل سطر هدر و ایندکس 0 مبنا
            
            // بررسی کنید که آیا حداقل یکی از شماره‌های شناسایی وجود دارد
            if (empty($car['interne_nummer']) && empty($car['bild_id'])) {
                $validation_errors[] = sprintf(
                    __('سطر %d: نه interne_nummer و نه bild_id مشخص شده است.', 'auto-car-importer'),
                    $row_number
                );
                continue;
            }
            
            // فیلدهای الزامی را بررسی کنید
            foreach ($required_fields as $field) {
                if (!isset($car[$field]) || $car[$field] === '') {
                    $validation_errors[] = sprintf(
                        __('سطر %d: فیلد الزامی "%s" وجود ندارد یا خالی است.', 'auto-car-importer'),
                        $row_number,
                        $field
                    );
                }
            }
            
            // اعتبار داده‌ها را بررسی کنید
            if (isset($car['kilometer']) && !empty($car['kilometer']) && !is_numeric($car['kilometer'])) {
                $validation_errors[] = sprintf(
                    __('سطر %d: مقدار کیلومتر "%s" عدد معتبری نیست.', 'auto-car-importer'),
                    $row_number,
                    $car['kilometer']
                );
            }
            
            if (isset($car['preis']) && !empty($car['preis']) && !is_numeric($car['preis'])) {
                $validation_errors[] = sprintf(
                    __('سطر %d: مقدار قیمت "%s" عدد معتبری نیست.', 'auto-car-importer'),
                    $row_number,
                    $car['preis']
                );
            }
            
            if (isset($car['leistung']) && !empty($car['leistung']) && !is_numeric($car['leistung'])) {
                $validation_errors[] = sprintf(
                    __('سطر %d: مقدار توان "%s" عدد معتبری نیست.', 'auto-car-importer'),
                    $row_number,
                    $car['leistung']
                );
            }
            
            // فیلدهای بولی را بررسی کنید
            $boolean_fields = array('mwst', 'oldtimer', 'beschaedigtes_fahrzeug', 'metallic', 'jahreswagen', 'neufahrzeug');
            foreach ($boolean_fields as $field) {
                if (isset($car[$field]) && !empty($car[$field]) && !in_array($car[$field], array('0', '1', ''), true)) {
                    $validation_errors[] = sprintf(
                        __('سطر %d: فیلد "%s" باید 0 یا 1 باشد، اما "%s" است.', 'auto-car-importer'),
                        $row_number,
                        $field,
                        $car[$field]
                    );
                }
            }
        }
        
        // اگر خطاهایی پیدا شد، آن‌ها را برگردانید
        if (!empty($validation_errors)) {
            $error_message = implode("\n", $validation_errors);
            $this->logger->log('خطای اعتبارسنجی: ' . $error_message, 'error');
            return new WP_Error('validation_error', $error_message);
        }
        
        return true;
    }
    
    /**
     * فایل CSV را از یک فایل ZIP استخراج کنید
     * 
     * @param string $zip_path مسیر به فایل ZIP
     * @param string $extract_dir دایرکتوری مقصد برای فایل‌های استخراج شده
     * @param string $csv_filename اختیاری: نام فایل CSV در آرشیو ZIP
     * @return string|WP_Error مسیر به فایل CSV استخراج شده یا WP_Error در صورت خطا
     */
    public function extract_csv_from_zip($zip_path, $extract_dir, $csv_filename = '') {
        if (!file_exists($zip_path)) {
            $this->logger->log('Fehler: ZIP-Datei nicht gefunden: ' . $zip_path, 'error');
            return new WP_Error('file_not_found', __('Die ZIP-Datei wurde nicht gefunden.', 'auto-car-importer'));
        }
        
        // Überprüfen, ob das Ziel-Verzeichnis existiert, sonst erstellen
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
        
        // Debug - Log alle Dateien im ZIP
        $this->logger->log('ZIP-Inhalt (' . $zip->numFiles . ' Dateien):', 'info');
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $this->logger->log('  - ' . $zip->getNameIndex($i), 'info');
        }
        
        // Wenn ein Dateiname angegeben wurde, direkt nach dieser Datei suchen
        if (!empty($csv_filename)) {
            $this->logger->log('Suche nach angegebener CSV-Datei: ' . $csv_filename, 'info');
            
            // Prüfen, ob die Datei mit exaktem Namen existiert
            $exact_match = false;
            $matched_file = '';
            
            for ($i = 0; $i < $zip->numFiles; $i++) {
                $filename = $zip->getNameIndex($i);
                
                // Exakter Dateiname?
                if ($filename === $csv_filename) {
                    $matched_file = $filename;
                    $exact_match = true;
                    $this->logger->log('Exakte Übereinstimmung gefunden: ' . $filename, 'info');
                    break;
                }
                
                // Nur Dateiname ohne Pfad?
                if (basename($filename) === $csv_filename) {
                    $matched_file = $filename;
                    $this->logger->log('Dateiname-Übereinstimmung gefunden: ' . $filename, 'info');
                    break;
                }
            }
            
            if (!empty($matched_file)) {
                $csv_file = $matched_file;
            } else {
                $zip->close();
                $this->logger->log('Fehler: Angegebene CSV-Datei nicht gefunden: ' . $csv_filename, 'error');
                return new WP_Error('csv_not_found', __('Die angegebene CSV-Datei wurde nicht im ZIP-Archiv gefunden.', 'auto-car-importer'));
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
        if ($zip->extractTo($extract_dir, $csv_file)) {
            $this->logger->log('CSV-Datei extrahiert zu: ' . $csv_path, 'info');
            
            // Überprüfen, ob die Datei tatsächlich existiert
            if (file_exists($csv_path)) {
                $this->logger->log('CSV-Datei existiert am Zielort: ' . $csv_path, 'info');
            } else {
                $this->logger->log('Warnung: CSV-Datei wurde extrahiert, existiert aber nicht am erwarteten Ort: ' . $csv_path, 'warning');
                
                // Versuchen, den Pfad anzupassen, falls die Datei in einem Unterverzeichnis extrahiert wurde
                $adjusted_path = $extract_dir . '/' . $csv_file;
                if (file_exists($adjusted_path)) {
                    $this->logger->log('CSV-Datei an alternativem Ort gefunden: ' . $adjusted_path, 'info');
                    $csv_path = $adjusted_path;
                }
            }
        } else {
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