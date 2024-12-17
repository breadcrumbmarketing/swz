<?php
/*
Template Name: Car Search Results
*/

get_header();

// Get the search query
$search_query = isset($_GET['s']) ? sanitize_text_field($_GET['s']) : '';

// Get search results
$results = handle_car_search($search_query);
$results_count = $results ? count($results) : 0;
?>



<div class="search-page-layout">
    <!-- Dashboard Panel -->
    <div class="search-dashboard">
        <!-- Search Status -->
        <div class="dashboard-section">
            <div class="dashboard-title">Suchstatus</div>
            <div class="status-indicator <?php echo $results ? '' : 'no-results'; ?>">
                <div class="status-dot"></div>
                <div class="status-text">
                    <?php echo $results ? 'Aktive Suche' : 'Keine Ergebnisse'; ?>
                </div>
            </div>
        </div>
        <div class="filter-compare">
            <button id="compare-selected-cars" class="compare-button" disabled>Vergleichen</button>
        </div>
        <!-- Search Term -->
        <div class="dashboard-section">
            <div class="dashboard-title">Suchbegriff</div>
            <div class="dashboard-value search-term">
                <?php echo $search_query ? esc_html($search_query) : 'Keine Suche'; ?>
            </div>
        </div>

        <!-- Stats Grid -->
        <div class="dashboard-section">
            <div class="dashboard-title">Suchergebnisse</div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-label">Gefundene Autos</div>
                    <div class="stat-value"><?php echo $results_count; ?></div>
                </div>
                <?php if ($results): ?>
                <div class="stat-card">
                    <div class="stat-label">Durchschnittspreis</div>
                    <div class="stat-value">
                        <?php
                        $total_price = 0;
                        foreach ($results as $car) {
                            $total_price += $car->price;
                        }
                        echo '€' . number_format($total_price / $results_count, 0, ',', '.');
                        ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$results): ?>
        <div class="dashboard-section">
            <div class="dashboard-title">Hinweis</div>
            <div class="dashboard-value" style="font-size: 16px;">
                Keine Autos gefunden. Bitte versuchen Sie eine andere Suche.
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Main Content Area -->
    <div class="search-results-container">
        <div class="car-grid">
            <?php 
            if ($results): 
                foreach ($results as $car):
                    include locate_template('parts/car-card.php');
                endforeach;
            endif; 
            ?>
        </div>
    </div>
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
    const searchDashboard = document.querySelector(".search-dashboard");
    const footer = document.querySelector(".site-footer");

    function hideDashboardOnFooterTouch() {
        const dashboardRect = searchDashboard.getBoundingClientRect();
        const footerRect = footer.getBoundingClientRect();

        // Check if the dashboard overlaps the footer
        if (dashboardRect.bottom > footerRect.top) {
            searchDashboard.style.visibility = "hidden";
        } else {
            searchDashboard.style.visibility = "visible";
        }
    }

    // Run on load and scroll events
    window.addEventListener("scroll", hideDashboardOnFooterTouch);
    window.addEventListener("resize", hideDashboardOnFooterTouch);

    // Initial check
    hideDashboardOnFooterTouch();
});
</script>

<?php get_footer(); ?>