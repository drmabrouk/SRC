jQuery(document).ready(function($) {
    // Tab & Link Toggling
    $('.src-auth-tab, .src-switch-form').on('click', function(e) {
        if ($(this).hasClass('src-switch-form')) e.preventDefault();

        const tab = $(this).data('tab');
        $('.src-auth-tab').removeClass('active');
        $(`.src-auth-tab[data-tab="${tab}"]`).addClass('active');

        $('.src-auth-form').removeClass('active');
        $(`#src-${tab}-form`).addClass('active');

        // Reset messages
        $('.src-form-msg').text('');
    });

    // Role-based field toggling (Institution)
    $('#reg_role').on('change', function() {
        if ($(this).val() === 'src_researcher') {
            $('#src-institution-field').slideDown();
            $('#reg_institution').attr('required', true);
        } else {
            $('#src-institution-field').slideUp();
            $('#reg_institution').attr('required', false);
        }
    });

    // Show/Hide Password
    $(document).on('click', '.src-toggle-pwd', function() {
        const $input = $(this).siblings('input');
        if ($input.attr('type') === 'password') {
            $input.attr('type', 'text');
            $(this).removeClass('dashicons-visibility').addClass('dashicons-hidden');
        } else {
            $input.attr('type', 'password');
            $(this).removeClass('dashicons-hidden').addClass('dashicons-visibility');
        }
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

        // Frontend Validation
        const username = $form.find('input[name="username"]').val();
        const password = $form.find('input[name="password"]').val();
        const confirm = $form.find('input[name="password_confirm"]').val();

        if (username.length < 4) {
            $msg.text('Username must be at least 4 characters long.').css('color', 'red');
            return;
        }

        if (password !== confirm) {
            $msg.text('Passwords do not match.').css('color', 'red');
            return;
        }

        $msg.text('Registering...').css('color', '#333');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_register',
                nonce: src_ajax.nonce,
                first_name: $form.find('input[name="first_name"]').val(),
                last_name: $form.find('input[name="last_name"]').val(),
                username: username,
                email: $form.find('input[name="email"]').val(),
                password: password,
                role: $form.find('select[name="role"]').val(),
                institution: $form.find('input[name="institution"]').val(),
                terms: $form.find('input[name="terms"]').is(':checked') ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    $msg.text(response.data.message).css('color', 'green');
                    $form[0].reset();
                    $('#src-institution-field').hide();
                } else {
                    $msg.text(response.data.message).css('color', 'red');
                }
            }
        });
    });
});
