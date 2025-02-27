<?php
/**
 * Admin-Bereich des Auto Car Importers
 */
class ACI_Admin {
    
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
        
        // Admin-Menü registrieren
        add_action('admin_menu', array($this, 'register_admin_menu'));
        
        // Admin-Assets registrieren
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_assets'));
        
        // AJAX-Aktionen registrieren
        add_action('wp_ajax_aci_import_from_upload', array($this, 'ajax_import_from_upload'));
        add_action('wp_ajax_aci_import_from_ftp', array($this, 'ajax_import_from_ftp'));
        add_action('wp_ajax_aci_get_import_stats', array($this, 'ajax_get_import_stats'));
        add_action('wp_ajax_aci_clear_logs', array($this, 'ajax_clear_logs'));
    }
    
    /**
     * Admin-Menü registrieren
     */
    public function register_admin_menu() {
        // Hauptmenü
        add_menu_page(
            __('Auto Import', 'auto-car-importer'),
            __('Auto Import', 'auto-car-importer'),
            'manage_options',
            'auto-car-importer',
            array($this, 'render_main_page'),
            'dashicons-car',
            25
        );
        
        // Untermenüs
        add_submenu_page(
            'auto-car-importer',
            __('Dashboard', 'auto-car-importer'),
            __('Dashboard', 'auto-car-importer'),
            'manage_options',
            'auto-car-importer',
            array($this, 'render_main_page')
        );
        
        add_submenu_page(
            'auto-car-importer',
            __('Einstellungen', 'auto-car-importer'),
            __('Einstellungen', 'auto-car-importer'),
            'manage_options',
            'auto-car-importer-settings',
            array($this, 'render_settings_page')
        );
        
        add_submenu_page(
            'auto-car-importer',
            __('Protokolle', 'auto-car-importer'),
            __('Protokolle', 'auto-car-importer'),
            'manage_options',
            'auto-car-importer-logs',
            array($this, 'render_logs_page')
        );
        
        // Einstellungen registrieren
        register_setting('aci_settings', 'aci_ftp_host');
        register_setting('aci_settings', 'aci_ftp_username');
        register_setting('aci_settings', 'aci_ftp_password');
        register_setting('aci_settings', 'aci_ftp_path');
        register_setting('aci_settings', 'aci_csv_delimiter');
        register_setting('aci_settings', 'aci_csv_enclosure');
        register_setting('aci_settings', 'aci_update_existing');
        register_setting('aci_settings', 'aci_skip_without_images');
        register_setting('aci_settings', 'aci_csv_filename');
    }
    
    /**
     * Admin-Assets laden
     * 
     * @param string $hook_suffix Der aktuelle Hook
     */
    public function enqueue_admin_assets($hook_suffix) {
        // Nur auf Plugin-Seiten laden
        if (strpos($hook_suffix, 'auto-car-importer') === false) {
            return;
        }
        
        // CSS
        wp_enqueue_style(
            'aci-admin-css',
            ACI_PLUGIN_URL . 'admin/css/admin.css',
            array(),
            ACI_VERSION
        );
        
        // JavaScript
        wp_enqueue_script(
            'aci-admin-js',
            ACI_PLUGIN_URL . 'admin/js/admin.js',
            array('jquery'),
            ACI_VERSION,
            true
        );
        
        // AJAX-URL und Nonce an JavaScript übergeben
        wp_localize_script(
            'aci-admin-js',
            'aciData',
            array(
                'ajaxUrl' => admin_url('admin-ajax.php'),
                'nonce'   => wp_create_nonce('aci_ajax_nonce'),
            )
        );
    }
    
    /**
     * Hauptseite rendern
     */
    public function render_main_page() {
        // Aktuellen Import-Status laden
        $import_status = get_option('aci_current_import_status', array());
        $last_import = get_option('aci_last_import_time', '');
        
        // Template einbinden
        include ACI_PLUGIN_DIR . 'admin/views/main-page.php';
    }
    
    /**
     * Einstellungsseite rendern
     */
    public function render_settings_page() {
        // Template einbinden
        include ACI_PLUGIN_DIR . 'admin/views/settings-page.php';
    }
    
    /**
     * Log-Seite rendern
     */
    public function render_logs_page() {
        // Logs laden
        $logs = $this->logger->get_logs(100);
        
        // Template einbinden
        include ACI_PLUGIN_DIR . 'admin/views/logs-page.php';
    }
    
/**
 * AJAX-Handler برای ایمپورت آپلود
 */
