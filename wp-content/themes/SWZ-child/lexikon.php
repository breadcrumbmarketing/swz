<?php
/*
Template Name: Lexikon
*/

get_header();  // Load header

// Fetch the HTML page content from the wp_html_pages table using slug from the URL
global $wpdb;

$slug = get_query_var('slug'); // Get slug from URL
$html_page = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}html_pages WHERE slug = %s", 
    $slug
));

if ($html_page) {
    // Display the content of the page
    echo '<div class="html-content">';
    echo wp_kses_post($html_page->content);  // Display the content, allowing certain HTML tags
    echo '</div>';

    // Set the first image from content as the post thumbnail
    preg_match('/<img.*?src=["\'](.*?)["\'].*?>/', $html_page->content, $matches);  // Match the first image in the content

    if (isset($matches[1])) {
        $image_url = $matches[1];

        // Use WordPress function to upload the image as a featured image
        $upload_dir = wp_upload_dir();
        $image_data = file_get_contents($image_url);  // Get image data

        $filename = basename($image_url);
        $file_path = $upload_dir['path'] . '/' . $filename;
        
        file_put_contents($file_path, $image_data);  // Save image to uploads directory

        // Check if the image was uploaded successfully
        $attachment = array(
            'guid' => $upload_dir['url'] . '/' . $filename,
            'post_mime_type' => 'image/jpeg',
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
        set_post_thumbnail($html_page->ID, $attachment_id);
    }
} else {
    echo '<p>HTML page not found.</p>';
}

get_footer();  // Load footer
?>
