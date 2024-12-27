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

//  --------------------------------  Remote Api --------------------------------  //
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
function handle_write_html_page( $data ) {
    global $wpdb;

      // Log the received content
      error_log('Received Content: ' . print_r($data['content'], true));

    // Sanitize input data
    $title = sanitize_text_field( $data['title'] );
    $slug = sanitize_title( $data['slug'] );
    $content = wp_kses_post( $data['content'] ); // Allow safe HTML tags in the content
    
    // Check if the slug already exists in the table
    $existing_page = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}html_pages WHERE slug = %s", 
        $slug
    ));

    if ( $existing_page ) {
        return new WP_REST_Response('Page with this slug already exists.', 400);
    }

    // Insert new page into the wp_html_pages table
    $insert = $wpdb->insert(
        "{$wpdb->prefix}html_pages", 
        array(
            'title'     => $title,
            'slug'      => $slug,
            'content'   => $content,
            'status'    => 'draft', // Default status can be 'draft' or 'published'
        )
    );

    if ( $insert ) {
        // Check if a WordPress page already exists with this slug
        $existing_page = get_page_by_path($slug, OBJECT, 'page');
        if (!$existing_page) {
            // Insert the page if it doesn't exist
            $page_id = wp_insert_post(array(
                'post_title'   => $title,
                'post_content' => $content,
                'post_status'  => 'publish', // You can set to 'draft' if needed
                'post_type'    => 'page',    // Specify it's a page
            ));

            if (!is_wp_error($page_id)) {
                // Set the custom template for the new page
                update_post_meta($page_id, '_wp_page_template', 'your_template.php'); // Specify your custom page template filename
                return new WP_REST_Response('Page created successfully.', 200);
            } else {
                return new WP_REST_Response('Failed to create page.', 400);
            }
        } else {
            return new WP_REST_Response('Page already exists.', 400);
        }
    } else {
        return new WP_REST_Response('Failed to insert HTML page.', 400);
    }
}


// API key check function for authentication
function check_api_key_permission( $request ) {
    $api_key = $request->get_header( 'API-Key' ); // Get the API key from the header
    if ( $api_key === 'swz_aschaffenburg_breadcrumb_hamy' ) { // Replace with your secure key
        return true;
    }
    return new WP_REST_Response( 'Unauthorized', 401 );
}

// Define Rewrite Rule for Lexikon Slug
function add_custom_rewrite_rule() {
    add_rewrite_rule(
        '^lexikon/([^/]+)/?$',
        'index.php?lexikon_slug=$matches[1]',
        'top'
    );
}
add_action('init', 'add_custom_rewrite_rule');

// Register custom query variable
function add_custom_query_var($vars) {
    $vars[] = 'lexikon_slug'; // Register the new query variable
    return $vars;
}
add_filter('query_vars', 'add_custom_query_var');





