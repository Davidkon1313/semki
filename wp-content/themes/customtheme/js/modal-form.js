jQuery(document).ready(function ($) {
    // Получаем элементы
    const orderModal = document.getElementById("orderModal");
    // const orderOpenBtn = document.getElementById("orderOpenBt    n");
    const orderCloseBtn = document.getElementById("order-close-btn");
    const orderSendCloseBtn = document.getElementById("order-send-close-btn");
    const orderInput = document.getElementById("order-form-posluga-header"); // Input field

    function openFormModal() {
        document.body.style.overflow = 'hidden';
        orderModal.style.display = "flex"; // Показываем модальное окно
        setTimeout(() => {
            orderModal.classList.add("open"); // Добавляем класс для анимации
        }, 10); // Небольшая задержка для активации анимации
    };

    orderSendCloseBtn.onclick = () => {
        const firstName = $('#input_name_form').val();
        const phoneNumber = $('#input_name_tel_form').val();
        const service = $('#order-form-posluga-header').val();
        const submitButton = $('#order-send-close-btn');
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

            $.ajax({
                url: my_ajax_object.ajax_url,
                method: 'POST',
                data: {
                    action: 'send_email',
                    first_name: firstName,
                    phone_number: phoneNumber,
                    service: service
                },
                success: function (response) {
                    submitButton.text('Замовити').prop('disabled', false);
                    // alert("Дякую, очікуйте на дзвінок від нашого менеджера.");
                    alert_Message_container.innerHTML =
                        "Дякую, очікуйте на дзвінок від нашого менеджера.";
                    alertBox.style.display = "block";
                    closeModalForm();
                },
                error: function () {
                    // alert('Failed to send email.');
                    alert_Message_container.innerHTML =
                        "Помилка запиту. Будь ласка спробуйте ще раз.";
                    alertBox.style.display = "block";
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
        orderModal.classList.remove("open");
        orderModal.classList.add("close");
        setTimeout(() => {
            orderModal.style.display = "none";
            orderModal.classList.remove("close");
        }, 300);
    };

    // Обработка кликов по кнопкам
    $("#btn-want-to-coop").click(function () {
        // Set the "Співпрацювання" text into the order input
        orderInput.value = "Співпрацювання"; // Set text for first button

        // Open the modal form
        openFormModal();

        // Get the values from the form inputs
        const firstName = $('#input_name_form').val(); // Get the first name value
        const phoneNumber = $('#input_name_tel_form').val(); // Get the phone number value

        // Set these values into the corresponding input fields in the modal
        $('#orderModal input[placeholder="Ваше імʼя"]').val(firstName); // Set the first name in modal input
        $('#orderModal input[placeholder="Номер телефону"]').val(phoneNumber); // Set the phone number in modal input
    });


    $("#calib-ta-cleaning").click(function () {
        orderInput.value = "Співпрацювання"; // Set text for first button
        openFormModal();
    });
    $("#calib-ta-cleaning-mob").click(function () {
        orderInput.value = "Співпрацювання"; // Set text for first button
        openFormModal();
    });

    $("#btn-calib").click(function () {
        orderInput.value = "Калібрування"; // Set text for first button
        openFormModal();
    });

    $("#btn-aspiratoin").click(function () {
        orderInput.value = "Аспірація"; // Set text for first button
        openFormModal();
    });
    $("#btn-pnevmo-stil").click(function () {
        orderInput.value = "Пневмо стіл"; // Set text for first button
        openFormModal();
    });
    $("#btn-photo-sort").click(function () {
        orderInput.value = "Фото сторування"; // Set text for first button
        openFormModal();
    });
    $("#btn-obezpil").click(function () {
        orderInput.value = "Обезпилення"; // Set text for first button
        openFormModal();
    });
    $("#btn-save-agro").click(function () {
        orderInput.value = "Зберігання"; // Set text for first button
        openFormModal();
    });
    $("#btn-buy-opt").click(function () {
        orderInput.value = "оптове замовлення індивідуально через менеджера"; // Set text for first button
        openFormModal();
    });
    // orderInput.setAttribute("readonly", true); // Prevent typing in the input

    const inputField = document.getElementById("input_name_tel_form");

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