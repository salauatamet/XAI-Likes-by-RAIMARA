<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class XAILikesDB {
    private static $db_version = '1.0';

    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'xai_likes';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
            post_id BIGINT(20) UNSIGNED NOT NULL,
            ip_address VARCHAR(100) NOT NULL,
            like_time DATETIME NOT NULL,
            PRIMARY KEY (id),
            KEY post_id (post_id)
        ) $charset_collate;";

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta($sql);

        if (!empty($wpdb->last_error)) {
            error_log('XAI Likes DB Error: ' . $wpdb->last_error);
            throw new Exception('Failed to create table: ' . $wpdb->last_error);
        }

        // Store DB version
        update_option('xai_likes_db_version', self::$db_version);
    }

    public static function add_like($post_id, $ip) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'xai_likes';
        
        $result = $wpdb->insert(
            $table_name,
            [
                'post_id' => $post_id,
                'ip_address' => $ip,
                'like_time' => current_time('mysql')
            ],
            ['%d', '%s', '%s']
        );

        if ($result === false) {
            error_log('XAI Likes DB Error: ' . $wpdb->last_error);
            throw new Exception('Failed to add like: ' . $wpdb->last_error);
        }

        return $result;
    }

    public static function can_user_like($post_id, $ip) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'xai_likes';
        
        $week_ago = date('Y-m-d H:i:s', strtotime('-7 days'));
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE post_id = %d AND ip_address = %s AND like_time > %s",
            $post_id,
            $ip,
            $week_ago
        ));

        if ($wpdb->last_error) {
            error_log('XAI Likes DB Error: ' . $wpdb->last_error);
            throw new Exception('Database error checking like: ' . $wpdb->last_error);
        }

        return $count == 0;
    }

    public static function get_like_count($post_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'xai_likes';
        
        $count = $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM $table_name WHERE post_id = %d",
            $post_id
        ));

        if ($wpdb->last_error) {
            error_log('XAI Likes DB Error: ' . $wpdb->last_error);
            throw new Exception('Database error getting like count: ' . $wpdb->last_error);
        }

        return $count ? (int) $count : 0;
    }
}
?>