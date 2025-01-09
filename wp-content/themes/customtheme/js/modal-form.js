jQuery(document).ready(function ($) {
    // Получаем элементы
    const orderModal = document.getElementById("orderModal");
    // const orderOpenBtn = document.getElementById("orderOpenBt    n");
    const orderCloseBtn = document.getElementById("order-close-btn");
    const orderSendCloseBtn = document.getElementById("order-send-close-btn");
    const orderInput = document.getElementById("order-form-posluga-header"); // Input field

    // Открытие модального окна
    // orderOpenBtn.onclick = () => {
    //     document.body.style.overflow = 'hidden';
    //     orderModal.style.display = "flex"; // Показываем модальное окно
    //     setTimeout(() => {
    //         orderModal.classList.add("open"); // Добавляем класс для анимации
    //     }, 10); // Небольшая задержка для активации анимации
    // };

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
        if (firstName.trim() !== "" && phoneNumber.trim() !== "") {
            closeModalForm();
            alert("Дякую, очікуйте на дзвінок від нашого менеджера.");
        } else {
            alert("Будь-ласка заповніть форму.");
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
        orderInput.value = "Оптова закупка"; // Set text for first button
        openFormModal();
    });
    // orderInput.setAttribute("readonly", true); // Prevent typing in the input
});