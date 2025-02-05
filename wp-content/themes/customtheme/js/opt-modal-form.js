jQuery(document).ready(function ($) {
    // Получаем элементы
    const optModal = document.getElementById("optModal");
    // const orderOpenBtn = document.getElementById("orderOpenBt    n");
    const orderCloseBtn = document.getElementById("opt-close-btn");
    const orderSendCloseBtn = document.getElementById("opt-send-close-btn");
    const orderInput = document.getElementById("order-form-posluga-header"); // Input field

    function openFormModal() {
        document.body.style.overflow = 'hidden';
        optModal.style.display = "flex"; // Показываем модальное окно
        setTimeout(() => {
            optModal.classList.add("open"); // Добавляем класс для анимации
        }, 10); // Небольшая задержка для активации анимации
    };

    orderSendCloseBtn.onclick = () => {
        const firstName = $('#opt_input_name_form').val();
        const phoneNumber = $('#opt_input_name_tel_form').val();
        const service = $('#order-form-opt').val();
        const submitButton = $('#opt-send-close-btn');
        let alertBox =
            document.getElementById("customAlertBox");
        let alert_Message_container =
            document.getElementById("alertMessage");
        let close_img =
            document.querySelector(".close-alert");

        close_img.addEventListener('click', function () {
            alertBox.style.display = "none";
        });
        if (firstName.trim() !== "" && phoneNumber.trim() !== "") {
            submitButton.text('Зачекайте...').prop('disabled', true);

            // Send AJAX request to create order
            $.ajax({
                url: ajax_object.ajax_url,
                type: 'POST',
                data: {
                    action: 'create_woocommerce_order', // Custom action
                    first_name: firstName,
                    phone_number: phoneNumber,
                    service: service,
                },
                success: function (response) {
                    if (response.success) {
                        alert_Message_container.innerHTML = "Ваше замовлення успішно створено!";
                        alertBox.style.display = "block";
                    } else {
                        alert_Message_container.innerHTML = "Виникла помилка при створенні замовлення.";
                        alertBox.style.display = "block";
                    }
                    submitButton.text('Надіслати').prop('disabled', false);
                },
                error: function () {
                    alert_Message_container.innerHTML = "Помилка сервера.";
                    alertBox.style.display = "block";
                    submitButton.text('Надіслати').prop('disabled', false);
                }
            });
        } else {
            // alert("Будь-ласка заповніть форму.");
            alert_Message_container.innerHTML =
                "Будь-ласка заповніть форму.";
            alertBox.style.display = "block";
        }
    };

    orderCloseBtn.onclick = () => closeModalForm();

    function closeModalForm() {
        document.body.style.overflow = 'auto';
        optModal.classList.remove("open");
        optModal.classList.add("close");
        setTimeout(() => {
            optModal.style.display = "none";
            optModal.classList.remove("close");
        }, 300);
    };

    $("#btn-buy-opt").click(function () {
        orderInput.value = "оптове замовлення індивідуально через менеджера"; // Set text for first button
        openFormModal();
    });
    // orderInput.setAttribute("readonly", true); // Prevent typing in the input

    const inputField = document.getElementById("opt_input_name_tel_form");

    inputField.addEventListener("input", (e) => {
        let input = e.target.value.replace(/\D/g, ""); // Keep only digits
        const startValue = "+38";

        // Prevent accidental deletion of the prefix
        if (!input.startsWith("38")) {
            input = "38" + input;
        }

        // Format the input
        let formatted = "+38";
        if (input.length > 2) formatted += `(${input.substring(2, 5)}`;
        if (input.length > 5) formatted += `) ${input.substring(5, 8)}`;
        if (input.length > 8) formatted += ` ${input.substring(8, 10)}`;
        if (input.length > 10) formatted += ` ${input.substring(10, 12)}`;

        // Set the formatted value
        e.target.value = formatted;

        // Handle cursor positioning
        const cursorPosition = e.target.selectionStart;
        setTimeout(() => e.target.setSelectionRange(cursorPosition, cursorPosition), 0);
    });

    // Optional: Prevent invalid keys (non-numeric except for deletion)
    inputField.addEventListener("keydown", (e) => {
        if (
            !(
                e.key === "Backspace" ||
                e.key === "Delete" ||
                e.key === "ArrowLeft" ||
                e.key === "ArrowRight" ||
                /^[0-9]$/.test(e.key)
            )
        ) {
            e.preventDefault();
        }
    });

});