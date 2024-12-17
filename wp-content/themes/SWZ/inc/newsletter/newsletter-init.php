<?php
// inc/newsletter/newsletter-init.php

class SWZ_Newsletter_System {
    private static $instance = null;
    private $table_name;

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        global $wpdb;
        $this->table_name = $wpdb->prefix . 'newsletter_subscribers';
        
        // Initialize the system
        $this->init();
    }

    private function init() {
        // Create database table
        add_action('after_setup_theme', array($this, 'create_database_table'));
        
        // Add admin menu
        add_action('admin_menu', array($this, 'add_admin_menu'));
        
        // Register AJAX handlers
        add_action('wp_ajax_subscribe_newsletter', array($this, 'handle_subscription'));
        add_action('wp_ajax_nopriv_subscribe_newsletter', array($this, 'handle_subscription'));
        
        // Enqueue scripts
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
    }

    public function create_database_table() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $this->table_name (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            email varchar(100) NOT NULL,
            ip_address varchar(45) NOT NULL,
            location varchar(255),
            date_subscribed datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY  (id),
            UNIQUE KEY email (email)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public function enqueue_scripts() {
        wp_enqueue_script('swz-newsletter', get_template_directory_uri() . '/inc/newsletter/js/newsletter.js', array('jquery'), '1.0', true);
        wp_localize_script('swz-newsletter', 'swzNewsletter', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('swz_newsletter_nonce')
        ));
    }

    public function handle_subscription() {
        check_ajax_referer('swz_newsletter_nonce', 'nonce');
    
        $email = sanitize_email($_POST['email']);
        
        // Check if email already exists
        global $wpdb;
        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM {$this->table_name} WHERE email = %s",
            $email
        ));
    
        if ($existing) {
            wp_send_json_error('Diese E-Mail-Adresse ist bereits registriert.');
            return;
        }
    
        $ip_address = $this->get_client_ip();
        $location = $this->get_ip_location($ip_address);
    
        $result = $wpdb->insert(
            $this->table_name,
            array(
                'email' => $email,
                'ip_address' => $ip_address,
                'location' => $location
            ),
            array('%s', '%s', '%s')
        );
    
        if ($result === false) {
            wp_send_json_error('Ein Fehler ist aufgetreten. Bitte versuchen Sie es später erneut.');
        } else {
            // Send welcome email using automation system
            $automation = SWZ_Newsletter_Automation::get_instance();
            $email_sent = $automation->send_welcome_email($email);
            
            $message = 'Vielen Dank für Ihre Anmeldung zum Newsletter!';
            if ($email_sent) {
                $message .= ' Eine Bestätigungs-E-Mail wurde an Sie gesendet.';
            }
            
            wp_send_json_success($message);
        }}



// Add this function to your newsletter-init.php for debugging    for test 
public function verify_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscribers';
    
    // Check if table exists
    $table_exists = $wpdb->get_var(
        $wpdb->prepare(
            "SHOW TABLES LIKE %s",
            $table_name
        )
    );
    
    if (!$table_exists) {
        // Table doesn't exist, create it
        $this->create_database_table();
        error_log('Newsletter table created');
    }
    
    // Verify table structure
    $columns = $wpdb->get_results("DESCRIBE {$table_name}");
    error_log('Table structure: ' . print_r($columns, true));
}



    private function get_client_ip() {
        $ipaddress = '';
        if (isset($_SERVER['HTTP_CLIENT_IP']))
            $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
        else if(isset($_SERVER['HTTP_X_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_X_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
        else if(isset($_SERVER['HTTP_FORWARDED_FOR']))
            $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
        else if(isset($_SERVER['HTTP_FORWARDED']))
            $ipaddress = $_SERVER['HTTP_FORWARDED'];
        else if(isset($_SERVER['REMOTE_ADDR']))
            $ipaddress = $_SERVER['REMOTE_ADDR'];
        else
            $ipaddress = 'UNKNOWN';
        return $ipaddress;
    }

    private function get_ip_location($ip) {
        $response = wp_remote_get("http://ip-api.com/json/" . $ip);
        if (is_wp_error($response)) {
            return 'Location unavailable';
        }
        $data = json_decode(wp_remote_retrieve_body($response));
        if ($data && $data->status === 'success') {
            return sprintf('%s, %s, %s', $data->city, $data->region, $data->country);
        }
        return 'Location unavailable';
    }

    public function add_admin_menu() {
        add_menu_page(
            'Newsletter Subscribers',    // Page title
            'Newsletter',               // Menu title
            'manage_options',           // Capability
            'newsletter-subscribers',   // Menu slug
            array($this, 'render_admin_page'), // Callback
            'dashicons-email',         // Icon
            30                         // Position
        );
    }

    public function render_admin_page() {
        require_once get_template_directory() . '/inc/newsletter/admin/dashboard.php';
    }
}

// Initialize the newsletter system
SWZ_Newsletter_System::get_instance();


 