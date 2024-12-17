<?php
global $wpdb;

// Prepare query filters
$car_model   = isset($_GET['car_model']) ? sanitize_text_field($_GET['car_model']) : '';
$price_range = isset($_GET['price_range']) ? intval($_GET['price_range']) : 150000;
$car_types   = isset($_GET['car_type']) ? (array) $_GET['car_type'] : []; // Ensure array
$fuel_types  = isset($_GET['fuel_type']) ? (array) $_GET['fuel_type'] : []; // Ensure array

// Start building the SQL query
$query = "SELECT * FROM wp_cars WHERE 1=1";

// Model filter
if (!empty($car_model)) {
    $query .= $wpdb->prepare(" AND car_name LIKE %s", '%' . $car_model . '%');
}

// Price filter
if (!empty($price_range)) {
    $query .= $wpdb->prepare(" AND price <= %d", $price_range);
}

// Car Type filter
if (!empty($car_types)) {
    $placeholders = implode(',', array_fill(0, count($car_types), '%s'));
    $query .= $wpdb->prepare(" AND car_type IN ($placeholders)", ...$car_types);
}

// Fuel Type filter
if (!empty($fuel_types)) {
    $placeholders = implode(',', array_fill(0, count($fuel_types), '%s'));
    $query .= $wpdb->prepare(" AND fuel_type IN ($placeholders)", ...$fuel_types);
}

// Fetch results
$results = $wpdb->get_results($query);

// Display Results
if ($results):
    foreach ($results as $car):
        include locate_template('parts/car-card.php');
    endforeach;
else:
    echo '<p>Keine Autos gefunden.</p>';
endif;
?>