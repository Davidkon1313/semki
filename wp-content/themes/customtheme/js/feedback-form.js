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
        document.body.style.overflow = 'auto';
        orderModal.classList.remove("open"); // Убираем анимацию
        orderModal.classList.add("close"); // Начинаем анимацию исчезновения
        setTimeout(() => {
            orderModal.style.display = "none"; // Скрываем окно после завершения анимации
            orderModal.classList.remove("close"); // Убираем класс для следующего открытия
        }, 300); // Время, равное длительности анимации
    };

    // Закрытие модального окна
    orderCloseBtn.onclick = () => {
        document.body.style.overflow = 'auto';
        orderModal.classList.remove("open"); // Убираем анимацию
        orderModal.classList.add("close"); // Начинаем анимацию исчезновения
        setTimeout(() => {
            orderModal.style.display = "none"; // Скрываем окно после завершения анимации
            orderModal.classList.remove("close"); // Убираем класс для следующего открытия
        }, 300); // Время, равное длительности анимации
    };

    $("#btn-add-feedback").click(function () {
        openFormModal();
    });
});