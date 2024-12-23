<?php
/**
 * Template Name: Lexikon
 */

get_header(); // Load header

// Fetch the slug from the URL
$slug = get_query_var('slug'); // Example: `yourdomain.com/lexikon/sample-html-page/`

global $wpdb;
$html_page = $wpdb->get_row($wpdb->prepare(
    "SELECT * FROM {$wpdb->prefix}html_pages WHERE slug = %s", 
    $slug
));

if ($html_page) {
    // Create a WordPress post automatically (if not already created)
    $existing_post = get_page_by_path($slug, OBJECT, 'post');
    
    if (!$existing_post) {
        // Insert the post automatically if it doesn't exist
        $post_id = wp_insert_post(array(
            'post_title'   => $html_page->title,
            'post_content' => $html_page->content,
            'post_status'  => 'publish',  // Change to 'draft' if you want
            'post_type'    => 'post',    // This will be a standard post
        ));
        
        // Set the first image from content as the post's featured image
        preg_match('/<img.*?src=["\'](.*?)["\'].*?>/', $html_page->content, $matches);  // Match the first image in content
        
        if (isset($matches[1])) {
            $image_url = $matches[1];
            
            // Use WordPress function to upload the image as a featured image
            $upload_dir = wp_upload_dir();
            $image_data = file_get_contents($image_url);  // Get image data
            
            $filename = basename($image_url);
            $file_path = $upload_dir['path'] . '/' . $filename;
            
            file_put_contents($file_path, $image_data);  // Save image to the uploads directory
            
            // Insert the image into the media library
            $attachment = array(
                'guid' => $upload_dir['url'] . '/' . $filename,
                'post_mime_type' => 'image/jpeg',
                'post_title' => $filename,
                'post_content' => '',
                'post_status' => 'inherit'
            );

            // Insert the image into the media library
            $attachment_id = wp_insert_attachment($attachment, $file_path);

            // Generate metadata for the image
            require_once(ABSPATH . 'wp-admin/includes/image.php');
            $attachment_metadata = wp_generate_attachment_metadata($attachment_id, $file_path);
            wp_update_attachment_metadata($attachment_id, $attachment_metadata);

            // Set the image as the post's featured image
            set_post_thumbnail($post_id, $attachment_id);
        }
    } else {
        // If the post already exists, use the post ID
        $post_id = $existing_post->ID;
    }

    // Display the content (without title)
    echo '<div class="html-content">';
    echo wp_kses_post($html_page->content);  // Display the content, allowing safe HTML
    echo '</div>';
} else {
    echo '<p>HTML page not found.</p>';
}

<?php
if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        the_content(); // This function outputs the content of the page, which is editable with Elementor.
    endwhile;
endif;

get_footer(); // This includes your theme's footer.
?>
