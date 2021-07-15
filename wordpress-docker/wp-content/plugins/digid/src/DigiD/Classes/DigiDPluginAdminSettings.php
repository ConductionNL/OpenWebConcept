<?php

namespace OWC\DigiD\Classes;

class DigiDPluginAdminSettings
{
    public function __construct()
    {
        // The Admin menu Item
        add_action('admin_menu', [$this, 'digid_options_page']);

        // Initiating the settings page
        add_action('admin_init', [$this, 'wporg_settings_init']);
    }

    /**
     * Lets define the basic settings page
     */
    public function digid_options_page_html()
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
                settings_fields('digid_options');
                // output setting sections and their fields
                // (sections are registered for "wporg", each field is registered to a specific section)
                do_settings_sections('digid_api');
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
    public function digid_options_page()
    {
        add_submenu_page(
            'options-general.php',
            'DigiD',
            'DigiD',
            'manage_options',
            'digid',
            [$this, 'digid_options_page_html']
        );
    }

    /**
     * Lets define some settings
     */
    public function wporg_settings_init()
    {
        // register a new setting for "reading" page
        register_setting('digid_options', 'digid_domain',['type'=>'string','description'=>'The DigiD domain to use','default'=>'https://digispoof.demodam.nl']);
        register_setting('digid_options', 'digid_certificate',['type'=>'string','description'=>'The certicate used to login to digispoof','default'=>'']);
        register_setting('digid_options', 'digid_type',['type'=>'string','description'=>'The login method used for DigiD (iether url or form)','default'=>'url']);
        register_setting('digid_options', 'digid_brpkey',['type'=>'string','description'=>'The key used to retrieve a brp person', 'default'=>'']);
        register_setting('digid_options', 'digid_brplocation',['type'=>'string','description'=>'The url used to retrieve a brp person', 'default'=>'']);

        // register a new section in the "reading" page
        add_settings_section(
            'default', // id
            'DigiD  Configuration', // title
            [$this, 'wporg_settings_section_callback'], // callback
            'digid_api' // page
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'digid_domain', // id
            'The DigiD domain to use',  // title
            [$this, 'digid_domain_field_callback'], //callback
            'digid_api',
            'default'
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'digid_certificate',
            'The certificate to use',
            [$this, 'digid_certificate_field_callback'],
            'digid_api',
            'default'
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'digid_type',
            'The type of login',
            [$this, 'digid_type_field_callback'],
            'digid_api',
            'default'
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'digid_brpkey',
            'The key used to retrieve a brp person (required)',
            [$this, 'digid_brpkey_field_callback'],
            'digid_api',
            'default'
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'digid_brplocation',
            'The url used to retrieve a brp person (required)',
            [$this, 'digid_brplocation_field_callback'],
            'digid_api',
            'default'
        );
    }

    /**
     * callback functions
     */

    // section content cb
    public function wporg_settings_section_callback()
    {
        echo '<p>In order to use DigiD you need to provide you logius credentials, these are default to digispoof.demodam.nl for testing purposes.</p>';
    }

    // field content cb
    public function digid_domain_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('digid_domain');
        // output the field
    ?>
        <input type="text" name="digid_domain" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
    <?php
    }

    public function digid_certificate_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('digid_certificate');
        // output the field
    ?>
        <input type="text" name="digid_certificate" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
    <?php
    }

    public function digid_type_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('digid_type');
        // output the field
    ?>
        <input type="text" name="digid_type" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
<?php
    }

    public function digid_brpkey_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('digid_brpkey');
        // output the field
    ?>
        <input type="text" name="digid_brpkey" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
<?php
    }

    public function digid_brplocation_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('digid_brplocation');
        // output the field
    ?>
        <input type="text" name="digid_brplocation" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
<?php
    }
}
