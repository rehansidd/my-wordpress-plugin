<?php
function cef_admin_menu() {
    add_menu_page('Enquiry Form Settings', 'Enquiry Form', 'manage_options', 'cef-settings', 'cef_settings_page', 'dashicons-email');
}
add_action('admin_menu', 'cef_admin_menu');

function cef_settings_page() {
    if (isset($_POST['submit'])) {
        // Save email settings
        update_option('cef_mail_type', sanitize_text_field($_POST['mail_type']));
        update_option('cef_smtp_host', sanitize_text_field($_POST['smtp_host']));
        update_option('cef_smtp_port', sanitize_text_field($_POST['smtp_port']));
        update_option('cef_smtp_user', sanitize_text_field($_POST['smtp_user']));
        update_option('cef_smtp_pass', sanitize_text_field($_POST['smtp_pass']));
        update_option('cef_to_email', sanitize_email($_POST['to_email']));
        update_option('cef_subject', sanitize_text_field($_POST['subject']));
        update_option('cef_message', sanitize_textarea_field($_POST['message']));

        echo '<div class="updated"><p>Settings saved successfully.</p></div>';
    }

    if (isset($_POST['test_email'])) {
        // Test email sending
        $to = get_option('cef_to_email');
        $subject = "Test Email from Enquiry Form Settings";
        $message = "This is a test email to verify your email settings.";
        $headers = ['Content-Type: text/html; charset=UTF-8'];

        if (wp_mail($to, $subject, $message, $headers)) {
            echo '<div class="updated"><p>Test email sent successfully to ' . esc_html($to) . '.</p></div>';
        } else {
            echo '<div class="error"><p>Failed to send test email. Please check your email settings.</p></div>';
        }
    }
    ?>
    <div class="wrap">
        <h1>Enquiry Form Settings</h1>
        <form method="POST">
            <h2>Email Settings</h2>
            <label for="mail_type">Mail Type:</label>
            <select name="mail_type" id="mail_type">
                <option value="wp_mail" <?php selected(get_option('cef_mail_type'), 'wp_mail'); ?>>WP Mail</option>
                <option value="smtp" <?php selected(get_option('cef_mail_type'), 'smtp'); ?>>SMTP</option>
            </select><br><br>

            <div id="smtp-settings" <?php if (get_option('cef_mail_type') == 'wp_mail') echo 'style="display:none;"'; ?>>
                <label for="smtp_host">SMTP Host:</label>
                <input type="text" name="smtp_host" value="<?php echo esc_attr(get_option('cef_smtp_host')); ?>"><br><br>
                <label for="smtp_port">SMTP Port:</label>
                <input type="text" name="smtp_port" value="<?php echo esc_attr(get_option('cef_smtp_port')); ?>"><br><br>
                <label for="smtp_user">SMTP User:</label>
                <input type="text" name="smtp_user" value="<?php echo esc_attr(get_option('cef_smtp_user')); ?>"><br><br>
                <label for="smtp_pass">SMTP Password:</label>
                <input type="password" name="smtp_pass" value="<?php echo esc_attr(get_option('cef_smtp_pass')); ?>"><br><br>
            </div>

            <h2>Email Details</h2>
            <label for="to_email">To Email:</label>
            <input type="email" name="to_email" value="<?php echo esc_attr(get_option('cef_to_email')); ?>"><br><br>
            <label for="subject">Subject:</label>
            <input type="text" name="subject" value="<?php echo esc_attr(get_option('cef_subject')); ?>"><br><br>
            <label for="message">Message Template:</label>
            <textarea name="message"><?php echo esc_textarea(get_option('cef_message')); ?></textarea><br><br>

            <input type="submit" name="submit" value="Save Settings">
            <input type="submit" name="test_email" value="Send Test Email">
        </form>
    </div>
    <script>
        jQuery('#mail_type').change(function() {
            if (jQuery(this).val() == 'smtp') {
                jQuery('#smtp-settings').show();
            } else {
                jQuery('#smtp-settings').hide();
            }
        });
    </script>
    <?php
}
?>
