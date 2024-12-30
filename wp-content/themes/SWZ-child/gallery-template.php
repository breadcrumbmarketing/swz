<?php
/**
 * Template Name: Car Gallery
 * Template Post Type: page
 */

get_header(); // Load the WordPress header

// List of sport car brands and models (expandable)
$sport_car_brands = array(
    'Porsche' => array('Taycan', '911', 'Panamera'),
    'Ferrari' => array('Roma', '488', 'SF90'),
    'Lamborghini' => array('Huracan', 'Aventador', 'Urus'),
    'BMW' => array('i8', 'M4', 'Z4'),
    'Mercedes' => array('AMG GT', 'SLS', 'C63'),
);

// Get the selected brand and model from the dropdown filter
$selected_brand = isset($_GET['brand']) ? sanitize_text_field($_GET['brand']) : '';
$selected_model = isset($_GET['model']) ? sanitize_text_field($_GET['model']) : '';

// Query for pages created with the carpage.php template
$args = array(
    'post_type' => 'page',
    'meta_query' => array(
        array(
            'key' => '_wp_page_template',
            'value' => 'carpage.php', // Filter pages created with carpage.php template
        ),
    ),
    'posts_per_page' => -1, // Get all matching pages
);

$query = new WP_Query($args);

?>
<style>
    .gallery-container {
        padding: 20px;
        max-width: 1200px; /* Limit the width of the gallery */
        margin: 0 auto; /* Center the gallery on the page */
        text-align: center;
    }
    .filter-bar {
        margin-bottom: 20px;
    }
    .filter-bar form {
        display: flex;
        justify-content: center;
        gap: 10px;
    }
    .filter-bar select {
        padding: 8px;
        font-size: 16px;
        width: 200px;
    }
    .filter-bar button {
        padding: 8px 16px;
        font-size: 16px;
    }
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); /* Minimum card size is 200px */
        gap: 10px; /* Smaller gap between cards */
        justify-items: center;
    }
    .gallery-card {
        background-size: cover;
        background-position: center;
        color: #fff;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 0;
        padding-bottom: 100%; /* Ensures the cards remain square */
        border-radius: 10px;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        transition: transform 0.3s ease;
    }
    .gallery-card:hover {
        transform: scale(1.05);
    }
    .gallery-card h3 {
        margin: 0;
        background: rgba(0, 0, 0, 0.6);
        padding: 10px;
        border-radius: 5px;
    }
    @media (max-width: 768px) {
        .gallery-grid {
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); /* Smaller cards for mobile */
        }
    }
</style>

<div class="gallery-container">
    <div class="filter-bar">
        <form method="GET">
            <select name="brand">
                <option value=""><?php esc_html_e('Marke auswählen', 'text-domain'); ?></option>
                <?php foreach ($sport_car_brands as $brand => $models) : ?>
                    <option value="<?php echo esc_attr($brand); ?>" <?php selected($selected_brand, $brand); ?>>
                        <?php echo esc_html($brand); ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <select name="model">
                <option value=""><?php esc_html_e('Modell auswählen', 'text-domain'); ?></option>
                <?php
                if ($selected_brand && isset($sport_car_brands[$selected_brand])) :
                    foreach ($sport_car_brands[$selected_brand] as $model) :
                ?>
                        <option value="<?php echo esc_attr($model); ?>" <?php selected($selected_model, $model); ?>>
                            <?php echo esc_html($model); ?>
                        </option>
                <?php
                    endforeach;
                endif;
                ?>
            </select>
            <button type="submit"><?php esc_html_e('Filtern', 'text-domain'); ?></button>
        </form>
    </div>

    <div class="gallery-grid">
        <?php if ($query->have_posts()) : ?>
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                // Get the featured image as background
                $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://via.placeholder.com/300';

                // Count occurrences of brand and model in the page content
                $post_content = strtolower(strip_tags(get_the_content()));
                $brand_count = $selected_brand ? substr_count($post_content, strtolower($selected_brand)) : 0;
                $model_count = $selected_model ? substr_count($post_content, strtolower($selected_model)) : 0;

                // Only display the card if the brand or model occurs more than 10 times
                if (
                    (!$selected_brand || $brand_count > 10) &&
                    (!$selected_model || $model_count > 10)
                ) :
                ?>
                    <a href="<?php the_permalink(); ?>" class="gallery-card" style="background-image: url('<?php echo esc_url($featured_image); ?>');">
                        <h3><?php the_title(); ?></h3>
                    </a>
                <?php endif; ?>
            <?php endwhile; ?>
        <?php else : ?>
            <p><?php esc_html_e('Keine Autos gefunden. Versuchen Sie, den Filter anzupassen.', 'text-domain'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php
wp_reset_postdata(); // Reset the query
get_footer(); // Load the WordPress footer
?>
