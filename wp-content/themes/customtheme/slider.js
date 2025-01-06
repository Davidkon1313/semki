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
    // Check if the parent element of the slider has an ID of 'blog'
    const isBlogSlider = this.container.parentElement.id === 'blog';

    const width = window.innerWidth;
    let slidesToShow = 3;

    if (width <= 1200) {
      slidesToShow = 2;
    }
    if (width <= 640) {
      slidesToShow = 1;
    }

    // If the parent ID is 'blog', we apply a different logic for active slides
    if (isBlogSlider) {
      this.slides.forEach((slide, index) => {
        slide.classList.remove('active'); // Clear active class from all slides
      });

      // Apply active class to the correct number of slides
      for (let i = 0; i < slidesToShow; i++) {
        const slideIndex = (this.currentIndex + i) % this.slides.length; // Ensure infinite loop
        this.slides[slideIndex].classList.add('active');
      }

      // Update container height based on the height of the last active slide
      const activeSlide = this.slides[(this.currentIndex + slidesToShow - 1) % this.slides.length]; // Last active slide
      if (activeSlide) {
        const height = activeSlide.offsetHeight;
        this.container.style.height = `${height}px`;
      }

    } else {
      // If the parent ID is not 'blog', apply the normal active slide logic
      this.slides.forEach((slide, index) => {
        slide.classList.toggle('active', index === this.currentIndex); // Only toggle the active class on the current slide
      });

      // Update container height based on the height of the active slide
      const activeSlide = this.slides[this.currentIndex];
      if (activeSlide) {
        const height = activeSlide.offsetHeight;
        this.container.style.height = `${height}px`;
      }
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

    this.slides.forEach((slide) => {
      slide.style.flex = `0 0 ${100 / slidesToShow}%`; // Adjusting flex-basis
    });

    this.updateSlides(); // Ensure slides are updated
  }
}

// Initialization
document.addEventListener('DOMContentLoaded', () => {
  ['.slider1', '.slider2', '.slider4', '.slider5', '.slider6'].forEach(selector => {
    const container = document.querySelector(selector);
    if (container) {
      new ResponsiveSlider(selector, {
        autoPlay: false,
        autoPlayInterval: 5000
      });
    }
  });
});