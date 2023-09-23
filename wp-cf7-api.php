<?php
/*
Plugin Name: AlbaTrip API
Plugin URI: https://www.fti.edu.al/
Author: FTI
Description: This plugin is built under the guidance of FTI-UPT to provide an API that connects a client's WordPress contact form with their managing system. Data inserted in the form are exposed externally to the system's database.
Version: 0.1.5
Text Domain: cf7_api_sender
*/

// Register plugin settings
function albatrip_api_register_settings() {
    add_option('albatrip_api_db_host', '');
    add_option('albatrip_api_db_name', '');
    add_option('albatrip_api_table_name', '');
    add_option('albatrip_api_db_user', '');
    add_option('albatrip_api_db_password', '');

    register_setting('albatrip_api_options_group', 'albatrip_api_db_host');
    register_setting('albatrip_api_options_group', 'albatrip_api_db_name');
    register_setting('albatrip_api_options_group', 'albatrip_api_table_name');
    register_setting('albatrip_api_options_group', 'albatrip_api_db_user');
    register_setting('albatrip_api_options_group', 'albatrip_api_db_password');
}

// Add a menu item for the plugin settings page
function albatrip_api_menu() {
    add_menu_page(
        'AlbaTrip API Settings',
        'AlbaTrip API',
        'manage_options',
        'albatrip-api-settings',
        'albatrip_api_settings_page'
    );
}

// Create the admin settings page
function albatrip_api_settings_page() {
    ?>
    <div class="wrap">
        <h2>AlbaTrip API Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('albatrip_api_options_group'); ?>
            <?php do_settings_sections('albatrip-api-settings'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Database Host</th>
                    <td><input type="text" name="albatrip_api_db_host" value="<?php echo esc_attr(get_option('albatrip_api_db_host')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Database Name</th>
                    <td><input type="text" name="albatrip_api_db_name" value="<?php echo esc_attr(get_option('albatrip_api_db_name')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Table Name</th>
                    <td><input type="text" name="albatrip_api_table_name" value="<?php echo esc_attr(get_option('albatrip_api_table_name')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Database User</th>
                    <td><input type="text" name="albatrip_api_db_user" value="<?php echo esc_attr(get_option('albatrip_api_db_user')); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Database Password</th>
                    <td><input type="password" name="albatrip_api_db_password" value="<?php echo esc_attr(get_option('albatrip_api_db_password')); ?>" /></td>
                </tr>
            </table>
            <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Connect"></p>
        </form>
    </div>
    <?php
}

// Hook into WordPress actions
add_action('admin_init', 'albatrip_api_register_settings');
add_action('admin_menu', 'albatrip_api_menu');

// Modify your existing code to include database connection settings from the admin
add_action('wpcf7_mail_sent', 'cf7_api_sender');

function cf7_api_sender($contact_form) {
    $title = $contact_form->title;

    if ($title == 'Contact Us') {
        $submission = WPCF7_Submission::get_instance();

        if ($submission) {
            $posted_data = $submission->get_posted_data();
            $name = $posted_data['your-name'];
            $email = $posted_data['your-email'];
            $subject = $posted_data['your-subject'];
            $message = $posted_data['your-message'];

            // Get the database connection settings from the admin settings
            $db_host = get_option('albatrip_api_db_host');
            $db_name = get_option('albatrip_api_db_name');
            $db_user = get_option('albatrip_api_db_user');
            $db_password = get_option('albatrip_api_db_password');

            // Establish the database connection
            $target_db = new wpdb($db_user, $db_password, $db_name, $db_host);

            $table_name = $target_db->prefix . get_option('albatrip_api_table_name');
            $target_db->insert(
                $table_name,
                array(
                    'name' => $name,
                    'email' => $email,
                    'subject' => $subject,
                    'message' => $message
                ),
                array(
                    '%s',
                    '%s',
                    '%s',
                    '%s'
                )
            );
        }
    }
}

// Data names of the form should match the submission posted_data
// i.e. <label>Your e-mail {required}
// [text* your-name] </label> matches $posted_data['your-email'];

