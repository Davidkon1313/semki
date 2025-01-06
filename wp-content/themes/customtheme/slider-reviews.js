// Получаем все слайды и кнопки
const slides = document.querySelectorAll('.slider3__slide');
const prevButton = document.querySelectorAll('.prev');
const nextButton = document.querySelectorAll('.next');

let index = 0;

// Функция для отображения текущего слайда и соседей
function showSlide() {
  const sliderWrapper = document.querySelector('.slider3__wrapper');
  const sliderWidth = sliderWrapper.offsetWidth;

  slides.forEach(slide => {
    slide.classList.remove('active', 'previous', 'next');
    slide.style.transform = '';
  });

  slides[index].classList.add('active');

  const previousIndex = (index - 1 + slides.length) % slides.length;
  slides[previousIndex].classList.add('previous');
  slides[previousIndex].style.transform = `translateX(-${sliderWidth * 0.1}px)`;

  const nextIndex = (index + 1) % slides.length;
  slides[nextIndex].classList.add('next');
  slides[nextIndex].style.transform = `translateX(${sliderWidth * 0.4}px)`;
}

// Навигация влево
function moveToPreviousSlide() {
  index = (index - 1 + slides.length) % slides.length;
  showSlide();
}

// Навигация вправо
function moveToNextSlide() {
  index = (index + 1) % slides.length;
  showSlide();
}

// Назначаем обработчики событий для каждой кнопки
prevButton.forEach(button => {
  button.addEventListener('click', moveToPreviousSlide);
});

nextButton.forEach(button => {
  button.addEventListener('click', moveToNextSlide);
});

// Перерисовка слайдов при изменении размеров экрана
window.addEventListener('resize', showSlide);

// Инициализация слайдера
showSlide();