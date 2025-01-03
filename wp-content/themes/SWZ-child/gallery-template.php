<?php
/**
 * Template Name: Car Gallery
 * Template Post Type: page
 */

get_header(); // Load the WordPress header

global $wpdb;

// Prepare dropdown data
$distinct_brands = $wpdb->get_results("SELECT DISTINCT car_brand FROM {$wpdb->prefix}html_pages WHERE car_brand IS NOT NULL ORDER BY car_brand ASC");

$brands_models = [];
foreach ($distinct_brands as $brand) {
    $models = $wpdb->get_results($wpdb->prepare(
        "SELECT DISTINCT car_model FROM {$wpdb->prefix}html_pages WHERE car_brand = %s AND car_model IS NOT NULL ORDER BY car_model ASC",
        $brand->car_brand
    ));
    $brands_models[$brand->car_brand] = array_map(function($item) {
        return $item->car_model;
    }, $models);
}

// Handle filter input
$selected_brand = isset($_GET['brand']) ? sanitize_text_field($_GET['brand']) : '';
$selected_model = isset($_GET['model']) ? sanitize_text_field($_GET['model']) : '';
$selected_sort = isset($_GET['sort']) ? $_GET['sort'] : '';

// Determine sort order
$order = 'ASC';
if (strpos($selected_sort, 'desc') !== false) {
    $order = 'DESC';
}

// Determine meta_key based on sort choice
$meta_key = '';
switch ($selected_sort) {
    case 'price_asc':
    case 'price_desc':
        $meta_key = 'price';
        break;
    case 'power_asc':
    case 'power_desc':
        $meta_key = 'power';
        break;
    case 'co2_asc':
    case 'co2_desc':
        $meta_key = 'co2';
        break;
    case 'date_newer':
        $meta_key = 'created_at';
        break;
}

$args = array(
    'post_type'      => 'page',
    'posts_per_page' => -1,
    'meta_key'       => $meta_key,
    'orderby'        => 'meta_value_num',
    'order'          => $order,
    'meta_query'     => array(
        array(
            'key'   => '_wp_page_template',
            'value' => 'carpage.php',
        )
    )
);

$query = new WP_Query($args);
?>


<style>
/* Import Poppins font */
@import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

/* Apply Poppins font */
body, .filter-bar select, .filter-bar button, .gallery-card h3, .gallery-card p {
    font-family: 'Poppins', sans-serif;
}

/* General Styling for Gallery */
.gallery-container {
    padding: 20px;
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
    background-color:rgba(245, 245, 245, 0); /* Light background for the gallery */
}

.filter-bar {
    margin-bottom: 20px;
}

.filter-bar form {
    display: flex;
    justify-content: center;
    gap: 10px;
}

.filter-bar select,
.filter-bar button {
    padding: 10px;
    font-size: 16px;
    border-radius: 5px;
    border: 1px solid #ddd;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); /* Adjusts to fit the container but not exceeding 1fr */
    gap: 10px; /* Minimal gap between cards */
    justify-content: center; /* Centers cards in the grid when fewer items */
}

.gallery-card {
    display: flex;
    flex-direction: column;
    background-color: #fff; /* White background */
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 300px; /* Fixed height for consistency */
    width: 280px; /* Fixed width */
}

.gallery-card:hover {
    transform: scale(1.03);
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
}

/* Top Section for Image */
.gallery-card .image-container {
    flex: 2; /* 2/3 of the card height */
    background-size: cover;
    background-position: center; /* Center the image */
    background-repeat: no-repeat; /* Prevent image repetition */
    background-color: #fff;
    display: flex;
    justify-content: center;
    align-items: center;
    border-bottom: 1px solid #ddd;
}

/* Fallback for no images */
.gallery-card .image-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

/* Bottom Section for Text */
.gallery-card .text-container {
    flex: 1; /* 1/3 of the card height */
    position: relative;
    padding: 10px 15px; /* Consistent padding */
    text-align: left; /* Align text to the left */
    font-size: 14px; /* Smaller font size */
    color: #555;
    overflow: hidden; /* Ensure consistent height */
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.gallery-card .text-container h3 {
    font-size: 14px;
    font-weight: 600;
    line-height: 1.4;
    margin: 0;
    display: -webkit-box;
    -webkit-line-clamp: 4; /* Display a maximum of 4 lines */
    -webkit-box-orient: vertical;
    overflow: hidden;
    text-overflow: ellipsis;
}

.gallery-card .text-container p {
    font-size: 12px;
    color: #777;
    margin: 0;
    position: absolute;
    bottom: 10px;
    left: 15px;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Smaller cards for mobile */
    }

    .gallery-card .text-container {
        padding: 8px 10px;
    }

    .gallery-card .text-container h3 {
        font-size: 12px;
    }

    .gallery-card .text-container p {
        font-size: 10px;
        bottom: 5px;
    }
}
</style>
<div class="gallery-container">
    <!-- Filtering Form -->
    <div class="filter-bar">
        <form method="GET">
            <!-- Brand Selection -->
            <select name="brand" onchange="this.form.submit()">
                <option value="">Marke auswählen</option>
                <?php foreach ($distinct_brands as $brand): ?>
                    <option value="<?php echo esc_attr($brand->car_brand); ?>" <?php selected($selected_brand, $brand->car_brand); ?>>
                        <?php echo esc_html($brand->car_brand); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- Model Selection -->
            <select name="model" onchange="this.form.submit()">
                <option value="">Modell auswählen</option>
                <?php if (!empty($selected_brand) && isset($brands_models[$selected_brand])): ?>
                    <?php foreach ($brands_models[$selected_brand] as $model): ?>
                        <option value="<?php echo esc_attr($model); ?>" <?php selected($selected_model, $model); ?>>
                            <?php echo esc_html($model); ?>
                        </option>
                    <?php endforeach; ?>
                <?php endif; ?>
            </select>
            <!-- Sorting Dropdown -->
            <select name="sort" onchange="this.form.submit()">
                <option value="">Sortieren nach</option>
                <option value="price_asc">Preis aufsteigend</option>
                <option value="price_desc">Preis absteigend</option>
                <option value="power_asc">Leistung aufsteigend</option>
                <option value="power_desc">Leistung absteigend</option>
                <option value="co2_asc">CO2 aufsteigend</option>
                <option value="co2_desc">CO2 absteigend</option>
                <option value="date_newer">Neueste</option>
            </select>
            <button type="submit">Filtern</button>
        </form>
    </div>

    <!-- Gallery Grid -->
    <div class="gallery-grid">
        <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="gallery-card">
                <div class="image-container" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://via.placeholder.com/300'; ?>');">
                </div>
                <div class="text-container">
                    <h3><?php the_title(); ?></h3>
                    <p>Mehr lesen</p>
                </div>
            </a>
        <?php endwhile; else : ?>
            <p>Keine Autos gefunden. Versuchen Sie, den Filter anzupassen.</p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>
