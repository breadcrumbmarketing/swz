<?php
/**
 * Template Name: Automate Post Creation for All Rows
 */

get_header(); // Load header

// Fetch all HTML page content from the database
global $wpdb;
$html_pages = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}html_pages");

if ($html_pages) {
    // Loop through each row and create a post if it doesn't already exist
    foreach ($html_pages as $html_page) {
        // Check if a post already exists with the same slug
        $existing_post = get_page_by_path($html_page->slug, OBJECT, 'post');
        
        if (!$existing_post) {
            // Create a new post automatically if it doesn't exist
            $post_data = array(
                'post_title'   => $html_page->title,
                'post_content' => $html_page->content,
                'post_status'  => 'publish',  // Set to 'draft' if you want to keep it unpublished
                'post_type'    => 'post',    // Post type (standard WordPress post)
                'post_name'    => $html_page->slug,  // This sets the post slug
            );
            
            // Insert the post into WordPress
            $post_id = wp_insert_post($post_data);
            
            if ($post_id) {
                // Optional: Set featured image if needed
                preg_match('/<img.*?src=["\'](.*?)["\'].*?>/', $html_page->content, $matches);  // Match the first image URL in content
                if (isset($matches[1])) {
                    $image_url = $matches[1];
                    
                    // Download and set the image as the post's featured image
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
                    
                    $attachment_id = wp_insert_attachment($attachment, $file_path, $post_id);
                    
                    // Generate metadata for the image
                    require_once(ABSPATH . 'wp-admin/includes/image.php');
                    $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
                    wp_update_attachment_metadata($attachment_id, $attachment_metadata);
                    
                    // Set the image as the post's thumbnail
                    set_post_thumbnail($post_id, $attachment_id);
                }

                // Optional: Log success
                echo 'Post created successfully for: ' . esc_html($html_page->title) . '<br>';
            } else {
                echo 'Failed to create post for: ' . esc_html($html_page->title) . '<br>';
            }
        } else {
            // Post already exists, log this
            echo 'Post already exists for: ' . esc_html($html_page->title) . '<br>';
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
