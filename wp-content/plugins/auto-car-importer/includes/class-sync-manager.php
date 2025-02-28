<?php
/**
 * Sync Manager برای Import و آپدیت داده‌های خودرو
 */
class ACI_Sync_Manager {
    
    /**
     * CSV Processor نمونه
     */
    private $csv_processor;
    
    /**
     * Image Handler نمونه
     */
    private $image_handler;
    
    /**
     * Logger نمونه
     */
    private $logger;
    
    /**
     * آمار برای عملیات sync فعلی
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
     * سازنده
     * 
     * @param ACI_CSV_Processor $csv_processor CSV Processor نمونه
     * @param ACI_Image_Handler $image_handler Image Handler نمونه
     * @param ACI_Logger $logger Logger نمونه
     */
    public function __construct($csv_processor, $image_handler, $logger) {
        $this->csv_processor = $csv_processor;
        $this->image_handler = $image_handler;
        $this->logger = $logger;
    }
    
    /**
     * پردازش یک فایل ZIP، استخراج CSV و تصاویر، و ایمپورت داده‌ها
     * 
     * @param string $zip_path مسیر به فایل ZIP
     * @param array $options گزینه‌های ایمپورت
     * @return array آمار عملیات ایمپورت
     */
    public function process_zip_file($zip_path, $options = array()) {
        // آمار را بازنشانی کنید
        $this->stats = array(
            'total' => 0,
            'created' => 0,
            'updated' => 0,
            'skipped' => 0,
            'errors' => 0,
            'images_total' => 0,
            'images_new' => 0,
        );
        
        $this->logger->log('پردازش فایل ZIP: ' . $zip_path, 'info');
        
        // اطلاعات دیباگ برای محتوای فایل ZIP نمایش داده شود
        $this->debug_zip_contents($zip_path);
        
        // دایرکتوری آپلود را آماده کنید
        $upload_dir = wp_upload_dir();
        $extract_dir = $upload_dir['basedir'] . '/aci-temp';
        $csv_extract_dir = $extract_dir . '/csv';
        $images_extract_dir = $extract_dir . '/images';
        
        // دایرکتوری‌ها را ایجاد کنید اگر وجود ندارند
        wp_mkdir_p($csv_extract_dir);
        wp_mkdir_p($images_extract_dir);
        
        // فایل CSV را استخراج کنید، با گزینه برای نام فایل
        $csv_filename = isset($options['csv_filename']) ? $options['csv_filename'] : '';
        $csv_path = $this->csv_processor->extract_csv_from_zip($zip_path, $csv_extract_dir, $csv_filename);
        
        if (is_wp_error($csv_path)) {
            $this->logger->log('خطا در استخراج فایل CSV: ' . $csv_path->get_error_message(), 'error');
            $this->stats['errors']++;
            return $this->stats;
        }
        
        $this->logger->log('فایل CSV پیدا شد و استخراج شد: ' . $csv_path, 'info');
        
        // تصاویر را استخراج کنید
        $image_paths = $this->image_handler->extract_images_from_zip($zip_path, $images_extract_dir);
        
        if (is_wp_error($image_paths)) {
            $this->logger->log('خطا در استخراج تصاویر: ' . $image_paths->get_error_message(), 'error');
            $this->stats['errors']++;
            // با این وجود با ایمپورت CSV ادامه دهید
        } else {
            $this->stats['images_total'] = count($image_paths);
            $this->logger->log($this->stats['images_total'] . ' تصویر با موفقیت استخراج شد', 'info');
        }
        
        // تصاویر را به خودروها تخصیص دهید
        $image_map = $this->image_handler->map_images_to_cars($image_paths);
        $this->logger->log('تصاویر به ' . count($image_map) . ' خودرو تخصیص داده شد', 'info');
        
        // داده‌های CSV را ایمپورت کنید
        $csv_data = $this->csv_processor->process_csv($csv_path, $options['delimiter'] ?? ';', $options['enclosure'] ?? '"');
        
        if (is_wp_error($csv_data)) {
            $this->logger->log('خطا در پردازش CSV: ' . $csv_data->get_error_message(), 'error');
            $this->stats['errors']++;
            return $this->stats;
        }
        
        $this->logger->log('پردازش CSV تکمیل شد. ' . count($csv_data) . ' رکورد یافت شد.', 'info');
        
        // داده‌ها را اعتبارسنجی کنید
        $validation = $this->csv_processor->validate_car_data($csv_data);
        
        if (is_wp_error($validation)) {
            $this->logger->log('خطای اعتبارسنجی: ' . $validation->get_error_message(), 'error');
            $this->stats['errors']++;
            return $this->stats;
        }
        
        $this->stats['total'] = count($csv_data);
        $this->logger->log('شروع ایمپورت ' . $this->stats['total'] . ' خودرو با ' . $this->stats['images_total'] . ' تصویر', 'info');
        
        // نمونه CPT Manager را ایجاد کنید
        $cpt_manager = new ACI_CPT_Manager();
        
        // نمونه ACF Manager را ایجاد کنید
        $acf_manager = new ACI_ACF_Manager($this->logger);
        
        // هر خودرو را ایمپورت کنید
        foreach ($csv_data as $car) {
            // شناسه منحصر به فرد را تعیین کنید (bild_id اولویت دارد، سپس interne_nummer)
            $car_identifier = !empty($car['bild_id']) ? $car['bild_id'] : $car['interne_nummer'];
            
            // تصاویر برای این خودرو را پیدا کنید
            $car_images = $this->image_handler->get_car_images($car, $image_map);
            
            // گزینه برای رد کردن خودروهایی که تصویر ندارند
            if ($options['skip_without_images'] && empty($car_images)) {
                $this->logger->log('خودرو رد شد (بدون تصویر): ' . $car_identifier, 'info');
                $this->stats['skipped']++;
                continue;
            }
            
            // داده‌های خودرو را با مسیرهای تصاویر غنی کنید
            $car['_image_paths'] = $car_images;
            
            // خودرو موجود را جستجو کنید
            $existing_car_id = null;
            
            if (!empty($car['bild_id'])) {
                $existing_car_id = $cpt_manager->get_car_by_bild_id($car['bild_id']);
            }
            
            if (!$existing_car_id && !empty($car['interne_nummer'])) {
                $existing_car_id = $cpt_manager->get_car_by_interne_nummer($car['interne_nummer']);
            }
            
            // تصمیم بگیرید که به‌روزرسانی کنیم یا جدید ایجاد کنیم
            if ($existing_car_id) {
                if ($options['update_existing']) {
                    $result = $this->update_car($existing_car_id, $car, $acf_manager);
                    
                    if (is_wp_error($result)) {
                        $this->logger->log('خطا در به‌روزرسانی خودرو ' . $car_identifier . ': ' . $result->get_error_message(), 'error');
                        $this->stats['errors']++;
                    } else {
                        $this->stats['updated']++;
                    }
                } else {
                    $this->logger->log('خودرو رد شد (از قبل وجود دارد): ' . $car_identifier, 'info');
                    $this->stats['skipped']++;
                }
            } else {
                $result = $this->create_car($car, $acf_manager);
                
                if (is_wp_error($result)) {
                    $this->logger->log('خطا در ایجاد خودرو ' . $car_identifier . ': ' . $result->get_error_message(), 'error');
                    $this->stats['errors']++;
                } else {
                    $this->stats['created']++;
                }
            }
        }
        
        $this->logger->log('ایمپورت تکمیل شد. ' . 
            'ایجاد شده: ' . $this->stats['created'] . ', ' . 
            'به‌روزرسانی شده: ' . $this->stats['updated'] . ', ' . 
            'رد شده: ' . $this->stats['skipped'] . ', ' . 
            'خطاها: ' . $this->stats['errors'], 'info');
        
        // فایل‌های موقت را پاک کنید
        $this->cleanup_temp_files($extract_dir);
        
        return $this->stats;
    }
    
