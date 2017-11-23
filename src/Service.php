<?php
namespace ThemesPond\Premium;

class Service {
    /**
     * API BASE
     * @var string
     */
    private $service;

    /**
     * Product ID
     * @var string
     */
    private $product;

    public function __construct($base) {
        $this->service = 'http://server-api.dev/api/v1/premium/';
        $this->product = $base;
    }

    public function validate_service() {
        $email = esc_html($_POST['email']);
        $key = esc_html($_POST['key']);
        $url = $this->service . 'product/activate';
        $data = array(
            'timeout' => 3000,
            'body'    => array(
                'email'   => $email,
                'key'     => $key,
                'product' => $this->product
            )
        );
        $response = wp_remote_post($url, $data);
        $status_code = wp_remote_retrieve_response_code($response);

        if ($status_code == 200) {
            $response = wp_remote_retrieve_body($response);
            $response = json_decode($response);
            if (isset($response->data)) {
                $this->create_license($email, $key);
                wp_send_json_success($response);
            }
        }
        wp_send_json_error(esc_html__('Service cannot established. ' . $status_code, 'tp-dashboard'));
    }

    private function create_license($email, $key) {
        $tp_premium_option = get_option('tp_premium_data');
        $tp_premium_option = json_decode($tp_premium_option, true);
        $tp_premium_option[$this->product] = array(
            'product' => $this->product,
            'key'     => $key,
            'email'   => $email,
            'active'  => true
        );
        $set_option = json_encode($tp_premium_option);
        update_option('tp_premium_data', $set_option);
    }

    private function remove_license() {
        $tp_premium_option = get_option('tp_premium_data');
        $tp_premium_option = json_decode($tp_premium_option, true);
        $tp_premium_option[$this->product] = array();
        $set_option = json_encode($tp_premium_option);
        set_option('tp_premium_data', $set_option);
    }
}