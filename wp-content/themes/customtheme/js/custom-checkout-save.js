jQuery(document).ready(function ($) {
    let timeout;

    // Detect changes in checkout fields
    $('#billing_first_name, #billing_last_name, #billing_email, #billing_phone').on('input', function () {
        clearTimeout(timeout);

        timeout = setTimeout(function () {
            // Collect all checkout data
            const checkoutData = {};
            $('form.woocommerce-checkout').serializeArray().forEach((field) => {
                checkoutData[field.name] = field.value;
            });

            // Send data to server via AJAX
            $.ajax({
                type: 'POST',
                url: ajax_object.ajax_url,
                data: {
                    action: 'save_checkout_data',
                    checkout_data: checkoutData,
                },
                success: function (response) {
                    if (response.success) {
                        console.log(response.data.message);
                    } else {
                        console.error(response.data.message);
                    }
                },
                error: function () {
                    console.error('Error saving checkout data.');
                },
            });
        }, 500); // Delay to reduce frequent calls
    });
});