<?php
/**
 * FTP Handler برای Auto Car Importer
 */
class ACI_FTP_Handler {
    
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
        
        // عملکردهای AJAX را ثبت کنید
        add_action('wp_ajax_aci_test_ftp', array($this, 'ajax_test_ftp'));
        add_action('wp_ajax_aci_refresh_ftp_files', array($this, 'ajax_refresh_ftp_files'));
    }
    
    /**
     * اجراکننده AJAX برای تست اتصال FTP
     */
    public function ajax_test_ftp() {
        // لاگ برای عیب‌یابی
        $this->logger->log('درخواست AJAX تست FTP دریافت شد', 'info');
        
        // بررسی nonce
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'aci_ajax_nonce')) {
            $this->logger->log('Nonce نامعتبر در تست FTP', 'error');
            wp_send_json_error('بررسی امنیتی ناموفق بود. لطفاً صفحه را بارگیری مجدد کنید و دوباره تلاش کنید.');
            return;
        }
        
        // بررسی مجوزها
        if (!current_user_can('manage_options')) {
            $this->logger->log('مجوزهای ناکافی برای تست FTP', 'error');
            wp_send_json_error('مجوز ندارید');
            return;
        }
        
        // تنظیمات FTP را دریافت کنید
        $host = isset($_POST['host']) ? sanitize_text_field($_POST['host']) : '';
        $username = isset($_POST['username']) ? sanitize_text_field($_POST['username']) : '';
        $password = isset($_POST['password']) ? sanitize_text_field($_POST['password']) : '';
        $path = isset($_POST['path']) ? sanitize_text_field($_POST['path']) : '/';
        
        $this->logger->log('پارامترهای تست FTP: Host=' . $host . ', User=' . $username . ', Path=' . $path, 'info');
        
        if (empty($host) || empty($username) || empty($password)) {
            $this->logger->log('داده‌های FTP ناقص در تست', 'error');
            wp_send_json_error(__('لطفاً تمام فیلدهای مورد نیاز را پر کنید.', 'auto-car-importer'));
            return;
        }
        
        // آزمایش اتصال FTP با مدیریت خطای اضافی
        try {
            // اتصال FTP را برقرار کنید
            $this->logger->log('تلاش برای برقراری اتصال FTP: ' . $host, 'info');
            
            $conn_id = @ftp_connect($host);
            if (!$conn_id) {
                $this->logger->log('اتصال FTP ناموفق بود: ' . $host, 'error');
                wp_send_json_error(__('اتصال FTP ناموفق بود. لطفاً نام هاست را بررسی کنید.', 'auto-car-importer'));
                return;
            }
            
            // ورود
            $this->logger->log('تلاش برای ورود به FTP: ' . $username, 'info');
            $login_result = @ftp_login($conn_id, $username, $password);
            if (!$login_result) {
                ftp_close($conn_id);
                $this->logger->log('ورود به FTP ناموفق بود: ' . $username, 'error');
                wp_send_json_error(__('ورود به FTP ناموفق بود. لطفاً نام کاربری و رمز عبور را بررسی کنید.', 'auto-car-importer'));
                return;
            }
            
            // حالت منفعل را فعال کنید
            $this->logger->log('فعال کردن حالت منفعل', 'info');
            @ftp_pasv($conn_id, true);
            
            // تغییر دایرکتوری
            $this->logger->log('تغییر به دایرکتوری: ' . $path, 'info');
            if (!@ftp_chdir($conn_id, $path)) {
                ftp_close($conn_id);
                $this->logger->log('دایرکتوری FTP مشخص شده پیدا نشد: ' . $path, 'error');
                wp_send_json_error(__('دایرکتوری FTP مشخص شده پیدا نشد.', 'auto-car-importer'));
                return;
            }
            
            // لیست کردن فایل‌های موجود در دایرکتوری
            $this->logger->log('لیست کردن فایل‌های دایرکتوری', 'info');
            $file_list = @ftp_nlist($conn_id, '.');
            
            // بستن اتصال FTP
            ftp_close($conn_id);
            
            if ($file_list === false) {
                $this->logger->log('فایلی در دایرکتوری FTP پیدا نشد: ' . $path, 'warning');
                wp_send_json_success(__('اتصال FTP موفق، اما دایرکتوری خالی است.', 'auto-car-importer'));
                return;
            }
            
            // گزارش موفقیت
            $this->logger->log('اتصال FTP موفق بود: ' . count($file_list) . ' فایل پیدا شد', 'info');
            wp_send_json_success(__('اتصال FTP موفق! ' . count($file_list) . ' فایل در دایرکتوری پیدا شد.', 'auto-car-importer'));
            
        } catch (Exception $e) {
            // مدیریت خطای کلی
            $this->logger->log('استثنا در تست FTP: ' . $e->getMessage(), 'error');
            wp_send_json_error(__('خطا در آزمایش اتصال FTP: ' . $e->getMessage(), 'auto-car-importer'));
        }
    }
    
    /**
     * اجراکننده AJAX برای به‌روزرسانی لیست فایل‌های FTP
     */
    public function ajax_refresh_ftp_files() {
        // بررسی nonce
        check_ajax_referer('aci_ajax_nonce', 'nonce');
        
        // بررسی مجوزها
        if (!current_user_can('manage_options')) {
            wp_send_json_error('مجوز ندارید');
        }
        
        // بارگذاری تنظیمات FTP
        $host = get_option('aci_ftp_host', '');
        $username = get_option('aci_ftp_username', '');
        $password = get_option('aci_ftp_password', '');
        $path = get_option('aci_ftp_path', '/');
        
        if (empty($host) || empty($username) || empty($password)) {
            wp_send_json_error(__('تنظیمات FTP ناقص', 'auto-car-importer'));
        }
        
        // دریافت فایل‌های FTP
        $files = $this->get_ftp_files($host, $username, $password, $path);
        
        if (is_wp_error($files)) {
            wp_send_json_error($files->get_error_message());
        } else {
            wp_send_json_success($files);
        }
    }
    
    /**
     * آزمایش اتصال FTP
     * 
     * @param string $host هاست FTP
     * @param string $username نام کاربری FTP
     * @param string $password رمز عبور FTP
     * @param string $path مسیر FTP
     * @return bool|WP_Error True در صورت موفقیت، WP_Error در صورت خطا
     */
    public function test_connection($host, $username, $password, $path) {
        // برقراری اتصال FTP
        $this->logger->log('آزمایش اتصال FTP: ' . $host, 'info');
        
        $conn_id = @ftp_connect($host);
        if (!$conn_id) {
            $this->logger->log('اتصال FTP ناموفق بود: ' . $host, 'error');
            return new WP_Error('ftp_connect_error', __('اتصال FTP ناموفق بود. لطفاً نام هاست را بررسی کنید.', 'auto-car-importer'));
        }
        
        // ورود
        $login_result = @ftp_login($conn_id, $username, $password);
        if (!$login_result) {
            ftp_close($conn_id);
            $this->logger->log('ورود به FTP ناموفق بود: ' . $username, 'error');
            return new WP_Error('ftp_login_error', __('ورود به FTP ناموفق بود. لطفاً نام کاربری و رمز عبور را بررسی کنید.', 'auto-car-importer'));
        }
        
        // حالت منفعل را فعال کنید
        @ftp_pasv($conn_id, true);
        
        // تغییر دایرکتوری
        if (!@ftp_chdir($conn_id, $path)) {
            ftp_close($conn_id);
            $this->logger->log('دایرکتوری FTP مشخص شده پیدا نشد: ' . $path, 'error');
            return new WP_Error('ftp_chdir_error', __('دایرکتوری FTP مشخص شده پیدا نشد.', 'auto-car-importer'));
        }
        
        // لیست کردن فایل‌های موجود در دایرکتوری
        $file_list = @ftp_nlist($conn_id, '.');
        if ($file_list === false) {
            ftp_close($conn_id);
            $this->logger->log('فایلی در دایرکتوری FTP پیدا نشد: ' . $path, 'warning');
            return new WP_Error('ftp_nlist_error', __('فایلی در دایرکتوری FTP پیدا نشد.', 'auto-car-importer'));
        }
        
        // بستن اتصال FTP
        ftp_close($conn_id);
        
        // گزارش موفقیت
        $this->logger->log('اتصال FTP با موفقیت برقرار شد: ' . $host . $path, 'info');
        return true;
    }
    
    /**
     * دریافت فایل‌های ZIP در دایرکتوری FTP
     * 
     * @param string $host هاست FTP
     * @param string $username نام کاربری FTP
     * @param string $password رمز عبور FTP
     * @param string $path مسیر FTP
     * @return array|WP_Error آرایه‌ای از فایل‌ها یا WP_Error در صورت خطا
     */
    public function get_ftp_files($host, $username, $password, $path) {
        // برقراری اتصال FTP
        $conn_id = @ftp_connect($host);
        if (!$conn_id) {
            $this->logger->log('اتصال FTP ناموفق بود: ' . $host, 'error');
            return new WP_Error('ftp_connect_error', __('اتصال FTP ناموفق بود.', 'auto-car-importer'));
        }
        
        // ورود
        $login_result = @ftp_login($conn_id, $username, $password);
        if (!$login_result) {
            ftp_close($conn_id);
            $this->logger->log('ورود به FTP ناموفق بود: ' . $username, 'error');
            return new WP_Error('ftp_login_error', __('ورود به FTP ناموفق بود.', 'auto-car-importer'));
        }
        
        // حالت منفعل را فعال کنید
        @ftp_pasv($conn_id, true);
        
        // تغییر دایرکتوری
        if (!@ftp_chdir($conn_id, $path)) {
            ftp_close($conn_id);
            $this->logger->log('دایرکتوری FTP مشخص شده پیدا نشد: ' . $path, 'error');
            return new WP_Error('ftp_chdir_error', __('دایرکتوری FTP مشخص شده پیدا نشد.', 'auto-car-importer'));
        }
        
        // لیست کردن فایل‌های موجود در دایرکتوری
        $file_list = @ftp_nlist($conn_id, '.');
        if ($file_list === false) {
            ftp_close($conn_id);
            $this->logger->log('فایلی در دایرکتوری FTP پیدا نشد: ' . $path, 'warning');
            return new WP_Error('ftp_nlist_error', __('فایلی در دایرکتوری FTP پیدا نشد.', 'auto-car-importer'));
        }
        
        // فیلتر کردن فایل‌های ZIP
        $zip_files = array();
        foreach ($file_list as $file) {
            $file_name = basename($file);
            $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            
            if ($file_ext === 'zip') {
                // دریافت اندازه فایل
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
        
        // بستن اتصال FTP
        ftp_close($conn_id);
        
        return $zip_files;
    }
    
    /**
     * دانلود فایل FTP
     * 
     * @param string $host هاست FTP
     * @param string $username نام کاربری FTP
     * @param string $password رمز عبور FTP
     * @param string $remote_file مسیر فایل از راه دور
     * @param string $local_file مسیر فایل محلی
     * @return bool|WP_Error True در صورت موفقیت، WP_Error در صورت خطا
     */
    public function download_file($host, $username, $password, $remote_file, $local_file) {
        // برقراری اتصال FTP
        $conn_id = @ftp_connect($host);
        if (!$conn_id) {
            $this->logger->log('اتصال FTP ناموفق بود: ' . $host, 'error');
            return new WP_Error('ftp_connect_error', __('اتصال FTP ناموفق بود.', 'auto-car-importer'));
        }
        
        // ورود
        $login_result = @ftp_login($conn_id, $username, $password);
        if (!$login_result) {
            ftp_close($conn_id);
            $this->logger->log('ورود به FTP ناموفق بود: ' . $username, 'error');
            return new WP_Error('ftp_login_error', __('ورود به FTP ناموفق بود.', 'auto-car-importer'));
        }
        
        // حالت منفعل را فعال کنید
        @ftp_pasv($conn_id, true);
        
        // دانلود فایل
        $result = @ftp_get($conn_id, $local_file, $remote_file, FTP_BINARY);
        
        // بستن اتصال FTP
        ftp_close($conn_id);
        
        if (!$result) {
            $this->logger->log('خطا در دانلود فایل: ' . $remote_file, 'error');
            return new WP_Error('ftp_get_error', __('خطا در دانلود فایل.', 'auto-car-importer'));
        }
        
        $this->logger->log('فایل با موفقیت دانلود شد: ' . $remote_file, 'info');
        return true;
    }
}