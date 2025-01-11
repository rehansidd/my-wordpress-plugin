<?php
/*
Plugin Name: Custom Enquiry Form
Description: A custom enquiry form with AJAX submission, Google reCAPTCHA, and configurable email settings.
Version: 1.1
Author: Rehan Zafar
*/

// Enqueue the CSS and JavaScript files for the form
function cef_enqueue_assets() {
    wp_enqueue_style('cef-form-css', plugin_dir_url(__FILE__) . 'form.css');
    wp_enqueue_script('cef-form-js', plugin_dir_url(__FILE__) . 'form.js', array('jquery'), null, true);
    wp_localize_script('cef-form-js', 'cef_ajax_object', array(
        'ajax_url' => admin_url('admin-ajax.php'),
        'cef_nonce' => wp_create_nonce('cef_form_nonce')
    ));
    // Enqueue Google reCAPTCHA script
    wp_enqueue_script('google-recaptcha', 'https://www.google.com/recaptcha/api.js', array(), null, true);
}
add_action('wp_enqueue_scripts', 'cef_enqueue_assets');

// Shortcode to display the form
function cef_enquiry_form() {
    ob_start();
    ?>
    <form id="cef-form">
        <div class="cef-form-field">
            <label for="cef-name"><?php esc_html_e('Full Name:', 'custom-enquiry-form'); ?></label>
            <input type="text" id="cef-name" name="cef_name" required>
        </div>
        <div class="cef-form-field">
            <label for="cef-email"><?php esc_html_e('Email Address:', 'custom-enquiry-form'); ?></label>
            <input type="email" id="cef-email" name="cef_email" required>
        </div>
        <div class="cef-form-field">
            <label for="cef-phone"><?php esc_html_e('Phone:', 'custom-enquiry-form'); ?></label>
            <input type="text" id="cef-phone" name="cef_phone" required>
        </div>
        <div class="cef-form-field">
            <label for="cef-message"><?php esc_html_e('Message:', 'custom-enquiry-form'); ?></label>
            <textarea id="cef-message" name="cef_message" required></textarea>
        </div>
        <!-- Google reCAPTCHA widget -->
        <div class="g-recaptcha" data-sitekey="<?php echo esc_attr(get_option('cef_recaptcha_site_key')); ?>" aria-label="<?php esc_attr_e('Google reCAPTCHA', 'custom-enquiry-form'); ?>"></div>
        <button type="submit"><?php esc_html_e('Submit', 'custom-enquiry-form'); ?></button>
    </form>
    <div id="cef-message-result"></div>
    <?php
    return ob_get_clean();
}
add_shortcode('custom_enquiry_form', 'cef_enquiry_form');

// Handle AJAX form submission
function cef_handle_form_submission() {
    // Validate nonce to protect from CSRF
    if (!isset($_POST['cef_nonce']) || !wp_verify_nonce($_POST['cef_nonce'], 'cef_form_nonce')) {
        wp_send_json_error(array('message' => esc_html__('Nonce validation failed.', 'custom-enquiry-form')));
    }

    // Retrieve and sanitize form fields
    $name = sanitize_text_field($_POST['cef_name']);
    $email = sanitize_email($_POST['cef_email']);
    $phone = sanitize_text_field($_POST['cef_phone']);
    $message = sanitize_textarea_field($_POST['cef_message']);
    $recaptcha_response = $_POST['g-recaptcha-response'];

    // Google reCAPTCHA verification
    $recaptcha_secret = get_option('cef_recaptcha_secret_key', '');
    if (empty($recaptcha_secret)) {
        wp_send_json_error(array('message' => esc_html__('reCAPTCHA secret key is not configured.', 'custom-enquiry-form')));
    }

    $recaptcha_verification = wp_remote_post('https://www.google.com/recaptcha/api/siteverify', array(
        'body' => array(
            'secret' => $recaptcha_secret,
            'response' => $recaptcha_response,
        ),
    ));

    if (is_wp_error($recaptcha_verification)) {
        wp_send_json_error(array('message' => esc_html__('Failed to validate reCAPTCHA. Please try again.', 'custom-enquiry-form')));
    }

    $recaptcha_result = json_decode(wp_remote_retrieve_body($recaptcha_verification));

    if (!$recaptcha_result->success) {
        wp_send_json_error(array('message' => esc_html__('reCAPTCHA verification failed.', 'custom-enquiry-form')));
    }

    // Check if any field is empty
    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        wp_send_json_error(array('message' => esc_html__('All fields are required.', 'custom-enquiry-form')));
    }

    // Email sending logic
    $to = get_option('cef_to_email', get_bloginfo('admin_email')); // Fallback to admin email
    $subject = get_option('cef_subject', esc_html__('New Enquiry Form Submission', 'custom-enquiry-form'));
    $email_message = str_replace(
        array('[NAME]', '[EMAIL]', '[PHONE]', '[TEXT]'),
        array($name, $email, $phone, $message),
        get_option('cef_message', "Name: [NAME]\nEmail: [EMAIL]\nPhone: [PHONE]\nMessage: [TEXT]")
    );

    $headers = array('Content-Type: text/html; charset=UTF-8');

    if (!wp_mail($to, $subject, nl2br($email_message), $headers)) {
        wp_send_json_error(array('message' => esc_html__('Failed to send email.', 'custom-enquiry-form')));
    }

    // Return success message
    wp_send_json_success(array('message' => esc_html__('Thank you for your enquiry! We will get back to you soon.', 'custom-enquiry-form')));
}

// Register AJAX handler
add_action('wp_ajax_cef_form_submission', 'cef_handle_form_submission');
add_action('wp_ajax_nopriv_cef_form_submission', 'cef_handle_form_submission');

// Add settings for reCAPTCHA keys
function cef_register_settings() {
    add_option('cef_recaptcha_site_key', '');
    add_option('cef_recaptcha_secret_key', '');
    register_setting('cef_options_group', 'cef_recaptcha_site_key');
    register_setting('cef_options_group', 'cef_recaptcha_secret_key');
}
add_action('admin_init', 'cef_register_settings');

// Add settings page
function cef_register_options_page() {
    add_options_page('Custom Enquiry Form', 'Custom Enquiry Form', 'manage_options', 'cef-plugin', 'cef_options_page');
}
add_action('admin_menu', 'cef_register_options_page');

function cef_options_page() {
    ?>
    <div>
        <h2><?php esc_html_e('Custom Enquiry Form Settings', 'custom-enquiry-form'); ?></h2>
        <form method="post" action="options.php">
            <?php settings_fields('cef_options_group'); ?>
            <table>
                <tr valign="top">
                    <th scope="row"><label for="cef_recaptcha_site_key"><?php esc_html_e('6LefjrQqAAAAAC7YPJjADrSsVZoBbDI0xG4-jH83', 'custom-enquiry-form'); ?></label></th>
                    <td><input type="text" id="cef_recaptcha_site_key" name="cef_recaptcha_site_key" value="<?php echo esc_attr(get_option('cef_recaptcha_site_key')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row"><label for="cef_recaptcha_secret_key"><?php esc_html_e('6LefjrQqAAAAABrVdlKyI-_mjkw0KG3Pco8QJS4H', 'custom-enquiry-form'); ?></label></th>
                    <td><input type="text" id="cef_recaptcha_secret_key" name="cef_recaptcha_secret_key" value="<?php echo esc_attr(get_option('cef_recaptcha_secret_key')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}
