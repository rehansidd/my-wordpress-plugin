<?php
function cef_admin_menu() {
    add_menu_page('Enquiry Form Settings', 'Enquiry Form', 'manage_options', 'cef-settings', 'cef_settings_page', 'dashicons-email');
}
add_action('admin_menu', 'cef_admin_menu');

function cef_settings_page() {
    if (isset($_POST['submit'])) {
        update_option('cef_mail_type', $_POST['mail_type']);
        update_option('cef_smtp_host', $_POST['smtp_host']);
        update_option('cef_smtp_port', $_POST['smtp_port']);
        update_option('cef_smtp_user', $_POST['smtp_user']);
        update_option('cef_smtp_pass', $_POST['smtp_pass']);
        update_option('cef_to_email', $_POST['to_email']);
        update_option('cef_subject', $_POST['subject']);
        update_option('cef_message', $_POST['message']);
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
                <input type="text" name="smtp_host" value="<?php echo get_option('cef_smtp_host'); ?>"><br><br>
                <label for="smtp_port">SMTP Port:</label>
                <input type="text" name="smtp_port" value="<?php echo get_option('cef_smtp_port'); ?>"><br><br>
                <label for="smtp_user">SMTP User:</label>
                <input type="text" name="smtp_user" value="<?php echo get_option('cef_smtp_user'); ?>"><br><br>
                <label for="smtp_pass">SMTP Password:</label>
                <input type="password" name="smtp_pass" value="<?php echo get_option('cef_smtp_pass'); ?>"><br><br>
            </div>

            <h2>Email Details</h2>
            <label for="to_email">To Email:</label>
            <input type="email" name="to_email" value="<?php echo get_option('cef_to_email'); ?>"><br><br>
            <label for="subject">Subject:</label>
            <input type="text" name="subject" value="<?php echo get_option('cef_subject'); ?>"><br><br>
            <label for="message">Message Template:</label>
            <textarea name="message"><?php echo get_option('cef_message'); ?></textarea><br><br>

            <input type="submit" name="submit" value="Save Settings">
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
