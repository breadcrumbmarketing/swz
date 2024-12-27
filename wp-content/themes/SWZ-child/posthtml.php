<?php
/**
 * Template Name: Display Raw HTML Content
 * Template Post Type: page
 */

get_header(); // Load the WordPress header

// Get the raw HTML content from the custom field
$raw_html_content = get_post_meta(get_the_ID(), '_raw_html_content', true);

if ($raw_html_content) {
    echo $raw_html_content; // Directly output the raw HTML content
} else {
    echo '<p>No HTML content found for this page.</p>';
}

get_footer(); // Load the WordPress footer
?>
