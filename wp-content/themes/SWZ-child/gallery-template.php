<?php
/**
 * Template Name: Car Gallery
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
    'post_type'      => 'page',
    'posts_per_page' => 12,
    'paged'          => $current_page,
    'meta_query'     => array(
        array(
            'key'   => '_wp_page_template',
            'value' => 'carpage.php',
        )
    )
);

// Add brand and model query if selected
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
$all_pages = get_posts(array(
    'post_type'      => 'page',
    'posts_per_page' => -1,
    'meta_key'       => '_wp_page_template',
    'meta_value'     => 'carpage.php'
));

$brands = [];
$models = [];

foreach ($all_pages as $page) {
    $brand = get_post_meta($page->ID, 'car_brand', true);
    $model = get_post_meta($page->ID, 'car_model', true);
    $brands[$brand] = $brand;
    if ($selected_brand && $brand === $selected_brand) {
        $models[$model] = $model;
    }
}

asort($brands); // Sort brands alphabetically
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
    background-color: rgba(245, 245, 245, 0); /* Light background for the gallery */
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
    grid-template-columns: repeat(3, 1fr); /* Three columns layout */
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
    height: 450px; /* Adjusted fixed height for consistency */
    width: 100%; /* Adjust width as per grid column width */
}

.gallery-card:hover {
    transform: scale(1.03);
    box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
}

/* Top Section for Image */
.gallery-card .image-container {
    height: 45%; /* Adjusted to 60% of the card's height */
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


.text-container {
    padding: 10px 15px;  /* Check if padding is too much */
    align-items: start;  /* Align items to the start to reduce vertical space usage */
}

/* Bottom Section for Text */
.gallery-card .text-container {
    height: 35%; /* Adjusted to 40% of the card's height */
    padding: 10px 15px; /* Consistent padding */
    text-align: left; /* Align text to the left */
    font-size: 14px; /* Smaller font size */
    color: #555;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}

.gallery-card .text-container h3 {
    font-size: 16px;
    color: black;
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
    color: black;
    position: absolute;
    bottom: 10px;
    left: 15px;
    z-index: 9999;
    top:0px
}

.filter-but {
    color: white;
}
.filter-but:hover {
  /* Hover state changes */
  background-color: #DE4F3E;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
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
.testbericht {
    font-size: 1,5em; /* Smaller font size */
    color: black; /* Dark grey color */
    font-style: default; /* Italic font style */
    margin-bottom: 5px; /* Extra space below the text */
    text-align: left;
    margin-left: 15px;
   
}


.text-container h2.testbericht {
    margin-bottom: 0; /* Reduces the space between the 'Testbericht' and the title */
    font-size: 2em; /* Adjust the font size if needed */
    font-weight: bold;
}

.text-container h3 {
    font-size: 1em;
    margin-top: 5px; /* Removes space above the h3 */
    margin-bottom: 10px; /* Adjusts space between the title and the paragraph */
}

.text-container p.more-info {
    visibility: visible; /* Makes the 'Mehr lesen' always visible */
    opacity: 1; /* Ensures it's not transparent */
    transition: opacity 0.3s ease; /* Smooth transition for hover effect */
    margin-top: 5px;
    color: #555; 
}
.text-container p.more-info:hover {
    color: #DE4F3E; /* Changes color on hover */
}

/* Pagination styles */
.paginationgswz {
    display: flex;
    justify-content: center; /* Center the pagination horizontally */
    padding: 20px 0; /* Padding around the pagination for spacing */
}

.page-number {
    margin: 0 5px;
    padding: 8px 16px;
    border: 1px solid #ddd;
    border-radius: 4px;
    background-color: #f5f5f5;
    cursor: pointer;
    transition: background-color 0.3s;
}

.page-number.active, .page-number:hover {
    background-color: #007bff;
    color: white;
}

.page-navigation-button {
    padding: 8px 16px;
    margin: 0 5px;
    background-color: #f5f5f5;
    border: 1px solid #ddd;
    color: #333;
    cursor: pointer;
    text-decoration: none;
    border-radius: 4px;
}

.page-navigation-button:hover {
    background-color: #e2e6ea;
}

.page-navigation-button.disabled {
    opacity: 0.6;
    cursor: not-allowed;
}

</style>
<div class="gallery-container">
    <div class="filter-bar">
        <form method="GET">
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
            <button type="submit" class="filter-but">Filtern</button>
        </form>
    </div>
    <div class="gallery-grid">
        <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="gallery-card">
                <div class="image-container" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://via.placeholder.com/300'; ?>');">
                </div>
                



                <h2 class="testbericht">Testbericht: <?php echo esc_html(get_post_meta(get_the_ID(), 'car_brand', true)); ?></h2> 
                <div class="text-container">
    
    <h3><?php the_title(); ?></h3> <!-- Title of the car -->
    <p class="more-info">Mehr lesen</p> <!-- More info button or link -->
</div>






            </a>
        <?php endwhile; ?>
        <!-- New Div for Pagination -->
    <div class="pagination-container">
        <div class="paginationgswz">
            <?php
            echo paginate_links(array(
                'total' => $query->max_num_pages,
                'current' => $current_page,
                'prev_text' => __('« Prev'),
                'next_text' => __('Next »'),
            ));
            ?>
        </div>
    </div>
        <?php else : ?>
            <p>Keine Autos gefunden. Versuchen Sie, den Filter anzupassen.</p>
        <?php endif; ?>
    </div>
</div>

<?php get_footer(); ?>