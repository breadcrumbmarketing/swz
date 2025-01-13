<?php
/*
Template Name: Testbericht Template
Template Post Type: post
*/
get_header(); ?>

<style>

/* General Body Styling */
body {
    font-family: 'Arial', sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    background-color: #f9f9f9; /* Light background for readability */
    color: #333; /* Dark gray for text */
}

/* Container for Post Content */
.testbericht-container {
    max-width: 800px;
    margin: 40px auto;
    padding: 20px;
    background-color: #ffffff; /* White background */
    border: 1px solid #e0e0e0; /* Light border for contrast */
    border-radius: 8px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1); /* Subtle shadow for depth */
}

/* Hero Image Styling */
.post-hero img {
    width: 100%;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.2);
}

/* Heading Styling */
.temp-content-section-heading3 {
    font-size: 24px;
    font-weight: bold;
    color: #0073aa; /* WordPress blue for emphasis */
    border-bottom: 2px solid #e0e0e0; /* Subtle underline */
    margin-bottom: 15px;
    padding-bottom: 5px;
}

/* Background-Colored Sections */
.edit-bgcolor {
    background-color: #f5f5f5; /* Light gray background */
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
}

/* Article Styling */
article {
    padding: 20px;
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
}

/* Subheadings (H3) Styling */
article h3 {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    margin-top: 20px;
    margin-bottom: 10px;
    border-left: 4px solid #0073aa; /* Blue accent for subheadings */
    padding-left: 10px;
}

/* Paragraph Styling */
article p {
    font-size: 16px;
    color: #555; /* Medium gray for readability */
    margin-bottom: 15px;
}

/* Responsive Design */
@media screen and (max-width: 768px) {
    .testbericht-container {
        padding: 15px;
    }

    .temp-content-section-heading3 {
        font-size: 20px;
    }

    article h3 {
        font-size: 18px;
    }

    article p {
        font-size: 15px;
    }
}



</style>
<div class="testbericht-container">
    <?php while (have_posts()) : the_post(); ?>

        <!-- Post Title -->
        <h1 class="post-title"><?php the_title(); ?></h1>

        <!-- Hero Image -->
        <div class="post-hero">
            <?php if (has_post_thumbnail()) : ?>
                <img src="<?php the_post_thumbnail_url('full'); ?>" alt="<?php the_title(); ?>">
            <?php endif; ?>
        </div>

        <!-- Post Content -->
        <div class="post-content">
            <?php the_content(); ?>
        </div>

    <?php endwhile; ?>
</div>


<?php get_footer(); ?>
