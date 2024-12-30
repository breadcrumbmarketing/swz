<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );


/** SEO */
function swz_add_seo_meta_tags() {
    ?>
    <!-- SEO Meta Tags -->
    <meta name="description" content="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sed urna in justo euismod condimentum.">
    <meta name="Breadcrumb" content="Sportwagen Zentrum">

    <!-- Open Graph / Facebook Meta Tags for rich previews in social sharing -->
    <meta property="og:title" content="<?php echo get_the_title(); ?> | Lorem Ipsum ">
    <meta property="og:description" content="<?php echo get_the_excerpt() ? get_the_excerpt() : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sed urna in justo euismod condimentum.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo get_permalink(); ?>">
    <meta property="og:image" content="<?php echo get_stylesheet_directory_uri(); ?>/path/to/your/default-og-image.jpg">
    <meta property="og:site_name" content="Sportwagen Zentrum">
    <meta property="og:locale" content="de_DE">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo get_the_title(); ?>">
    <meta name="twitter:description" content="<?php echo is_single() || is_page() ? get_the_excerpt() : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sed urna in justo euismod condimentum.'; ?>">
    <meta name="twitter:image" content="<?php echo get_stylesheet_directory_uri(); ?>/path/to/your/default-twitter-image.jpg">
    <meta name="twitter:site" content="@YourTwitterHandle">
    <meta name="twitter:creator" content="@YourTwitterHandle">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/site.webmanifest">
    <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/favicon.ico">
    <meta name="theme-color" content="#ffffff">
    <?php
}
add_action('wp_head', 'swz_add_seo_meta_tags');




// -------------------------------- Remote API -------------------------------- //

// Register the custom REST API endpoint
function register_html_pages_endpoint() {
    register_rest_route('myapi/v1', '/write_html_page/', array(
        'methods' => 'POST',
        'callback' => 'handle_write_html_page',
        'permission_callback' => 'check_api_key_permission', // Permission check function
    ));
}
add_action('rest_api_init', 'register_html_pages_endpoint');

// Callback function to handle the insertion of data into the wp_html_pages table
function handle_write_html_page($data) {
    global $wpdb;

    // Sanitize input data
    $title = sanitize_text_field($data['title']); // Sanitize the title
    $slug = sanitize_title($data['slug']);       // Convert the slug into a URL-friendly format
    $content = $data['content'];                // Use raw HTML content without sanitization
    $car_brand = sanitize_text_field($data['car_brand']); // Sanitize the car brand
    $car_model = sanitize_text_field($data['car_model']); // Sanitize the car model
    $price = sanitize_text_field($data['price']);         // Sanitize the price
    $co2 = sanitize_text_field($data['co2']);             // Sanitize the CO2
    $power = sanitize_text_field($data['power']);         // Sanitize the power
    $image = esc_url_raw($data['image']);                 // Sanitize the image URL

    // Check if the slug already exists in the table
    $existing_page = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}html_pages WHERE slug = %s",
        $slug
    ));

    if ($existing_page) {
        return new WP_REST_Response('Dieses Fahrzeug wurde bereits zu Ihrer Website hinzugefügt.', 400);
    }

    // Insert new page into the wp_html_pages table
    $insert = $wpdb->insert(
        "{$wpdb->prefix}html_pages",
        array(
            'title'     => $title,
            'slug'      => $slug,
            'content'   => $content,
            'status'    => 'draft', // Set initial status to draft
            'car_brand' => $car_brand,
            'car_model' => $car_model,
            'price'     => $price,
            'co2'       => $co2,
            'power'     => $power,
            'image'     => $image,
        )
    );

    if ($insert) {
        // Trigger dynamic page creation immediately
        create_html_pages_from_database(); // Call the function to create the page

        return new WP_REST_Response('HTML-Seite wurde zu Ihrer Website hinzugefügt und erfolgreich erstellt.', 200);
    } else {
        return new WP_REST_Response('Das Einfügen der HTML-Seite in Ihre Website ist fehlgeschlagen.', 400);
    }
}

// Example function to dynamically create pages
function create_html_pages_from_database() {
    // Your logic for dynamically creating pages goes here.
    // It can loop through the database and create WordPress pages.
}

// API key check function for authentication
function check_api_key_permission($request) {
    $api_key = $request->get_header('API-Key'); // Get the API key from the header
    if ($api_key === 'your_secure_api_key_here') { // Replace with your secure key
        return true;
    }
    return new WP_REST_Response('Unauthorized', 401);
}
// -------------------------------- Dynamic Page Creation -------------------------------- //

