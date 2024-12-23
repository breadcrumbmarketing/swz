<?php
/**
 * Template Name: Display HTML Content
 */

get_header(); // Load header

// Fetch the HTML page content with id = 6 from the database
global $wpdb;
$html_page = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}html_pages WHERE id = 6");

if ($html_page) {
    // Output the title and content of the page
    echo '<h1>' . esc_html($html_page->title) . '</h1>';
    echo '<div class="html-content">';
    echo wp_kses_post($html_page->content); // Display HTML content safely
    echo '</div>';
} else {
    echo '<p>HTML page with ID 6 not found.</p>';
}

get_footer(); // Load footer
