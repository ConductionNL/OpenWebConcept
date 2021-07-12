<?php

namespace OWC\ZGW\Classes;

class ZGWPluginAdminSettings
{
    public function __construct()
    {
        // The Admin menu Item
        add_action('admin_menu', [$this, 'zgw_options_page']);

        // Initiating the settings page
        add_action('admin_init', [$this, 'wporg_settings_init']);
    }

    /**
     * Lets define the basic settings page
     */
    public function zgw_options_page_html()
    {
        // check user capabilities
        if (!current_user_can('manage_options')) {
            return;
        }

?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                // output security fields for the registered setting "wporg_options"
                settings_fields('zgw_options');

                // output setting sections and their fields
                // (sections are registered for "wporg", each field is registered to a specific section)
                do_settings_sections('zgw_configuration');
                // output save settings button
                submit_button(__('Save Settings', 'textdomain'));
                ?>
            </form>
        </div>
    <?php
    }

    /**
     * The settings menu item
     */
    public function zgw_options_page()
    {
        add_submenu_page(
            'options-general.php',
            'ZGW',
            'ZGW',
            'manage_options',
            'zgw',
            [$this, 'zgw_options_page_html']
        );
    }

    /**
     * Lets define some settings
     */
    public function wporg_settings_init()
    {
        // register a new setting for "reading" page
        register_setting('zgw_options', 'zgw_api_zaken_url');
        register_setting('zgw_options', 'zgw_api_catalogus_url');
        register_setting('zgw_options', 'zgw_api_client_id');
        register_setting('zgw_options', 'zgw_api_client_secret');

        // register a new section in the "reading" page
        add_settings_section(
            'default', // id
            'Zaakgericht werken API connection', // title
            [$this, 'wporg_settings_section_callback'], // callback
            'zgw_configuration' // page
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'zgw_api_zaken_url',
            'Url of the ca API',
            [$this, 'zgw_api_zaken_url_field_callback'],
            'zgw_configuration',
            'default'
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'zgw_api_catalogus_url',
            'Url of the catalogus API',
            [$this, 'zgw_api_catalogus_url_field_callback'],
            'zgw_configuration',
            'default'
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'zgw_api_client_id',
            'This applications client id for ZGW',
            [$this, 'zgw_api_client_id_field_callback'],
            'zgw_configuration',
            'default'
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'zgw_api_client_secret',
            'This application clients secret for zgw',
            [$this, 'zgw_api_client_secret_field_callback'],
            'zgw_configuration',
            'default'
        );
    }

    /**
     * callback functions
     */

    // section content cb
    public function wporg_settings_section_callback()
    {
        echo '<p>In order to use ZGW apis you must provide api location and credentials.</p>';
    }


    public function zgw_api_zaken_url_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('zgw_api_zaken_url');
        // output the field
    ?>
        <input type="text" name="zgw_api_zaken_url" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
<?php
    }


    public function zgw_api_catalogus_url_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('zgw_api_catalogus_url');
        // output the field
        ?>
        <input type="text" name="zgw_api_catalogus_url" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
        <?php
    }


    public function zgw_api_client_id_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('zgw_api_client_id');
        // output the field
        ?>
        <input type="text" name="zgw_api_client_id" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
        <?php
    }


    public function zgw_api_client_secret_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('zgw_api_client_secret');
        // output the field
        ?>
        <input type="text" name="zgw_api_client_secret" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
        <?php
    }
}
