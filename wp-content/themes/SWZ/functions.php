<?php
// ==========================
// Theme Setup and Assets
// ==========================

// Theme Setup: Add theme supports and menus
function swz_theme_setup() {
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_theme_support('elementor'); // Elementor Compatibility

    // Register Navigation Menus
    register_nav_menus(array(
        'primary' => __('Primary Menu', 'swz'),
        'footer'  => __('Footer Menu', 'swz'),
    ));
}
add_action('after_setup_theme', 'swz_theme_setup');


// ==========================
// Enqueue Styles and Scripts
// ==========================

function swz_enqueue_assets() {
    // Main Stylesheet
    wp_enqueue_style('swz-main-style', get_stylesheet_uri());

    // Header Stylesheet
    wp_enqueue_style(
        'swz-header-style',
        get_template_directory_uri() . '/assets/css/header.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/header.css'), // File version
        'all'
    );

    // Footer Stylesheet
    wp_enqueue_style(
        'swz-footer-style',
        get_template_directory_uri() . '/assets/css/footer.css',
        array(),
        filemtime(get_template_directory() . '/assets/css/footer.css'), // File version
        'all'
    );

    // Font Awesome for Social Media Icons
    wp_enqueue_style(
        'font-awesome',
        'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css',
        array(),
        '6.0.0',
        'all'
    );

    // Header Script
    wp_enqueue_script(
        'swz-header-script',
        get_template_directory_uri() . '/assets/js/header.js',
        array('jquery'),
        filemtime(get_template_directory() . '/assets/js/header.js'), // File version
        false // Load in header
    );

    // Footer Script
    wp_enqueue_script(
        'swz-footer-script',
        get_template_directory_uri() . '/assets/js/footer.js',
        array('jquery'),
        filemtime(get_template_directory() . '/assets/js/footer.js'),
        true // Load in footer
    );

    // Localize Footer Script for AJAX
    wp_localize_script(
        'swz-footer-script',
        'swzAjax',
        array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('swz_newsletter_nonce')
        )
    );
}
add_action('wp_enqueue_scripts', 'swz_enqueue_assets');


// ==========================
// Newsletter System
// ==========================

// Register Newsletter System
function swz_register_newsletter_system() {
    require_once get_template_directory() . '/inc/newsletter/newsletter-init.php';
}
add_action('after_setup_theme', 'swz_register_newsletter_system');

// Verify Newsletter Setup
function swz_verify_newsletter_setup() {
    $newsletter = SWZ_Newsletter_System::get_instance();
    $newsletter->verify_table();
}
add_action('admin_init', 'swz_verify_newsletter_setup');

// Temporarily Check Newsletter Subscribers
function swz_check_newsletter_subscribers() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'newsletter_subscribers';
    
    $subscribers = $wpdb->get_results("SELECT * FROM {$table_name}");
    error_log('Current subscribers: ' . print_r($subscribers, true));
}
add_action('admin_init', 'swz_check_newsletter_subscribers');


// ==========================
// Register Menus
// ==========================
function register_swz_menus() {
    register_nav_menus(
        array(
            'primary-menu' => __('Primary Menu', 'swz'),
        )
    );
}
add_action('init', 'register_swz_menus');


