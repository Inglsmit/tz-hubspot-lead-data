<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

class HubspotHandler {
    private static $instance = null;
    private $api_key;

    private function __construct() {
        if (is_admin()) {
            return;
        }

        $this->api_key = get_option('pryvus_hubspot_api_key');
        add_action('woocommerce_thankyou', [$this, 'send_order_data_to_hubspot']);
    }

    public static function get_instance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function send_order_data_to_hubspot($order_id) {
        if (!$this->api_key) {
            return;
        }

        $order = wc_get_order($order_id);
        $lead_data = [
            "properties" => [
                "email" => $order->get_billing_email(),
                "firstname" => $order->get_billing_first_name(),
                "lastname" => $order->get_billing_last_name(),
                "phone" => $order->get_billing_phone()
            ]
        ];

        $response = wp_remote_post('https://api.hubapi.com/crm/v3/objects/contacts', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->api_key
            ],
            'body' => json_encode($lead_data)
        ]);

        if (is_wp_error($response)) {
            $error_message = "HubSpot API Critical error: {$response->get_error_message()}";
            file_put_contents(WP_CONTENT_DIR . '/debug.log', $error_message . "\n", FILE_APPEND);
        } else {
            if (isset($response['response']['code']) && $response['response']['code'] !== 200 && $response['response']['code'] !== 201) {
                $error_code = $response['response']['code'];
                $response_message = isset($response['body']) ? json_decode($response['body'], true)['message'] ?? 'Unknown error' : 'Unknown error';
                $error_message = "HubSpot API error: {$error_code}: {$response_message}";
                file_put_contents(WP_CONTENT_DIR . '/debug.log', $error_message . "\n", FILE_APPEND);
            } else {
                $note = "Lead data sent to HubSpot";
                $order->add_order_note($note);

//                $success_message = 'HubSpot API: data successful sent.';
//                file_put_contents(WP_CONTENT_DIR . '/debug.log', $success_message . "\n", FILE_APPEND);
            }
        }

    }
}
