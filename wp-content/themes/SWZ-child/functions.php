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

<?php
// -------------------------------- Remote API -------------------------------- //

// Register the custom REST API endpoint
function register_html_pages_endpoint() {
    register_rest_route( 'myapi/v1', '/write_html_page/', array(
        'methods' => 'POST',
        'callback' => 'handle_write_html_page',
        'permission_callback' => 'check_api_key_permission', // Permission check function
    ));
}
add_action( 'rest_api_init', 'register_html_pages_endpoint' );

// Callback function to handle the insertion of data into the wp_html_pages table
function handle_write_html_page($data) {
    global $wpdb;

    // Sanitize input data
    $title = sanitize_text_field($data['title']); // Sanitize the title
    $slug = sanitize_title($data['slug']);       // Convert the slug into a URL-friendly format
    $content = $data['content'];                // Use raw HTML content without sanitization

    // Check if the slug already exists in the table
    $existing_page = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}html_pages WHERE slug = %s",
        $slug
    ));

    if ($existing_page) {
        return new WP_REST_Response('Page with this slug already exists.', 400);
    }

    // Insert new page into the wp_html_pages table
    $insert = $wpdb->insert(
        "{$wpdb->prefix}html_pages",
        array(
            'title'   => $title,
            'slug'    => $slug,
            'content' => $content,
            'status'  => 'draft', // Set initial status to draft
        )
    );

    if ($insert) {
        return new WP_REST_Response('HTML page added to database successfully.', 200);
    } else {
        return new WP_REST_Response('Failed to insert HTML page into database.', 400);
    }
}

// API key check function for authentication
function check_api_key_permission( $request ) {
    $api_key = $request->get_header('API-Key'); // Get the API key from the header
    if ($api_key === 'swz_aschaffenburg_breadcrumb_hamy') { // Replace with your secure key
        return true;
    }
    return new WP_REST_Response('Unauthorized', 401);
}

// -------------------------------- Dynamic Page Creation -------------------------------- //

// Create dynamic WordPress pages for unpublished rows in the wp_html_pages table
function create_html_pages_from_database() {
    global $wpdb;

    // Fetch rows from the wp_html_pages table with status 'draft'
    $rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}html_pages WHERE status = 'draft'");

    foreach ($rows as $row) {
        // Check if a WordPress page already exists with the slug
        $existing_page = get_page_by_path($row->slug, OBJECT, 'page');

        if (!$existing_page) {
            // Insert a new WordPress page
            $page_id = wp_insert_post(array(
                'post_title'   => $row->title,
                'post_name'    => $row->slug,
                'post_content' => $row->content,
                'post_status'  => 'publish', // Publish the page
                'post_type'    => 'page',    // Create as a WordPress page
            ));

            if (!is_wp_error($page_id)) {
                // Mark the row as 'published' in the database
                $wpdb->update(
                    "{$wpdb->prefix}html_pages",
                    array('status' => 'published'),
                    array('id' => $row->id)
                );

                // Assign a custom page template (optional)
                update_post_meta($page_id, '_wp_page_template', 'your_template.php'); // Replace with your template filename
            }
        }
    }
}
add_action('wp_hourly_check_html_pages', 'create_html_pages_from_database');

// Schedule the dynamic page creation check if not already scheduled
if (!wp_next_scheduled('wp_hourly_check_html_pages')) {
    wp_schedule_event(time(), 'hourly', 'wp_hourly_check_html_pages');
}

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
