// Получаем все слайды и кнопки
const slidess = document.querySelectorAll('.slider7__slide');
const prevButtonn = document.querySelectorAll('.slider7prev');
const nextButtonn = document.querySelectorAll('.slider7next');

let indexx = 0;

// Функция для отображения текущего слайда и соседей
function showSlidee() {
  // Скрываем все слайды
  slidess.forEach(slide => {
    slide.classList.remove('active', 'previous', 'next');
  });

  // Устанавливаем активный слайд
  slidess[indexx].classList.add('active');

  // Устанавливаем предыдущий слайд
  const previousIndexx = (indexx - 1 + slidess.length) % slidess.length;
  slidess[previousIndexx].classList.add('previous');

  // Устанавливаем следующий слайд
  const nextIndexx = (indexx + 1) % slidess.length;
  slidess[nextIndexx].classList.add('next');
}

// Навигация влево
function moveToPreviousSlidee() {
  indexx = (indexx - 1 + slidess.length) % slidess.length;
  showSlidee();
}

// Навигация вправо
function moveToNextSlidee() {
  indexx = (indexx + 1) % slidess.length;
  showSlidee();
}

// Назначаем обработчики событий для каждой кнопки
prevButtonn.forEach(button => {
  button.addEventListener('click', moveToPreviousSlidee);
});

nextButtonn.forEach(button => {
  button.addEventListener('click', moveToNextSlidee);
});

// Инициализация слайдера
showSlidee();
