<?php
/**
 * Template Name: Display HTML Content
 * Template Post Type: page, post  // Specify both page and post if you want this template to be available for both
 */

get_header(); // Load the WordPress header

global $wpdb;
$post_slug = get_post_field('post_name', get_the_ID()); // Fetch the slug of the current post or page

$html_page = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}html_pages WHERE slug = %s", $post_slug));

if ($html_page) {
    echo '<h1>' . esc_html($html_page->title) . '</h1>'; // Safely output the title with HTML escape

    // Disable WordPress's automatic paragraph tagging before outputting the HTML content
    remove_filter('the_content', 'wpautop');

    echo '<div class="html-content">' . $html_page->content . '</div>'; // Output the HTML content directly

    // Re-enable WordPress's automatic paragraph tagging after outputting the HTML content
    add_filter('the_content', 'wpautop');
} else {
    echo '<p>HTML page not found for this post or page.</p>'; // Display a message if no content is found
}

get_footer(); // Load the WordPress footer
?>
