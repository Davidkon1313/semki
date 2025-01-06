<div class="opt" id="opt">
  <img src="<?php echo get_template_directory_uri(); ?>/images/opt.png">
</div>
<div class="cashTooltip" id="cashTooltip">
  <img src="<?php echo get_template_directory_uri(); ?>/images/cash.webp">
</div>
<div class="modal orderModal" id="orderModal">
  <div class="modal-content">
    <span class="close-btn" id="order-close-btn"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/close.png" alt=""></span>
    <div class="order">
      <div class="order__head">
        <h2>Бажаєте замовити:</h2>
      </div>
      <div class="order__list">
        <input type="text" placeholder="Калібрування" id="order-form-posluga-header" readonly=true>
        <p>Залиште ваші данні та ми звʼяжемось з вами найближчим часом</p>
        <input type="text" placeholder="Ваше імʼя">
        <input type="number" placeholder="Номер телефону">
        <button class="btn btn__yellow" id="order-send-close-btn">Замовити</button>
        <span>Всі данні захищено</span>
      </div>
    </div>
  </div>
</div>
</div>
<div class="modal orderModal" id="feedbackModal">
  <div class="modal-content">
    <span class="close-btn" id="feedback-close-btn"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/close.png" alt=""></span>
    <div class="order">
      <div class="order__list">
        <p><b>Залиште ваш відгук</b></p>
        <input type="text" placeholder="Ваше імʼя">
        <input type="text" placeholder="Ваш відгук">
        <button class="btn btn__yellow" id="btn-send-feedback">Залишити відгук</button>
      </div>
    </div>
  </div>
</div>
<footer class="footer" id="footer">
  <div class="footer__wrapper container">
    <img class="fotterLogo" src="<?php echo get_template_directory_uri(); ?>/images/footer.webp" alt="">
    <div class="footer__list">
      <!-- <div class="footer__list__item"> -->
      <a href="<?php echo get_home_url(); ?>/#about-us">Про нас</a>
      <a href="<?php echo get_home_url(); ?>/poslugi">Послуги</a>
      <a href="<?php echo get_home_url(); ?>/#cooperative">Співпраця</a>
      <div class="menu">
        <span href="<?php echo get_home_url(); ?>#gold-niva">Продукція <img class="arrow" src="<?php echo get_template_directory_uri(); ?>/images/ui/herederResp.webp"></span>
        <div class="menu__content">
          <a href="<?php echo get_home_url(); ?>#gold-niva">Золота Нива</a>
          <a href="<?php echo get_home_url(); ?>#jaguar">Ягуар</a>
          <a href="<?php echo get_home_url(); ?>#guli-guli">Гулі</a>
          <a href="<?php echo get_home_url(); ?>#smakolik">Смаколик</a>
        </div>
      </div>
      <a href="<?php echo get_home_url(); ?>/checkout">Купити онлайн</a>
      <a href="<?php echo get_home_url(); ?>/#blog">Блог</a>
      <a href="#"><b>Купити в пачках</b></a>
      <a href="#"><b>Купити в коробках</b></a>
      <a href="#"><b>Купити на вагу</b></a>
      <!-- </div> -->
    </div>
    <a class="anchor" href="/"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/Arrow-top.webp" alt=""></a>
  </div>
</footer>
<div class="banner__wrapper">
  <div class="banner" id="banner">
    <div class="banner__list">
      <div class="banner__item">
        <img src="<?php echo get_template_directory_uri(); ?>/images/banner/1.webp">
        <p>Купити в пачках</p>
      </div>
      <div class="banner__item">
        <img src="<?php echo get_template_directory_uri(); ?>/images/banner/2.webp">
        <p>Купити в коробках</p>
      </div>
      <div class="banner__item">
        <img src="<?php echo get_template_directory_uri(); ?>/images/banner/3.webp">
        <p>Купити на вагу</p>
      </div>
      <div class="banner__item">
        <img src="<?php echo get_template_directory_uri(); ?>/images/banner/4.webp">
        <p>Контакти</p>
      </div>
    </div>
  </div>
</div>
<?php wp_footer(); ?>
<script type="text/javascript">
  var wc_cart_params = <?php echo json_encode(array('ajax_url' => admin_url('admin-ajax.php'))); ?>;
</script>
<script src="<?php echo get_template_directory_uri(); ?>/slider.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/custom-slider.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/main.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/slider-reviews.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/slider-production.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/modal-form.js"></script>
<script src="<?php echo get_template_directory_uri(); ?>/js/feedback-form.js"></script>
<script type="text/javascript">
  const checkoutUrl = "<?php echo esc_url(wc_get_checkout_url()); ?>"; // Correctly pass the checkout URL to JavaScript
</script>

