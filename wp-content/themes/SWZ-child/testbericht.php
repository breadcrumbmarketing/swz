<?php

function createPostFromDb(localhost, aaedwdrcxz, 4zzHJ93zcm, aaedwdrcxz, $table_name = 'wp_html_pages') {
    // Database connection
    $conn = new mysqli($db_host, $db_user, $db_password, $db_name);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query to fetch rows where post hasn't been created
    $query = "SELECT * FROM $table_name WHERE status = 'draft'";
    $result = $conn->query($query);

    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $id = $row['id'];
            $title = $row['title'];
            $slug = $row['slug'];
            $content = $row['content'];
            $image_url = $row['image']; // Image URL for hero/thumbnail

            // Check if "testReport" exists in the HTML content
            if (strpos($content, 'testReport') !== false) {
                // Extract the container with "testReport"
                $dom = new DOMDocument();
                @$dom->loadHTML($content);
                $xpath = new DOMXPath($dom);

                // Extract the "testReport" container
                $container = $xpath->query("//div[input[@value='testReport']]");

                if ($container->length > 0) {
                    $testReportHtml = $dom->saveHTML($container->item(0));

                    // Create post template with extracted content
                    $post_content = "
                    <div class='post-hero'>
                        <img src='$image_url' alt='$title' />
                    </div>
                    <div class='post-content'>
                        $testReportHtml
                    </div>
                    ";

                    // Insert post into WordPress (assuming wp_insert_post function is available)
                    $post_data = [
                        'post_title'   => $title,
                        'post_content' => $post_content,
                        'post_status'  => 'publish',
                        'post_type'    => 'post',
                        'post_name'    => $slug,
                    ];

                    $post_id = wp_insert_post($post_data);

                    // Set the featured image for the post
                    if ($post_id && !is_wp_error($post_id)) {
                        // Download image and attach it as featured image
                        $image_id = media_sideload_image($image_url, $post_id, null, 'id');
                        if (!is_wp_error($image_id)) {
                            set_post_thumbnail($post_id, $image_id);
                        }

                        // Update the status in the database to avoid duplicate posts
                        $update_query = "UPDATE $table_name SET status = 'published' WHERE id = $id";
                        $conn->query($update_query);
                    }
                }
            }
        }
    } else {
        echo "No new posts to create.";
    }

    $conn->close();
}
