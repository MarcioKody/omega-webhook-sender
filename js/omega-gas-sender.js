document.addEventListener('wpcf7mailsent', function (event) {

    let formData = new FormData();
    event.detail.inputs.forEach(input => {
        formData.append(input.name, input.value);
    });

    formData.append('nonce', omega_gas_sender_obj.nonce);

    // Wywołanie proxy w WP
    navigator.sendBeacon(
        `${omega_gas_sender_obj.ajaxurl}?action=send_to_gas`,
        formData
    );
}, false);