<?php

/**
 * Plugin Name: Omega CF7 AJAX Webhook Sender
 * Description: Allows to send Contact Form 7 Form data asynchronously to webhook
 * Version: 1.0
 * Author: Marcin Hopa
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

add_action('wp_ajax_send_to_gas', 'send_to_gas');
add_action('wp_ajax_nopriv_send_to_gas', 'send_to_gas'); 

add_action('wp_enqueue_scripts', 'enqueue_omega_gas_sender');

function enqueue_omega_gas_sender()
{
    wp_register_script(
        'omega-gas-sender',
        plugin_dir_url(__FILE__) . 'js/omega-gas-sender.js'
    );

    wp_localize_script(
        'omega-gas-sender',
        'omega_gas_sender_obj',
        [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('ogs-ajax-nonce')
        ]
    );

    wp_enqueue_script('omega-gas-sender');
}

function send_to_gas()
{
    try {
        $nonce = $_POST['nonce'];
        if (!wp_verify_nonce($nonce, 'ogs-ajax-nonce')) {
            throw new \Exception("Nonce verification failed");
        }

        if (!empty($_POST)) {

            $gas_url = get_option('omega_gas_sender_webhook_url');
            if (!$gas_url) {
                throw new \Exception("Brak skonfigurowanego webhooka GAS w ustawieniach");
            }

            $data = $_POST;

            $response = wp_remote_post($gas_url, array(
                'body'    => json_encode($data),
                'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
                'timeout' => 300,
            ));

            if (is_wp_error($response)) {
                throw new \Exception($response->get_error_message());
            }
        } else {
            throw new \Exception("Brak obiektu POST");
        }
    } catch (\Throwable $e) {
        $gas_url = get_option('omega_gas_sender_webhook_url');

        $response = wp_remote_post($gas_url, array(
            'body'    => json_encode(["error_message" => $e->getMessage()]),
            'headers' => ['Content-Type' => 'application/json; charset=utf-8'],
            'timeout' => 300,
        ));

        throw $e;
    }
}



add_action('admin_menu', function () {
    add_options_page(
        'Omega GAS Sender',
        'Omega GAS Sender',
        'manage_options',
        'omega-gas-sender',
        'omega_gas_sender_settings_page'
    );
});

function omega_gas_sender_settings_page()
{
    if (isset($_POST['omega_gas_sender_webhook_url'])) {
        update_option('omega_gas_sender_webhook_url', sanitize_text_field($_POST['omega_gas_sender_webhook_url']));
        echo '<div class="updated"><p>URL zapisany!</p></div>';
    }

    $current = get_option('omega_gas_sender_webhook_url', '');
?>
    <div class="wrap">
        <h1>CF7 → GAS Webhook</h1>
        <form method="post">
            <label for="omega_gas_sender_webhook_url">Adres webhooka GAS:</label><br>
            <input type="text" name="omega_gas_sender_webhook_url" id="omega_gas_sender_webhook_url"
                value="<?php echo esc_attr($current); ?>" size="80">
            <?php submit_button('Zapisz'); ?>
        </form>
    </div>
<?php
}
