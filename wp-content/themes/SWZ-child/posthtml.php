<?php
/**
 * Template Name: Display HTML Content
 * Template Post Type: page, post
 */

get_header(); // Load the WordPress header

global $wpdb;

// Fetch the slug of the current page or post
$post_slug = get_post_field('post_name', get_the_ID());

// Debugging: Log the slug
error_log('Post Slug: ' . $post_slug);

if ($post_slug) {
    // Fetch the corresponding row from the database
    $html_page = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}html_pages WHERE slug = %s", $post_slug));

    if ($html_page) {
        // Debugging: Log the query result
        error_log('Query Result: ' . print_r($html_page, true));

        // Display the title and content
        echo '<h1>' . esc_html($html_page->title) . '</h1>';
        echo '<div class="html-content">' . html_entity_decode($html_page->content) . '</div>'; // Decode and display content
    } else {
        // Debugging: No row found
        error_log('No matching row found for slug: ' . $post_slug);

        // Display error message
        echo '<p>HTML page not found for this post or page.</p>';
    }
} else {
    echo '<p>Slug not found for this post or page.</p>';
}

get_footer(); // Load the WordPress footer
?>
