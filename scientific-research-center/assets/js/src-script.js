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

        // Handle Submenu Toggle (Independent of Section Activation)
        if ($(e.target).closest('.src-menu-toggle').length && $item.find('.src-submenu').length) {
            const isExpanded = $item.hasClass('expanded');

            // Optional: Collapse others
            // $('.src-menu-item').not($item).removeClass('expanded').find('.src-submenu').slideUp();

            $item.toggleClass('expanded');
            $item.find('.src-submenu').slideToggle();

            // If just toggling submenu, don't necessarily switch section unless it's a direct click
        }

        // Switch Main Section
        if (section) {
            $('.src-menu-item').removeClass('active');
            $item.addClass('active');

            $('.src-cp-section').removeClass('active');
            $(`#src-cp-content-${section}`).addClass('active');

            if (section === 'users-management' || section === 'institution-members') {
                loadSystemUsers(section === 'institution-members');
            } else if (section === 'submissions-management') {
                loadSubmissions();
            } else if (section === 'research-engine') {
                loadTaxonomyEditor();
            }
        }

        // Close sidebar on mobile
        if ($(window).width() <= 992 && section) {
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

    $(document).on('change', '#src-user-role-filter, #src-user-sort', function() {
        loadSystemUsers();
    });

    // AJAX Load Users
    function loadSystemUsers(isInstitution = false) {
        const $container = $('#src-user-list');
        if (isInstitution) {
            // handle institution members if needed differently
        }
        const search = $('#src-user-search').val() || '';
        const roleFilter = $('#src-user-role-filter').val() || '';
        const sortVal = $('#src-user-sort').val() || 'display_name-ASC';
        const [orderby, order] = sortVal.split('-');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_load_system_users',
                nonce: src_ajax.nonce,
                search: search,
                role_filter: roleFilter,
                orderby: orderby,
                order: order,
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

    // Research Submission Wizard
    let currentWizardStep = 1;

    $(document).on('click', '.src-wizard-next', function() {
        const $currentStepContent = $(`.src-wizard-step-content[data-step="${currentWizardStep}"]`);
        let valid = true;

        // Simple Validation for required fields in current step
        $currentStepContent.find('[required]').each(function() {
            if (!$(this).val() || ($(this).attr('type') === 'checkbox' && !$(this).is(':checked'))) {
                $(this).addClass('src-error');
                valid = false;
            } else {
                $(this).removeClass('src-error');
            }
        });

        if (!valid) return;

        if (currentWizardStep < 4) {
            currentWizardStep++;
            updateWizardUI();
        }
    });

    $(document).on('click', '.src-wizard-prev', function() {
        if (currentWizardStep > 1) {
            currentWizardStep--;
            updateWizardUI();
        }
    });

    function updateWizardUI() {
        $('.src-wizard-step-content').removeClass('active');
        $(`.src-wizard-step-content[data-step="${currentWizardStep}"]`).addClass('active');

        $('.src-step').removeClass('active completed');
        $('.src-step').each(function() {
            const stepNum = parseInt($(this).data('step'));
            if (stepNum < currentWizardStep) $(this).addClass('completed');
            if (stepNum === currentWizardStep) $(this).addClass('active');
        });

        if (currentWizardStep === 1) {
            $('.src-wizard-prev').hide();
            $('.src-wizard-next').show();
            $('.src-wizard-submit').hide();
        } else if (currentWizardStep === 4) {
            $('.src-wizard-prev').show();
            $('.src-wizard-next').hide();
            $('.src-wizard-submit').show();
        } else {
            $('.src-wizard-prev').show();
            $('.src-wizard-next').show();
            $('.src-wizard-submit').hide();
        }
    }

    // Research Submission Submission
    $(document).on('submit', '#src-research-submission-action', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $msg = $form.find('.src-form-msg');
        $msg.text('Submitting research for review...').css('color', '#333');

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
                    currentWizardStep = 1;
                    updateWizardUI();
                } else {
                    $msg.text(response.data.message).css('color', 'red');
                }
            }
        });
    });

    // Library Search Hierarchical Logic
    $(document).on('change', '#lib_faculty', function() {
        const id = $(this).val();
        const $spec = $('#lib_specialty');
        const $sub = $('#lib_sub_specialty');

        if (!id) {
            $spec.html('<option value="">Specialty</option>').attr('disabled', true);
            $sub.html('<option value="">Sub-specialty</option>').attr('disabled', true);
            return;
        }

        updateChildTax(id, 'src_specialty', 'Specialty', $spec);
    });

    $(document).on('change', '#lib_specialty', function() {
        const id = $(this).val();
        const $sub = $('#lib_sub_specialty');

        if (!id) {
            $sub.html('<option value="">Sub-specialty</option>').attr('disabled', true);
            return;
        }

        updateChildTax(id, 'src_sub_specialty', 'Sub-specialty', $sub);
    });

    $(document).on('change', '#res_faculty', function() {
        const id = $(this).val();
        const $spec = $('#res_specialty');

        if (id == 0) {
            $spec.html('<option value="0">All Specialties</option>').attr('disabled', true);
            return;
        }

        updateChildTax(id, 'src_specialty', 'All Specialties', $spec);
    });

    function updateChildTax(parentId, targetTax, label, $el) {
        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_get_child_taxonomies',
                nonce: src_ajax.nonce,
                parent_id: parentId,
                target_tax: targetTax,
                label: label
            },
            success: function(response) {
                if (response.success) {
                    $el.html(response.data).attr('disabled', false);
                }
            }
        });
    }

    // Library Search Redirection
    $(document).on('click', '#lib_search_btn', function() {
        const query = $('#lib_search').val();
        const faculty = $('#lib_faculty').val();
        const specialty = $('#lib_specialty').val();
        const subspecialty = $('#lib_sub_specialty').val();
        const institution = $('#lib_institution').val();

        let resultsUrl = src_ajax.site_url + '/research-results/?s=' + encodeURIComponent(query);
        if (faculty) resultsUrl += '&faculty=' + faculty;
        if (specialty) resultsUrl += '&specialty=' + specialty;
        if (subspecialty) resultsUrl += '&subspecialty=' + subspecialty;
        if (institution) resultsUrl += '&institution=' + institution;

        window.location.href = resultsUrl;
    });

    // Horizontal Carousel Scroll (Simulation)
    $(document).on('wheel', '.src-carousel-grid', function(e) {
        if (e.originalEvent.deltaY > 0) {
            $(this).scrollLeft($(this).scrollLeft() + 200);
        } else {
            $(this).scrollLeft($(this).scrollLeft() - 200);
        }
        e.preventDefault();
    });

    // Submission Filtering Logic
    $(document).on('keyup', '#src-sub-search', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadSubmissions();
        }, 500);
    });

    $(document).on('change', '#src-sub-filter-type, #src-sub-filter-status', function() {
        loadSubmissions();
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
                nonce: src_ajax.nonce,
                search: $('#src-sub-search').val() || '',
                type: $('#src-sub-filter-type').val() || '',
                status: $('#src-sub-filter-status').val() || 'pending'
            },
            beforeSend: function() {
                $container.html('<div class="src-loading-skeleton"></div>');
            },
            success: function(response) {
                if (response.success) {
                    $container.html(response.data);
                }
            }
        });
    }

    // Header List Interactions
    $(document).on('click', '.src-pill-welcome', function(e) {
        e.stopPropagation();
        $('.src-noti-dropdown').removeClass('active');
        $('.src-header-dropdown').not('.src-noti-dropdown').toggleClass('active');
    });

    $(document).on('click', '#src-noti-trigger', function(e) {
        e.stopPropagation();
        $('.src-header-dropdown').not('.src-noti-dropdown').removeClass('active');
        const $dropdown = $('.src-noti-dropdown');
        $dropdown.toggleClass('active');

        if ($dropdown.hasClass('active')) {
            // Mark as read
            $.ajax({
                type: 'POST',
                url: src_ajax.ajax_url,
                data: {
                    action: 'src_mark_notifications_read',
                    nonce: src_ajax.nonce
                },
                success: function() {
                    $('.src-icon-badge').fadeOut(300, function() {
                        $(this).remove();
                    });
                    $('.src-header-icon-circle').removeClass('has-badge');
                    $('.src-noti-item').removeClass('unread');
                }
            });
        }
    });

    $(document).on('click', '.src-noti-item', function() {
        const url = $(this).data('url');
        if (url) {
            window.location.href = url;
        } else {
            $(this).fadeOut();
        }
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

    $(document).on('click', '#src-trigger-dashboard-upload img, #src-trigger-profile-upload', function() {
        const $input = $(this).find('input[type="file"]');
        if ($input.length) {
            $input.click();
        } else {
            $('#src-dashboard-avatar-input').click();
        }
    });

    $(document).on('change', '#src-dashboard-avatar-input, #prof_picture_input', function() {
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
                    $('.src-cp-avatar img, .src-pill-avatar img, .src-profile-avatar').attr('src', response.data.url);
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    // Taxonomy Management Handlers
    $(document).on('change', '#src-hier-type', function() {
        loadTaxonomyEditor();
    });

    $(document).on('click', '#src-add-taxonomy-item', function() {
        const type = $('#src-hier-type').val();
        const name = $('#src-hier-name').val();
        const parent = $('#src-hier-parent').val();

        if (!name) return alert('Please enter a name.');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_manage_taxonomy',
                nonce: src_ajax.nonce,
                hier_action: 'add',
                hier_type: type,
                hier_name: name,
                hier_parent: parent
            },
            success: function(response) {
                if (response.success) {
                    $('#src-hier-name').val('');
                    loadTaxonomyEditor();
                } else {
                    alert(response.data.message);
                }
            }
        });
    });

    $(document).on('click', '.src-hier-act', function() {
        const $btn = $(this);
        const action = $btn.data('action');
        const id = $btn.data('id');
        const type = $btn.data('type');

        if (action === 'delete' && !confirm('Remove this category?')) return;

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_manage_taxonomy',
                nonce: src_ajax.nonce,
                hier_action: action,
                hier_id: id,
                hier_type: type
            },
            success: function(response) {
                if (response.success) {
                    loadTaxonomyEditor();
                }
            }
        });
    });

    function loadTaxonomyEditor() {
        const $container = $('.src-engine-hierarchy .src-user-list-container');
        const type = $('#src-hier-type').val() || 'src_faculty';

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_manage_taxonomy',
                nonce: src_ajax.nonce,
                hier_action: 'load',
                hier_type: type
            },
            beforeSend: function() {
                $container.html('<div class="src-loading-skeleton"></div>');
            },
            success: function(response) {
                if (response.success) {
                    $container.html(response.data.html);
                    $('#src-hier-parent').html(response.data.parents);
                }
            }
        });
    }

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
