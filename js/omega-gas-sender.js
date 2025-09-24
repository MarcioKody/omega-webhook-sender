document.addEventListener('wpcf7submit', function (event) {

    let formData = new FormData();
    event.detail.inputs.forEach(input => {
        formData.append(input.name, input.value);
    });

    formData.append('nonce', omega_gas_sender_obj.nonce);

    // Wywołanie proxy w WP
    fetch(`${omega_gas_sender_obj.ajaxurl}?action=send_to_gas`, {
        method: 'POST',
        body: formData
    })
        .then(r => r.json())
        .then(data => {
            console.log("Wysłane do GAS:", data);
        })
        .catch(err => console.error("Błąd:", err));
}, false);