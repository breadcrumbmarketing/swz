<?php
/*
 * Template Name: SWZ
 * Description: A custom template compatible with Elementor for editing content.
 */

get_header(); ?>

<!-- Start Elementor-Compatible Template -->
<div id="primary" class="content-area">
    <main id="main" class="site-main" role="main">
        <?php
        // Elementor Content: Allow Elementor to take over page content
        while (have_posts()) :
            the_post();
            the_content(); // Renders content editable via Elementor
        endwhile;
        ?>
    </main>
</div>
<!-- End Elementor-Compatible Template -->

<?php get_footer(); ?>
