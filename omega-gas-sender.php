<?php

/**
 * Plugin Name: Omega GAS Sender
 * Description: Przekierowuje dane z formularza CF7 przez WordPress AJAX do Google Apps Script.
 * Version: 1.0
 * Author: Marcin Hopa
 */

add_action('wp_ajax_send_to_gas', 'send_to_gas');
add_action('wp_ajax_nopriv_send_to_gas', 'send_to_gas'); // dla niezalogowanych

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
    $nonce = $_POST['nonce'];
    if (!wp_verify_nonce($nonce, 'ogs-ajax-nonce')) {
        wp_send_json_error("Nonce verification failed");
    }

    if (!empty($_POST)) {

        $gas_url = get_option('omega_gas_sender_webhook_url');
        if (!$gas_url) {
            wp_send_json_error("Brak skonfigurowanego webhooka GAS w ustawieniach");
        }

        // Odbieramy dane z AJAX
        $data = $_POST;

        // Wysyłamy dalej do GAS
        $response = wp_remote_post($gas_url, array(
            'body'    => json_encode($data),
            'timeout' => 300,
        ));

        if (is_wp_error($response)) {
            wp_send_json_error($response->get_error_message());
        } else {
            wp_send_json_success(wp_remote_retrieve_body($response));
        }
    } else {
        wp_send_json_error("Brak obiektu POST");
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
