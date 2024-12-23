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
    echo $html_page->content; // Output the raw HTML content
    echo '</div>';

    // Enqueue custom styles and scripts if needed
    wp_enqueue_style('custom-carousel-style', 'path/to/your/carousel.css');
    wp_enqueue_script('custom-carousel-script', 'path/to/your/carousel.js', array('jquery'), null, true);
} else {
    echo '<p>HTML page with ID 6 not found.</p>';
}

get_footer(); // Load footer

// Add custom inline styles to make the background transparent for this template
?>
<style>
    /* Override body background to be transparent */
    body {
        background-color:rgb(238, 238, 238) !important;
    }

    /* Keep the HTML background gradient working */
    html {
        background-color:rgb(238, 238, 238) !important;
    }

</style>
