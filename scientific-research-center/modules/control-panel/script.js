jQuery(document).ready(function($) {
    // Tab & Link Toggling (Auth Form)
    // Profile Tabs Navigation
    $(document).on('click', '.src-prof-tab-btn', function() {
        const tab = $(this).data('tab');
        $('.src-prof-tab-btn').removeClass('active');
        $(this).addClass('active');

        $('.src-prof-tab-content').removeClass('active');
        $(`#src-prof-tab-${tab}`).addClass('active');
    });

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

    // Handle specific section from URL
    const urlParams = new URLSearchParams(window.location.search);
    const targetSection = urlParams.get('section');
    if (targetSection) {
        $(`.src-menu-item[data-section="${targetSection}"]`).trigger('click');
    }

    // Control Panel Navigation (Partial AJAX Removal - Full Page Transitions)
    // We keep these for non-link UI parts if needed, but the template now uses <a> tags.
    $(document).on('click', '.src-menu-item:not(.has-submenu)', function(e) {
        if ($(window).width() <= 992) {
            $('.src-cp-sidebar').removeClass('active');
        }
    });

    // Auto-load data for the active section on page load
    const activeSection = urlParams.get('section') || 'overview';
    if (activeSection === 'users-management' || activeSection === 'institution-members') {
        loadSystemUsers(activeSection === 'institution-members');
    } else if (activeSection === 'submissions-management') {
        loadSubmissions();
    } else if (activeSection === 'research-engine') {
        loadTaxonomyEditor();
        loadSearchAnalytics();
    } else if (activeSection === 'settings') {
        loadEmailTemplate();
    }

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

    $(document).on('change', '#src-user-role-filter, #src-user-inst-filter, #src-user-status-filter, #src-user-sort', function() {
        loadSystemUsers();
    });

    $(document).on('keyup', '#src-user-specialty-filter', function() {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            loadSystemUsers();
        }, 500);
    });

    // AJAX Load Users
    function loadSystemUsers(isInstitution = false) {
        const $container = $('#src-user-list');
        const search = $('#src-user-search').val() || '';
        const roleFilter = $('#src-user-role-filter').val() || '';
        const instFilter = $('#src-user-inst-filter').val() || '';
        const statusFilter = $('#src-user-status-filter').val() || '';
        const specFilter = $('#src-user-specialty-filter').val() || '';
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
                institution_filter: isInstitution ? 'current' : instFilter,
                status_filter: statusFilter,
                specialty_filter: specFilter,
                orderby: orderby,
                order: order
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

    // User Actions (Edit, Delete, Suspend, Notify, Logs)
    $(document).on('click', '.src-user-act', function() {
        const $btn = $(this);
        const action = $btn.data('action');
        const userId = $btn.data('id');

        if (action === 'logs') {
            const $modal = $('#src-user-log-modal');
            const $content = $('#src-user-log-content');
            $modal.fadeIn();

            $.ajax({
                type: 'POST',
                url: src_ajax.ajax_url,
                data: {
                    action: 'src_get_user_logs',
                    nonce: src_ajax.nonce,
                    user_id: userId
                },
                beforeSend: function() {
                    $content.html('<div class="src-loading-skeleton"></div>');
                },
                success: function(response) {
                    if (response.success) {
                        $content.html(response.data);
                    }
                }
            });
            return;
        }

        if (action === 'delete' && !confirm('Are you sure you want to delete this user?')) return;

        if (action === 'edit') {
            $.ajax({
                type: 'POST',
                url: src_ajax.ajax_url,
                data: {
                    action: 'src_get_user_data',
                    nonce: src_ajax.nonce,
                    user_id: userId
                },
                success: function(response) {
                    if (response.success) {
                        $('#src-modal-title').text('Edit Platform User');
                        $('#src-modal-submit-btn').text('Update Account');
                        $('#modal_user_id').val(userId);
                        $('#add_fn').val(response.data.first_name);
                        $('#add_ln').val(response.data.last_name);
                        $('#add_user').val(response.data.user_login).attr('disabled', true);
                        $('#add_email').val(response.data.user_email);
                        $('#add_role').val(response.data.role);
                        $('#add_inst').val(response.data.institution);
                        $('#add_spec').val(response.data.specialty);
                        $('#add_pass').val('');
                        $('#src-user-modal').fadeIn();
                    }
                }
            });
            return;
        }

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

    // Add User Modal Handlers
    $(document).on('click', '.src-add-user-trigger', function() {
        $('#src-modal-title').text('Add New Platform User');
        $('#src-modal-submit-btn').text('Create User Account');
        $('#modal_user_id').val('');
        $('#add_user').attr('disabled', false);
        $('#src-user-form')[0].reset();
        $('#src-user-modal').fadeIn();
    });

    $(document).on('click', '.src-modal-close', function() {
        $('.src-modal').fadeOut();
    });

    $(document).on('submit', '#src-user-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $msg = $form.find('.src-form-msg');
        const userId = $('#modal_user_id').val();

        $msg.text(userId ? 'Updating user...' : 'Creating user...').css('color', '#000');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: userId ? 'src_user_action' : 'src_add_new_user',
                user_action: userId ? 'update' : '',
                user_id: userId,
                nonce: src_ajax.nonce,
                first_name: $form.find('input[name="first_name"]').val(),
                last_name: $form.find('input[name="last_name"]').val(),
                username: $form.find('input[name="username"]').val(),
                email: $form.find('input[name="email"]').val(),
                role: $form.find('select[name="role"]').val(),
                password: $form.find('input[name="password"]').val(),
                institution: $form.find('input[name="institution"]').val(),
                specialty: $form.find('input[name="specialty"]').val()
            },
            success: function(response) {
                if (response.success) {
                    $msg.text(response.data.message).css('color', 'green');
                    setTimeout(() => {
                        $('#src-user-modal').fadeOut();
                        $form[0].reset();
                        $msg.text('');
                        loadSystemUsers();
                    }, 1500);
                } else {
                    $msg.text(response.data.message).css('color', 'red');
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

    // Drag and Drop Implementation
    const $dropZone = $('#src-main-file-zone');
    if ($dropZone.length) {
        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            $dropZone.on(eventName, e => {
                e.preventDefault();
                e.stopPropagation();
            });
        });

        $dropZone.on('dragenter dragover', () => $dropZone.addClass('drag-active'));
        $dropZone.on('dragleave drop', () => $dropZone.removeClass('drag-active'));

        $dropZone.on('drop', e => {
            const dt = e.originalEvent.dataTransfer;
            const files = dt.files;
            if (files.length) {
                $('#res_file')[0].files = files;
                $dropZone.find('p').text(`File selected: ${files[0].name}`);
                $dropZone.find('.src-upload-icon .dashicons').removeClass('dashicons-cloud-upload').addClass('dashicons-yes');
            }
        });

        // Update UI on standard input change
        $(document).on('change', '#res_file', function() {
            if (this.files.length) {
                $dropZone.find('p').text(`File selected: ${this.files[0].name}`);
                $dropZone.find('.src-upload-icon .dashicons').removeClass('dashicons-cloud-upload').addClass('dashicons-yes');
            }
        });
    }

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

    // Search Button Redirection (Always to Results Page)
    $(document).on('click', '#lib_search_btn', function() {
        const query = $('#lib_search').val() || '';

        if (query) {
            $.ajax({
                type: 'POST',
                url: src_ajax.ajax_url,
                data: { action: 'src_log_search', nonce: src_ajax.nonce, keyword: query }
            });
        }
        const faculty = $('#lib_faculty').val();
        const specialty = $('#lib_specialty').val();
        const subspecialty = $('#lib_sub_specialty').val();
        const institution = $('#lib_institution').val();
        const category = $('#lib_category').val();
        const year = $('#lib_year').val();

        let resultsUrl = src_ajax.site_url.replace(/\/$/, '') + '/research-results/?s=' + encodeURIComponent(query);
        if (faculty) resultsUrl += '&faculty=' + faculty;
        if (specialty) resultsUrl += '&specialty=' + specialty;
        if (subspecialty) resultsUrl += '&subspecialty=' + subspecialty;
        if (institution) resultsUrl += '&institution=' + institution;
        if (category) resultsUrl += '&category=' + category;
        if (year) resultsUrl += '&year=' + year;

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

    $(document).on('change', '#src-sub-filter-type, #src-sub-filter-inst, #src-sub-filter-cat, #src-sub-filter-status', function() {
        loadSubmissions();
    });

    // Submission Management Actions
    $(document).on('click', '.src-sub-act', function() {
        const $btn = $(this);
        const action = $btn.data('action');
        const subId = $btn.data('id');

        if (action === 'assign') {
            $('#assign_sub_id').val(subId);
            $('#src-assign-reviewer-modal').fadeIn();
            return;
        }

        if (action === 'history') {
            const $modal = $('#src-sub-history-modal');
            const $content = $('#src-sub-history-content');
            $modal.fadeIn();
            $.ajax({
                type: 'POST',
                url: src_ajax.ajax_url,
                data: { action: 'src_get_submission_history', nonce: src_ajax.nonce, sub_id: subId },
                beforeSend: function() { $content.html('<div class="src-loading-skeleton"></div>'); },
                success: function(response) { if (response.success) $content.html(response.data); }
            });
            return;
        }

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

    $(document).on('submit', '#src-assign-reviewer-form', function(e) {
        e.preventDefault();
        const $form = $(this);
        const $msg = $form.find('.src-form-msg');
        $msg.text('Assigning...').css('color', '#000');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_assign_reviewer',
                nonce: src_ajax.nonce,
                sub_id: $('#assign_sub_id').val(),
                reviewer_id: $('#assign_reviewer').val(),
                deadline: $('#assign_deadline').val()
            },
            success: function(response) {
                if (response.success) {
                    $msg.text(response.data.message).css('color', 'green');
                    setTimeout(() => { $('#src-assign-reviewer-modal').fadeOut(); loadSubmissions(); }, 1500);
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
                institution: $('#src-sub-filter-inst').val() || '',
                category: $('#src-sub-filter-cat').val() || '',
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
    $(document).on('click', '.src-dropdown-trigger-area', function(e) {
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

    $(document).on('click', '#src-trigger-workspace-upload img, #src-trigger-profile-upload', function() {
        const $input = $(this).closest('.src-cp-avatar, .src-profile-avatar-wrapper').find('input[type="file"]');
        if ($input.length) {
            $input.click();
        } else {
            $('#src-workspace-avatar-input').click();
        }
    });

    $(document).on('change', '#src-workspace-avatar-input, #prof_picture_input', function() {
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

    $(document).on('click', '#src-rebuild-index-btn', function() {
        const $btn = $(this);
        $btn.text('Indexing...').attr('disabled', true);
        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: { action: 'src_rebuild_index', nonce: src_ajax.nonce },
            success: function(response) {
                alert(response.data.message);
                $btn.text('Rebuild Search Index').attr('disabled', false);
            }
        });
    });

    $(document).on('click', '#src-save-search-settings-btn', function() {
        const metaVis = [];
        $('.src-meta-vis-check:checked').each(function() { metaVis.push($(this).val()); });

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_save_search_settings',
                nonce: src_ajax.nonce,
                card_design: $('#src-card-design').val(),
                metadata_visibility: metaVis
            },
            success: function(response) { alert(response.data.message); }
        });
    });

    function loadSearchAnalytics() {
        const $container = $('#src-search-analytics-content');
        if (!$container.length) return;
        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: { action: 'src_get_search_analytics', nonce: src_ajax.nonce },
            success: function(response) { if (response.success) $container.html(response.data); }
        });
    }

    // Settings AJAX Handlers
    $(document).on('change', '#src-email-template-select', function() {
        loadEmailTemplate();
    });

    $(document).on('change', '#src-role-perm-select', function() {
        loadRolePermissions();
    });

    $(document).on('click', '#src-save-email-tpl-btn', function() {
        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_save_email_template',
                nonce: src_ajax.nonce,
                template_id: $('#src-email-template-select').val(),
                subject: $('#src-email-subject').val(),
                body: $('#src-email-body').val()
            },
            success: function(response) { alert(response.data.message); }
        });
    });

    function loadEmailTemplate() {
        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_get_email_template',
                nonce: src_ajax.nonce,
                template_id: $('#src-email-template-select').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#src-email-subject').val(response.data.subject);
                    $('#src-email-body').val(response.data.body);
                }
            }
        });
    }

    function loadRolePermissions() {
        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_get_role_permissions',
                nonce: src_ajax.nonce,
                role_slug: $('#src-role-perm-select').val()
            },
            success: function(response) {
                if (response.success) {
                    $('#src-role-caps-list input').prop('checked', false);
                    $.each(response.data, function(cap, val) {
                        $(`#src-role-caps-list input[value="${cap}"]`).prop('checked', val);
                    });
                }
            }
        });
    }

    $(document).on('click', '#src-save-role-perms-btn', function() {
        const caps = [];
        $('#src-role-caps-list input:checked').each(function() { caps.push($(this).val()); });
        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_save_role_permissions',
                nonce: src_ajax.nonce,
                role_slug: $('#src-role-perm-select').val(),
                caps: caps
            },
            success: function(response) { alert(response.data.message); }
        });
    });

    $(document).on('click', '#src-save-security-btn', function() {
        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_save_security_settings',
                nonce: src_ajax.nonce,
                min_length: $('#src-min-pwd').val(),
                timeout: $('#src-session-time').val()
            },
            success: function(response) { alert(response.data.message); }
        });
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

    // Inline Administrative Editing
    $(document).on('click', '.src-inline-edit-trigger', function() {
        const $section = $(this).closest('.src-detail-section');
        $section.find('.src-abstract-content').hide();
        $section.find('.src-inline-editor').fadeIn();
        $(this).hide();
    });

    $(document).on('click', '.src-cancel-edit', function() {
        const $section = $(this).closest('.src-detail-section');
        $section.find('.src-inline-editor').hide();
        $section.find('.src-abstract-content').fadeIn();
        $section.find('.src-inline-edit-trigger').show();
    });

    $(document).on('click', '.src-save-inline-edit', function() {
        const $btn = $(this);
        const $section = $btn.closest('.src-detail-section');
        const postId = $btn.data('id');
        const newContent = $section.find('.src-abstract-edit-area').val();

        $btn.text('Saving...').attr('disabled', true);

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_save_inline_research',
                nonce: src_ajax.nonce,
                post_id: postId,
                content: newContent
            },
            success: function(response) {
                if (response.success) {
                    $section.find('.src-abstract-content').html(newContent.replace(/\n/g, '<br>')).fadeIn();
                    $section.find('.src-inline-editor').hide();
                    $section.find('.src-inline-edit-trigger').show();
                    alert('Research version saved and indexed.');
                }
                $btn.text('Save Version').attr('disabled', false);
            }
        });
    });

    // Favorites Toggling
    $(document).on('click', '.src-fav-toggle', function(e) {
        e.preventDefault();
        const $btn = $(this);
        const postId = $btn.closest('.src-research-card').data('id');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_toggle_favorite',
                nonce: src_ajax.nonce,
                post_id: postId
            },
            success: function(response) {
                if (response.success) {
                    $btn.toggleClass('active');

                    // Update Header Badge
                    const $headerIcon = $('#src-fav-header-icon');
                    const $badge = $('.fav-badge');

                    if (response.data.count > 0) {
                        $headerIcon.addClass('has-badge');
                        $badge.text(response.data.count).show();
                    } else {
                        $headerIcon.removeClass('has-badge');
                        $badge.hide();
                    }

                    // If we are in the favorites section, maybe remove the card
                    if ($('#src-cp-content-favorites').hasClass('active') && response.data.status === 'removed') {
                        $btn.closest('.src-research-card').fadeOut();
                    }
                }
            }
        });
    });

    $(document).on('click', '#src-system-refresh', function(e) {
        e.preventDefault();
        const $btn = $(this);
        $btn.find('.dashicons').addClass('spin');

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_system_refresh',
                nonce: src_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    alert(response.data.message);
                    location.reload();
                }
            }
        });
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

    $(document).on('click', '#src-export-users-trigger', function() {
        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: {
                action: 'src_bulk_export_users',
                nonce: src_ajax.nonce
            },
            success: function(response) {
                if (response.success) {
                    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(response.data));
                    const downloadAnchorNode = document.createElement('a');
                    downloadAnchorNode.setAttribute("href",     dataStr);
                    downloadAnchorNode.setAttribute("download", "src_users_export.json");
                    document.body.appendChild(downloadAnchorNode);
                    downloadAnchorNode.click();
                    downloadAnchorNode.remove();
                }
            }
        });
    });

    $(document).on('change', '#src-import-users-input', function() {
        const file = this.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('action', 'src_bulk_import_users');
        formData.append('nonce', src_ajax.nonce);
        formData.append('import_file', file);

        $.ajax({
            type: 'POST',
            url: src_ajax.ajax_url,
            data: formData,
            contentType: false,
            processData: false,
            success: function(response) {
                alert(response.data.message);
                if (response.success) loadSystemUsers();
            }
        });
    });
});
