jQuery(document).ready(function ($) {
    // Получаем элементы
    const orderModal = document.getElementById("feedbackModal");
    const orderCloseBtn = document.getElementById("feedback-close-btn");
    const sendFeedbackBtn = document.getElementById("btn-send-feedback");

    function openFormModal() {
        document.body.style.overflow = 'hidden';
        orderModal.style.display = "flex"; // Показываем модальное окно
        setTimeout(() => {
            orderModal.classList.add("open"); // Добавляем класс для анимации
        }, 10); // Небольшая задержка для активации анимации
    };

    sendFeedbackBtn.onclick = () => {
        const fname = $('#feedback_name').val();
        const ftext = $('#feedback_text').val();
        const submitButton = $('#btn-send-feedback');
        if (fname.trim() !== "" && ftext.trim() !== "") {
            submitButton.text('Зачекайте...').prop('disabled', true);
            $.ajax({
                url: my_ajax_object.ajax_url,
                method: 'POST',
                data: {
                    action: 'send_feedback',
                    fname: fname,
                    ftext: ftext
                },
                success: function (response) {
                    submitButton.text('Замовити').prop('disabled', false);
                    alert("Дякую, очікуйте на дзвінок від нашого менеджера.");
                    closeForm();
                },
                error: function () {
                    alert('Failed to send email.');
                }
            });
        } else {
            alert("Будь-ласка заповніть форму.");
        }
    };

    function closeForm() {
        document.body.style.overflow = 'auto';
        orderModal.classList.remove("open");
        orderModal.classList.add("close");
        setTimeout(() => {
            orderModal.style.display = "none";
            orderModal.classList.remove("close");
        }, 300);
    };

    // Закрытие модального окна
    orderCloseBtn.onclick = () => {
        closeForm();
    };

    $("#btn-add-feedback").click(function () {
        openFormModal();
    });
});