    /**
     * یک ورودی جدید Sportwagen ایجاد کنید
     * 
     * @param array $car_data داده‌های خودرو
     * @param ACI_ACF_Manager $acf_manager نمونه ACF Manager
     * @return int|WP_Error Post-ID در صورت موفقیت، WP_Error در صورت خطا
     */
    private function create_car($car_data, $acf_manager) {
        // عنوان را از داده‌ها تولید کنید
        $title = '';
        if (!empty($car_data['marke']) && !empty($car_data['modell'])) {
            $title = $car_data['marke'] . ' ' . $car_data['modell'];
            
            if (!empty($car_data['baujahr'])) {
                $title .= ' (' . $car_data['baujahr'] . ')';
            }
        } else {
            $title = 'Sportwagen ' . (!empty($car_data['interne_nummer']) ? $car_data['interne_nummer'] : uniqid());
        }
        
        // خودرو جدید ایجاد کنید
        $post_data = array(
            'post_title'   => $title,
            'post_content' => '',
            'post_status'  => 'publish',
            'post_type'    => 'sportwagen',
        );
        
        // توضیحات را به عنوان محتوا قرار دهید، اگر موجود است
        if (!empty($car_data['bemerkung'])) {
            $post_data['post_content'] = $car_data['bemerkung'];
        }
        
        // پست را ایجاد کنید
        $post_id = wp_insert_post($post_data, true);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        
        // فیلدهای ACF را به‌روزرسانی کنید
        $result = $acf_manager->update_car_acf_fields($post_id, $car_data);
        
        if (is_wp_error($result)) {
            $this->logger->log('خطا در به‌روزرسانی فیلدهای ACF: ' . $result->get_error_message(), 'error');
        }
        
        // تصاویر را ایمپورت کنید، اگر موجود هستند
        if (!empty($car_data['_image_paths'])) {
            $attachment_ids = $this->image_handler->import_car_images($post_id, $car_data['_image_paths']);
            $this->stats['images_new'] += count($attachment_ids);
        }
        
        $this->logger->log('خودرو جدید ایجاد شد: ' . $title . ' (ID: ' . $post_id . ')', 'info');
        
        return $post_id;
    }
    
