<?php
/*
Plugin Name: XAI Likes by RAIMARA
Plugin URI: https://github.com/salauatamet/XAI-Likes-by-RAIMARA
Description: Легкий плагин WordPress для добавления кнопки «Мне нравится» с недельным лимитом на основе IP-адресов и интеграцией динамических тегов Elementor.
Version: 1.1
Author: SalauatDiiN Ametov
Author URI: https://diinweb.aqulas.me/
License: GPL2
*/

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('XAI_LIKES_VERSION', '1.1');
define('XAI_LIKES_DIR', plugin_dir_path(__FILE__));
define('XAI_LIKES_URL', plugin_dir_url(__FILE__));

// Include necessary files
require_once XAI_LIKES_DIR . 'includes/db.php';
require_once XAI_LIKES_DIR . 'includes/shortcode.php';
require_once XAI_LIKES_DIR . 'includes/elementor-tags.php';
require_once XAI_LIKES_DIR . 'includes/admin.php';

// Initialize plugin
class XAILikes {
    public function __construct() {
        // Register activation hook to create database table
        register_activation_hook(XAI_LIKES_DIR . 'xai-likes.php', [$this, 'activate']);
        
        // Enqueue scripts and styles
        add_action('wp_enqueue_scripts', [$this, 'enqueue_assets']);
        
        // Initialize admin settings
        add_action('admin_menu', ['XAILikesAdmin', 'add_admin_menu']);
        add_action('admin_init', ['XAILikesAdmin', 'register_settings']);
    }

    public function activate() {
        try {
            if (class_exists('XAILikesDB')) {
                XAILikesDB::create_table();
            } else {
                throw new Exception('XAILikesDB class not found');
            }
        } catch (Exception $e) {
            error_log('XAI Likes Activation Error: ' . $e->getMessage());
            wp_die('Error activating XAI Likes plugin. Please check debug.log for details.');
        }
    }

    public function enqueue_assets() {
        // Enqueue WordPress dashicons for heart icon
        wp_enqueue_style('dashicons');

        // Enqueue styles
        wp_enqueue_style(
            'xai-likes-css',
            XAI_LIKES_URL . 'assets/css/likes.css',
            [],
            XAI_LIKES_VERSION
        );

        // Enqueue scripts
        wp_enqueue_script(
            'xai-likes-js',
            XAI_LIKES_URL . 'assets/js/likes.js',
            ['jquery'],
            XAI_LIKES_VERSION,
            true
        );

        // Localize script for AJAX
        wp_localize_script('xai-likes-js', 'xaiLikes', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('xai_likes_nonce')
        ]);
    }
}

// Check if WordPress version is compatible
if (version_compare(get_bloginfo('version'), '4.7', '>=')) {
    new XAILikes();
} else {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>XAI Likes requires WordPress 4.7 or higher.</p></div>';
    });
}

// AJAX handler for liking
add_action('wp_ajax_xai_like_post', 'xai_like_post');
add_action('wp_ajax_nopriv_xai_like_post', 'xai_like_post');

/**
 * Get the correct declension of the word "лайк" based on the number
 *
 * @param int $number The number of likes
 * @return string The correct form ("лайк", "лайка", "лайков")
 */
function xai_likes_decline($number) {
    $number = abs($number);
    $mod100 = $number % 100;
    $mod10 = $number % 10;

    if ($mod100 >= 11 && $mod100 <= 14) {
        return 'лайков';
    }
    if ($mod10 == 1) {
        return 'лайк';
    }
    if ($mod10 >= 2 && $mod10 <= 4) {
        return 'лайка';
    }
    return 'лайков';
}

function xai_like_post() {
    try {
        check_ajax_referer('xai_likes_nonce', 'nonce');

        $post_id = isset($_POST['post_id']) ? intval($_POST['post_id']) : 0;
        $ip = $_SERVER['REMOTE_ADDR'];

        if (!$post_id || !get_post($post_id)) {
            wp_send_json_error(['message' => 'Invalid post ID']);
        }

        if (!class_exists('XAILikesDB')) {
            throw new Exception('XAILikesDB class not found');
        }

        $can_like = XAILikesDB::can_user_like($post_id, $ip);
        
        if ($can_like) {
            XAILikesDB::add_like($post_id, $ip);
            $like_count = XAILikesDB::get_like_count($post_id);
            $declined_text = xai_likes_decline($like_count);
            wp_send_json_success([
                'message' => get_option('xai_likes_message', 'Спасибо за лайк!'),
                'count' => $like_count,
                'text' => $declined_text
            ]);
        } else {
            wp_send_json_error(['message' => 'Вы уже поставили лайк. Попробуйте снова через неделю.']);
        }
    } catch (Exception $e) {
        error_log('XAI Likes AJAX Error: ' . $e->getMessage());
        wp_send_json_error(['message' => 'Server error. Please try again later.']);
    }
}
?>