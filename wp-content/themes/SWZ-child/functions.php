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
    function upload_image_to_media_library($image_url)
    {
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

        // Fetch rows from the wp_html_pages table
        $rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}html_pages");

        if (empty($rows)) {
            error_log('No rows found in the database for processing.');
            return;
        }

        foreach ($rows as $row) {
            // Check if a WordPress page already exists with the slug
            $existing_page = get_page_by_path($row->slug, OBJECT, 'page');

            if ($existing_page) {
                error_log('Page already exists for slug: ' . $row->slug);
                continue; // Skip if page already exists
            }

            // Prepare data, handle potential null or missing fields
            $post_title = !empty($row->title) ? $row->title : 'Untitled Page';
            $post_content = !empty($row->content) ? $row->content : 'No content available.';
            $post_slug = !empty($row->slug) ? $row->slug : uniqid('page-');

            // Insert a new WordPress page
            $page_id = wp_insert_post(array(
                'post_title'   => $post_title,
                'post_name'    => $post_slug,
                'post_content' => $post_content,
                'post_status'  => 'publish',
                'post_type'    => 'page',
            ));

            if (!is_wp_error($page_id)) {
                error_log('Page created successfully with ID: ' . $page_id);

                // Handle image upload if provided
                if (!empty($row->image) && filter_var($row->image, FILTER_VALIDATE_URL)) {
                    $attachment_id = upload_image_to_media_library($row->image);
                    if ($attachment_id) {
                        set_post_thumbnail($page_id, $attachment_id);
                        error_log('Featured image set for page ID: ' . $page_id);
                    }
                }
            } else {
                error_log('Failed to create page for slug: ' . $row->slug . '. Error: ' . $page_id->get_error_message());
            }
        }
    }
}


// Immediate execution to process new and unprocessed rows
add_action('init', 'create_html_pages_from_database');

// -------------------------------- Rewrite Rules -------------------------------- //

function add_custom_rewrite_rule()
{
    add_rewrite_rule(
        '^lexikon/([^/]+)/?$',
        'index.php?lexikon_slug=$matches[1]',
        'top'
    );
}
add_action('init', 'add_custom_rewrite_rule');

function add_custom_query_var($vars)
{
    $vars[] = 'lexikon_slug';
    return $vars;
}
add_filter('query_vars', 'add_custom_query_var');

// -------------------------------- Admin Dashboard Button -------------------------------- //

// Add admin menu for triggering page creation
function add_create_pages_menu()
{
    add_submenu_page(
        'tools.php',
        'Create Pages from Database',
        'Create Pages',
        'manage_options',
        'create-pages-from-db',
        'create_pages_from_db_callback'
    );
}
add_action('admin_menu', 'add_create_pages_menu');

// Callback for the admin page
function create_pages_from_db_callback()
{
    if (isset($_POST['create_pages'])) {
        create_html_pages_from_database(); // Trigger the page creation function
        echo '<div class="notice notice-success"><p>Pages have been created successfully from the database.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Create Pages from Database</h1>
        <form method="post">
            <p>Click the button below to create pages for all database rows that do not yet have a corresponding WordPress page.</p>
            <button type="submit" name="create_pages" class="button button-primary">Create Pages</button>
        </form>
    </div>
    <?php
}
