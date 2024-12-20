<?php
/**
 * Template Name: Home SWZ
 * @package WordPress
 * @subpackage SWZ
 * @since Breadcrumb
 */

get_header(); // This includes your theme's header.

if ( have_posts() ) :
    while ( have_posts() ) : the_post();
        the_content(); // This function outputs the content of the page, which is editable with Elementor.
    endwhile;
endif;

get_footer(); // This includes your theme's footer.
?>