// ==========================
// Hero Slider
// ==========================
function enqueue_hero_slider_assets() {
    if (is_page_template('template-hero-slider.php')) {
        wp_enqueue_style('hero-slider', get_template_directory_uri() . '/assets/css/hero-slider.css', array(), '1.0.0');
        wp_enqueue_script('hero-slider', get_template_directory_uri() . '/assets/js/hero-slider.js', array(), '1.0.0', true);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_hero_slider_assets');

// Add template to Elementor
function add_hero_slider_template_to_elementor($templates) {
    $templates['template-hero-slider.php'] = 'Hero Slider Template';
    return $templates;
}
add_filter('theme_page_templates', 'add_hero_slider_template_to_elementor', 10, 1);


// ==========================
// Filter Search Car
// ==========================
// Register and Enqueue Car Grid Stylesheet and JavaScript
function register_car_grid_assets() {
    // Register and Enqueue Car Grid CSS
    wp_enqueue_style(
        'car-grid-styles',                                // Handle name
        get_template_directory_uri() . '/assets/css/car-grid.css', // Path to CSS file
        array(),                                         // Dependencies (if any)
        '1.0',                                           // Version
        'all'                                            // Media type
    );

    // Register and Enqueue Car Filter CSS
    wp_enqueue_style(
        'car-filter-styles',                             // Handle name
        get_template_directory_uri() . '/assets/css/car-filter.css', // Path to CSS file
        array(),                                         // Dependencies (if any)
        '1.0',                                           // Version
        'all'                                            // Media type
    );

    // Register and Enqueue Car Filter JavaScript
    wp_enqueue_script(
        'car-filter-script',                             // Handle name
        get_template_directory_uri() . '/assets/js/car-filter.js', // Path to JS file
        array('jquery'),                                 // Dependencies (requires jQuery)
        '1.0',                                           // Version
        true                                             // Load in footer
    );
}
add_action('wp_enqueue_scripts', 'register_car_grid_assets');
// Comparition 

// AJAX Handler for Fetching Car Data
function fetch_car_data() {
    if (isset($_POST['car_ids'])) {
        global $wpdb;

        $car_ids = array_map('intval', explode(',', sanitize_text_field($_POST['car_ids'])));
        $placeholders = implode(',', array_fill(0, count($car_ids), '%d'));

        // Query the database
        $query = "
            SELECT id, car_name, car_year, fuel_type, mileage_km, power_kw, price, image_thumbnail
            FROM wp_cars
            WHERE id IN ($placeholders)
        ";

        $results = $wpdb->get_results($wpdb->prepare($query, $car_ids));

        wp_send_json($results); // Send JSON response
    } else {
        wp_send_json_error(['error' => 'No car IDs provided.']);
    }
    wp_die(); // Terminate properly
}
add_action('wp_ajax_fetch_car_data', 'fetch_car_data');
add_action('wp_ajax_nopriv_fetch_car_data', 'fetch_car_data'); // Allow non-logged-in users

function register_car_filter_script() {
    wp_enqueue_script('car-filter', get_template_directory_uri() . '/assets/js/car-filter.js', ['jquery'], '1.0', true);

    // Localize script to pass the AJAX URL
    wp_localize_script('car-filter', 'ajaxurl', admin_url('admin-ajax.php'));
}
add_action('wp_enqueue_scripts', 'register_car_filter_script');



// -----------  //
//  search in header //
// ---------- //

function handle_car_search($search_query) {
    global $wpdb;
    
    // Start with the base query
    $query = "SELECT * FROM wp_cars WHERE 1=1";
    
    // Add search condition if search term exists
    if (!empty($search_query)) {
        $query .= $wpdb->prepare(
            " AND (
                car_name LIKE %s 
                OR car_type LIKE %s 
                OR car_year LIKE %s 
                OR fuel_type LIKE %s
            )",
            '%' . $wpdb->esc_like($search_query) . '%',
            '%' . $wpdb->esc_like($search_query) . '%',
            '%' . $wpdb->esc_like($search_query) . '%',
            '%' . $wpdb->esc_like($search_query) . '%'
        );
    }
    
    // Execute the query
    return $wpdb->get_results($query);
}

function swz_enqueue_search_header_css() {
    if (is_search()) { // Check if the search results page is loaded
        wp_enqueue_style(
            'swz-search-header', // Handle for the stylesheet
            get_template_directory_uri() . '/assets/css/search-header.css', // Path to CSS file
            array(), // Dependencies (empty)
            '1.0.0', // Version
            'all' // Media type
        );
    }
}
add_action('wp_enqueue_scripts', 'swz_enqueue_search_header_css');
?>