public function ajax_import_from_upload() {
    // بررسی nonce
    check_ajax_referer('aci_ajax_nonce', 'nonce');
    
    // بررسی مجوزها
    if (!current_user_can('manage_options')) {
        wp_send_json_error('مجوز ندارید');
    }
    
    // بررسی کنید که آیا فایلی آپلود شده است
    if (!isset($_FILES['import_file']) || $_FILES['import_file']['error'] !== UPLOAD_ERR_OK) {
        wp_send_json_error('فایل معتبری آپلود نشده است');
    }
    
    $file = $_FILES['import_file'];
    
    // بررسی کنید که آیا یک فایل ZIP است
    $file_ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    if (strtolower($file_ext) !== 'zip') {
        wp_send_json_error('فایل باید به فرمت ZIP باشد');
    }
    
    // مسیر موقت را ذخیره کنید
    $temp_file = $file['tmp_name'];
    
    // گزینه‌ها را از فرم دریافت کنید
    $options = array(
        'delimiter' => isset($_POST['delimiter']) ? sanitize_text_field($_POST['delimiter']) : ';',
        'enclosure' => isset($_POST['enclosure']) ? sanitize_text_field($_POST['enclosure']) : '"',
        'update_existing' => isset($_POST['update_existing']) && $_POST['update_existing'] === 'yes',
        'skip_without_images' => isset($_POST['skip_without_images']) && $_POST['skip_without_images'] === 'yes',
        'csv_filename' => isset($_POST['csv_filename']) ? sanitize_text_field($_POST['csv_filename']) : '',
    );
    
    // دایرکتوری آپلود را آماده کنید
    $upload_dir = wp_upload_dir();
    $dest_path = $upload_dir['basedir'] . '/aci-temp/' . $file['name'];
    
    // اطمینان حاصل کنید که دایرکتوری مقصد وجود دارد
    wp_mkdir_p(dirname($dest_path));
    
    // انتقال فایل
    if (!move_uploaded_file($temp_file, $dest_path)) {
        wp_send_json_error('خطا در انتقال فایل');
    }
    
    // پردازشگرهای موردنیاز را ایجاد کنید
    $csv_processor = new ACI_CSV_Processor($this->logger);
    $image_handler = new ACI_Image_Handler($this->logger);
    $sync_manager = new ACI_Sync_Manager($csv_processor, $image_handler, $this->logger);
    
    // شروع ایمپورت
    $stats = $sync_manager->process_zip_file($dest_path, $options);
    
    // ذخیره وضعیت ایمپورت
    update_option('aci_current_import_status', $stats);
    update_option('aci_last_import_time', current_time('mysql'));
    
    // گزارش موفقیت
    wp_send_json_success(array(
        'stats' => $stats,
        'message' => sprintf(
            __('%d خودرو ایمپورت شد (%d جدید، %d به‌روزرسانی شد، %d رد شد، %d خطا)', 'auto-car-importer'),
            $stats['total'],
            $stats['created'],
            $stats['updated'],
            $stats['skipped'],
            $stats['errors']
        )
    ));
}

/**
 * AJAX-Handler برای ایمپورت FTP
 */
