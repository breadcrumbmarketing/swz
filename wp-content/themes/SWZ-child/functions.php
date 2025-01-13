<?php
/**
 * Theme functions and definitions.
 *
 * For additional information on potential customization options,
 * read the developers' documentation:
 *
 * https://developers.elementor.com/docs/hello-elementor-theme/
 *
 * @package HelloElementorChild
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define( 'HELLO_ELEMENTOR_CHILD_VERSION', '2.0.0' );

/**
 * Load child theme scripts & styles.
 *
 * @return void
 */
function hello_elementor_child_scripts_styles() {

	wp_enqueue_style(
		'hello-elementor-child-style',
		get_stylesheet_directory_uri() . '/style.css',
		[
			'hello-elementor-theme-style',
		],
		HELLO_ELEMENTOR_CHILD_VERSION
	);

}
add_action( 'wp_enqueue_scripts', 'hello_elementor_child_scripts_styles', 20 );


/** SEO */
function swz_add_seo_meta_tags() {
    ?>
    <!-- SEO Meta Tags -->
    <meta name="description" content="Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sed urna in justo euismod condimentum.">
    <meta name="Breadcrumb" content="Sportwagen Zentrum">

    <!-- Open Graph / Facebook Meta Tags for rich previews in social sharing -->
    <meta property="og:title" content="<?php echo get_the_title(); ?> | Lorem Ipsum ">
    <meta property="og:description" content="<?php echo get_the_excerpt() ? get_the_excerpt() : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sed urna in justo euismod condimentum.'; ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo get_permalink(); ?>">
    <meta property="og:image" content="<?php echo get_stylesheet_directory_uri(); ?>/screenshot.png">
    <meta property="og:site_name" content="Sportwagen Zentrum">
    <meta property="og:locale" content="de_DE">

    <!-- Twitter Card Meta Tags -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo get_the_title(); ?>">
    <meta name="twitter:description" content="<?php echo is_single() || is_page() ? get_the_excerpt() : 'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Suspendisse sed urna in justo euismod condimentum.'; ?>">
    <meta name="twitter:image" content="<?php echo get_stylesheet_directory_uri(); ?>/path/to/your/default-twitter-image.jpg">
    <meta name="twitter:site" content="@YourTwitterHandle">
    <meta name="twitter:creator" content="@YourTwitterHandle">

    <!-- Favicons -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/favicon-16x16.png">
    <link rel="manifest" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/site.webmanifest">
    <link rel="shortcut icon" href="<?php echo get_stylesheet_directory_uri(); ?>/favicon/favicon.ico">
    <meta name="theme-color" content="#ffffff">
    <?php
}
add_action('wp_head', 'swz_add_seo_meta_tags');



// -------------------------------- Remote API -------------------------------- //

// Register the custom REST API endpoint
function register_html_pages_endpoint() {
    register_rest_route('myapi/v1', '/write_html_page/', array(
        'methods' => 'POST',
        'callback' => 'handle_write_html_page',
        'permission_callback' => 'check_api_key_permission', // Permission check function
    ));
}
add_action('rest_api_init', 'register_html_pages_endpoint');

// Callback function to handle the insertion of data into the wp_html_pages table
function handle_write_html_page($data) {
    global $wpdb;

    // Sanitize input data
    $title = sanitize_text_field($data['title']);
    $slug = sanitize_title($data['slug']);
    $content = $data['content'];
    $car_brand = sanitize_text_field($data['car_brand']);
    $car_model = sanitize_text_field($data['car_model']);
    $price = sanitize_text_field($data['price']);
    $co2 = sanitize_text_field($data['co2']);
    $power = sanitize_text_field($data['power']);
    $image = esc_url_raw($data['image']);
    $testbericht = $data ['testbericht'];

    // Check if the slug already exists in the table
    $existing_page = $wpdb->get_var($wpdb->prepare(
        "SELECT id FROM {$wpdb->prefix}html_pages WHERE slug = %s",
        $slug
    ));

    if ($existing_page) {
        return new WP_REST_Response('Dieses Fahrzeug wurde bereits zu Ihrer Website hinzugefügt.', 400);
    }

    // Insert new page into the wp_html_pages table
    $insert = $wpdb->insert(
        "{$wpdb->prefix}html_pages",
        array(
            'title'     => $title,
            'slug'      => $slug,
            'content'   => $content,
            'car_brand' => $car_brand,
            'car_model' => $car_model,
            'price'     => $price,
            'co2'       => $co2,
            'power'     => $power,
            'image'     => $image,
            'testbericht'   => $testbericht,
        )
    );

    if ($insert) {
        // Trigger dynamic page creation immediately
        create_html_pages_from_database();
        return new WP_REST_Response('HTML-Seite wurde zu Ihrer Website hinzugefügt und erfolgreich erstellt.', 200);
    } else {
        return new WP_REST_Response('Das Einfügen der HTML-Seite in Ihre Website ist fehlgeschlagen.', 400);
    }
}

