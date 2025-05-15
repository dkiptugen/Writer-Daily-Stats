<?php

function wds_stats_page() {
    global $wpdb;

    $per_page = 20;
    $paged = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
    $offset = ($paged - 1) * $per_page;

    // Get total number of grouped rows
    $total_rows = $wpdb->get_var("
        SELECT COUNT(*) FROM (
            SELECT post_author, DATE(post_date) AS post_day
            FROM $wpdb->posts
            WHERE post_type = 'post' AND post_status = 'publish'
            GROUP BY post_author, post_day
        ) AS counted
    ");

    // Get paginated results
    $results = $wpdb->get_results($wpdb->prepare("
        SELECT 
            post_author, 
            DATE(post_date) AS post_day, 
            COUNT(ID) AS post_count
        FROM $wpdb->posts
        WHERE post_type = 'post' AND post_status = 'publish'
        GROUP BY post_author, post_day
        ORDER BY post_day DESC
        LIMIT %d OFFSET %d
    ", $per_page, $offset));

    $authors = [];
    foreach ($results as $row) {
        $author = get_the_author_meta('display_name', $row->post_author);
        $authors[] = [
            'author' => $author,
            'date'   => $row->post_day,
            'posts'  => $row->post_count,
            'views'  => wds_get_views_by_author_and_day($row->post_author, $row->post_day),
        ];
    }

    echo '<div class="wrap"><h1>Writer Daily Stats</h1>';
    echo '<table class="widefat fixed striped">';
    echo '<thead><tr><th>Author</th><th>Date</th><th>Posts</th><th>Views</th></tr></thead><tbody>';

    foreach ($authors as $row) {
        echo '<tr>';
        echo '<td>' . esc_html($row['author']) . '</td>';
        echo '<td>' . esc_html($row['date']) . '</td>';
        echo '<td>' . intval($row['posts']) . '</td>';
        echo '<td>' . intval($row['views']) . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';

    $total_pages = ceil($total_rows / $per_page);
    if ($total_pages > 1) {
        echo '<div class="tablenav"><div class="tablenav-pages">';
        for ($i = 1; $i <= $total_pages; $i++) {
            $class = $paged == $i ? ' class="current-page button"' : ' class="button"';
            $url = admin_url('admin.php?page=writer-stats&paged=' . $i);
            echo "<a href='$url'$class>$i</a> ";
        }
        echo '</div></div>';
    }

    echo '</div>';
}

function wds_get_views_by_author_and_day($author_id, $date) {
    global $wpdb;

    $results = $wpdb->get_var($wpdb->prepare("
        SELECT SUM(meta_value)
        FROM $wpdb->posts p
        JOIN $wpdb->postmeta pm ON p.ID = pm.post_id
        WHERE p.post_author = %d AND DATE(p.post_date) = %s AND pm.meta_key = 'views'
    ", $author_id, $date));

    return $results ? $results : 0;
}
	
