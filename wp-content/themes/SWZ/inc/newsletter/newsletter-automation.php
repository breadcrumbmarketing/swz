<?php
// Create this file at: inc/newsletter/newsletter-automation.php

class SWZ_Newsletter_Automation {
    private static $instance = null;
    private $sender_email = 'hi@hamyvosugh.com';
    private $sender_name = 'SWZ Newsletter';

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        // Set up WordPress email filters
        add_filter('wp_mail_from', array($this, 'set_email_from'));
        add_filter('wp_mail_from_name', array($this, 'set_email_from_name'));
    }

    public function set_email_from($original_email_address) {
        return $this->sender_email;
    }

    public function set_email_from_name($original_email_from) {
        return $this->sender_name;
    }

    public function send_welcome_email($email) {
        $subject = 'Willkommen bei unserem Newsletter!';
        $message = $this->get_welcome_email_template($email);
        
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . $this->sender_name . ' <' . $this->sender_email . '>'
        );

        $sent = wp_mail($email, $subject, $message, $headers);
        
        if ($sent) {
            $this->log_email_sent($email, 'welcome');
        } else {
            $this->log_email_error($email, 'welcome');
        }
        
        return $sent;
    }

    private function get_welcome_email_template($email) {
        ob_start();
        include get_template_directory() . '/inc/newsletter/templates/welcome-email.php';
        return ob_get_clean();
    }

    private function log_email_sent($email, $type) {
        error_log(sprintf(
            '[Newsletter] Email type: %s successfully sent to: %s',
            $type,
            $email
        ));
    }

    private function log_email_error($email, $type) {
        error_log(sprintf(
            '[Newsletter] Failed to send %s email to: %s',
            $type,
            $email
        ));
    }
}

// Initialize the automation system
SWZ_Newsletter_Automation::get_instance();