public function ajax_import_from_ftp() {
    // بررسی nonce
    check_ajax_referer('aci_ajax_nonce', 'nonce');
    
    // بررسی مجوزها
    if (!current_user_can('manage_options')) {
        wp_send_json_error('مجوز ندارید');
    }
    
    // بارگذاری تنظیمات FTP
    $ftp_host = get_option('aci_ftp_host', '');
    $ftp_username = get_option('aci_ftp_username', '');
    $ftp_password = get_option('aci_ftp_password', '');
    $ftp_path = get_option('aci_ftp_path', '/');
    
    // بررسی کنید که آیا تمام تنظیمات FTP موجود هستند
    if (empty($ftp_host) || empty($ftp_username) || empty($ftp_password)) {
        wp_send_json_error('تنظیمات FTP ناقص');
    }
    
    // گزینه‌ها را از فرم دریافت کنید یا به تنظیمات برگردید
    $options = array(
        'delimiter' => isset($_POST['delimiter']) ? sanitize_text_field($_POST['delimiter']) : get_option('aci_csv_delimiter', ';'),
        'enclosure' => isset($_POST['enclosure']) ? sanitize_text_field($_POST['enclosure']) : get_option('aci_csv_enclosure', '"'),
        'update_existing' => isset($_POST['update_existing']) ? ($_POST['update_existing'] === 'yes') : (get_option('aci_update_existing', 'yes') === 'yes'),
        'skip_without_images' => isset($_POST['skip_without_images']) ? ($_POST['skip_without_images'] === 'yes') : (get_option('aci_skip_without_images', 'no') === 'yes'),
        'csv_filename' => isset($_POST['csv_filename']) ? sanitize_text_field($_POST['csv_filename']) : get_option('aci_csv_filename', ''),
    );
    
    // FTP-Handler را مقداردهی اولیه کنید
    $ftp_handler = new ACI_FTP_Handler($this->logger);
    
    // دریافت فایل‌های ZIP از سرور FTP
    $files = $ftp_handler->get_ftp_files($ftp_host, $ftp_username, $ftp_password, $ftp_path);
    
    if (is_wp_error($files)) {
        wp_send_json_error($files->get_error_message());
    }
    
    if (empty($files)) {
        wp_send_json_error(__('فایل ZIP در دایرکتوری FTP پیدا نشد', 'auto-car-importer'));
    }
    
    // دایرکتوری آپلود را آماده کنید
    $upload_dir = wp_upload_dir();
    $local_dir = $upload_dir['basedir'] . '/aci-temp/';
    
    // ایجاد دایرکتوری، اگر وجود ندارد
    wp_mkdir_p($local_dir);
    
    // آمار برای تمام فایل‌های پردازش شده
    $all_stats = array(
        'total' => 0,
        'created' => 0,
        'updated' => 0,
        'skipped' => 0,
        'errors' => 0,
        'images_total' => 0,
        'images_new' => 0,
        'processed_files' => array(),
    );
    
    // Sync Manager را مقداردهی اولیه کنید
    $csv_processor = new ACI_CSV_Processor($this->logger);
    $image_handler = new ACI_Image_Handler($this->logger);
    $sync_manager = new ACI_Sync_Manager($csv_processor, $image_handler, $this->logger);
    
    // هر فایل ZIP را دانلود و پردازش کنید
    foreach ($files as $file_info) {
        $file_name = $file_info['name'];
        $remote_path = $ftp_path . '/' . $file_name;
        $local_path = $local_dir . $file_name;
        
        // دانلود فایل
        $this->logger->log('دانلود فایل FTP: ' . $remote_path, 'info');
        $download_result = $ftp_handler->download_file(
            $ftp_host, 
            $ftp_username, 
            $ftp_password, 
            $remote_path, 
            $local_path
        );
        
        if (is_wp_error($download_result)) {
            $this->logger->log('خطا در دانلود فایل: ' . $remote_path, 'error');
            continue;
        }
        
        // پردازش فایل
        $stats = $sync_manager->process_zip_file($local_path, $options);
        
        // ترکیب آمار
        $all_stats['total'] += $stats['total'];
        $all_stats['created'] += $stats['created'];
        $all_stats['updated'] += $stats['updated'];
        $all_stats['skipped'] += $stats['skipped'];
        $all_stats['errors'] += $stats['errors'];
        $all_stats['images_total'] += $stats['images_total'];
        $all_stats['images_new'] += $stats['images_new'];
        $all_stats['processed_files'][] = $file_name;
    }
    
    // ذخیره وضعیت ایمپورت
    update_option('aci_current_import_status', $all_stats);
    update_option('aci_last_import_time', current_time('mysql'));
    
    // گزارش موفقیت
    wp_send_json_success(array(
        'stats' => $all_stats,
        'message' => sprintf(
            __('%d خودرو ایمپورت شد (%d جدید، %d به‌روزرسانی شد، %d رد شد، %d خطا) از %d فایل', 'auto-car-importer'),
            $all_stats['total'],
            $all_stats['created'],
            $all_stats['updated'],
            $all_stats['skipped'],
            $all_stats['errors'],
            count($all_stats['processed_files'])
        )
    ));
}
    /**
     * AJAX-Handler für Import-Statistiken
     */
    public function ajax_get_import_stats() {
        // Nonce prüfen
        check_ajax_referer('aci_ajax_nonce', 'nonce');
        
        // Berechtigungen prüfen
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        // Import-Status laden
        $stats = get_option('aci_current_import_status', array());
        $last_import = get_option('aci_last_import_time', '');
        
        wp_send_json_success(array(
            'stats' => $stats,
            'last_import' => $last_import,
        ));
    }
    
    /**
     * AJAX-Handler zum Löschen der Logs
     */
    public function ajax_clear_logs() {
        // Nonce prüfen
        check_ajax_referer('aci_ajax_nonce', 'nonce');
        
        // Berechtigungen prüfen
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Keine Berechtigung');
        }
        
        // Logs löschen
        $this->logger->clear_logs();
        
        wp_send_json_success('Protokolle wurden gelöscht');
    }
}