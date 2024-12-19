<?php
// inc/newsletter/admin/dashboard.php

// Security check
if (!defined('ABSPATH')) exit;

global $wpdb;
$table_name = $wpdb->prefix . 'newsletter_subscribers';

// Handle bulk actions
if (isset($_POST['action']) && $_POST['action'] === 'delete' && isset($_POST['subscribers'])) {
    $ids = array_map('intval', $_POST['subscribers']);
    $wpdb->query("DELETE FROM $table_name WHERE id IN (" . implode(',', $ids) . ")");
}

// Handle CSV export
if (isset($_POST['export_csv'])) {
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="newsletter_subscribers.csv"');
    $output = fopen('php://output', 'w');
    fputcsv($output, array('Email', 'IP Address', 'Location', 'Date Subscribed'));

    $rows = $wpdb->get_results("SELECT email, ip_address, location, date_subscribed FROM $table_name", ARRAY_A);
    foreach ($rows as $row) {
        fputcsv($output, $row);
    }
    fclose($output);
    exit;
}

// Get subscribers with pagination
$page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
$items_per_page = 20;
$offset = ($page - 1) * $items_per_page;

$total_items = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
$total_pages = ceil($total_items / $items_per_page);

$subscribers = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $table_name ORDER BY date_subscribed DESC LIMIT %d OFFSET %d",
        $items_per_page,
        $offset
    )
);

$week_count = $wpdb->get_var(
    "SELECT COUNT(*) FROM $table_name WHERE date_subscribed >= DATE_SUB(CURRENT_DATE(), INTERVAL DAYOFWEEK(CURRENT_DATE())-1 DAY)"
);
?>

<div class="wrap">
    <h1>Newsletter Subscribers</h1>
    
    <div class="newsletter-stats">
        <div class="stat-card">
            <h3>Total Subscribers</h3>
            <p><?php echo $total_items; ?></p>
        </div>
        <div class="stat-card">
            <h3>This Month</h3>
            <p><?php echo $wpdb->get_var("SELECT COUNT(*) FROM $table_name WHERE MONTH(date_subscribed) = MONTH(CURRENT_DATE())"); ?></p>
        </div>
        <div class="stat-card">
            <h3>This Week</h3>
            <p><?php echo $week_count; ?></p>
        </div>
    </div>

    <!-- Export CSV Button -->
    <form method="post">
        <input type="submit" name="export_csv" class="button button-primary" value="Export CSV">
    </form>

    <!-- Subscriber List Form -->
    <form method="post">
        <div class="tablenav top">
            <div class="alignleft actions bulkactions">
                <select name="action">
                    <option value="-1">Bulk Actions</option>
                    <option value="delete">Delete</option>
                </select>
                <input type="submit" class="button action" value="Apply">
            </div>
            <div class="tablenav-pages">
                <?php echo paginate_links(array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => __('&laquo;'),
                    'next_text' => __('&raquo;'),
                    'total' => $total_pages,
                    'current' => $page
                )); ?>
            </div>
        </div>

        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <td class="manage-column column-cb check-column">
                        <input type="checkbox" />
                    </td>
                    <th>Email</th>
                    <th>IP Address</th>
                    <th>Location</th>
                    <th>Date Subscribed</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($subscribers as $subscriber): ?>
                <tr>
                    <th scope="row" class="check-column">
                        <input type="checkbox" name="subscribers[]" value="<?php echo $subscriber->id; ?>" />
                    </th>
                    <td><?php echo esc_html($subscriber->email); ?></td>
                    <td><?php echo esc_html($subscriber->ip_address); ?></td>
                    <td><?php echo esc_html($subscriber->location); ?></td>
                    <td><?php echo esc_html($subscriber->date_subscribed); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </form>
</div>

<style>
.newsletter-stats {
    display: flex;
    gap: 20px;
    margin: 20px 0;
}

.stat-card {
    background: #fff;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    min-width: 200px;
}

.stat-card h3 {
    margin: 0 0 10px 0;
    color: #23282d;
}

.stat-card p {
    font-size: 24px;
    margin: 0;
    color: #2271b1;
    font-weight: bold;
}
</style>
