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
/* Override background for the Gallery Template page */
body.gallery-template-page {
    margin: 0;
    height: 100vh;
    
      /* background: linear-gradient(180deg, #ffffff, rgb(238, 247, 252)) !important;*/
}
.copyright.show {
    display: none !important ;
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
    position: relative; /* Establishes a positioning context for absolutely positioned children */
    flex: 1; /* Takes up the remaining space in the flex container */
    padding: 10px 15px; /* Consistent padding around the content */
    text-align: left; /* Text aligned to the left */
    display: flex;
    flex-direction: column;
    justify-content: space-between; /* Space distribution for inner items */
    height: auto; /* Adjusted for content size */
    overflow: hidden; /* Hides anything out of the bound */
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
    position: absolute; /* Absolutely positioned relative to the nearest positioned ancestor */
    bottom: 10px; /* 10px from the bottom of the container */
    left: 15px; /* 15px from the left of the container */
    margin: 0; /* Resets any default margin */
    color: black; /* Text color */
    font-size: 12px; /* Font size */
    visibility: visible; /* Always visible */
    opacity: 1; /* Full opacity */
    transition: color 0.3s ease;
    background-color: transparent;
}

.gallery-card .text-container p:hover {
   color: #DE4F3E !important;
   
   
}





.gallery-card {
    text-decoration: none; /* Ensure no underline by default */
}

.gallery-card:hover {
    text-decoration: none; /* Ensure no underline on hover */
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

.filter-but {
    color: white;
    
}
.clear-filter-button:hover {
  /* Hover state changes */
  background-color: #DE4F3E !important;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1)!important;
  color: white!important;
}
.clear-filter-button 
{
    color: white;
}







/* Responsive Adjustments */
/* Responsive Adjustments */
@media (max-width: 768px) {
    .gallery-grid {
        grid-template-columns: 1fr; /* Display only one card per row on mobile */
        gap: 20px; /* Add some spacing between cards */
    }

    .gallery-card {
        height: 420px; /* Allow the card height to adjust based on content */
        width: 100%; /* Ensure the card takes up the full width */
    }

    .gallery-card .text-container {
        padding: 10px 15px; /* Adjust padding for better spacing */
    }

    .gallery-card .text-container h3 {
        font-size: 14px; /* Slightly larger font size for better readability */
    }

    .gallery-card .text-container p {
        font-size: 12px; /* Adjust font size for mobile */
        bottom: 10px; /* Position the "Mehr lesen" text */
    }

    .testbericht {
        font-size: 1.2em; /* Adjust font size for mobile */
    }

    .filter-bar form {
        flex-direction: column; /* Stack filter dropdowns vertically on mobile */
        gap: 10px; /* Add spacing between dropdowns */
    }

    .filter-bar select,
    .filter-bar button {
        width: 100%; /* Make dropdowns and buttons full width */
        font-size: 14px; /* Adjust font size for mobile */
      
    }
}

.testbericht {
    font-size: 1.5em;  
    color: black;  
    margin-bottom: 5px;  
    text-align: left;
    margin-left: 15px;
    font-weight: bold;
}

.text-container h2.testbericht {
    margin-bottom: 0; /* Reduces the space between the 'Testbericht' and the title */
    font-size: 1.5em; /* Adjust the font size if needed */
    font-weight: bold;
}

.text-container h3 {
    font-size: 1em;
    margin-top: 5px; /* Removes space above the h3 */
    margin-bottom: 10px; /* Adjusts space between the title and the paragraph */
}



/* Pagination Container */
.pagination-container {
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
}

/* Pagination Links */
.paginationgswz {
    display: flex;
    gap: 10px; /* Space between pagination items */
}

.paginationgswz a,
.paginationgswz span {
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
.paginationgswz span.current {
  /*  background-color: #DE4F3E!important; /* Highlight color for current page */
  border: solid 2px  #DE4F3E;
    color: white;
    border-radius: 4px;
}

/* Hover Effect */
.paginationgswz a:hover {
    background-color: #f0f0f0; /* Light background on hover */
    color: #DE4F3E !important; /* Change text color on hover */
}

/* Previous and Next Buttons */
.paginationgswz .prev,
.paginationgswz .next {
    font-weight: 600;
    background-color: #f0f0f0; /* Light background for prev/next buttons */
    border-radius: 4px;
    color:  #DE4F3E;
}

.paginationgswz .prev:hover,
.paginationgswz .next:hover {
    background-color: #DE4F3E !important; /* Highlight on hover */
    color: #fff !important;
}

/* Disabled Buttons (if applicable) */
.paginationgswz .disabled {
    color: #ccc;
    pointer-events: none;
    cursor: not-allowed;
}

.custom-logo-link {
    display: none !important;
}

</style>
<div class="gallery-container">
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
    <div class="gallery-grid">
        <?php if ($query->have_posts()) : while ($query->have_posts()) : $query->the_post(); ?>
            <a href="<?php the_permalink(); ?>" class="gallery-card">
                <div class="image-container" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://via.placeholder.com/300'; ?>');">
                </div>
                <h2 class="testbericht">
    <?php 
        // Get the car brand and car model from the database
        $car_brand = esc_html(get_post_meta(get_the_ID(), 'car_brand', true));
        $car_model = esc_html(get_post_meta(get_the_ID(), 'car_model', true));
        
        // Concatenate the car brand and car model with a space in between
        echo $car_brand . ' ' . $car_model;
    ?>
</h2>
</h2>                <div class="text-container">
                    <h3><?php the_title(); ?></h3>
                    <p class="more-info">Mehr lesen</p>
                </div>
            </a>
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
            <p>Keine Autos gefunden. Versuchen Sie, den Filter anzupassen.</p>
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