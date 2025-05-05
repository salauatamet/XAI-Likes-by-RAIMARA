jQuery(document).ready(function($) {
    $('.xai-likes-button').on('click', function() {
        var $container = $(this).closest('.xai-likes-container');
        var post_id = $container.data('post-id');

        $.ajax({
            url: xaiLikes.ajax_url,
            type: 'POST',
            data: {
                action: 'xai_like_post',
                post_id: post_id,
                nonce: xaiLikes.nonce
            },
            success: function(response) {
                if (response.success) {
                    $container.find('.xai-likes-count').text(response.data.count + ' ' + response.data.text);
                    alert(response.data.message);
                } else {
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('Ошибка сервера. Пожалуйста, попробуйте снова.');
            }
        });
    });
});