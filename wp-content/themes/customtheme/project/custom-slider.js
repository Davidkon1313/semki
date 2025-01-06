class Carousel {
    constructor(containerSelector, options = {}) {
        this.container = document.querySelector(containerSelector);
        this.track = this.container.querySelector('.carousel-track');
        this.items = Array.from(this.track.children);
        this.prevButton = this.container.querySelector('.prev-btn');
        this.nextButton = this.container.querySelector('.next-btn');
        this.currentIndex = this.items.length - 1; // Начинаем с последнего слайда
        this.totalItems = this.items.length;

        this.options = {
            slideWidth: options.slideWidth || 20,
            slideGap: options.slideGap || 10,
            transitionDuration: options.transitionDuration || 0.5,
            ...options
        };

        this.init();
    }

    init() {
        this.updateTrackStyle();
        this.updateTrackPosition();
        this.updateItemClasses();

        this.prevButton.addEventListener('click', () => this.moveToPrev());
        this.nextButton.addEventListener('click', () => this.moveToNext());
    }

    updateTrackStyle() {
        this.track.style.transition = `transform ${this.options.transitionDuration}s ease-in-out`;

        this.items.forEach(item => {
            item.style.flex = `0 0 ${this.options.slideWidth}%`;
            item.style.margin = `0 ${this.options.slideGap}px`;
        });
    }

    moveToPrev() {
        if (this.currentIndex === 0) {
            this.currentIndex = this.totalItems - 1; // Переход на последний слайд
        } else {
            this.currentIndex--;
        }
        this.updateTrackPosition();
        this.updateItemClasses();
    }

    moveToNext() {
        if (this.currentIndex === this.totalItems - 1) {
            this.currentIndex = 0; // Переход на первый слайд
        } else {
            this.currentIndex++;
        }
        this.updateTrackPosition();
        this.updateItemClasses();
    }

    updateTrackPosition() {
        const offset = -this.items[0].getBoundingClientRect().width * this.currentIndex;
        this.track.style.transform = `translateX(${offset}px)`;
    }

    updateItemClasses() {
        this.items.forEach(item => item.classList.remove('active'));
        this.items[this.currentIndex].classList.add('active');
        this.items.forEach((item, index) => {
            item.style.opacity = index === this.currentIndex ? 1 : 0.5;
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    new Carousel('#carousel1', {
        slideWidth: 33,
        slideGap: 10,
        transitionDuration: 0.5
    });
});
