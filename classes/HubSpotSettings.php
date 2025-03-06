<?php

class HubSpotSettings {
    private static $instance = null;

    private function __construct() {
        add_action('admin_menu', [$this, 'add_plugin_page'], 99);
        add_action('admin_init', [$this, 'page_init']);
    }

    public static function get_instance() {
        if (null === self::$instance) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function add_plugin_page() {
        add_submenu_page(
            'woocommerce',
            'HubSpot API Settings',
            'HubSpot API',
            'manage_options',
            'pryvus-hubspot-settings',
            [$this, 'create_admin_page']
        );
    }

    public function create_admin_page() {
        ?>
        <div class="wrap">
            <h1>HubSpot API Settings</h1>
            <form method="post" action="options.php">
                <?php
                settings_fields('pryvus_hubspot_option_group');
                do_settings_sections('pryvus-hubspot-settings-admin');
                submit_button();
                ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'pryvus_hubspot_option_group',
            'pryvus_hubspot_api_key',
            [$this, 'sanitize']
        );

        add_settings_section(
            'pryvus_hubspot_setting_section',
            'Settings',
            null,
            'pryvus-hubspot-settings-admin'
        );

        add_settings_field(
            'pryvus_hubspot_api_key',
            'API Key',
            [$this, 'api_key_callback'],
            'pryvus-hubspot-settings-admin',
            'pryvus_hubspot_setting_section'
        );
    }

    public function sanitize($input) {
        $encrypted_input = $input;
        return sanitize_text_field($encrypted_input);
    }

    public function api_key_callback() {
        printf(
            '<input type="text" id="pryvus_hubspot_api_key" name="pryvus_hubspot_api_key" value="%s" />',
            esc_attr(get_option('pryvus_hubspot_api_key'))
        );
    }
}