<script>
  // Получаем элементы
  const orderModal = document.getElementById("orderModal");
  const orderOpenBtn = document.getElementById("orderOpenBtn");
  const orderCloseBtn = document.getElementById("order-close-btn");

  // Открытие модального окна
  orderOpenBtn.onclick = () => {
    document.body.style.overflow = 'hidden';
    orderModal.style.display = "flex"; // Показываем модальное окно
    setTimeout(() => {
      orderModal.classList.add("open"); // Добавляем класс для анимации
    }, 10); // Небольшая задержка для активации анимации
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
</script>

<script type="module">
  // This will remove the inline height style for .swiper elements
  document.querySelectorAll('.swiper-wrapper').forEach(function(swiperElement) {
    swiperElement.style.height = 'auto';
  });

  import Swiper from 'https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.mjs';

  const swiper = new Swiper('#swiper2', {
    direction: 'horizontal',
    loop: true,
    //   slidesPerView: 2,
    //   slidesPerGroup: 1,
    spaceBetween: 20,
    breakpoints: {
      // when window width is >= 320px
      1200: {
        slidesPerView: 2,
        slidesPerGroup: 2,
      }
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  });

  const swiper1 = new Swiper('#swiper1', {
    direction: 'horizontal',
    loop: true,
    slidesPerView: 1.4,
    slidesPerGroup: 1,
    centeredSlides: true,
    breakpoints: {
      992: {
        slidesPerView: 1.2,
        slidesPerGroup: 1,
      },
      1201: {
        slidesPerView: 1.7,
        slidesPerGroup: 1,
      }
    },
    navigation: {
      nextEl: '.swiper-button-next',
      prevEl: '.swiper-button-prev',
    },
  });
</script>

<script>
  jQuery(document).ready(function($) {
    autoSelectFirstVariationMobile();

    function autoSelectFirstVariationMobile() {
      // When the variation select changes
      $('.section__itemResponsive .variation-select').on('change', function() {
        var selectedOption = $(this).find('option:selected');
        var imageUrl = selectedOption.data('image');
        var price = selectedOption.data('price');

        // Get the closest product container
        var productContainer = $(this).closest('.section__itemResponsive');

        // Update the specific image and price within the container
        productContainer.find('.product-image').attr('src', imageUrl);
        productContainer.find('.mobile-product-price').text('Ціна: ' + price + ' грн');
      });

      // Automatically select the first variation on page load
      $('.section__itemResponsive .variation-select').each(function() {
        var firstOption = $(this).find('option:first');
        firstOption.prop('selected', true); // Select the first option
        $(this).trigger('change'); // Trigger the change event to update SKU, image, and price
      });
    }
  });


  jQuery(document).ready(function($) {
    autoSelectFirstVariation();
  });

  function autoSelectFirstVariation() {
    // When the variation select changes
    $('.variation-select').on('change', function() {
      var selectedOption = $(this).find('option:selected');
      var sku = selectedOption.data('sku');
      var imageUrl = selectedOption.data('image');
      var price = selectedOption.data('price'); // Get the price

      // Get the closest product container using the product's unique ID (using .closest('.section__item'))
      var productContainer = $(this).closest('.section__item');

      // Update SKU
      productContainer.find('.product-sku').text(sku);

      // Update Image
      productContainer.find('.product-image').attr('src', imageUrl);

      // Update Price
      productContainer.find('.product-price').text('Ціна: ' + price + ' грн'); // Update the price dynamically
    });

    // Automatically select the first variation on page load
    $('.variation-select').each(function() {
      var firstOption = $(this).find('option:first');
      firstOption.prop('selected', true); // Select the first option
      $(this).trigger('change'); // Trigger the change event to update SKU, image, and price
    });
  }
</script>

<script>
  jQuery(document).ready(function($) {
    if (window.location.href.indexOf('/checkout') > -1) {
      // Get the values from sessionStorage
      var firstName = sessionStorage.getItem('first_name');
      var phoneNumber = sessionStorage.getItem('phone_number');

      // Populate the WooCommerce checkout fields with the values from sessionStorage
      if (firstName) {
        $('#billing_first_name').val(firstName); // Set the first name field on checkout page
      }
      if (phoneNumber) {
        $('#billing_phone').val(phoneNumber); // Set the phone number field on checkout page
      }
    }
  });
</script>

<script>
  document.addEventListener("DOMContentLoaded", function() {
    const videoButton = document.querySelector(".videoButton");
    const rightWrapper = document.querySelector(".S7__rightWrapper");

    videoButton.addEventListener("click", function() {
      // Hide existing elements
      rightWrapper.innerHTML = `
            <iframe class="ytvideo" 
                    src="https://www.youtube.com/embed/aAkMkVFwAoo?si=j0eSFlqwtfuscii0" 
                    title="YouTube video player" 
                    frameborder="0" 
                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" 
                    referrerpolicy="strict-origin-when-cross-origin" 
                    allowfullscreen>
            </iframe>
        `;
    });
  });
</script>

<script type="text/javascript">
  (function() {
    var gt = document.createElement('script');
    gt.type = 'text/javascript';
    gt.async = true;
    gt.src = 'https://translate.google.com/translate_a/element.js?cb=googleTranslateElementInit';
    var s = document.getElementsByTagName('script')[0];
    s.parentNode.insertBefore(gt, s);
  })();
</script>

</body>

</html>