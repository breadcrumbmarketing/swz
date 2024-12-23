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

    // Sanitize input data
    $title = sanitize_text_field( $data['title'] );
    $slug = sanitize_title( $data['slug'] ); // Slug is usually sanitized and converted to lowercase
    $content = sanitize_textarea_field( $data['content'] );

    // Check if the slug already exists in the table
    $existing_page = $wpdb->get_var( $wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}html_pages WHERE slug = %s", 
        $slug
    ));

    if ( $existing_page ) {
        return new WP_REST_Response( 'Page with this slug already exists.', 400 );
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
        return new WP_REST_Response( 'HTML page inserted successfully.', 200 );
    } else {
        return new WP_REST_Response( 'Failed to insert HTML page.', 400 );
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


//  --------------------------------  Post created by Lexikon Datas  --------------------------------  //

// Callback function to handle the insertion of data into the wp_html_pages table
function handle_write_html_page( $data ) {
    global $wpdb;

    // Sanitize input data
    $title = sanitize_text_field( $data['title'] );
    $slug = sanitize_title( $data['slug'] ); // Slug is usually sanitized and converted to lowercase
    $content = sanitize_textarea_field( $data['content'] );

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
        // Now automatically create a WordPress post from the HTML content
        $post_id = wp_insert_post(array(
            'post_title'   => $title,
            'post_content' => $content,
            'post_status'  => 'publish', // You can set to 'draft' if needed
            'post_type'    => 'post', // Make this a post type
        ));

        // After inserting the post, we can also set the first image from the content as the featured image
        preg_match('/<img.*?src=["\'](.*?)["\'].*?>/', $content, $matches);  // Match the first image in the content

        if (isset($matches[1])) {
            $image_url = $matches[1];

            // Use WordPress function to upload the image as a featured image
            $upload_dir = wp_upload_dir();
            $image_data = @file_get_contents($image_url);  // Suppress errors temporarily

            if ($image_data === false) {
                return new WP_REST_Response('Failed to download image.', 400);  // Error handling
            }

            $filename = basename($image_url);
            $file_path = $upload_dir['path'] . '/' . $filename;
            
            file_put_contents($file_path, $image_data);  // Save image to uploads directory

            // Check if the image was uploaded successfully
            $file_type = wp_check_filetype($filename);
            $mime_type = $file_type['type'];  // Dynamically get MIME type

            $attachment = array(
                'guid' => $upload_dir['url'] . '/' . $filename,
                'post_mime_type' => $mime_type,
                'post_title' => $filename,
                'post_content' => '',
                'post_status' => 'inherit'
            );

            // Insert the image into the media library
            $attachment_id = wp_insert_attachment($attachment, $file_path);

            // Generate the metadata for the image
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_metadata);

            // Set the image as the post's thumbnail (featured image)
            set_post_thumbnail($post_id, $attachment_id);
        }
    }
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