// Function to check API key permission
function check_api_key_permission($request) {
    $api_key = $request->get_header('API-Key');
    if ($api_key === 'swz_aschaffenburg_breadcrumb_hamy') { 
        return true;
    }
    return new WP_REST_Response('Unauthorized', 401);
}
// -------------------------------- Dynamic Page Creation -------------------------------- //

if (!function_exists('upload_image_to_media_library')) {
    function upload_image_to_media_library($image_url) {
        if (!function_exists('media_handle_sideload')) {
            require_once(ABSPATH . 'wp-admin/includes/file.php');
            require_once(ABSPATH . 'wp-admin/includes/media.php');
            require_once(ABSPATH . 'wp-admin/includes/image.php');
        }

        $temp_file = download_url($image_url);
        if (is_wp_error($temp_file)) {
            return false;
        }

        $file = array(
            'name'     => basename($image_url),
            'type'     => mime_content_type($temp_file),
            'tmp_name' => $temp_file,
            'error'    => 0,
            'size'     => filesize($temp_file),
        );

        $attachment_id = media_handle_sideload($file, 0);
        @unlink($temp_file);

        if (is_wp_error($attachment_id)) {
            return false;
        }

        return $attachment_id;
    }
}
if (!function_exists('create_html_pages_from_database')) {
    function create_html_pages_from_database() {
        global $wpdb;

        // Fetch all rows from the wp_html_pages table
        $rows = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}html_pages");

        foreach ($rows as $row) {
            // Check if a WordPress page already exists with the slug
            $existing_page = get_page_by_path($row->slug, OBJECT, 'page');

            if (!$existing_page) {
                $post_title = !empty($row->title) ? $row->title : 'Untitled Page';
                $post_content = !empty($row->content) ? $row->content : 'No content available.';
                $post_slug = !empty($row->slug) ? $row->slug : uniqid('page-');

                $page_id = wp_insert_post(array(
                    'post_title'   => $post_title,
                    'post_name'    => $post_slug,
                    'post_content' => $post_content,
                    'post_status'  => 'publish',
                    'post_type'    => 'page',
                ));

                if (!is_wp_error($page_id)) {
                    // Use the "image" column to set the featured image
                    if (!empty($row->image) && filter_var($row->image, FILTER_VALIDATE_URL)) {
                        $attachment_id = upload_image_to_media_library($row->image);
                        if ($attachment_id) {
                            set_post_thumbnail($page_id, $attachment_id);
                        }
                    }

                    // Save car details as post meta
                    update_post_meta($page_id, 'car_brand', $row->car_brand);
                    update_post_meta($page_id, 'car_model', $row->car_model);
                    update_post_meta($page_id, 'price', $row->price);
                    update_post_meta($page_id, 'co2', $row->co2);
                    update_post_meta($page_id, 'power', $row->power);
                    update_post_meta($page_id, 'created_at', $row->created_at);

                    // Assign the "carpage.php" template to the new page
                    update_post_meta($page_id, '_wp_page_template', 'carpage.php');
                }
            }
        }
    }
}

// Immediate execution to process new and unprocessed rows
add_action('init', 'create_html_pages_from_database');

// -------------------------------- Dynamic Post Creation -------------------------------- //
function create_post_from_testbericht_column() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'html_pages'; // Custom table name
    $row_id = 100; // ID of the row to fetch for testing

    // Include WordPress media functions
    if (!function_exists('media_sideload_image')) {
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/media.php';
        require_once ABSPATH . 'wp-admin/includes/image.php';
    }

    // Fetch the row with ID 100
    $row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE id = %d", $row_id));

    if ($row) {
        $title = $row->title;
        $slug = $row->slug;
        $testbericht_content = $row->testbericht; // Fetch content from the new 'testbericht' column
        $image_url = $row->image;

        // Check if the 'testbericht' column has content
        if (!empty($testbericht_content)) {
            // Build the post content using the 'testbericht' data
            $post_content = "
            <div class='post-hero'>
                <img src='$image_url' alt='$title' />
            </div>
            <div class='post-content'>
                $testbericht_content
            </div>
            ";

            // Check if a post with this slug already exists
            $existing_post = get_page_by_path($slug, OBJECT, 'post');
            if (!$existing_post) {
                // Insert the post
                $post_data = [
                    'post_title'   => $title,
                    'post_content' => $post_content,
                    'post_status'  => 'publish',
                    'post_type'    => 'post',
                    'post_name'    => $slug,
                    'meta_input'   => ['_wp_page_template' => 'testbericht.php'], // Assign the custom template
                ];

                $post_id = wp_insert_post($post_data);

                if (!is_wp_error($post_id)) {
                    // Download and set the featured image
                    $image_id = media_sideload_image($image_url, $post_id, null, 'id');
                    if (!is_wp_error($image_id)) {
                        set_post_thumbnail($post_id, $image_id);
                        error_log("Post created successfully with ID $post_id and featured image.");
                    } else {
                        error_log('Error in media_sideload_image: ' . $image_id->get_error_message());
                    }
                } else {
                    error_log('Error in wp_insert_post: ' . $post_id->get_error_message());
                }
            } else {
                error_log("Post with slug '$slug' already exists.");
            }
        } else {
            error_log("The 'testbericht' column is empty for row ID $row_id.");
        }
    } else {
        error_log("No row found with ID $row_id.");
    }
}


