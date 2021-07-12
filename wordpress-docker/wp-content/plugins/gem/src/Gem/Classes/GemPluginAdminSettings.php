<?php

namespace OWC\Gem\Classes;

class GemPluginAdminSettings
{
    public function __construct()
    {
        // The Admin menu Item
        add_action('admin_menu', [$this, 'gem_options_page']);

        // Initiating the settings page
        add_action('admin_init', [$this, 'wporg_settings_init']);
    }

    /**
     * Lets define the basic settings page
     */
    public function gem_options_page_html()
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
                settings_fields('gem_options');

                // output setting sections and their fields
                // (sections are registered for "wporg", each field is registered to a specific section)
                do_settings_sections('gem_configuration');
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
    public function gem_options_page()
    {
        add_submenu_page(
            'options-general.php',
            'Gem',
            'Gem',
            'manage_options',
            'gem',
            [$this, 'gem_options_page_html']
        );
    }

    /**
     * Lets define some settings
     */
    public function wporg_settings_init()
    {
        // register a new setting for "reading" page
        register_setting('gem_options', 'gem_organization');

        // register a new section in the "reading" page
        add_settings_section(
            'default', // id
            'Configuration', // title
            [$this, 'wporg_settings_section_callback'], // callback
            'gem_configuration' // page
        );

        // register a new field in the "wporg_settings_section" section, inside the "reading" page
        add_settings_field(
            'gem_organization_field',
            'Organization',
            [$this, 'gem_organization_field_callback'],
            'gem_configuration',
            'default'
        );
    }

    /**
     * callback functions
     */

    // section content cb
    public function wporg_settings_section_callback()
    {
        echo '<p>In order to use gem you must provide your gem organization.</p>';
    }


    public function gem_organization_field_callback()
    {
        // get the value of the setting we've registered with register_setting()
        $setting = get_option('gem_organization');
        // output the field
    ?>
        <input type="text" name="gem_organization" value="<?php echo isset($setting) ? esc_attr($setting) : ''; ?>">
<?php
    }
}
