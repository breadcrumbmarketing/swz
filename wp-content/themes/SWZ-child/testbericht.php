<?php
/*
Template Name: Testbericht Template
Template Post Type: post
*/
get_header(); ?>

<div class="testbericht-container">
    <?php
    while (have_posts()) : the_post(); ?>
        <div class="post-hero">
            <?php if (has_post_thumbnail()) : ?>
                <img src="<?php the_post_thumbnail_url('full'); ?>" alt="<?php the_title(); ?>">
            <?php endif; ?>
        </div>

        <div class="post-content">
            <?php the_content(); ?>
        </div>
    <?php endwhile; ?>
</div>

<?php get_footer(); ?>
