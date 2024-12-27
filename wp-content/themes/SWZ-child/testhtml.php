<?php
/*
Template Name: Full Width HTML Page
*/

get_header();

// Establish a database connection
global $wpdb;

// Fetch the HTML content from the database
$html_content = $wpdb->get_var("SELECT content FROM wp_html_pages WHERE id = 50");

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
        }
        .full-width-content {
            width: 100%;
            height: 100%;
            overflow: auto;
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
