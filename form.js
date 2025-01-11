jQuery(document).ready(function($) {
    $('#cef-form').submit(function(e) {
        e.preventDefault();

        // Get reCAPTCHA response
        var recaptchaResponse = grecaptcha.getResponse();

        if (recaptchaResponse.length === 0) {
            // If reCAPTCHA is not completed, show an error message
            $('#cef-message-result').html('<p style="color: red;">Please complete the reCAPTCHA.</p>');
            return;
        }

        // Prepare form data
        var formData = {
            action: 'cef_form_submission',
            cef_name: $('#cef-name').val(),
            cef_email: $('#cef-email').val(),
            cef_phone: $('#cef-phone').val(),
            cef_message: $('#cef-message').val(),
            g_recaptcha_response: recaptchaResponse,
            cef_nonce: cef_ajax_object.cef_nonce // Pass the nonce securely
        };

        // Send AJAX POST request
        $.post(cef_ajax_object.ajax_url, formData, function(response) {
            // Display the server response
            $('#cef-message-result').html('<p style="color: green;">' + response + '</p>');

            // Reset the form and reCAPTCHA
            $('#cef-form')[0].reset();
            grecaptcha.reset();
        }).fail(function() {
            // Handle any AJAX errors
            $('#cef-message-result').html('<p style="color: red;">An error occurred. Please try again.</p>');
        });
    });
});
