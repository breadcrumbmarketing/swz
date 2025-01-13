
<?php
// Database connection
$servername = "localhost";
$username = "aaedwdrcxz";
$password = "4zzHJ93zcm";
$dbname = "aaedwdrcxz";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to extract testReport container
function extractTestReport($html) {
    $dom = new DOMDocument();
    @$dom->loadHTML($html); // Suppress warnings for invalid HTML
    $xpath = new DOMXPath($dom);

    // Find the div with the testReport container
    $nodes = $xpath->query('//div[input[@name="fieldname" and @value="testReport"]]');

    if ($nodes->length > 0) {
        $container = $dom->saveHTML($nodes->item(0));
        return $container;
    }
    return null;
}

// Function to check if a post exists
function postExists($conn, $id) {
    $stmt = $conn->prepare("SELECT id FROM wp_posts WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

// Function to create a post
function createPost($conn, $id, $title, $slug, $content, $image) {
    // Insert into wp_posts table
    $stmt = $conn->prepare("INSERT INTO wp_posts (id, post_title, post_name, post_content, post_status, post_date, post_date_gmt, post_modified, post_modified_gmt) VALUES (?, ?, ?, ?, 'publish', NOW(), NOW(), NOW(), NOW())");
    $stmt->bind_param("isss", $id, $title, $slug, $content);

    if ($stmt->execute()) {
        // Insert the image as a featured image (thumbnail) in wp_postmeta
        $meta_key = '_thumbnail_id';
        $meta_value = $image; // Assuming the image column contains the attachment ID or URL
        $stmt_meta = $conn->prepare("INSERT INTO wp_postmeta (post_id, meta_key, meta_value) VALUES (?, ?, ?)");
        $stmt_meta->bind_param("iss", $id, $meta_key, $meta_value);
        $stmt_meta->execute();
        return true;
    }
    return false;
}

// Fetch rows from the wp_html_pages table
$sql = "SELECT id, title, slug, content, image FROM wp_html_pages WHERE content LIKE '%testReport%'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $id = $row['id'];
        $title = $row['title'];
        $slug = $row['slug'];
        $content = $row['content'];
        $image = $row['image'];

        // Extract the testReport container
        $testReportContent = extractTestReport($content);

        if ($testReportContent && !postExists($conn, $id)) {
            // Create the post
            if (createPost($conn, $id, $title, $slug, $testReportContent, $image)) {
                echo "Post created for ID: $id\n";
            } else {
                echo "Failed to create post for ID: $id\n";
            }
        } else {
            echo "Post already exists or no testReport found for ID: $id\n";
        }
    }
} else {
    echo "No rows found with testReport content.\n";
}

$conn->close();
?>