    /**
     * یک ورودی Sportwagen موجود را به‌روزرسانی کنید
     * 
     * @param int $post_id Post-ID خودرو برای به‌روزرسانی
     * @param array $car_data داده‌های جدید خودرو
     * @param ACI_ACF_Manager $acf_manager نمونه ACF Manager
     * @return int|WP_Error Post-ID در صورت موفقیت، WP_Error در صورت خطا
     */
    private function update_car($post_id, $car_data, $acf_manager) {
        // عنوان را از داده‌ها تولید کنید
        $title = '';
        if (!empty($car_data['marke']) && !empty($car_data['modell'])) {
            $title = $car_data['marke'] . ' ' . $car_data['modell'];
            
            if (!empty($car_data['baujahr'])) {
                $title .= ' (' . $car_data['baujahr'] . ')';
            }
        } else {
            // عنوان موجود را حفظ کنید
            $post = get_post($post_id);
            $title = $post->post_title;
        }
        
        // داده‌های خودرو را به‌روزرسانی کنید
        $post_data = array(
            'ID'           => $post_id,
            'post_title'   => $title,
        );
        
        // توضیحات را به عنوان محتوا قرار دهید، اگر موجود است
        if (!empty($car_data['bemerkung'])) {
            $post_data['post_content'] = $car_data['bemerkung'];
        }
        
        // پست را به‌روزرسانی کنید
        $result = wp_update_post($post_data, true);
        
        if (is_wp_error($result)) {
            return $result;
        }
        
        // فیلدهای ACF را به‌روزرسانی کنید
        $result = $acf_manager->update_car_acf_fields($post_id, $car_data);
        
        if (is_wp_error($result)) {
            $this->logger->log('خطا در به‌روزرسانی فیلدهای ACF: ' . $result->get_error_message(), 'error');
        }
        
        // تصاویر را ایمپورت کنید، اگر موجود هستند
        if (!empty($car_data['_image_paths'])) {
            $attachment_ids = $this->image_handler->import_car_images($post_id, $car_data['_image_paths']);
            $this->stats['images_new'] += count($attachment_ids);
        }
        
        $this->logger->log('خودرو به‌روزرسانی شد: ' . $title . ' (ID: ' . $post_id . ')', 'info');
        
        return $post_id;
    }
    
    /**
     * محتوای یک فایل ZIP را ثبت کنید
     * 
     * @param string $zip_path مسیر به فایل ZIP
     * @return void
     */
    private function debug_zip_contents($zip_path) {
        if (!file_exists($zip_path)) {
            $this->logger->log('خطا: فایل ZIP برای دیباگ پیدا نشد: ' . $zip_path, 'error');
            return;
        }
        
        try {
            $zip = new ZipArchive();
            if ($zip->open($zip_path) === TRUE) {
                $this->logger->log('=== دیباگ محتوای فایل ZIP ===', 'info');
                $this->logger->log('فایل ZIP: ' . $zip_path, 'info');
                $this->logger->log('تعداد فایل‌ها: ' . $zip->numFiles, 'info');
                
                // تمام فایل‌ها را لیست کنید
                $files_by_type = array();
                for ($i = 0; $i < $zip->numFiles; $i++) {
                    $filename = $zip->getNameIndex($i);
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (!isset($files_by_type[$ext])) {
                        $files_by_type[$ext] = array();
                    }
                    
                    $files_by_type[$ext][] = $filename;
                }
                
                // بر اساس نوع فایل گروه‌بندی و ثبت کنید
                foreach ($files_by_type as $ext => $files) {
                    $this->logger->log('نوع ".' . $ext . '": ' . count($files) . ' فایل', 'info');
                    // حداکثر 10 فایل در هر نوع نمایش دهید تا لاگ را پر نکنید
                    $sample_files = array_slice($files, 0, 10);
                    $this->logger->log('نمونه‌ها: ' . implode(', ', $sample_files), 'info');
                }
                
                $zip->close();
                $this->logger->log('=== پایان دیباگ فایل ZIP ===', 'info');
            } else {
                $this->logger->log('نمی‌توان فایل ZIP را برای دیباگ باز کرد: ' . $zip_path, 'error');
            }
        } catch (Exception $e) {
            $this->logger->log('خطا در دیباگ فایل ZIP: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * فایل‌های موقت را پاک کنید
     * 
     * @param string $dir دایرکتوری برای پاک کردن
     */
    private function cleanup_temp_files($dir) {
        if (!is_dir($dir)) {
            return;
        }
        
        // محتوای دایرکتوری را بخوانید
        $files = glob($dir . '/{,.}*', GLOB_BRACE);
        
        foreach ($files as $file) {
            $basename = basename($file);
            
            // '.' و '..' را رد کنید
            if ($basename === '.' || $basename === '..') {
                continue;
            }
            
            if (is_dir($file)) {
                // زیردایرکتوری را بازگشتی پاک کنید
                $this->cleanup_temp_files($file);
                @rmdir($file);
            } else {
                // .htaccess را پاک نکنید
                if ($basename !== '.htaccess') {
                    @unlink($file);
                }
            }
        }
    }
}