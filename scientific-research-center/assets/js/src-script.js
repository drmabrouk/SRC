jQuery(document).ready(function($) {
    // Tab & Link Toggling (Auth Form)
    $('.src-auth-tab, .src-switch-form').on('click', function(e) {
        if ($(this).hasClass('src-switch-form')) e.preventDefault();

        const tab = $(this).data('tab');
        $('.src-auth-tab').removeClass('active');
        $(`.src-auth-tab[data-tab="${tab}"]`).addClass('active');

        $('.src-auth-form').removeClass('active');
        $(`#src-${tab}-form`).addClass('active');

        // Hide forgot form if open
        $('#src-login-action').show();
        $('#src-forgot-form').hide();

        // Reset messages
        $('.src-form-msg').text('');
    });

    // Control Panel Navigation
    $('.src-cp-nav li').on('click', function() {
        const section = $(this).data('section');
        $('.src-cp-nav li').removeClass('active');
        $(this).addClass('active');

        $('.src-cp-section').removeClass('active');
        $(`#src-cp-content-${section}`).addClass('active');

        if (section === 'users-management' || section === 'institution-members') {
            loadSystemUsers(section === 'institution-members');
        }
    });

    // User Search Handler
    let searchTimeout;
    $(document).on('keyup', '#src-user-search', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadSystemUsers();
        }, 500);
    });

    // AJAX Load Users
    function loadSystemUsers(isInstitution = false) {
        const $container = $('.src-user-list-container');
        const search = $('#src-user-search').val() || '';

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_load_system_users',
                nonce: src_ajax.nonce,
                search: search,
                institution_filter: isInstitution ? 'current' : ''
            },
            beforeSend: function() {
                $container.html('<div class="src-loading-skeleton"></div>');
            },
            success: function(response) {
                if (response.success) {
                    $container.html(response.data);
                } else {
                    $container.html(`<p style="color:red;">${response.data.message}</p>`);
                }
            }
        });
    }

    // User Actions (Edit, Delete, Suspend, Notify)
    $(document).on('click', '.src-user-act', function() {
        const $btn = $(this);
        const action = $btn.data('action');
        const userId = $btn.data('id');

        if (action === 'delete' && !confirm('Are you sure you want to delete this user?')) return;

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_user_action',
                nonce: src_ajax.nonce,
                user_id: userId,
                user_action: action
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    loadSystemUsers($('.src-cp-nav li.active').data('section') === 'institution-members');
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // User Type Selection
    $('.src-type-box').on('click', function() {
        $('.src-type-box').removeClass('active');
        $(this).addClass('active');
        const role = $(this).data('role');
        $('#reg_role').val(role);

        if (role === 'src_researcher') {
            $('#src-institution-field').slideDown();
            $('#reg_institution').attr('required', true);
        } else {
            $('#src-institution-field').slideUp();
            $('#reg_institution').attr('required', false);
        }
    });

    // Forgot Password Toggle
    $('#src-show-forgot').on('click', function(e) {
        e.preventDefault();
        $('#src-login-action').fadeOut(200, function() {
            $('#src-forgot-form').fadeIn(200);
        });
    });

    $('#src-back-to-login').on('click', function() {
        $('#src-forgot-form').fadeOut(200, function() {
            $('#src-login-action').fadeIn(200);
        });
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

    // Forgot Password Submission
    $('#src-forgot-action').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $msg = $form.find('.src-form-msg');
        $msg.text('Processing...').css('color', '#333');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_forgot_password',
                nonce: src_ajax.nonce,
                user_login: $form.find('input[name="user_login"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $msg.text(response.data.message).css('color', 'green');
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
                role: $('#reg_role').val(),
                institution: $form.find('input[name="institution"]').val(),
                terms: $form.find('input[name="terms"]').is(':checked') ? 1 : 0
            },
            success: function(response) {
                if (response.success) {
                    $msg.text(response.data.message).css('color', 'green');
                    $form[0].reset();
                    $('#src-institution-field').hide();
                    $('.src-type-box').removeClass('active');
                    $('.src-type-box[data-role="src_member"]').addClass('active');
                    $('#reg_role').val('src_member');
                } else {
                    $msg.text(response.data.message).css('color', 'red');
                }
            }
        });
    });

    // Profile Completion Submission
    $('#src-profile-completion-action').on('submit', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $msg = $form.find('.src-form-msg');
        $msg.text('Saving profile...').css('color', '#333');

        const formData = new FormData(this);
        formData.append('action', 'src_complete_profile');
        formData.append('nonce', src_ajax.nonce);

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    $msg.text(response.data.message).css('color', 'green');
                    setTimeout(function() {
                        window.location.href = response.data.redirect;
                    }, 1500);
                } else {
                    $msg.text(response.data.message).css('color', 'red');
                }
            }
        });
    });
});
