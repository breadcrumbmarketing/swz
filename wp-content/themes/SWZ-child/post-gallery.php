<?php
/**
 * Template Name: Testbericht Gallery
 * Template Post Type: page
 */

get_header(); // Load the WordPress header

global $wpdb;

// Initialize filters
$selected_brand = isset($_GET['brand']) ? sanitize_text_field($_GET['brand']) : '';
$selected_model = isset($_GET['model']) ? sanitize_text_field($_GET['model']) : '';
$current_page = get_query_var('paged') ? get_query_var('paged') : 1;

// Build the base args for WP_Query with pagination
$args = array(
    'post_type'      => 'post',
    'posts_per_page' => 12,
    'paged'          => $current_page,
    'category_name'  => 'testbericht', // Only include posts from "Testbericht" category
    'meta_query'     => array(
        'relation' => 'AND', // Ensures multiple conditions work together
    ),
);

// Add brand and model filters if selected
if (!empty($selected_brand)) {
    $args['meta_query'][] = array(
        'key'     => 'car_brand',
        'value'   => $selected_brand,
        'compare' => '='
    );
}

if (!empty($selected_model)) {
    $args['meta_query'][] = array(
        'key'     => 'car_model',
        'value'   => $selected_model,
        'compare' => '='
    );
}

$query = new WP_Query($args);

// Gather data for dropdowns
$all_posts = get_posts(array(
    'post_type'      => 'post',
    'posts_per_page' => -1,
    'category_name'  => 'testbericht', // Only gather from the "Testbericht" category
));

$brands = [];
$models = [];

foreach ($all_posts as $post) {
    $brand = get_post_meta($post->ID, 'car_brand', true);
    $model = get_post_meta($post->ID, 'car_model', true);

    if (!empty($brand)) {
        $brands[$brand] = $brand;
    }
    if (!empty($model) && $selected_brand === $brand) { // Filter models based on the selected brand
        $models[$model] = $model;
    }
}

asort($brands); // Sort brands alphabetically
asort($models); // Sort models alphabetically
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
    padding: 35px;
    max-width: 1200px;
    margin: 0 auto;
    text-align: center;
    background-color: rgba(192, 17, 17, 0); /* Light background for the gallery */
    padding-bottom: 80px;
}

.gallery-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr); /* Three columns layout */
    gap: 20px; /* Gap between cards */
    justify-content: center;
}

.gallery-card {
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Space out sections inside the card */
    background-color: #fff; /* White background */
    border: 1px solid #ddd;
    border-radius: 10px;
    overflow: hidden;
    box-shadow: 0px 2px 5px rgba(0, 0, 0, 0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    height: 100%; /* Ensures all cards are the same height */
    text-decoration: none; /* Remove link styles */
}

.gallery-card:hover {
    transform: scale(1.03);
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
}

/* Image Section */
.gallery-card .image-container {
    height: 200px; /* Fixed height for image */
    background-size: cover;
    background-position: center; /* Center the image */
    background-repeat: no-repeat;
    display: flex;
    justify-content: center;
    align-items: center;
    border-bottom: 1px solid #ddd;
}

.gallery-card .image-container img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
}

/* Text Section */
.gallery-card .text-container {
    display: flex;
    flex-direction: column;
    justify-content: space-between;
    padding: 15px;
    text-align: left;
    height: 100%;
}

.gallery-card .text-container h2.testbericht {
    font-size: 16px;
    color: #333;
    font-weight: bold;
    margin-bottom: 8px; /* Space below the brand/model title */
}

.gallery-card .text-container h3 {
    font-size: 16px;
    color: black;
    font-weight: 600;
    line-height: 1.4;
    margin: 0;
}

.gallery-card .text-container .excerpt {
    margin: 15px 0; /* Adds spacing above and below the excerpt */
    font-size: 14px;
    color: #666;
    line-height: 1.5;
    overflow: hidden; /* Hides overflowing content */
    text-overflow: ellipsis; /* Adds "..." for overflowing text */
    display: -webkit-box;
    -webkit-line-clamp: 3; /* Limits text to 3 lines */
    -webkit-box-orient: vertical;
}

/* CTA (Mehr lesen) Button */
.gallery-card .text-container .more-info {
    text-align: center;
    font-size: 14px;
    font-weight: bold;
    color: #DE4F3E;
    cursor: pointer;
    transition: color 0.3s ease;
}

