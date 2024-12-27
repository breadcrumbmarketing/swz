<?php
/**
 * Template Name: Display HTML Content
 * Template Post Type: post
 */

get_header(); // Load the WordPress header

global $wpdb;
$post_slug = get_post_field('post_name', get_the_ID()); // Fetch the slug of the current post
$html_page = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}html_pages WHERE slug = %s", $post_slug));

if ($html_page) {
    echo '<h1>' . esc_html($html_page->title) . '</h1>'; // Safely output the title with HTML escape

    // Temporarily disable automatic paragraph tagging
    remove_filter('the_content', 'wpautop');

    echo '<div class="html-content">' . $html_page->content . '</div>'; // Output the HTML content directly

    // Re-enable automatic paragraph tagging
    add_filter('the_content', 'wpautop');
} else {
    echo '<p>HTML page not found for this post.</p>'; // Display a message if no content is found
}

get_footer(); // Load the WordPress footer
?>
