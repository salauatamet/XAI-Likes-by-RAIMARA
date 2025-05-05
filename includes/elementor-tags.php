<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Register Elementor dynamic tag only if Elementor is active and compatible
if (did_action('elementor/loaded') && class_exists('Elementor\Core\DynamicTags\Tag')) {
    add_action('elementor/dynamic_tags/register', function($dynamic_tags) {
        class XAILikesDynamicTag extends \Elementor\Core\DynamicTags\Tag {
            public function get_name() {
                return 'xai-likes-count';
            }

            public function get_title() {
                return 'XAI Likes Count';
            }

            public function get_group() {
                return 'post';
            }

            public function get_categories() {
                return [\Elementor\Modules\DynamicTags\Module::NUMBER_CATEGORY];
            }

            public function render() {
                $post_id = get_the_ID();
                if (!$post_id || !class_exists('XAILikesDB')) {
                    echo '0';
                    return;
                }

                $like_count = XAILikesDB::get_like_count($post_id);

                // Normalize like count to a 0-5 scale for rating widgets
                // Assume 10 likes = 5 stars (maximum rating)
                $max_likes_for_full_rating = 10;
                $normalized_rating = min(5, ($like_count / $max_likes_for_full_rating) * 5);

                // Output normalized value with 1 decimal place for partial stars
                echo number_format($normalized_rating, 1, '.', '');
            }
        }

        try {
            $dynamic_tags->register(new XAILikesDynamicTag());
        } catch (Exception $e) {
            error_log('XAI Likes Elementor Tag Registration Error: ' . $e->getMessage());
        }
    });
} else {
    add_action('admin_notices', function() {
        echo '<div class="error"><p>XAI Likes requires Elementor 3.0 or higher to be activated for dynamic tag functionality.</p></div>';
    });
}
?>