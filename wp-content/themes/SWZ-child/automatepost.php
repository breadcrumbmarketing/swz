<?php
/**
 * Template Name: Automate Page Creation for All Rows
 */

get_header(); // Load header

// Fetch all HTML page content from the database
global $wpdb;
$html_pages = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}html_pages");

if ($html_pages) {
    // Loop through each row and create a page if it doesn't already exist
    foreach ($html_pages as $html_page) {
        // Check if a page already exists with the same slug
        $existing_page = get_page_by_path($html_page->slug, OBJECT, 'page');

        if (!$existing_page) {
            // Create a new page automatically if it doesn't exist
            $page_data = array(
                'post_title'   => $html_page->title,
                'post_content' => $html_page->content, // Use the raw HTML content
                'post_status'  => 'publish',          // Set to 'publish' or 'draft' based on your needs
                'post_type'    => 'page',             // Set to 'page' to create pages
                'post_name'    => $html_page->slug,   // Set the page slug
            );

            // Insert the page into WordPress
            $page_id = wp_insert_post($page_data);

            if ($page_id) {
                // Set a custom template for the page
                update_post_meta($page_id, '_wp_page_template', 'posthtml.php'); // Specify the template file name

                // Optional: Set featured image if needed
                preg_match('/<img.*?src=["\'](.*?)["\'].*?>/', $html_page->content, $matches); // Match the first image URL in content
                if (isset($matches[1])) {
                    $image_url = $matches[1];

                    // Download and set the image as the page's featured image
                    $upload_dir = wp_upload_dir();
                    $image_data = file_get_contents($image_url);
                    $filename = basename($image_url);
                    $file_path = $upload_dir['path'] . '/' . $filename;
                    file_put_contents($file_path, $image_data);

                    // Insert image into media library
                    $attachment = array(
                        'guid' => $upload_dir['url'] . '/' . $filename,
                        'post_mime_type' => 'image/jpeg',
                        'post_title' => $filename,
                        'post_content' => '',
                        'post_status' => 'inherit'
                    );

                    $attachment_id = wp_insert_attachment($attachment, $file_path, $page_id);

                    // Generate metadata for the image
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
                    wp_update_attachment_metadata($attachment_id, $attachment_metadata);

                    // Set the image as the page's thumbnail
                    set_post_thumbnail($page_id, $attachment_id);
                }

                // Log success
                echo 'Page created successfully with custom template for: ' . esc_html($html_page->title) . '<br>';
            } else {
                echo 'Failed to create page for: ' . esc_html($html_page->title) . '<br>';
            }
        } else {
            // Page already exists, log this
            echo 'Page already exists for: ' . esc_html($html_page->title) . '<br>';
        }
    }
} else {
    echo '<p>No HTML pages found in the database.</p>';
}

get_footer(); // Load footer

// Custom CSS to make the background transparent
?>
<style>
    /* Override body background to be transparent */
    body {
        background-color: rgb(238, 238, 238) !important;
    }

    /* Ensure the HTML background remains */
    html {
        background-color: rgb(238, 238, 238) !important;
    }
</style>
