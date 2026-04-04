jQuery(document).ready(function($) {
    // Tab & Link Toggling (Auth Form)
    $(document).on('click', '.src-auth-tab, .src-switch-form', function(e) {
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

    // Control Panel Navigation (Collapsible)
    $(document).on('click', '.src-menu-item', function(e) {
        const $item = $(this);
        const section = $item.data('section');

        // Toggle submenu if exists
        if ($item.find('.src-submenu').length) {
            $item.toggleClass('expanded');
            $item.find('.src-submenu').slideToggle();
        }

        $('.src-menu-item').removeClass('active');
        $item.addClass('active');

        $('.src-cp-section').removeClass('active');
        $(`#src-cp-content-${section}`).addClass('active');

        if (section === 'users-management' || section === 'institution-members') {
            loadSystemUsers(section === 'institution-members');
        }

        // Close sidebar on mobile
        if ($(window).width() <= 992) {
            $('.src-cp-sidebar').removeClass('active');
        }
    });

    // Mobile Sidebar Toggle
    $(document).on('click', '#src-cp-mobile-toggle', function() {
        $('.src-cp-sidebar').toggleClass('active');
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
    $(document).on('click', '.src-type-box', function() {
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
    $(document).on('click', '#src-show-forgot', function(e) {
        e.preventDefault();
        $('#src-login-action').fadeOut(200, function() {
            $('#src-forgot-form').fadeIn(200);
        });
    });

    $(document).on('click', '#src-back-to-login', function() {
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
    $(document).on('submit', '#src-login-action', function(e) {
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
    $(document).on('submit', '#src-forgot-action', function(e) {
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
    $(document).on('submit', '#src-register-action', function(e) {
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
    $(document).on('submit', '#src-profile-completion-action', function(e) {
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

    // Research Submission
    $(document).on('submit', '#src-research-submission-action', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $msg = $form.find('.src-form-msg');
        $msg.text('Submitting research...').css('color', '#333');

        const formData = new FormData(this);
        formData.append('action', 'src_submit_research');
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
                    $form[0].reset();
                } else {
                    $msg.text(response.data.message).css('color', 'red');
                }
            }
        });
    });

    // Library Filtering
    $(document).on('click', '#lib_filter_btn', function() {
        const $results = $('#src-library-results');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_filter_research',
                nonce: src_ajax.nonce,
                search: $('#lib_search').val(),
                type: $('#lib_type').val(),
                sort: $('#lib_sort').val()
            },
            beforeSend: function() {
                $results.css('opacity', '0.5');
            },
            success: function(response) {
                $results.css('opacity', '1');
                if (response.success) {
                    $results.html(response.data);
                }
            }
        });
    });

    // Submission Management Actions
    $(document).on('click', '.src-sub-act', function() {
        const $btn = $(this);
        const action = $btn.data('action');
        const subId = $btn.data('id');

        if (action === 'view') {
            window.open(src_ajax.site_url + '?p=' + subId + '&preview=true', '_blank');
            return;
        }

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_process_submission',
                nonce: src_ajax.nonce,
                sub_id: subId,
                sub_action: action
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    loadSubmissions();
                }
            }
        });
    });

    function loadSubmissions() {
        const $container = $('#src-submission-list');
        if (!$container.length) return;

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_load_submissions',
                nonce: src_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    $container.html(response.data);
                }
            }
        });
    }

    // Load submissions if section active
    $(document).on('click', '[data-section="submissions-management"]', function() {
        loadSubmissions();
    });

    // Header List Interactions
    $(document).on('click', '.src-pill-welcome', function(e) {
        e.stopPropagation();
        $('.src-noti-dropdown').removeClass('active');
        $('.src-header-dropdown').not('.src-noti-dropdown').toggleClass('active');
    });

    $(document).on('click', '#src-noti-trigger', function(e) {
        e.stopPropagation();
        $('.src-header-dropdown').not('.src-noti-dropdown').removeClass('active');
        $('.src-noti-dropdown').toggleClass('active');
    });

    $(document).on('click', function() {
        $('.src-header-dropdown').removeClass('active');
    });

    // Avatar Upload Trigger
    $(document).on('click', '#src-trigger-upload img, #src-trigger-upload-link', function(e) {
        e.preventDefault();
        e.stopPropagation();
        $('#src-header-avatar-input').click();
    });

    $(document).on('change', '#src-header-avatar-input', function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('action', 'src_upload_avatar');
        formData.append('nonce', src_ajax.nonce);
        formData.append('avatar', file);

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                if (response.success) {
                    $('.src-pill-avatar img, .src-dropdown-header img').attr('src', response.data.url);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });
});
