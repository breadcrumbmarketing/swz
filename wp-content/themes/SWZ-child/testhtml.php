<?php
/*
Template Name: Full Width HTML Page
*/

get_header();

// Establish a database connection
global $wpdb;

// Get the slug from the current URL
$current_slug = get_query_var('pagename');

// Fetch the HTML content from the database using the slug
$html_content = $wpdb->get_var($wpdb->prepare(
    "SELECT content FROM wp_html_pages WHERE slug = %s AND status = 'published'",
    $current_slug
));

// Check if content exists for the given slug
if (!$html_content) {
    $html_content = "<h1>Content not found</h1>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Width HTML Page</title>
    <style>
        html, body {
            margin: 0;
            padding: 0;
            width: 100%;
            height: 100%;
            background-color: white !important; 
        }
        .full-width-content {
    width: 100%;
    height: 100%;
    overflow-x: hidden !important; /* Hide horizontal scrolling */
    overflow-y: auto !important; /* Allow vertical scrolling */
    background: linear-gradient(180deg, #ffffff,rgb(188, 226, 245)) !important; /* Minimal white gradient */
    margin: 0 !important; /* Ensure no margins that cause overflow */
    padding: 0 !important; /* Ensure no padding that causes overflow */
    box-sizing: border-box !important; /* Include padding and borders in the width */
}

        .aa-col-12, .aa-m-col-12 {
    width: 100% !important; /* Ensure full width */
    padding: 10px !important; /* Add spacing */
    box-sizing: border-box !important; /* Include padding and borders in width */
}

/* Media query for smaller screens (e.g., mobile) */
@media (max-width: 768px) {
    .aa-col-12, .aa-m-col-12 {
        width: 100% !important; /* Maintain full width on mobile */
        padding: 8px !important; /* Reduce padding */
        margin-bottom: 15px !important; /* Add spacing between sections */
    }

    /* Adjust accordion sections for smaller screens */
    .accordion-section {
        margin: 0 auto !important; /* Center content on mobile */
        padding: 10px !important; /* Add some spacing */
    }
}


@media (max-width: 768px) {
    .accordion-tab-title-wrapper{
        width: 100% !important; /* Maintain full width on mobile */
        padding: 8px !important; /* Reduce padding */
        margin-bottom: 15px !important; /* Add spacing between sections */
    }

    /* Adjust accordion sections for smaller screens */
    .accordion-tab-title-wrapper {
        margin: 0 auto !important; /* Center content on mobile */
        padding: 10px !important; /* Add some spacing */
    }
}


    </style>
</head>
<body>
    <div class="full-width-content">
        <?php echo $html_content; ?>
    </div>
</body>
</html>
<?php
get_footer();
