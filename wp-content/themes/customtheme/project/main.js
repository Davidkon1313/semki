const toggleButtons = document.querySelectorAll('.toggle-btn');

// Функция для обновления высоты слайдера
function updateSliderHeight(sliderContainer) {
    const activeSlide = sliderContainer.querySelector('.slide.active');
    if (activeSlide) {
        const height = activeSlide.offsetHeight;
        sliderContainer.style.height = `${height}px`;
        // sliderContainer.style.height = `auto`;
    }
}

toggleButtons.forEach(button => {
    button.addEventListener('click', () => {
        const textContainer = button.previousElementSibling;
        textContainer.classList.toggle('expanded');
        button.classList.toggle('expanded');

        const sliderContainer = button.closest('.slider-container');
        if (sliderContainer) {
            updateSliderHeight(sliderContainer);
        }
    });
});

// Получаем элементы
const modal = document.getElementById("myModal");
const openBtn = document.getElementById("openBtn");
const closeBtn = document.getElementById("closeBtn");

// Открытие модального окна
openBtn.onclick = () => {
  document.body.style.overflow = 'hidden';
  modal.style.display = "flex"; // Показываем модальное окно
  setTimeout(() => {
    modal.classList.add("open"); // Добавляем класс для анимации
  }, 10); // Небольшая задержка для активации анимации
};

// Закрытие модального окна
closeBtn.onclick = () => {
  document.body.style.overflow = 'auto';
  modal.classList.remove("open"); // Убираем анимацию
  modal.classList.add("close"); // Начинаем анимацию исчезновения
  setTimeout(() => {
    modal.style.display = "none"; // Скрываем окно после завершения анимации
    modal.classList.remove("close"); // Убираем класс для следующего открытия
  }, 300); // Время, равное длительности анимации
};

// Закрытие модального окна при клике вне окна
window.onclick = (event) => {
  if (event.target === modal) {
    closeBtn.onclick(); // Закрываем окно, если клик был за пределами
  }
};

document.querySelector('.anchor').addEventListener('click', function(event) {
    // Отменяем стандартное поведение ссылки
    event.preventDefault();
    // Выполняем плавный скролл вверх
    window.scrollTo({
      top: 0,
      behavior: 'smooth'
    });
  });

  const button = document.getElementById('burger');
  const buttonClose = document.getElementById('burgerClose');
  const responsive = document.getElementsByClassName('header__responsive')[0];

  button.addEventListener('click', function() {
    document.body.style.overflow = 'hidden';
    responsive.style.display = 'block';
    responsive.style.transform.translateY = '0%';
  });
  buttonClose.addEventListener('click', function() {
    document.body.style.overflow = 'auto';
    responsive.style.display = 'none';
    responsive.style.transform.translateY = '-100%';
  });

  const item = document.getElementById('responsiveDrop');
  const dropdown = document.getElementById('responsiveDropMenu');

  item.addEventListener('click', function(event) {
    event.stopPropagation();
    dropdown.style.display = 'flex';
  });

  document.addEventListener('click', function(event) {
    if (!dropdown.contains(event.target) && event.target !== item) {
      dropdown.style.display = 'none';
    }
  });

  const sectionList = document.getElementsByClassName('section__itemResponsive')[0];

if (sectionList) {
  const switcher = document.getElementById('switcher');
  const switcher2 = document.getElementById('switcher2');

  if (switcher) {
    switcher.addEventListener('click', function() {
      sectionList.classList.toggle('listed');
    });
  }

  if (switcher2) {
    switcher2.addEventListener('click', function() {
      sectionList.classList.toggle('listed');
    });
  }
}

  const handleParallax = () => {
    document.querySelectorAll('.parallax').forEach(section => {
        const sectionTop = section.getBoundingClientRect().top;
        const sectionHeight = section.clientHeight;
        const halfSection = sectionHeight / 2;

        section.querySelectorAll('.seed').forEach(seed => {
            const seedTop = seed.getBoundingClientRect().top - sectionTop;
            const direction = seedTop < halfSection ? 1 : -1;  
            const moveAmount = direction * (150 + Math.random() * 50);

            seed.style.transform = `translateY(${moveAmount}px)`;
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
  const block = document.getElementById('banner');
  const opt = document.getElementById('opt');
  const cashTooltip = document.getElementById('cashTooltip');
  const footer = document.getElementById('footer');

  const observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          block.classList.add('footer-reached');
          opt.classList.add('footer-reached');
          cashTooltip.classList.add('footer-reached');
        } else {
          block.classList.remove('footer-reached');
          opt.classList.remove('footer-reached');
          cashTooltip.classList.remove('footer-reached');
        }
      });
    },
    {
      root: null, // Весь вьюпорт
      threshold: 0.6, // 10% футера достаточно для срабатывания
    }
  );

  observer.observe(footer);
});

window.addEventListener('scroll', handleParallax);