<?php
/*
Plugin Name: WooCommerce HubSpot Integration
Plugin URI: https://pryvus.com/plugins/woocommerce-hubspot-integration
Description: Integrates WooCommerce with HubSpot to send lead data after a successful order.
Version: 1.0.0
Author: PRYVUS COMPANY
Author URI: https://pryvus.com
Text Domain: pryvus
*/

// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if(!defined('WHLDP_PLUGIN_DIR')){
    define('WHLDP_PLUGIN_DIR', plugin_dir_path(__FILE__));
}

if(!class_exists('PryvusWooHubspotLeadDataPlugin')) {
    class PryvusWooHubspotLeadDataPlugin
    {
        private $plugin_dir;

        public function __construct() {
            $this->plugin_dir = WHLDP_PLUGIN_DIR;
        }

        function init_plugin() {
            require_once $this->plugin_dir . 'classes/HubSpotSettings.php';
            HubSpotSettings::get_instance();

            require_once $this->plugin_dir . 'classes/HubspotHandler.php';
            HubspotHandler::get_instance();
        }

        // Function will launch only once
        function activate_plugin() {
            if (!class_exists('WooCommerce')) {
                deactivate_plugins(plugin_basename(__FILE__));
                $error_message = __('WooCommerce needs to be installed and activated before activating this plugin.', 'pryvus');
                $btn_text = __('Go back', 'pryvus');
                wp_die('<div class="notice notice-error is-dismissible"><p>' . $error_message . ' <a href="' . esc_url(admin_url('plugins.php')) . '">' . $btn_text . '</a></p></div>');
            }
        }
        function deactivate_plugin() {
            delete_option('pryvus_hubspot_api_key');
        }
    }

    $WooHubspotLeadDataPlugin = new PryvusWooHubspotLeadDataPlugin();

    // Activation hooks
    register_activation_hook(__FILE__, [$WooHubspotLeadDataPlugin, 'activate_plugin']);

    // Deactivation hooks
    register_deactivation_hook(__FILE__, [$WooHubspotLeadDataPlugin, 'deactivate_plugin']);

    // Initialize the plugin
    add_action('init', [$WooHubspotLeadDataPlugin, 'init_plugin']);

}
