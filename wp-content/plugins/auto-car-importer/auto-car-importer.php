<?php
/**
 * Plugin Name: Auto Car Importer
 * Plugin URI: 
 * Description: Importiert Fahrzeugdaten aus CSV-Dateien und verknüpft zugehörige Bilder
 * Version: 1.0.0
 * Author: Hamy Vosugh
 * Text Domain: auto-car-importer
 * Domain Path: /languages
 */

// خروج اگر به صورت مستقیم دسترسی پیدا کردید
if (!defined('ABSPATH')) {
    exit;
}

// ثابت‌های پلاگین را تعریف کنید
define('ACI_VERSION', '1.0.0');
define('ACI_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('ACI_PLUGIN_URL', plugin_dir_url(__FILE__));
define('ACI_PLUGIN_BASENAME', plugin_basename(__FILE__));

// کلاس‌ها را وارد کنید
require_once ACI_PLUGIN_DIR . 'includes/class-admin.php';
require_once ACI_PLUGIN_DIR . 'includes/class-cpt-manager.php';
require_once ACI_PLUGIN_DIR . 'includes/class-csv-processor.php';
require_once ACI_PLUGIN_DIR . 'includes/class-image-handler.php';
require_once ACI_PLUGIN_DIR . 'includes/class-sync-manager.php';
require_once ACI_PLUGIN_DIR . 'includes/class-logger.php';
require_once ACI_PLUGIN_DIR . 'includes/class-ftp-handler.php';
require_once ACI_PLUGIN_DIR . 'includes/class-acf-manager.php';

/**
 * کلاس اصلی برای پلاگین
 */
class Auto_Car_Importer {
    
    /**
     * اعضای کلاس
     */
    private $admin;
    private $cpt_manager;
    private $csv_processor;
    private $image_handler;
    private $sync_manager;
    private $logger;
    private $ftp_handler;
    private $acf_manager;
    
    /**
     * سازنده
     */
    public function __construct() {
        // پلاگین را مقداردهی اولیه کنید
        add_action('plugins_loaded', array($this, 'init'));
    }
    
    /**
     * پلاگین را مقداردهی اولیه کنید
     */
    public function init() {
        // Logger را مقداردهی اولیه کنید (ابتدا، تا تمام کلاس‌های دیگر بتوانند از آن استفاده کنند)
        $this->logger = new ACI_Logger();
        
        // Custom Post Type Manager را مقداردهی اولیه کنید
        $this->cpt_manager = new ACI_CPT_Manager();
        
        // CSV-Processor را مقداردهی اولیه کنید
        $this->csv_processor = new ACI_CSV_Processor($this->logger);
        
        // Image-Handler را مقداردهی اولیه کنید
        $this->image_handler = new ACI_Image_Handler($this->logger);
        
        // ACF Manager را مقداردهی اولیه کنید
        $this->acf_manager = new ACI_ACF_Manager($this->logger);
        
        // FTP-Handler را مقداردهی اولیه کنید
        $this->ftp_handler = new ACI_FTP_Handler($this->logger);
        
        // بخش مدیریت را مقداردهی اولیه کنید
        if (is_admin()) {
            $this->admin = new ACI_Admin($this->logger);
        }
        
        // Sync-Manager را مقداردهی اولیه کنید - اینجا اصلاح شده است
        $this->sync_manager = new ACI_Sync_Manager(
            $this->csv_processor, 
            $this->image_handler,
            $this->logger  // اصلاح شد، نه $this->acf_manager
        );
        
        // هوک‌های فعال‌سازی و غیرفعال‌سازی پلاگین را ثبت کنید
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
    }
    
    /**
     * پلاگین را فعال کنید
     */
    public function activate() {
        // Custom Post Type را ثبت کنید
        $this->cpt_manager->register_post_type();
        
        // نقش‌ها و مجوزها را به‌روزرسانی کنید
        $this->update_roles_and_capabilities();
        
        // سیستم فایل را مقداردهی اولیه کنید (دایرکتوری آپلود ایجاد کنید)
        $this->init_filesystem();
        
        // قوانین rewrite را به‌روزرسانی کنید
        flush_rewrite_rules();
    }
    
    /**
     * پلاگین را غیرفعال کنید
     */
    public function deactivate() {
        // قوانین rewrite را به‌روزرسانی کنید
        flush_rewrite_rules();
    }
    
    /**
     * نقش‌ها و مجوزها را به‌روزرسانی کنید
     */
    private function update_roles_and_capabilities() {
        // نقش مدیر
        $admin_role = get_role('administrator');
        $admin_role->add_cap('manage_car_importer');
    }
    
    /**
     * سیستم فایل را مقداردهی اولیه کنید
     */
    private function init_filesystem() {
        // دایرکتوری آپلود برای فایل‌های موقت ایجاد کنید
        $upload_dir = wp_upload_dir();
        $aci_dir = $upload_dir['basedir'] . '/aci-temp';
        
        if (!file_exists($aci_dir)) {
            wp_mkdir_p($aci_dir);
        }
        
        // فایل .htaccess برای محافظت از دایرکتوری ایجاد کنید
        if (file_exists($aci_dir) && !file_exists($aci_dir . '/.htaccess')) {
            $htaccess_content = "Deny from all\n";
            @file_put_contents($aci_dir . '/.htaccess', $htaccess_content);
        }
    }
}

// نمونه پلاگین را شروع کنید
$auto_car_importer = new Auto_Car_Importer();