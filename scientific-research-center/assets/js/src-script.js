jQuery(document).ready(function($) {
    // Tab Toggling
    $('.src-auth-tab').on('click', function() {
        const tab = $(this).data('tab');
        $('.src-auth-tab').removeClass('active');
        $(this).addClass('active');
        $('.src-auth-form').removeClass('active');
        $(`#src-${tab}-form`).addClass('active');
    });

    // Login Submission
    $('#src-login-action').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $msg = $form.find('.src-form-msg');
        $msg.text('Logging in...').css('color', '#333');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_login',
                nonce: src_ajax.nonce,
                log: $form.find('input[name="log"]').val(),
                pwd: $form.find('input[name="pwd"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $msg.text(response.data.message).css('color', 'green');
                    window.location.href = response.data.redirect;
                } else {
                    $msg.text(response.data.message).css('color', 'red');
                }
            }
        });
    });

    // Register Submission
    $('#src-register-action').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $msg = $form.find('.src-form-msg');
        $msg.text('Registering...').css('color', '#333');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_register',
                nonce: src_ajax.nonce,
                username: $form.find('input[name="username"]').val(),
                email: $form.find('input[name="email"]').val(),
                password: $form.find('input[name="password"]').val(),
                role: $form.find('select[name="role"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $msg.text(response.data.message).css('color', 'green');
                    $form[0].reset();
                } else {
                    $msg.text(response.data.message).css('color', 'red');
                }
            }
        });
    });
});
