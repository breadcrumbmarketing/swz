<?php
/*
Template Name: Testbericht Template
Template Post Type: post
*/
get_header(); ?>

<style>

@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap');


            .entry-title { /* Adjust the selector as per your theme */
                display: none;
            }



            /* rest */
        html    body {
            width: 100vw !important;
            height: 100% !important;
            font-family: 'Poppins', sans-serif;
    line-height: 1.6;
    margin: 0;
    padding: 0;
    background-color: #ffffff !important; /* Light background for readability */
    color: #333; /* Dark gray for text */
}

/* Container for Post Content */
.testbericht-container {
    max-width: 1200px;
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
    color: #DE4F3E; /* WordPress blue for emphasis */
    border-bottom: 2px solid #e0e0e0; /* Subtle underline */
    margin-bottom: 15px;
    padding-bottom: 5px;
    font-family: 'Poppins', sans-serif;}

/* Background-Colored Sections */
.edit-bgcolor {
    background-color: #f5f5f5; /* Light gray background */
    padding: 15px;
    border-radius: 6px;
    margin-bottom: 20px;
    margin-top: -70px;
}

/* Article Styling */
article {
    padding: 20px;
    background-color: #fff;
    border: 1px solid #e0e0e0;
    border-radius: 8px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    font-family: 'Poppins', sans-serif;
}
.temp-content-section-heading3.edit-bgcolor {
    display: none !Important;
}
/* Subheadings (H3) Styling */
article h3 {
    font-size: 20px;
    font-weight: bold;
    color: #333;
    margin-top: 20px;
    margin-bottom: 10px;
    border-left: 4px solid #DE4F3E; /* Blue accent for subheadings */
    padding-left: 10px;
    font-family: 'Poppins', sans-serif;
}

/* Paragraph Styling */
article p {
    font-size: 16px;
    color: #555; /* Medium gray for readability */
    margin-bottom: 15px;
    font-family: 'Poppins', sans-serif;
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
.post-title {
    font-family: 'Poppins', sans-serif;
    font-weight: 700;
}
.back-button {
    display: flex !important;
    justify-content: center!important; 
    align-items: center!important;  
    margin: 0 auto!important;  
    background-color: #DE4F3E!important;
    color: white!important;
    border-radius: 12px!important;
    padding-left: 40px!important;
    padding-right: 40px!important;
    font-family: 'Poppins', sans-serif!important;
    font-size: 20px!important;
    border: solid 2px!important;
}
.back-button:hover {
    background-color: transparent!important;
    color: #DE4F3E!important;
    border-color: #DE4F3E!important;
    border: solid 2px!important;
}   

.nav.elementor-pagination{
    display: flex;
    justify-content: center;
    align-items: center;
    width: 100%;
    padding: 20px 0;
    position: fixed;
    bottom: 0;
    left: 0;
    background-color: rgba(37, 37, 37, 0.9);  
    box-shadow: 0 -2px 10px rgba(0, 0, 0, 0.1);  
    z-index: 1000;  
    gap:10px; 
}




.nav.elementor-pagination a,
.nav.elementor-pagination span {
    display: inline-block;
    padding: 8px 12px;
    text-decoration: none;
    color: #333;
    font-size: 14px;
    font-weight: 500;
    border-radius: 4px;
    transition: all 0.3s ease;
    color: white;
}

/* Current Page */
.nav.elementor-pagination span.current {
  /*  background-color: #DE4F3E!important; /* Highlight color for current page */
  border: solid 2px  #DE4F3E;
    color: white;
    border-radius: 4px;
}

/* Hover Effect */
.nav.elementor-pagination a:hover {
    background-color: #f0f0f0; /* Light background on hover */
    color: #DE4F3E !important; /* Change text color on hover */
}

/* Previous and Next Buttons */
.nav.elementor-pagination .prev,
.nav.elementor-pagination .next {
    font-weight: 600;
    background-color: #f0f0f0; /* Light background for prev/next buttons */
    border-radius: 4px;
    color:  #DE4F3E;
}

.nav.elementor-pagination .prev:hover,
.nav.elementor-pagination .next:hover {
    background-color: #DE4F3E !important; /* Highlight on hover */
    color: #fff !important;
}

/* Disabled Buttons (if applicable) */
.nav.elementor-pagination .disabled {
    color: #ccc;
    pointer-events: none;
    cursor: not-allowed;
}
        </style>

<div class="testbericht-container">
    <?php while (have_posts()) : the_post(); ?>

        <!-- Custom Title as H1 -->
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
        <button onclick="window.history.back();" class="back-button">Zurück</button>

    <?php endwhile; ?>
</div>





<?php get_footer(); ?>
