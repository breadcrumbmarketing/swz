<?php
/**
 * Template Name: Car Gallery
 * Template Post Type: page
 */

get_header(); // Load the WordPress header

// Get the filter values from the query string
$search_brand = isset($_GET['brand']) ? sanitize_text_field($_GET['brand']) : '';
$search_model = isset($_GET['model']) ? sanitize_text_field($_GET['model']) : '';

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
    's' => $search_brand . ' ' . $search_model, // Search in title (brand and model are included in the title)
);

$query = new WP_Query($args);
?>

<style>
    .gallery-container {
        padding: 20px;
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
    .filter-bar input {
        padding: 8px;
        font-size: 16px;
        width: 200px;
    }
    .gallery-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
        gap: 20px;
    }
    .gallery-card {
        background-size: cover;
        background-position: center;
        color: #fff;
        padding: 20px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        height: 200px;
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
</style>

<div class="gallery-container">
    <div class="filter-bar">
        <form method="GET">
            <input type="text" name="brand" placeholder="Search by Brand" value="<?php echo esc_attr($search_brand); ?>" />
            <input type="text" name="model" placeholder="Search by Model" value="<?php echo esc_attr($search_model); ?>" />
            <button type="submit">Search</button>
        </form>
    </div>

    <div class="gallery-grid">
        <?php if ($query->have_posts()) : ?>
            <?php while ($query->have_posts()) : $query->the_post(); ?>
                <?php
                // Get the featured image as background
                $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://via.placeholder.com/300';

                // Extract brand and model from the title
                $title_parts = explode('–', get_the_title());
                $brand_model = isset($title_parts[0]) ? $title_parts[0] : get_the_title();
                ?>
                <a href="<?php the_permalink(); ?>" class="gallery-card" style="background-image: url('<?php echo esc_url($featured_image); ?>');">
                    <h3><?php echo esc_html($brand_model); ?></h3>
                </a>
            <?php endwhile; ?>
        <?php else : ?>
            <p>No cars found. Try adjusting the filter.</p>
        <?php endif; ?>
    </div>
</div>

<?php
wp_reset_postdata(); // Reset the query
get_footer(); // Load the WordPress footer
?>
