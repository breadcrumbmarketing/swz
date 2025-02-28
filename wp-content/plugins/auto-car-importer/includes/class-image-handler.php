<?php
/**
 * Image Handler برای مدیریت تصاویر خودرو
 */
class ACI_Image_Handler {
    
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
     * تصاویر را از یک فایل ZIP استخراج کنید
     * 
     * @param string $zip_path مسیر به فایل ZIP
     * @param string $extract_dir دایرکتوری مقصد برای فایل‌های استخراج شده
     * @return array|WP_Error آرایه‌ای از مسیرها به تصاویر استخراج شده یا WP_Error در صورت خطا
     */
    public function extract_images_from_zip($zip_path, $extract_dir) {
        if (!file_exists($zip_path)) {
            $this->logger->log('خطا: فایل ZIP پیدا نشد: ' . $zip_path, 'error');
            return new WP_Error('file_not_found', __('فایل ZIP پیدا نشد.', 'auto-car-importer'));
        }
        
        // بررسی کنید که آیا دایرکتوری مقصد وجود دارد، در غیر این صورت آن را ایجاد کنید
        if (!file_exists($extract_dir)) {
            if (!wp_mkdir_p($extract_dir)) {
                $this->logger->log('خطا: دایرکتوری مقصد را نمی‌توان ایجاد کرد: ' . $extract_dir, 'error');
                return new WP_Error('dir_create_error', __('دایرکتوری مقصد را نمی‌توان ایجاد کرد.', 'auto-car-importer'));
            }
        }
        
        // ZIP-Datei öffnen
        $zip = new ZipArchive();
        if ($zip->open($zip_path) !== true) {
            $this->logger->log('Fehler: ZIP-Datei konnte nicht geöffnet werden: ' . $zip_path, 'error');
            return new WP_Error('zip_open_error', __('Die ZIP-Datei konnte nicht geöffnet werden.', 'auto-car-importer'));
        }
        
        // به دنبال تصاویر بگردید
        $image_paths = array();
        $image_extensions = array('jpg', 'jpeg', 'png', 'gif');
        
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);
            $extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
            
            if (in_array($extension, $image_extensions)) {
                // تصویر پیدا شد
                $image_path = $extract_dir . '/' . basename($filename);
                
                // فایل را استخراج کنید
                if ($zip->extractTo($extract_dir, basename($filename))) {
                    $image_paths[] = $image_path;
                } else {
                    $this->logger->log('هشدار: نمی‌توان فایل را استخراج کرد: ' . $filename, 'warning');
                }
            }
        }
        
        $zip->close();
        
        if (empty($image_paths)) {
            $this->logger->log('هشدار: تصویری در فایل ZIP پیدا نشد', 'warning');
        } else {
            $this->logger->log(count($image_paths) . ' تصویر با موفقیت استخراج شد', 'info');
        }
        
        return $image_paths;
    }
    
    /**
     * نگاشت تصاویر به خودروها بر اساس نام فایل
     * 
     * @param array $image_paths آرایه‌ای از مسیرها به تصاویر
     * @return array آرایه انجمنی با نگاشت ID => تصاویر
     */
    public function map_images_to_cars($image_paths) {
        $image_map = array();
        
        if (!is_array($image_paths)) {
            return $image_map; // آرایه خالی برگردانید اگر ورودی معتبر نیست
        }
        
        foreach ($image_paths as $image_path) {
            $filename = basename($image_path);
            
            // عبارت منظم برای الگوهای تصویر
            // فرمت: bild_id_1.jpg یا bild_id_01.jpg
            if (preg_match('/^(.+?)_(\d+)\.(jpg|jpeg|png|gif)$/i', $filename, $matches)) {
                $car_id = $matches[1];
                $image_number = (int)$matches[2];
                
                if (!isset($image_map[$car_id])) {
                    $image_map[$car_id] = array();
                }
                
                $image_map[$car_id][$image_number] = $image_path;
            } else {
                $this->logger->log('هشدار: نام فایل با فرمت مورد انتظار مطابقت ندارد: ' . $filename, 'warning');
            }
        }
        
        return $image_map;
    }
    
    /**
     * تصاویر یک خودرو را به کتابخانه رسانه وردپرس وارد کنید
     * 
     * @param int $post_id Post-ID خودرو
     * @param array $image_paths آرایه‌ای از مسیرها به تصاویر
     * @return array|WP_Error آرایه‌ای از ID‌های پیوست یا WP_Error در صورت خطا
     */
    public function import_car_images($post_id, $image_paths) {
        if (empty($image_paths) || !is_array($image_paths)) {
            return array();
        }
        
        if (!function_exists('media_handle_sideload')) {
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
        }
        
        $attachment_ids = array();
        
        // پیوست‌های موجود را بازیابی کنید تا از تکراری‌ها جلوگیری شود
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
            
            // بررسی کنید که آیا تصویر از قبل وجود دارد
            if (isset($existing_filenames[$filename])) {
                // تصویر از قبل وجود دارد، از ID پیوست دوباره استفاده کنید
                $attachment_ids[] = $existing_filenames[$filename];
                $this->logger->log("تصویر از قبل وجود دارد، استفاده مجدد: " . $filename, 'info');
                continue;
            }
            
            // بررسی کنید که آیا فایل وجود دارد
            if (!file_exists($image_path)) {
                $this->logger->log("فایل پیدا نشد: " . $image_path, 'error');
                continue;
            }
            
            // فایل را به کتابخانه آپلود کنید
            $file_array = array(
                'name'     => $filename,
                'tmp_name' => $image_path,
                'error'    => 0,
                'size'     => filesize($image_path),
            );
            
            // تصویر را به کتابخانه رسانه آپلود کنید
            $attachment_id = media_handle_sideload($file_array, $post_id);
            
            if (is_wp_error($attachment_id)) {
                $this->logger->log("خطا در آپلود تصویر: " . $attachment_id->get_error_message(), 'error');
            } else {
                $attachment_ids[] = $attachment_id;
                $this->logger->log("تصویر با موفقیت آپلود شد: " . $filename, 'info');
                
                // متا‌دیتای تصویر را به‌روزرسانی کنید
                update_post_meta($attachment_id, '_aci_original_filename', $filename);
            }
        }
        
        // اگر تصاویر موجود هستند، اولین تصویر را به عنوان تصویر شاخص تنظیم کنید
        if (!empty($attachment_ids)) {
            set_post_thumbnail($post_id, $attachment_ids[0]);
            $this->logger->log("تصویر شاخص برای خودروی ID {$post_id} تنظیم شد", 'info');
            
            // فیلد گالری تصاویر ACF را به‌روزرسانی کنید، اگر موجود است
            if (function_exists('update_field') && !empty($attachment_ids)) {
                update_field('bilder_galerie', $attachment_ids, $post_id);
                $this->logger->log("گالری ACF برای خودروی ID {$post_id} به‌روزرسانی شد", 'info');
            }
        }
        
        return $attachment_ids;
    }
    
    /**
     * تصاویر مربوط به یک خودرو را پیدا می‌کند
     * 
     * @param array $car_data داده‌های خودرو از CSV
     * @param array $image_map نگاشت ID‌ها به تصاویر
     * @return array آرایه‌ای از مسیرهای تصویر
     */
    public function get_car_images($car_data, $image_map) {
        $car_images = array();
        
        // ابتدا بر اساس bild_id جستجو کنید، اگر موجود است
        if (!empty($car_data['bild_id']) && isset($image_map[$car_data['bild_id']])) {
            $car_images = $image_map[$car_data['bild_id']];
        } 
        // به صورت جایگزین بر اساس interne_nummer جستجو کنید
        else if (!empty($car_data['interne_nummer']) && isset($image_map[$car_data['interne_nummer']])) {
            $car_images = $image_map[$car_data['interne_nummer']];
        }
        
        // بر اساس شماره تصویر مرتب کنید
        if (!empty($car_images)) {
            ksort($car_images);
        }
        
        return array_values($car_images); // ایندکس‌ها را بازنشانی کنید تا یک آرایه ترتیبی برگردانیم
    }


    /**
 * Wandelt CSV-Daten in das richtige Format für ACF-Felder um und überspringt fehlende Felder
 * 
 * @param array $csv_data Die Rohdaten aus der CSV
 * @return array Konvertierte Daten für ACF-Felder
 */
