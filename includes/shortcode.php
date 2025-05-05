<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

add_shortcode('xai_likes', 'xai_likes_shortcode');

function xai_likes_shortcode($atts) {
    $atts = shortcode_atts([
        'post_id' => get_the_ID()
    ], $atts);

    $post_id = intval($atts['post_id']);
    if (!$post_id || !class_exists('XAILikesDB')) {
        return '';
    }

    $like_count = XAILikesDB::get_like_count($post_id);
    $button_type = get_option('xai_likes_button_type', 'heart');
    $button_text = get_option('xai_likes_button_text', 'Нравится');
    $declined_text = xai_likes_decline($like_count);

    ob_start();
    ?>
    <div class="xai-likes-wrapper">
        <p class="xai-likes-call-to-action">Понравился товар? Поставьте лайк, чтобы поднять его рейтинг! Спасибо за поддержку!</p>
        <div class="xai-likes-container" data-post-id="<?php echo esc_attr($post_id); ?>">
            <?php if ($button_type === 'heart') : ?>
                <span class="xai-likes-button"><span class="dashicons dashicons-heart"></span></span>
            <?php else : ?>
                <span class="xai-likes-button"><?php echo esc_html($button_text); ?></span>
            <?php endif; ?>
            <span class="xai-likes-count"><?php echo esc_html($like_count); ?> <?php echo esc_html($declined_text); ?></span>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
?>