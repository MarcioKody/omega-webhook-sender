
function OGSsafeSend(url, params) {
    if (navigator.sendBeacon) {
        navigator.sendBeacon(url, params);
    } else {
        fetch(url, {
            method: 'POST',
            body: params,
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            keepalive: true,
            credentials: 'same-origin'
        });
    }
}

document.addEventListener('wpcf7mailsent', function (event) {

    try {
        let params = new URLSearchParams();
        
        event.detail.inputs.forEach(input => {
            params.append(input.name, input.value);
        });

        params.append('nonce', omega_gas_sender_obj.nonce);
        params.append('action', 'send_to_gas');

        OGSsafeSend(
            `${omega_gas_sender_obj.ajaxurl}`,
            params
        );
    }
    catch (e) {
        let params = new URLSearchParams();

        params.append('error', e.message ?? "Wystąpił błąd");
        params.append('action', 'send_to_gas');

        OGSsafeSend(
            `${omega_gas_sender_obj.ajaxurl}`,
            params
        );

        throw e;
    }

}, false);

