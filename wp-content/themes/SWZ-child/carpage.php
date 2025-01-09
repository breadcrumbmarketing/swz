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
    "SELECT content FROM wp_html_pages WHERE slug = %s AND (status = 'published' OR status = 'draft')",
    $current_slug
));


// Check if content exists for the given slug
if (!$html_content) {
    $html_content = "<h1>Content not found</h1>";
}
?>
<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Full Width HTML Page</title>
    <style>
/* Ensure no gap between HTML, header, and footer */
html, body {
    margin: 0 !important;
    padding: 0 !important;
    width: 100% !important;
    height: 100% !important;
    background-color: white !important;
}

/* Ensure the full-width-content fills the entire viewport */
.full-width-content {
    width: 100% !important;
    overflow-x: hidden !important; /* Hide horizontal scrolling */
    overflow-y: auto !important; /* Allow vertical scrolling */
    background: linear-gradient(180deg, #ffffff, rgb(238, 247, 252)) !important; /* Minimal white gradient */
    margin: 0 !important;
    padding: 0 !important;
    box-sizing: border-box !important;
    margin-bottom: 0;
    padding-bottom: 0;
    padding-top: -10px !important;
}

/* Ensure header and footer have no margin or padding */
header, footer {
    margin: 0 !important;
    padding: 0 !important;
}

/* General styles for the accordion */
.accordion {
    margin: 0 auto !important;
    padding: 10px !important;
    width: 100% !important;
    max-width: 1200px !important; /* Limit the max width on large screens */
    box-sizing: border-box !important;
}

.accordion-header {
    font-size: 1rem !important;
    padding: 10px !important;
}

.accordion-button {
    display: flex !important;
    justify-content: space-between !important;
    align-items: center !important;
    width: 100% !important;
    font-size: 1rem !important;
    padding: 12px !important;
    background-color: #f8f9fa !important;
    border: none !important;
    box-shadow: none !important;
    transition: background-color 0.2s !important;
}

.accordion-button:hover {
    background-color: #eaeaea !important;
}

.accordion-button:focus {
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25) !important;
}

.accordion-button.collapsed {
    background-color: #ffffff !important;
    color: #333 !important;
}

.accordion-body {
    padding: 15px !important;
    font-size: 0.9rem !important;
    line-height: 1.5 !important;
    background-color: #f9f9f9 !important;
}

/* Table responsiveness inside accordion */
.table-section {
    overflow-x: auto !important;
    margin: 0 !important;
}

.table-section table {
    width: 100% !important;
    border-collapse: collapse !important;
    font-size: 0.85rem !important;
}

.table-section th,
.table-section td {
    text-align: left !important;
    padding: 8px !important;
    border: 1px solid #ddd !important;
}

.table-section th {
    background-color: #f1f1f1 !important;
    font-weight: bold !important;
}

.custom-prices {
font-size: 30px !important ; 
background-color: transparent !important ; 
}

.footer_bottom {

    display: none !important;

}
/* Mobile responsiveness */
@media (max-width: 768px) {
    .accordion-header {
        font-size: 0.9rem !important;
    }

    .accordion-button {
        font-size: 0.85rem !important;
        padding: 10px !important;
        flex-direction: column !important;
        align-items: flex-start !important;
    }

    .accordion-tab-title-wrapper {
    word-wrap: break-word !important; /* Allow long words to break and wrap */
    word-break: break-word !important; /* Ensure breaking behavior */
    white-space: normal !important; /* Allow wrapping of text to the next line */
    display: block !important; /* Ensure it behaves like a block element */
    overflow-wrap: break-word !important; /* Prevent overflow on small screens */
    width: 100% !important; /* Ensure the content fits within its container */
    text-align: left !important; /* Align text to the left */
}

    .table-section table {
        font-size: 0.75rem !important;
    }

    .table-section th,
    .table-section td {
        padding: 6px !important;
    }
}

/* Very small screens (e.g., phones) */
@media (max-width: 768px) {
    .accordion-button {
        font-size: 0.8rem !important;
        padding: 8px !important;
    }

    .accordion-body {
        font-size: 0.8rem !important;
    }

    .table-section th,
    .table-section td {
        font-size: 0.7rem !important;
        padding: 5px !important;
    }
    .accordion-tab-title-wrapper {
        font-size: 0.85rem !important; /* Adjust font size for mobile */
    }
}
.back-button {
    display: block;
    width: 200px;
    margin: 20px auto;
    padding: 10px 20px;
    font-size: 16px;
    text-align: center;
    background-color: #DE4F3E; /* Solid background color */
    color: white;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: background-color 0.3s, color 0.3s; /* Smooth transition for hover effects */
}

.back-button:hover {
    background-color: transparent; /* Transparent background on hover */
    color: #DE4F3E; /* Orange text color on hover */
    border: 2px solid#DE4F3E; /* Solid border with orange color on hover */
}

footer {
            position: relative;
        }

.elementor-1319.elementor-element.elementor-element-379a34c.elementor-widget-container  {
    
color: white !important;
}




</style>
</head>
<body>
    <div class="full-width-content">
        <?php echo $html_content; ?>
        <button onclick="window.history.back();" class="back-button">Zurück</button>
    </div>
   
</body>
</html>
<?php
get_footer();