if (!function_exists('upload_image_to_media_library')) {
    function upload_image_to_media_library($image_url) {
        // Include WordPress file handling functions
        if (!function_exists('media_handle_sideload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }

        // Download the image from the URL
        $temp_file = download_url($image_url);

        if (is_wp_error($temp_file)) {
            return false; // Return false if the download failed
        }

        // Prepare file information
        $file = array(
            'name'     => basename($image_url),
            'type'     => mime_content_type($temp_file),
            'tmp_name' => $temp_file,
            'error'    => 0,
            'size'     => filesize($temp_file),
        );

        // Upload the file to the WordPress media library
        $attachment_id = media_handle_sideload($file, 0);

        // Clean up temporary file
        @unlink($temp_file);

        // Check for upload errors
        if (is_wp_error($attachment_id)) {
            return false;
        }

        return $attachment_id;
    }
}

if (!function_exists('create_html_pages_from_database')) {
    function create_html_pages_from_database() {
        global $wpdb;

        // Fetch rows from the wp_html_pages table with status 'draft'
        $rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}html_pages WHERE status = 'draft'");

        foreach ($rows as $row) {
            // Check if a WordPress page already exists with the slug
            $existing_page = get_page_by_path($row->slug, OBJECT, 'page');

            if (!$existing_page) {
                // Prepare data, handle potential null or missing fields
                $post_title = !empty($row->title) ? $row->title : 'Untitled Page';
                $post_content = !empty($row->content) ? $row->content : 'No content available.';
                $post_slug = !empty($row->slug) ? $row->slug : uniqid('page-');

                // Insert a new WordPress page
                $page_id = wp_insert_post(array(
                    'post_title'   => $post_title,
                    'post_name'    => $post_slug,
                    'post_content' => $post_content,
                    'post_status'  => 'publish', // Publish the page
                    'post_type'    => 'page',    // Create as a WordPress page
                ));

                if (!is_wp_error($page_id)) {
                    // Extract the first image from the HTML content
                    if (!empty($row->content) && preg_match('/<img[^>]+src="([^">]+)"/i', $row->content, $matches)) {
                        $image_url = $matches[1]; // Get the image URL

                        // Upload the image to the WordPress Media Library
                        $attachment_id = upload_image_to_media_library($image_url);

                        if ($attachment_id) {
                            // Set the uploaded image as the featured image for the page
                            set_post_thumbnail($page_id, $attachment_id);
                        }
                    }

                    // Mark the row as 'published' in the database
                    $wpdb->update(
                        "{$wpdb->prefix}html_pages",
                        array('status' => 'published'),
                        array('id' => $row->id)
                    );

                    // Assign a custom page template (optional)
                    update_post_meta($page_id, '_wp_page_template', 'carpage.php'); // Replace with your template filename
                }
            }
        }
    }
}

// Immediate execution to process new and unprocessed rows
add_action('init', 'create_html_pages_from_database');

// -------------------------------- Rewrite Rules -------------------------------- //

// Define rewrite rule for custom lexikon slugs
function add_custom_rewrite_rule() {
    add_rewrite_rule(
        '^lexikon/([^/]+)/?$',
        'index.php?lexikon_slug=$matches[1]',
        'top'
    );
}
add_action('init', 'add_custom_rewrite_rule');

// Register custom query variable for lexikon slug
function add_custom_query_var($vars) {
    $vars[] = 'lexikon_slug'; // Register the new query variable
    return $vars;
}
add_filter('query_vars', 'add_custom_query_var');


// -------------------------------- Database for car listing names -------------------------------- //
function create_brands_models_table() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'car_brands_models';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id INT AUTO_INCREMENT PRIMARY KEY,
        brand_name VARCHAR(255) NOT NULL,
        model_name VARCHAR(255) NOT NULL
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
add_action('after_switch_theme', 'create_brands_models_table');


// -------------------------------- Dashboard for listing names -------------------------------- //

// Add admin menu for managing brands and models
function add_brands_models_admin_menu() {
    add_menu_page(
        'Car Brands & Models', 
        'Car Brands', 
        'manage_options', 
        'car-brands-models', 
        'brands_models_admin_page', 
        'dashicons-car', 
        20
    );
}
add_action('admin_menu', 'add_brands_models_admin_menu');

// Display admin page content
function brands_models_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'car_brands_models';

    // Handle form submissions
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['add_brand_model'])) {
            $brand = sanitize_text_field($_POST['brand_name']);
            $model = sanitize_text_field($_POST['model_name']);
            $wpdb->insert($table_name, ['brand_name' => $brand, 'model_name' => $model]);
        } elseif (isset($_POST['delete_brand_model'])) {
            $id = intval($_POST['id']);
            $wpdb->delete($table_name, ['id' => $id]);
        }
    }

    // Fetch all brands and models
    $brands_models = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="wrap">
        <h1>Manage Car Brands & Models</h1>
        <form method="post">
            <h2>Add Brand & Model</h2>
            <input type="text" name="brand_name" placeholder="Brand Name" required>
            <input type="text" name="model_name" placeholder="Model Name" required>
            <button type="submit" name="add_brand_model" class="button button-primary">Add</button>
        </form>
        <hr>
        <h2>Existing Brands & Models</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>Brand</th>
                    <th>Model</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($brands_models as $item) : ?>
                    <tr>
                        <td><?php echo esc_html($item->brand_name); ?></td>
                        <td><?php echo esc_html($item->model_name); ?></td>
                        <td>
                            <form method="post" style="display: inline;">
                                <input type="hidden" name="id" value="<?php echo esc_attr($item->id); ?>">
                                <button type="submit" name="delete_brand_model" class="button button-secondary">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}
