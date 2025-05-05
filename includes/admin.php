<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

class XAILikesAdmin {
    public static function add_admin_menu() {
        add_options_page(
            'XAI Likes Settings',
            'XAI Likes',
            'manage_options',
            'xai-likes',
            [__CLASS__, 'settings_page']
        );
    }

    public static function register_settings() {
        register_setting('xai_likes_settings', 'xai_likes_button_type', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('xai_likes_settings', 'xai_likes_button_text', ['sanitize_callback' => 'sanitize_text_field']);
        register_setting('xai_likes_settings', 'xai_likes_message', ['sanitize_callback' => 'sanitize_text_field']);
    }

    public static function settings_page() {
        ?>
        <div class="wrap">
            <h1>XAI Likes Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('xai_likes_settings');
                do_settings_sections('xai_likes_settings');
                ?>
                <table class="form-table">
                    <tr>
                        <th><label for="xai_likes_button_type">Button Type</label></th>
                        <td>
                            <select name="xai_likes_button_type" id="xai_likes_button_type">
                                <option value="heart" <?php selected(get_option('xai_likes_button_type', 'heart'), 'heart'); ?>>Heart Icon</option>
                                <option value="text" <?php selected(get_option('xai_likes_button_type', 'heart'), 'text'); ?>>Text</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th><label for="xai_likes_button_text">Button Text</label></th>
                        <td>
                            <input type="text" name="xai_likes_button_text" id="xai_likes_button_text" value="<?php echo esc_attr(get_option('xai_likes_button_text', 'Нравится')); ?>">
                        </td>
                    </tr>
                    <tr>
                        <th><label for="xai_likes_message">Message After Like</label></th>
                        <td>
                            <input type="text" name="xai_likes_message" id="xai_likes_message" value="<?php echo esc_attr(get_option('xai_likes_message', 'Спасибо за лайк!')); ?>">
                        </td>
                    </tr>
                </table>
                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
?>