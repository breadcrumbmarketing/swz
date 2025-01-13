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
    'post_type'      => 'post', // Change to "post" for Testberichte
    'posts_per_page' => 12, // Display 12 posts per page
    'paged'          => $current_page,
    'category_name'  => 'Testbericht', // Only show posts from the "Testbericht" category
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
$all_posts = get_posts(array(
    'post_type'      => 'post',
    'posts_per_page' => -1,
    'category_name'  => 'Testbericht', // Fetch only posts from the "Testbericht" category
));

$brands = [];
$models = [];

foreach ($all_posts as $post) {
    $brand = get_post_meta($post->ID, 'car_brand', true);
    $model = get_post_meta($post->ID, 'car_model', true);
    $brands[$brand] = $brand;
    if ($selected_brand && $brand === $selected_brand) {
        $models[$model] = $model;
    }
}

asort($brands); // Sort brands alphabetically
?>

<style>
/* Add your existing CSS styles from your gallery */
</style>

<div class="gallery-container">
    <!-- Filter Bar -->
    <div class="filter-bar">
        <form method="GET" id="filter-form">
            <!-- Dropdown for Car Brand -->
            <select name="brand" onchange="this.form.submit()">
                <option value="">Marke auswählen</option>
                <?php foreach ($brands as $brand): ?>
                    <option value="<?php echo esc_attr($brand); ?>" <?php selected($selected_brand, $brand); ?>>
                        <?php echo esc_html($brand); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <!-- Dropdown for Car Model -->
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
            <a href="<?php the_permalink(); ?>" class="gallery-card">
                <!-- Image Container -->
                <div class="image-container" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://via.placeholder.com/300'; ?>');">
                </div>
                <!-- Car Title -->
                <h2 class="testbericht">
                    <?php 
                        $car_brand = esc_html(get_post_meta(get_the_ID(), 'car_brand', true));
                        $car_model = esc_html(get_post_meta(get_the_ID(), 'car_model', true));
                        echo $car_brand . ' ' . $car_model;
                    ?>
                </h2>
                <!-- Post Title -->
                <div class="text-container">
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
            <p>Keine Testberichte gefunden. Versuchen Sie, den Filter anzupassen.</p>
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
