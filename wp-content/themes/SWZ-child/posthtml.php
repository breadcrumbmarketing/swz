<?php
/**
 * Template Name: Display Raw HTML Content
 * Template Post Type: page
 */

get_header(); // Load the WordPress header

// Get the raw HTML content from the custom field
$raw_html_content = get_post_meta(get_the_ID(), '_raw_html_content', true);

if ($raw_html_content) {
    // Output the raw HTML content directly
    echo $raw_html_content;
} else {
    // Debugging: Log the issue if no content is found
    error_log('No HTML content found for page ID: ' . get_the_ID());
    echo '<p>No HTML content found for this page.</p>';
}

get_footer(); // Load the WordPress footer
?>