add_action('init', 'create_post_from_html_pages');

// -------------------------------- Admin Dashboard Button for post testbericht -------------------------------- //

add_action('init', function () {
    if (is_admin() && current_user_can('manage_options')) { // Restrict execution to admin users
        test_create_single_post();
    }
});


// -------------------------------- Admin Dashboard Button -------------------------------- //

function add_create_pages_menu() {
    add_menu_page(
        'Create Pages from Database',
        'Create Pages',
        'manage_options',
        'create-pages-from-db',
        'create_pages_from_db_callback',
        'dashicons-welcome-add-page',
        20
    );
}
add_action('admin_menu', 'add_create_pages_menu');

function create_pages_from_db_callback() {
    if (isset($_POST['create_pages'])) {
        create_html_pages_from_database();
        echo '<div class="notice notice-success"><p>Pages have been created successfully from the database.</p></div>';
    }

    ?>
    <div class="wrap">
        <h1>Create Pages from Database</h1>
        <form method="post">
            <p>Click the button below to create pages for all database rows that do not yet have a corresponding WordPress page.</p>
            <button type="submit" name="create_pages" class="button button-primary">Create Pages</button>
        </form>
    </div>
    <?php
}




// ------------------------- for home page recently viewd cars ......... //
function enqueue_slick_slider() {
    // Path to the Slick Carousel CSS and JS files in your theme or use a CDN
    wp_enqueue_style('slick-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.css');
    wp_enqueue_style('slick-theme-css', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick-theme.css');
    wp_enqueue_script('slick-js', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_slick_slider');

function recently_viewed_cars_shortcode() {
    ob_start();
    ?>

    <div class="recently-viewed-slider">
        <?php
        $args = array(
            'post_type' => 'page',
            'meta_query' => array(
                array(
                    'key' => '_wp_page_template',
                    'value' => 'carpage.php',
                ),
            ),
            'posts_per_page' => 10,
        );
        $query = new WP_Query($args);
        if ($query->have_posts()) :
            while ($query->have_posts()) : $query->the_post();
                $image_url = get_the_post_thumbnail_url(get_the_ID(), 'large') ?: 'https://via.placeholder.com/300x150';
                ?>
               <div class="slide">
    <div class="image-container" style="background-image: url('<?php echo esc_url($image_url); ?>');"></div>
    <h3><?php the_title(); ?></h3>
    <a href="<?php the_permalink(); ?>" class="more-link">Mehr lesen</a>
</div>
                <?php
            endwhile;
        endif;
        wp_reset_postdata();
        ?>
    </div>
    <script type="text/javascript">
jQuery(document).ready(function($) {
    var $slider = $('.recently-viewed-slider');

    $slider.slick({
        autoplay: true,
        autoplaySpeed: 2000,
        arrows: false, // Disable default arrows
        infinite: true,
        slidesToShow: 3,
        slidesToScroll: 1,
        responsive: [
            {
                breakpoint: 768,
                settings: {
                    slidesToShow: 1,
                    slidesToScroll: 1
                }
            }
        ]
    });

    // Custom arrow functionality
    $('.slick-prev').click(function() {
        $slider.slick('slickPrev');
    });

    $('.slick-next').click(function() {
        $slider.slick('slickNext');
    });
});



</script>

    <?php
    return ob_get_clean();
}
add_shortcode('recently_viewed_cars', 'recently_viewed_cars_shortcode');



////// for custome body of templates

function add_body_class_to_elementor() {
    // Check if Elementor is active
    if (defined('ELEMENTOR_VERSION')) {
        // Add body_class() to the body tag
        add_filter('body_class', function($classes) {
            return $classes;
        });
    }
}
add_action('init', 'add_body_class_to_elementor');

function custom_body_class_for_gallery_template($classes) {
    // Check if the current page is using the 'gallery-template.php' template
    if (is_page_template('gallery-template.php')) {
        $classes[] = 'gallery-template-page'; // Add a custom class
    }
    return $classes;
}
add_filter('body_class', 'custom_body_class_for_gallery_template');