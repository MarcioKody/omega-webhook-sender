
function OGSsafeSend(url, formData) {
    if (navigator.sendBeacon) {
        navigator.sendBeacon(url, formData);
    } else {
        fetch(url, {
            method: 'POST',
            body: formData,
            keepalive: true
        });
    }
}

document.addEventListener('wpcf7mailsent', function (event) {

    try {
        let formData = new FormData();
        event.detail.inputs.forEach(input => {
            formData.append(input.name, input.value);
        });

        OGSsafeSend(
            `${omega_gas_sender_obj.ajaxurl}?action=send_to_gas&nonce=${omega_gas_sender_obj.nonce}`,
            formData
        );
    }
    catch (e) {
        let formData = new FormData();

        formData.append('error', e.message ?? "Wystąpił błąd");

        OGSsafeSend(
            `${omega_gas_sender_obj.ajaxurl}?action=send_to_gas`,
            formData
        );

        throw e;
    }

}, false);

