(function($) {
    'use strict';

    $(document).ready(function() {
        var currentStep = 1;
        var $form = $('#src-submission-form');
        var $steps = $('.src-wizard-steps li');
        var $nextBtn = $('#src-next-step');

        // Handle filter form submission with skeleton loading
        $('.src-sidebar-filter form').on('submit', function() {
            $('.src-grid-view').hide();
            $('#src-loading-skeletons').show();
        });

        $nextBtn.on('click', function() {
            if (currentStep === 1) {
                // Basic validation for Step 1
                if (!$('input[name="paper_title"]').val() || !$('textarea[name="paper_abstract"]').val()) {
                    alert('Please fill in all details.');
                    return;
                }

                // Transition to Step 2
                $('#step-1').hide();
                $form.append('<div class="src-step-content" id="step-2">' +
                    '<label for="paper_file">Upload Research (PDF/DOCX):</label>' +
                    '<input type="file" name="paper_file" accept=".pdf,.docx" required>' +
                    '</div>');
                currentStep = 2;
                $steps.eq(1).addClass('active');
            } else if (currentStep === 2) {
                if (!$('input[name="paper_file"]').val()) {
                    alert('Please upload a file.');
                    return;
                }

                // Transition to Step 3
                $('#step-2').hide();
                $form.append('<div class="src-step-content" id="step-3">' +
                    '<h3>Review your submission</h3>' +
                    '<p><strong>Title:</strong> ' + $('input[name="paper_title"]').val() + '</p>' +
                    '<p>By clicking submit, you agree to the scientific ethics of Healthedia.</p>' +
                    '</div>');
                currentStep = 3;
                $steps.eq(2).addClass('active');
                $nextBtn.text('Submit Research').attr('type', 'submit');
            }
        });
    });

})(jQuery);
