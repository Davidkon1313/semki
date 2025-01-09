<!DOCTYPE html>
<html <?php language_attributes(); ?>
   <head>
<!-- <title><?php bloginfo('name'); ?> &raquo; <?php is_front_page() ? bloginfo('description') : wp_title(''); ?></title> -->
<title>Epic Foods &raquo; <?php is_front_page() ? bloginfo('description') : wp_title(''); ?></title>
<link href="https://vjs.zencdn.net/8.16.1/video-js.css" rel="stylesheet" />
<meta charset="<?php bloginfo('charset'); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="stylesheet" href="<?php bloginfo('stylesheet_url'); ?>">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/helpers/reset.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/helpers/globalStyles.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/main.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/UI/button.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/UI/input.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/UI/header.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/UI/fotter.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/fonts/akrobat.css">
<link rel="stylesheet" href="<?php echo get_template_directory_uri(); ?>/styles/UI/modal.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@10/swiper-bundle.min.css" />
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
   <header class="header">
      <div class="container">
         <div class="header__wrapper">
            <div class="header__left">
               <img
                  src="<?php echo get_template_directory_uri(); ?>/images/Logo.svg"
                  alt="Epic Foods"
                  id="homeLogo"
                  style="cursor: pointer;">
               <script>
                  document.getElementById('homeLogo').addEventListener('click', function() {
                     window.location.href = "<?php echo get_home_url(); ?>";
                  });
               </script>

            </div>
            <div class="header__right">
               <nav>
                  <a href="<?php echo get_home_url(); ?>/#about-us">Про нас</a>
                  <a href="<?php echo get_home_url(); ?>/poslugi">Послуги</a>
                  <a href="<?php echo get_home_url(); ?>/#cooperative">Співпраця</a>
                  <div class="menu">
                     <a href="#">Продукція <img class="arrow" src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-down.webp"></a>
                     <div class="menu__content">
                        <a href="<?php echo get_home_url(); ?>#gold-niva">Золота Нива</a>
                        <a href="<?php echo get_home_url(); ?>#jaguar">Ягуар</a>
                        <a href="<?php echo get_home_url(); ?>#guli-guli">Гулі</a>
                        <a href="<?php echo get_home_url(); ?>#smakolik">Смаколик</a>
                     </div>
                  </div>
                  <!-- <a href="<?php echo get_home_url(); ?>/checkout">Купити онлайн</a> -->
                  <a href="<?php echo get_home_url(); ?>/#blog">Блог</a>
                  <a href="<?php echo get_home_url(); ?>/#contacts">Контакти</a>
                  <!-- Button for switching languages -->
                  <button class="btn btn__switch">
                     <div class="btn__switch__side active" data-lang="uk">
                        <p>UA</p>
                     </div>
                     <div class="btn__switch__side" data-lang="en">
                        <p>EN</p>
                     </div>
                  </button>
                  <button class="btn btn__yellow btn__cart" id="openBtn"><img src="<?php echo get_template_directory_uri(); ?>/images/cart.svg"
                        alt="cart"></button>
               </nav>
               <button class="btn btn__yellow btn__burger" id="burger"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/burger.webp" alt="cart"></button>
            </div>
            <div class="header__responsive">
               <div class="header__responsiveList">
                  <a href="<?php echo get_home_url(); ?>/#about-us">Про нас</a>
                  <a href="<?php echo get_home_url(); ?>/poslugi">Послуги</a>
                  <a href="<?php echo get_home_url(); ?>/#cooperative">Співпраця</a>
                  <div class="menu">
                     <span id="responsiveDrop">Продукція <img class="arrow" src="<?php echo get_template_directory_uri(); ?>/images/ui/herederResp.webp"></span>
                     <div class="menu__content" id="responsiveDropMenu">
                        <a href="<?php echo get_home_url(); ?>#gold-niva">Золота Нива</a>
                        <a href="<?php echo get_home_url(); ?>#jaguar">Ягуар</a>
                        <a href="<?php echo get_home_url(); ?>#guli-guli">Гулі</a>
                        <a href="<?php echo get_home_url(); ?>#smakolik">Смаколик</a>
                     </div>
                  </div>
                  <a href="<?php echo get_home_url(); ?>/checkout">Купити онлайн</a>
                  <a href="<?php echo get_home_url(); ?>/#blog">Блог</a>
                  <a href="<?php echo get_home_url(); ?>/#contacts">Контакти</a>
                  <button class="btn btn__yellow btn__cart" id="openBtn"><img src="<?php echo get_template_directory_uri(); ?>/images/cart.svg" alt="cart"></button>
                  <button class="btn btn__switch">
                     <div id="ua-button" class="btn__switch__side active" data-lang="uk">
                        <p>UA</p>
                     </div>
                     <div id="en-button" class="btn__switch__side" data-lang="en">
                        <p>EN</p>
                     </div>
                  </button>
                  <button class="btn btn__yellow btn__close" id="burgerClose"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/close.webp"></button>
               </div>
            </div>
         </div>
      </div>
   </header>
   <div class="modal" id="myModal">
      <div class="modal-content">
         <span class="close-btn" id="closeBtn"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/close.webp" alt=""></span>
         <div class="cash">
            <div class="cash__head">
               <p>Ваша корзина</p>
            </div>
            <div class="cash__item">
               <p>Завантаження...</p>
            </div>
         </div>
      </div>
   </div>
   <div class="header__fix"></div>
   <!-- <?php wp_nav_menu(array('header-menu' => 'header-menu')); ?> -->