public function convert_csv_data_for_acf($csv_data) {
    $mapping = $this->get_csv_field_mapping();
    $converted_data = array();
    
    foreach ($csv_data as $key => $value) {
        // Prüfen, ob ein Mapping für dieses Feld existiert
        if (isset($mapping[$key])) {
            $acf_field = $mapping[$key];
            
            // Prüfen, ob das ACF-Feld existiert
            if (!$this->acf_field_exists($acf_field)) {
                $this->logger->log('ACF-Feld nicht gefunden, wird übersprungen: ' . $acf_field, 'warning');
                continue; // Feld überspringen
            }
            
            // Spezielle Konvertierungen für bestimmte Feldtypen
            switch ($acf_field) {
                // Boolesche Felder (0/1 zu true/false)
                case 'mwst':
                case 'oldtimer':
                case 'beschaedigtes_fahrzeug':
                case 'metallic':
                case 'jahreswagen':
                case 'neufahrzeug':
                case 'unsere_empfehlung':
                // Hier können weitere boolesche Felder hinzugefügt werden
                    $converted_data[$acf_field] = ($value == '1');
                    break;
                
                // Numerische Felder
                case 'leistung':
                case 'ccm':
                case 'kilometer':
                case 'preis':
                // Hier können weitere numerische Felder hinzugefügt werden
                    $converted_data[$acf_field] = is_numeric($value) ? (float)$value : $value;
                    break;
                
                // Textfelder bleiben unverändert
                default:
                    $converted_data[$acf_field] = $value;
                    break;
            }
        }
    }
    
    return $converted_data;
}

/**
 * Prüft, ob ein ACF-Feld existiert
 * 
 * @param string $field_name Der Name des ACF-Feldes
 * @return bool True, wenn das Feld existiert, ansonsten False
 */
private function acf_field_exists($field_name) {
    // Wenn ACF nicht aktiv ist, immer false zurückgeben
    if (!function_exists('acf_get_field')) {
        return false;
    }
    
    // Prüfen, ob das Feld existiert
    $field = acf_get_field("field_" . $field_name);
    return !empty($field);
}
}