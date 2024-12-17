<?php
/*
Template Name: Car Filter and Grid
*/

get_header(); ?>
<!-- Elementor-Compatible Template -->
<div id="primary" class="content-area">
    <main id="main" class="site-main">

        <?php
        // Start the Loop to integrate Elementor content
        while ( have_posts() ) :
            the_post();
            the_content(); // Allow Elementor to inject content
        endwhile;
        ?>

        <!-- Page Layout Container -->
        <div class="page-container">
            <!-- Sidebar Filter -->
            <?php get_template_part('parts/car-filter-form'); ?>

            <!-- Grid of Cards -->
            <div class="grid-container">
                <div class="car-grid">
                    <?php 
                    // Include the query and fetch cars
                    include locate_template('parts/car-query.php'); 
                    ?>
                </div>
            </div>
        </div>

        

    </main>
</div>

<!-- Full-Screen Comparison Popup -->
<div id="comparison-popup" class="comparison-popup hidden">
    <div class="comparison-popup-content">
        <!-- Close Button -->
        <button id="close-comparison-popup" class="close-popup">&times;</button>

        <!-- Popup Title -->
        <h2 class="comparison-title">Vergleich der ausgewählten Autos</h2>

        <!-- Comparison Table -->
        <div class="comparison-table-container">
            <table id="comparison-table">
                <thead>
                    <tr id="table-header">
                        <th class="comparison-attribute-header">Eigenschaften</th>
                        <!-- Car names and images dynamically added here -->
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="comparison-attribute-label">Baujahr</td>
                    </tr>
                    <tr>
                        <td class="comparison-attribute-label">Kraftstoff</td>
                    </tr>
                    <tr>
                        <td class="comparison-attribute-label">Kilometerstand</td>
                    </tr>
                    <tr>
                        <td class="comparison-attribute-label">Leistung</td>
                    </tr>
                    <tr>
                        <td class="comparison-attribute-label">Preis</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {
    const filterSidebar = document.querySelector(".filter-sidebar");
    const footer = document.querySelector(".site-footer");

    function hideSidebarOnFooterTouch() {
        const footerRect = footer.getBoundingClientRect();
        const sidebarHeight = filterSidebar.offsetHeight;
        const sidebarTop = parseInt(window.getComputedStyle(filterSidebar).top); // 15vh converted to px

        // Calculate sidebar's bottom position relative to the viewport
        const sidebarBottom = sidebarTop + sidebarHeight;

        // Check if sidebar visually touches the footer
        if (sidebarBottom > footerRect.top) {
            filterSidebar.style.opacity = "0";
            filterSidebar.style.pointerEvents = "none"; // Disable interactions
        } else {
            filterSidebar.style.opacity = "1";
            filterSidebar.style.pointerEvents = "auto";
        }
    }

    // Run the function on load, scroll, and resize
    window.addEventListener("scroll", hideSidebarOnFooterTouch);
    window.addEventListener("resize", hideSidebarOnFooterTouch);
    hideSidebarOnFooterTouch();
});
</script>


<?php get_footer(); ?>