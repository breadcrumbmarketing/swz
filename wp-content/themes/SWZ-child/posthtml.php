<?php
/*
* Template Name: Display HTML Content
* Template Post Type: post
*/

get_header(); // Load header

// Fetch the HTML page content based on the post's slug
global $wpdb;
$post_slug = get_post_field('post_name', get_the_ID()); // Get the slug of the current post
$html_page = $wpdb->get_row($wpdb->prepare("SELECT * FROM {$wpdb->prefix}html_pages WHERE slug = %s", $post_slug));

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
    echo '<p>HTML page not found for this post.</p>';
}

get_footer(); // Load footer

// Custom inline styles to make the background transparent
?>
<style>
    /* Override body background to be transparent */
    body {
        background-color: rgb(238, 238, 238) !important;
    }

    /* Ensure the HTML background remains */
    html {
        background-color: rgb(238, 238, 238) !important;
    }
</style>
