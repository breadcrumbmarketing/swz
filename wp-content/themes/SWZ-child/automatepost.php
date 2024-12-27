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
                'post_content' => '', // Leave post_content empty to avoid WordPress sanitization
                'post_status'  => 'publish',  // Set to 'publish' or 'draft' based on your needs
                'post_type'    => 'page',     // Set to 'page' to create pages
                'post_name'    => $html_page->slug,  // Set the page slug
            );

            // Insert the page into WordPress
            $page_id = wp_insert_post($page_data);

            if ($page_id) {
                // Save the raw HTML content in a custom field
                update_post_meta($page_id, '_raw_html_content', $html_page->content);

                // Set a custom template for the page
                update_post_meta($page_id, '_wp_page_template', 'posthtml.php'); // Specify the template file name

                // Log success
                echo 'Page created successfully with raw HTML content for: ' . esc_html($html_page->title) . '<br>';
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
?>
