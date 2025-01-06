class Slider {
  constructor(containerSelector, options = {}) {
    this.container = document.querySelector(containerSelector);

    this.slides = this.container.querySelectorAll('.slide');

    this.currentIndex = 0;
    this.autoPlay = options.autoPlay || false;
    this.autoPlayInterval = options.autoPlayInterval || 3000;

    this.init();
  }

  init() {
    this.setupNavigationButtons();
    this.updateSlides();

    if (this.autoPlay) {
      this.startAutoPlay();
    }
  }

  setupNavigationButtons() {
    const prevButton = this.container.querySelector('.prev-button');
    const nextButton = this.container.querySelector('.next-button');

    if (!prevButton || !nextButton) {
      console.error("Navigation buttons '.prev-button' and '.next-button' not found in the slider container.");
      return;
    }

    prevButton.addEventListener('click', () => this.prevSlide());
    nextButton.addEventListener('click', () => this.nextSlide());
  }

  updateSlides() {
    // Снять класс active со всех слайдов
    this.slides.forEach((slide, index) => {
      slide.classList.toggle('active', index === this.currentIndex);
    });

    // Обновить высоту контейнера в соответствии с активным слайдом
    const activeSlide = this.slides[this.currentIndex];
    if (activeSlide) {
      const height = activeSlide.offsetHeight;
      this.container.style.height = `${height}px`;
      // this.container.style.height = `auto`;
    }
  }

  prevSlide() {
    this.currentIndex = (this.currentIndex - 1 + this.slides.length) % this.slides.length;
    this.updateSlides();
  }

  nextSlide() {
    this.currentIndex = (this.currentIndex + 1) % this.slides.length;
    this.updateSlides();
  }

  startAutoPlay() {
    this.autoPlayIntervalId = setInterval(() => this.nextSlide(), this.autoPlayInterval);
  }

  stopAutoPlay() {
    clearInterval(this.autoPlayIntervalId);
  }
}

class ResponsiveSlider extends Slider {
  constructor(containerSelector, options = {}) {
    super(containerSelector, options);
    this.updateSlideCount();
    window.addEventListener('resize', () => this.updateSlideCount());
  }

  updateSlideCount() {
    const width = window.innerWidth;
    let slidesToShow = 3;

    if (width <= 1200) {
      slidesToShow = 2;
    }
    if (width <= 640) {
      slidesToShow = 1;
    }

    this.slides.forEach(slide => {
      slide.style.flex = `0 0 ${100 / slidesToShow }%`;
    });

    this.updateSlides();
  }
}

// Инициализация слайдеров
document.addEventListener('DOMContentLoaded', () => {
  ['.slider1', '.slider2', '.slider4', '.slider5', '.slider6'].forEach(selector => {
    const container = document.querySelector(selector);
    if (container) {
      new ResponsiveSlider(selector, { autoPlay: false, autoPlayInterval: 5000 });
    }
  });
});