.gallery-card .text-container .more-info:hover {
    color: #A3241E; /* Darker red on hover */
}

/* Filter Bar */
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

.clear-filter-button {
    color: white;
    background-color: #DE4F3E;
    border: none;
    padding: 10px 15px;
    cursor: pointer;
    font-size: 16px;
    font-weight: bold;
    border-radius: 5px;
    transition: background-color 0.3s ease, box-shadow 0.3s ease;
}

.clear-filter-button:hover {
    background-color: #A3241E;
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
}

/* Pagination */
.pagination-container {
    display: flex;
    justify-content: center;
    padding: 20px 0;
}

.paginationgswz {
    display: flex;
    gap: 10px;
}

.paginationgswz a,
.paginationgswz span {
    display: inline-block;
    padding: 8px 12px;
    text-decoration: none;
    font-size: 14px;
    font-weight: 500;
    border-radius: 4px;
    background-color: #f0f0f0;
    color: #DE4F3E;
    transition: background-color 0.3s ease;
}

.paginationgswz a:hover,
.paginationgswz span.current {
    background-color: #DE4F3E;
    color: white;
}

/* Responsive Adjustments */
@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: 1fr; /* Single column on smaller screens */
        gap: 20px;
    }

    .gallery-card .image-container {
        height: 150px; /* Adjust image height */
    }

    .gallery-card .text-container {
        padding: 10px;
    }

    .gallery-card .text-container h3 {
        font-size: 14px; /* Smaller font size for mobile */
    }

    .gallery-card .text-container .excerpt {
        font-size: 13px; /* Smaller excerpt font size */
    }
}

</style>
<div class="gallery-container">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" id="filter-form">
            <select name="brand" onchange="this.form.submit()">
                <option value="">Marke auswählen</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo esc_attr($brand); ?>" <?php selected($selected_brand, $brand); ?>>
                        <?php echo esc_html($brand); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="model" onchange="this.form.submit()">
                <option value="">Modell auswählen</option>
                <?php foreach ($models as $model): ?>
                    <option value="<?php echo esc_attr($model); ?>" <?php selected($selected_model, $model); ?>>
                        <?php echo esc_html($model); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- Clear Filter Button -->
            <button type="button" class="clear-filter-button" onclick="clearFilters()">Filter entfernen</button>
        </form>
    </div>

    <!-- Gallery Grid -->
    <div class="gallery-grid">
        <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>
            <div class="gallery-card">
                <!-- Image Section -->
                <div class="image-container" 
                     style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://via.placeholder.com/300'; ?>');">
                </div>

                <!-- Text Section -->
                <div class="text-container">
                    <!-- Car Brand and Model -->
                    <h2 class="testbericht">
                        <?php 
                            $car_brand = esc_html(get_post_meta(get_the_ID(), 'car_brand', true));
                            $car_model = esc_html(get_post_meta(get_the_ID(), 'car_model', true));
                            echo $car_brand . ' ' . $car_model;
                        ?>
                    </h2>

                    <!-- Post Title -->
                    <h3><?php the_title(); ?></h3>

                    <!-- Excerpt -->
                    <p class="excerpt">
                        <?php echo wp_trim_words(get_the_content(), 20, '...'); ?>
                    </p>

                    <!-- Read More Button -->
                    <p class="more-info">
                        <a href="<?php the_permalink(); ?>">Mehr lesen</a>
                    </p>
                </div>
            </div>
        <?php endwhile; ?>

        <!-- Pagination -->
        <div class="pagination-container">
            <div class="paginationgswz">
                <?php
                echo paginate_links(array(
                    'total' => $query->max_num_pages,
                    'current' => $current_page,
                    'prev_text' => __('« Zurück'),
                    'next_text' => __('Weiter »'),
                ));
                ?>
            </div>
        </div>
        <?php else : ?>
            <p>Keine Testberichte gefunden. Passen Sie den Filter an.</p>
        <?php endif; ?>
    </div>
</div>

<script>
    function clearFilters() {
        // Reset the form to default values
        document.getElementById('filter-form').reset();

        // Clear the URL parameters (brand and model)
        const url = new URL(window.location.href);
        url.searchParams.delete('brand');
        url.searchParams.delete('model');

        // Redirect to the updated URL (without filters)
        window.location.href = url.toString();
    }
</script>


<?php get_footer(); ?>