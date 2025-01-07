<?php get_header(); ?>

<main class="body">
    <div class="action">
        <div class="marquee">
            <p>Заробляйте до 60% з нашою продукцією у вашому магазині. Дистриб'ютори можуть отримувати до 35% прибутку з оптових закупівель</p>
        </div>
    </div>
    <div class="S1 parallax">
        <div class="container">
            <div class="S1__wrapper">
                <div class="S1__left">
                    <h1>Епік Фудс – якість та смак для кожного дня</h1>
                    <div class="S1__list">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/switcher.svg" alt="">
                        <p><b>Найкращі снеки:</b> насіння, горіхи, арахіс (перелік можна зробити ротатором)</p>
                    </div>
                    <div class="S1__list">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/switcher.svg" alt="">
                        <p>Мінімальне замовлення – <b>від 500 грн.</b></p>
                    </div>
                    <a href="<?php echo get_home_url(); ?>/#gold-niva">
                        <button class="btn btn__yellow desktop">ОБИРАЙ свій смак!</button>
                    </a>
                </div>
                <!-- <div class="S1__right"> -->

                <div class="slider-container slider1" id="first-banner">
                    <?php
                    // Get all PNG files from the /images directory
                    $image_files = glob(get_template_directory() . '/images/products/*.webp');

                    // Loop through each file and display the image in a slide
                    if ($image_files) {
                        foreach ($image_files as $image_file) {
                            // Get the image URL by replacing the server path with the URL
                            $image_url = str_replace(get_template_directory(), get_template_directory_uri(), $image_file);
                    ?>
                            <div class="slide">
                                <img src="<?php echo esc_url($image_url); ?>" alt="Slider Image">
                            </div>
                    <?php
                        }
                    } else {
                        echo '<p>No images found.</p>';
                    }
                    ?>

                    <!-- <button class="prev-button">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-left.webp" alt="Previous">
                    </button>
                    <button class="next-button">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-right.webp" alt="Next">
                    </button> -->
                </div>
                <a href="<?php echo get_home_url(); ?>/#gold-niva">
                    <button class="btn btn__yellow responsive">ОБИРАЙ свій смак!</button>
                </a>
                <!-- </div> -->
                <img class="seed seed1 responsiveseed" src="<?php echo get_template_directory_uri(); ?>/images/S1/seed1.png">
                <img class="seed seed2 responsiveseed" src="<?php echo get_template_directory_uri(); ?>/images/S1/seed2.png">
            </div>
        </div>
    </div>
    <div class="action action2">
        <div class="marquee">
            <p>Заробляйте до 60% з нашою продукцією у вашому магазині. Дистриб'ютори можуть отримувати до 35% прибутку з оптових закупівель</p>
        </div>
    </div>
    <!-- CATEGORY GOLD NIVA -->
    <div class="S2 parallax" id="gold-niva">
        <div class="section__wrapper">
            <div class="section__head">
                <h2><b>Золота Нива</b> – Найкращі снеки для щоденного перекусу</h2>
                <p> Продукція високої якості для перекусів: насіння соняшника, гарбузове насіння, арахіс.</p>
            </div>
            <div class="section__list" id="product-block-1">
                <?php
                // Ensure WooCommerce functions are available
                if (class_exists('WooCommerce')) {

                    // Arguments for the query
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => 4,
                        'status' => 'publish',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                                'field' => 'slug',           // Use the category slug
                                'terms' => 'category-gold-niva', // The slug of the category
                            ),
                        ),
                    );

                    // The Query
                    $query = new WP_Query($args);

                    // The Loop
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();

                            // Get the global product object
                            global $product;

                            // Product variables
                            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                            $product_title = get_the_title();
                            $product_sku   = $product->get_sku() ?: 'N/A';
                            $product_price = $product->get_price();
                            $product_desc  = wp_trim_words($product->get_description(), 20, '...');

                            // Use a unique class for each product item (by using the product ID)
                            echo '<div class="section__item product-' . $product->get_id() . '">';
                            echo '<img class="product-image" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
                            echo '<p>' . esc_html($product_title) . '</p>';
                            echo '<span>SKU: <span class="product-sku">' . esc_html($product_sku) . '</span></span>';
                            echo '<span class="desc">' . esc_html($product_desc) . '</span>';
                            echo '<b class="product-price">Ціна: ' . esc_html($product_price) . ' грн</b>';

                            // Check if the product is simple or variable
                            if ($product->get_type() === 'variable') {
                                // Show variations only for variable products
                                $variations = $product->get_available_variations();
                                if ($variations) {
                                    echo '<select class="variation-select">';
                                    foreach ($variations as $variation) {
                                        $id = $variation['variation_id'];
                                        $sku = $variation['sku'] ?: 'N/A';
                                        $image_id = $variation['image_id'];
                                        $image_url = wp_get_attachment_image_url($image_id, 'full');
                                        $price = $variation['display_price'];  // Get variation price
                                        $pack_size_slug = esc_html($variation['attributes']['attribute_pa_pack-size']);
                                        $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
                                        $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';

                                        echo '<option value="' . $id . '" data-sku="' . $sku . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_html($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    echo '<p>Немає доступних варіацій</p>';
                                }
                            }

                            // Add to Cart button (will be updated dynamically)
                            echo '<button class="btn btn__white" id="add-to-cart-button" data-product-id="' . $product->get_id() . '">Купити зараз!</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Немає змінних продуктів для показу.</p>';
                    }

                    // Reset Post Data
                    wp_reset_postdata();
                } else {
                    echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
                }
                ?>

            </div>
            <?php
            // Show the "Show More" button only if there are more products
            if ($query->found_posts > 4) {
                echo '<button class="btn btn__showMore section__button" id="load-more-products-1" data-offset="4" data-category="category-gold-niva">Показати ще</button>';
            }
            ?>
            <img class="seed seed1" src="<?php echo get_template_directory_uri(); ?>/images/S2/S2__1.webp">
            <img class="seed seed2" src="<?php echo get_template_directory_uri(); ?>/images/S2/S2__2.webp">
            <img class="seed seed3" src="<?php echo get_template_directory_uri(); ?>/images/S2/S2__3.webp">
            <img class="seed seed4" src="<?php echo get_template_directory_uri(); ?>/images/S2/S2__4.webp">
            <?php
            // Ensure WooCommerce functions are available
            if (class_exists('WooCommerce')) {

                // Arguments for the query
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'status' => 'publish',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                            'field' => 'slug',           // Use the category slug
                            'terms' => 'category-gold-niva', // The slug of the category
                        ),
                    ),
                );

                // The Query
                $query = new WP_Query($args);

                // The Loop
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();

                        // Get the global product object
                        global $product;

                        // Product variables
                        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                        $product_title = get_the_title();
                        $product_price = $product->get_price();
                        $product_desc  = wp_trim_words($product->get_description(), 20, '...');
                        $variations = $product->get_type() === 'variable' ? $product->get_available_variations() : null;

                        // Render the product in the desired layout
                        echo '<div class="section__listResponsive">';
                        echo '<div class="section__itemResponsive listed">';
                        echo '<div class="section__itemResponsiveWrapper">';
                        echo '<img id="switcher" class="switcher" src="' . esc_url(get_template_directory_uri() . '/images/ui/section-dropdown.svg') . '">';
                        echo '<h2>' . esc_html($product_title) . '</h2>';
                        echo '<div class="line"></div>';
                        echo '<img class="product-image" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
                        echo '<div class="line"></div>';
                        echo '<p>' . esc_html($product_desc) . '</p>';

                        // Variations dropdown for variable products
                        if ($variations) {
                            echo '<select class="variation-select">';
                            foreach ($variations as $variation) {
                                $id = $variation['variation_id'];
                                $sku = $variation['sku'] ?: 'N/A';
                                $image_id = $variation['image_id'];
                                $image_url = wp_get_attachment_image_url($image_id, 'full');
                                $price = $variation['display_price'];
                                $pack_size_slug = esc_html($variation['attributes']['attribute_pa_pack-size']);
                                $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
                                $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';

                                echo '<option value="' . esc_attr($id) . '" data-sku="' . esc_attr($sku) . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_attr($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
                            }
                            echo '</select>';
                        }

                        echo '<span class="mobile-product-price">Ціна: ' . esc_html($product_price) . ' грн</span>';
                        echo '<button class="btn btn__yellow" id="add-to-cart-button" data-product-id="' . $product->get_id() . '">Купити зараз!</button>';
                        echo '</div>';

                        echo '<div class="section__itemResponsiveListed">';
                        echo '<p>' . esc_html($product_title) . '</p>';
                        echo '<div class="section__itemResponsiveListedPrice">';
                        echo '<span>Ціна: ' . esc_html($product_price) . ' грн</span>';
                        echo '<button class="btn btn__yellow">Купити</button>';
                        echo '</div>';
                        echo '<img id="switcher2" src="' . esc_url(get_template_directory_uri() . '/images/ui/section-dropdown.svg') . '">';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Немає продуктів для показу.</p>';
                }

                // Reset Post Data
                wp_reset_postdata();
            } else {
                echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
            }
            ?>
        </div>
    </div>
    </div>
    <img class="seed seed1" src="<?php echo get_template_directory_uri(); ?>/images/S2/S2__1.webp">
    <img class="seed seed2" src="<?php echo get_template_directory_uri(); ?>/images/S2/S2__2.webp">
    <img class="seed seed3" src="<?php echo get_template_directory_uri(); ?>/images/S2/S2__3.webp">
    <img class="seed seed4" src="<?php echo get_template_directory_uri(); ?>/images/S2/S2__4.webp">
    </div>
    <!-- CATEGORY GULI GULI -->
    <div class="S3 parallax" id="guli-guli">
        <div class="section__wrapper">
            <div class="section__head">
                <h2><b> Гулі-Гулі</b> – Смаколики для справжніх поціновувачів</h2>
                <p>Виготовлене з натуральної сировини. Насіння соняшника та гарбузове насіння в найкращих варіаціях.
                </p>
            </div>
            <div class="section__list" id="product-block-2">
                <?php
                // Ensure WooCommerce functions are available
                if (class_exists('WooCommerce')) {
                    // Arguments for the query
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => 4,
                        'status' => 'publish',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                                'field' => 'slug',           // Use the category slug
                                'terms' => 'category-guli-guli', // The slug of the category
                            ),
                        ),
                    );

                    // The Query
                    $query = new WP_Query($args);

                    // The Loop
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();

                            // Get the global product object
                            global $product;

                            // Product variables
                            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                            $product_title = get_the_title();
                            $product_sku   = $product->get_sku() ?: 'N/A';
                            $product_price = $product->get_price();
                            $product_desc  = wp_trim_words($product->get_description(), 20, '...');

                            // Use a unique class for each product item (by using the product ID)
                            echo '<div class="section__item product-' . $product->get_id() . '">';
                            echo '<img class="product-image" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
                            echo '<p>' . esc_html($product_title) . '</p>';
                            echo '<span>SKU: <span class="product-sku">' . esc_html($product_sku) . '</span></span>';
                            echo '<span class="desc">' . esc_html($product_desc) . '</span>';
                            echo '<b class="product-price">Ціна: ' . esc_html($product_price) . ' грн</b>';

                            // Check if the product is simple or variable
                            if ($product->get_type() === 'variable') {
                                // Show variations only for variable products
                                $variations = $product->get_available_variations();
                                if ($variations) {
                                    echo '<select class="variation-select">';
                                    foreach ($variations as $variation) {
                                        $id = $variation['variation_id'];
                                        $sku = $variation['sku'] ?: 'N/A';
                                        $image_id = $variation['image_id'];
                                        $image_url = wp_get_attachment_image_url($image_id, 'full');
                                        $price = $variation['display_price'];  // Get variation price
                                        $pack_size_slug = esc_html($variation['attributes']['attribute_pa_pack-size']);
                                        $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
                                        $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';

                                        echo '<option value="' . $id . '" data-sku="' . $sku . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_html($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    echo '<p>Немає доступних варіацій</p>';
                                }
                            }

                            // Add to Cart button (will be updated dynamically)
                            echo '<button class="btn btn__white" id="add-to-cart-button" data-product-id="' . $product->get_id() . '">Купити зараз!</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Немає змінних продуктів для показу.</p>';
                    }

                    // Reset Post Data
                    wp_reset_postdata();
                } else {
                    echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
                }
                ?>
            </div>

            <?php
            // Show the "Show More" button only if there are more products
            if ($query->found_posts > 4) {
                echo '<button class="btn btn__showMore section__button" id="load-more-products-2" data-offset="4" data-category="category-guli-guli">Показати ще</button>';
            }
            ?>
            <?php
            // Ensure WooCommerce functions are available
            if (class_exists('WooCommerce')) {

                // Arguments for the query
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'status' => 'publish',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                            'field' => 'slug',           // Use the category slug
                            'terms' => 'category-guli-guli', // The slug of the category
                        ),
                    ),
                );

                // The Query
                $query = new WP_Query($args);

                // The Loop
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();

                        // Get the global product object
                        global $product;

                        // Product variables
                        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                        $product_title = get_the_title();
                        $product_price = $product->get_price();
                        $product_desc  = wp_trim_words($product->get_description(), 20, '...');
                        $variations = $product->get_type() === 'variable' ? $product->get_available_variations() : null;

                        // Render the product in the desired layout
                        echo '<div class="section__listResponsive">';
                        echo '<div class="section__itemResponsive listed">';
                        echo '<div class="section__itemResponsiveWrapper">';
                        echo '<img id="switcher" class="switcher" src="' . esc_url(get_template_directory_uri() . '/images/ui/section-dropdown.svg') . '">';
                        echo '<h2>' . esc_html($product_title) . '</h2>';
                        echo '<div class="line"></div>';
                        echo '<img class="product-image" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
                        echo '<div class="line"></div>';
                        echo '<p>' . esc_html($product_desc) . '</p>';

                        // Variations dropdown for variable products
                        if ($variations) {
                            echo '<select class="variation-select">';
                            foreach ($variations as $variation) {
                                $id = $variation['variation_id'];
                                $sku = $variation['sku'] ?: 'N/A';
                                $image_id = $variation['image_id'];
                                $image_url = wp_get_attachment_image_url($image_id, 'full');
                                $price = $variation['display_price'];
                                $pack_size_slug = esc_html($variation['attributes']['attribute_pa_pack-size']);
                                $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
                                $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';

                                echo '<option value="' . esc_attr($id) . '" data-sku="' . esc_attr($sku) . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_attr($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
                            }
                            echo '</select>';
                        }

                        echo '<span class="mobile-product-price">Ціна: ' . esc_html($product_price) . ' грн</span>';
                        echo '<button class="btn btn__yellow" id="add-to-cart-button" data-product-id="' . $product->get_id() . '">Купити зараз!</button>';
                        echo '</div>';

                        echo '<div class="section__itemResponsiveListed">';
                        echo '<p>' . esc_html($product_title) . '</p>';
                        echo '<div class="section__itemResponsiveListedPrice">';
                        echo '<span>Ціна: ' . esc_html($product_price) . ' грн</span>';
                        echo '<button class="btn btn__yellow">Купити</button>';
                        echo '</div>';
                        echo '<img id="switcher2" src="' . esc_url(get_template_directory_uri() . '/images/ui/section-dropdown.svg') . '">';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Немає продуктів для показу.</p>';
                }

                // Reset Post Data
                wp_reset_postdata();
            } else {
                echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
            }
            ?>
        </div>
        <img class="seed seed1" src="<?php echo get_template_directory_uri(); ?>/images/S3/S3__1.webp">
        <img class="seed seed2" src="<?php echo get_template_directory_uri(); ?>/images/S3/S3__2.webp">
        <img class="seed seed3" src="<?php echo get_template_directory_uri(); ?>/images/S3/S3__3.webp">
        <img class="seed seed4" src="<?php echo get_template_directory_uri(); ?>/images/S3/S3__4.webp">
        <img class="seed seed5" src="<?php echo get_template_directory_uri(); ?>/images/S3/S3__5.webp">
    </div>
    <!-- CATEGORY JAGUAR -->
    <div class="S4 parallax" id="jaguar">
        <div class="section__wrapper">
            <div class="section__head">
                <h2><b>Сонячний Ягуар</b> – Ідеальні снеки для перекус</h2>
                <p>Лінійка продуктів для активних людей, які люблять смачні та корисні снеки</p>
            </div>
            <div class="section__list" id="product-block-3">
                <?php
                // Ensure WooCommerce functions are available
                if (class_exists('WooCommerce')) {

                    // Arguments for the query
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => 4,
                        'status' => 'publish',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                                'field' => 'slug',           // Use the category slug
                                'terms' => 'category-jaguar', // The slug of the category
                            ),
                        ),
                    );

                    // The Query
                    $query = new WP_Query($args);

                    // The Loop
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();

                            // Get the global product object
                            global $product;

                            // Product variables
                            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                            $product_title = get_the_title();
                            $product_sku   = $product->get_sku() ?: 'N/A';
                            $product_price = $product->get_price();
                            $product_desc  = wp_trim_words($product->get_description(), 20, '...');

                            // Use a unique class for each product item (by using the product ID)
                            echo '<div class="section__item product-' . $product->get_id() . '">';
                            echo '<img class="product-image" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
                            echo '<p>' . esc_html($product_title) . '</p>';
                            echo '<span>SKU: <span class="product-sku">' . esc_html($product_sku) . '</span></span>';
                            echo '<span class="desc">' . esc_html($product_desc) . '</span>';
                            echo '<b class="product-price">Ціна: ' . esc_html($product_price) . ' грн</b>';

                            // Check if the product is simple or variable
                            if ($product->get_type() === 'variable') {
                                // Show variations only for variable products
                                $variations = $product->get_available_variations();
                                if ($variations) {
                                    echo '<select class="variation-select">';
                                    foreach ($variations as $variation) {
                                        $id = $variation['variation_id'];
                                        $sku = $variation['sku'] ?: 'N/A';
                                        $image_id = $variation['image_id'];
                                        $image_url = wp_get_attachment_image_url($image_id, 'full');
                                        $price = $variation['display_price'];  // Get variation price
                                        $pack_size_slug = esc_html($variation['attributes']['attribute_pa_pack-size']);
                                        $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
                                        $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';

                                        echo '<option value="' . $id . '" data-sku="' . $sku . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_html($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    echo '<p>Немає доступних варіацій</p>';
                                }
                            }

                            // Add to Cart button (will be updated dynamically)
                            echo '<button class="btn btn__white" id="add-to-cart-button" data-product-id="' . $product->get_id() . '">Купити зараз!</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Немає змінних продуктів для показу.</p>';
                    }

                    // Reset Post Data
                    wp_reset_postdata();
                } else {
                    echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
                }
                ?>
            </div>

            <?php
            // Show the "Show More" button only if there are more products
            if ($query->found_posts > 4) {
                echo '<button class="btn btn__showMore section__button" id="load-more-products-3" data-offset="4" data-category="category-jaguar">Показати ще</button>';
            }
            ?>
            <?php
            // Ensure WooCommerce functions are available
            if (class_exists('WooCommerce')) {

                // Arguments for the query
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'status' => 'publish',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                            'field' => 'slug',           // Use the category slug
                            'terms' => 'category-jaguar', // The slug of the category
                        ),
                    ),
                );

                // The Query
                $query = new WP_Query($args);

                // The Loop
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();

                        // Get the global product object
                        global $product;

                        // Product variables
                        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                        $product_title = get_the_title();
                        $product_price = $product->get_price();
                        $product_desc  = wp_trim_words($product->get_description(), 20, '...');
                        $variations = $product->get_type() === 'variable' ? $product->get_available_variations() : null;

                        // Render the product in the desired layout
                        echo '<div class="section__listResponsive">';
                        echo '<div class="section__itemResponsive listed">';
                        echo '<div class="section__itemResponsiveWrapper">';
                        echo '<img id="switcher" class="switcher" src="' . esc_url(get_template_directory_uri() . '/images/ui/section-dropdown.svg') . '">';
                        echo '<h2>' . esc_html($product_title) . '</h2>';
                        echo '<div class="line"></div>';
                        echo '<img class="product-image" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
                        echo '<div class="line"></div>';
                        echo '<p>' . esc_html($product_desc) . '</p>';

                        // Variations dropdown for variable products
                        if ($variations) {
                            echo '<select class="variation-select">';
                            foreach ($variations as $variation) {
                                $id = $variation['variation_id'];
                                $sku = $variation['sku'] ?: 'N/A';
                                $image_id = $variation['image_id'];
                                $image_url = wp_get_attachment_image_url($image_id, 'full');
                                $price = $variation['display_price'];
                                $pack_size_slug = esc_html($variation['attributes']['attribute_pa_pack-size']);
                                $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
                                $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';

                                echo '<option value="' . esc_attr($id) . '" data-sku="' . esc_attr($sku) . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_attr($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
                            }
                            echo '</select>';
                        }

                        echo '<span class="mobile-product-price">Ціна: ' . esc_html($product_price) . ' грн</span>';
                        echo '<button class="btn btn__yellow" id="add-to-cart-button" data-product-id="' . $product->get_id() . '">Купити зараз!</button>';
                        echo '</div>';

                        echo '<div class="section__itemResponsiveListed">';
                        echo '<p>' . esc_html($product_title) . '</p>';
                        echo '<div class="section__itemResponsiveListedPrice">';
                        echo '<span>Ціна: ' . esc_html($product_price) . ' грн</span>';
                        echo '<button class="btn btn__yellow">Купити</button>';
                        echo '</div>';
                        echo '<img id="switcher2" src="' . esc_url(get_template_directory_uri() . '/images/ui/section-dropdown.svg') . '">';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Немає продуктів для показу.</p>';
                }

                // Reset Post Data
                wp_reset_postdata();
            } else {
                echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
            }
            ?>
        </div>
        <img class="seed seed1" src="<?php echo get_template_directory_uri(); ?>/images/S4/S4__1.webp">
        <img class="seed seed2" src="<?php echo get_template_directory_uri(); ?>/images/S4/S4__2.webp">
        <img class="seed seed3" src="<?php echo get_template_directory_uri(); ?>/images/S4/S4__3.webp">
        <img class="seed seed4" src="<?php echo get_template_directory_uri(); ?>/images/S4/S4__4.webp">
        <img class="seed seed5" src="<?php echo get_template_directory_uri(); ?>/images/S4/S4__5.webp">
        <img class="seed seed6" src="<?php echo get_template_directory_uri(); ?>/images/S4/S4__6.webp">
        <img class="seed seed7" src="<?php echo get_template_directory_uri(); ?>/images/S4/S4__7.webp">
    </div>
    <!-- CATEGORY SMAKOLIK -->
    <div class="S5 parallax" id="smakolik">
        <div class="section__wrapper">
            <div class="section__head">
                <h2><b>Смаколик</b> – Смаколики для справжніх поціновувачів</h2>
                <p> Виготовлене з натуральної сировини. Насіння соняшника та гарбузове насіння в найкращих
                    варіаціях.</p>
            </div>
            <div class="section__list" id="product-block-4">
                <?php
                // Ensure WooCommerce functions are available
                if (class_exists('WooCommerce')) {

                    // Arguments for the query
                    $args = array(
                        'post_type' => 'product',
                        'posts_per_page' => 4,
                        'status' => 'publish',
                        'tax_query' => array(
                            array(
                                'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                                'field' => 'slug',           // Use the category slug
                                'terms' => 'category-smakolik', // The slug of the category
                            ),
                        ),
                    );

                    // The Query
                    $query = new WP_Query($args);

                    // The Loop
                    if ($query->have_posts()) {
                        while ($query->have_posts()) {
                            $query->the_post();

                            // Get the global product object
                            global $product;

                            // Product variables
                            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                            $product_title = get_the_title();
                            $product_sku   = $product->get_sku() ?: 'N/A';
                            $product_price = $product->get_price();
                            $product_desc  = wp_trim_words($product->get_description(), 20, '...');

                            // Use a unique class for each product item (by using the product ID)
                            echo '<div class="section__item product-' . $product->get_id() . '">';
                            echo '<img class="product-image" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
                            echo '<p>' . esc_html($product_title) . '</p>';
                            echo '<span>SKU: <span class="product-sku">' . esc_html($product_sku) . '</span></span>';
                            echo '<span class="desc">' . esc_html($product_desc) . '</span>';
                            echo '<b class="product-price">Ціна: ' . esc_html($product_price) . ' грн</b>';

                            // Check if the product is simple or variable
                            if ($product->get_type() === 'variable') {
                                // Show variations only for variable products
                                $variations = $product->get_available_variations();
                                if ($variations) {
                                    echo '<select class="variation-select">';
                                    foreach ($variations as $variation) {
                                        $id = $variation['variation_id'];
                                        $sku = $variation['sku'] ?: 'N/A';
                                        $image_id = $variation['image_id'];
                                        $image_url = wp_get_attachment_image_url($image_id, 'full');
                                        $price = $variation['display_price'];  // Get variation price
                                        $pack_size_slug = esc_html($variation['attributes']['attribute_pa_pack-size']);
                                        $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
                                        $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';

                                        echo '<option value="' . $id . '" data-sku="' . $sku . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_html($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
                                    }
                                    echo '</select>';
                                } else {
                                    echo '<p>Немає доступних варіацій</p>';
                                }
                            }

                            // Add to Cart button (will be updated dynamically)
                            echo '<button class="btn btn__white" id="add-to-cart-button" data-product-id="' . $product->get_id() . '">Купити зараз!</button>';
                            echo '</div>';
                        }
                    } else {
                        echo '<p>Немає змінних продуктів для показу.</p>';
                    }

                    // Reset Post Data
                    wp_reset_postdata();
                } else {
                    echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
                }
                ?>
            </div>

            <?php
            // Show the "Show More" button only if there are more products
            if ($query->found_posts > 4) {
                echo '<button class="btn btn__showMore section__button" id="load-more-products-4" data-offset="4" data-category="category-smakolik">Показати ще</button>';
            }
            ?>
            <?php
            // Ensure WooCommerce functions are available
            if (class_exists('WooCommerce')) {

                // Arguments for the query
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => -1,
                    'status' => 'publish',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                            'field' => 'slug',           // Use the category slug
                            'terms' => 'category-smakolik', // The slug of the category
                        ),
                    ),
                );

                // The Query
                $query = new WP_Query($args);

                // The Loop
                if ($query->have_posts()) {
                    while ($query->have_posts()) {
                        $query->the_post();

                        // Get the global product object
                        global $product;

                        // Product variables
                        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                        $product_title = get_the_title();
                        $product_price = $product->get_price();
                        $product_desc  = wp_trim_words($product->get_description(), 20, '...');
                        $variations = $product->get_type() === 'variable' ? $product->get_available_variations() : null;

                        // Render the product in the desired layout
                        echo '<div class="section__listResponsive">';
                        echo '<div class="section__itemResponsive listed">';
                        echo '<div class="section__itemResponsiveWrapper">';
                        echo '<img id="switcher" class="switcher" src="' . esc_url(get_template_directory_uri() . '/images/ui/section-dropdown.svg') . '">';
                        echo '<h2>' . esc_html($product_title) . '</h2>';
                        echo '<div class="line"></div>';
                        echo '<img class="product-image" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
                        echo '<div class="line"></div>';
                        echo '<p>' . esc_html($product_desc) . '</p>';

                        // Variations dropdown for variable products
                        if ($variations) {
                            echo '<select class="variation-select">';
                            foreach ($variations as $variation) {
                                $id = $variation['variation_id'];
                                $sku = $variation['sku'] ?: 'N/A';
                                $image_id = $variation['image_id'];
                                $image_url = wp_get_attachment_image_url($image_id, 'full');
                                $price = $variation['display_price'];
                                $pack_size_slug = esc_html($variation['attributes']['attribute_pa_pack-size']);
                                $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
                                $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';

                                echo '<option value="' . esc_attr($id) . '" data-sku="' . esc_attr($sku) . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_attr($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
                            }
                            echo '</select>';
                        }

                        echo '<span class="mobile-product-price">Ціна: ' . esc_html($product_price) . ' грн</span>';
                        echo '<button class="btn btn__yellow" id="add-to-cart-button" data-product-id="' . $product->get_id() . '">Купити зараз!</button>';
                        echo '</div>';

                        echo '<div class="section__itemResponsiveListed">';
                        echo '<p>' . esc_html($product_title) . '</p>';
                        echo '<div class="section__itemResponsiveListedPrice">';
                        echo '<span>Ціна: ' . esc_html($product_price) . ' грн</span>';
                        echo '<button class="btn btn__yellow">Купити</button>';
                        echo '</div>';
                        echo '<img id="switcher2" src="' . esc_url(get_template_directory_uri() . '/images/ui/section-dropdown.svg') . '">';
                        echo '</div>';
                        echo '</div>';
                        echo '</div>';
                    }
                } else {
                    echo '<p>Немає продуктів для показу.</p>';
                }

                // Reset Post Data
                wp_reset_postdata();
            } else {
                echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
            }
            ?>
        </div>
        <img class="seed seed1" src="<?php echo get_template_directory_uri(); ?>/images/S5/S5__1.webp">
        <img class="seed seed2" src="<?php echo get_template_directory_uri(); ?>/images/S5/S5__2.webp">
        <img class="seed seed3" src="<?php echo get_template_directory_uri(); ?>/images/S5/S5__3.webp">
        <img class="seed seed4" src="<?php echo get_template_directory_uri(); ?>/images/S5/S5__4.webp">
        <img class="seed seed5" src="<?php echo get_template_directory_uri(); ?>/images/S5/S5__5.webp">
    </div>
    <div class="S6 parallax" id="section5">
        <div class="S6__title">
            <h2>Спеціальні пропозиції</h2>
            <p>Ви просили, і ми це зробили!</p>
        </div>
        <div class="S6__wrapper desktop">
            <div class="S6__left">
                <div class="S6__list slider-container slider5">
                    <?php
                    // Ensure WooCommerce functions are available
                    if (class_exists('WooCommerce')) {

                        // Arguments for the query
                        $args = array(
                            'post_type' => 'product',
                            'posts_per_page' => -1, // Change to -1 to display all products
                            'status' => 'publish',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                                    'field' => 'slug',           // Use the category slug
                                    'terms' => 'category-sale',  // The slug of the category
                                    'operator' => 'IN',
                                ),
                            ),
                        );

                        // The Query
                        $query = new WP_Query($args);

                        // The Loop
                        if ($query->have_posts()) {
                            while ($query->have_posts()) {
                                $query->the_post();

                                // Get the global product object
                                global $product;

                                // Product variables
                                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                                $product_title = get_the_title();
                                $product_sku   = $product->get_sku() ?: 'N/A';
                                $product_price = $product->get_price();
                                $product_desc  = wp_trim_words($product->get_description(), 20, '...');

                                // Sale image (You can add more logic to display if the product is on sale)
                                $sale_image = get_template_directory_uri() . '/images/S6/S6_saleLeft.webp'; // Assuming this is the sale icon
                    ?>

                                <div class="slide">
                                    <div class="S6__item">
                                        <div class="S6__item__img">
                                            <!-- Product image -->
                                            <img class="item" src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_title); ?>">
                                            <!-- Sale icon -->
                                            <img class="sale" src="<?php echo esc_url($sale_image); ?>" alt="Sale">
                                        </div>
                                        <p><?php echo esc_html($product_title); ?></p>
                                        <span><span class="size">80г</span> SKU: <?php echo esc_html($product_sku); ?></span>
                                        <span class="desc"><?php echo esc_html($product_desc); ?></span>
                                        <b>Ціна: <?php echo esc_html($product_price); ?> грн</b>
                                        <button class="btn btn__white" id="add-to-cart-button" data-product-id="<?php echo $product->get_id(); ?>">Купити зараз!</button>
                                    </div>
                                </div>

                    <?php
                            }
                        } else {
                            echo '<p>Немає продуктів в цій категорії.</p>';
                        }

                        // Reset Post Data
                        wp_reset_postdata();
                    } else {
                        echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
                    }
                    ?>
                    <button class="prev-button"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-left.webp"></button>
                    <button class="next-button"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-right.webp"></button>
                </div>
                <img class="seed seed1" src="<?php echo get_template_directory_uri(); ?>/images/S6/seed1.png">
                <img class="seed seed2" src="<?php echo get_template_directory_uri(); ?>/images/S6/seed2.png">
            </div>
            <div class="S6__right">
                <div class="S6__list slider-container slider6">
                    <?php
                    // Ensure WooCommerce functions are available
                    if (class_exists('WooCommerce')) {

                        // Arguments for the query
                        $args = array(
                            'post_type' => 'product',
                            'posts_per_page' => -1, // Change to -1 to display all products
                            'status' => 'publish',
                            'tax_query' => array(
                                array(
                                    'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                                    'field' => 'slug',           // Use the category slug
                                    'terms' => 'category-sale',  // The slug of the category
                                    'operator' => 'IN',
                                ),
                            ),
                        );

                        // The Query
                        $query = new WP_Query($args);

                        // The Loop
                        if ($query->have_posts()) {
                            while ($query->have_posts()) {
                                $query->the_post();

                                // Get the global product object
                                global $product;

                                // Product variables
                                $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                                $product_title = get_the_title();
                                $product_sku   = $product->get_sku() ?: 'N/A';
                                $product_price = $product->get_price();
                                $product_desc  = wp_trim_words($product->get_description(), 20, '...');

                                // Sale image (You can add more logic to display if the product is on sale)
                                $sale_image = get_template_directory_uri() . '/images/S6/S6_saleLeft.webp'; // Assuming this is the sale icon
                    ?>

                                <div class="slide">
                                    <div class="S6__item">
                                        <div class="S6__item__img">
                                            <!-- Product image -->
                                            <img class="item" src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_title); ?>">
                                            <!-- Sale icon -->
                                            <img class="sale" src="<?php echo esc_url($sale_image); ?>" alt="Sale">
                                        </div>
                                        <p><?php echo esc_html($product_title); ?></p>
                                        <span><span class="size">80г</span> SKU: <?php echo esc_html($product_sku); ?></span>
                                        <span class="desc"><?php echo esc_html($product_desc); ?></span>
                                        <b>Ціна: <?php echo esc_html($product_price); ?> грн</b>
                                        <button class="btn btn__white" id="add-to-cart-button" data-product-id="<?php echo $product->get_id(); ?>">Купити зараз!</button>
                                    </div>
                                </div>

                    <?php
                            }
                        } else {
                            echo '<p>Немає продуктів в цій категорії.</p>';
                        }

                        // Reset Post Data
                        wp_reset_postdata();
                    } else {
                        echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
                    }
                    ?>
                    <button class="prev-button"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-left.webp"></button>
                    <button class="next-button"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-right.webp"></button>
                </div>
                <img class="seed seed3" src="<?php echo get_template_directory_uri(); ?>/images/S6/seed3.png">
                <img class="seed seed4" src="<?php echo get_template_directory_uri(); ?>/images/S6/seed4.png">
            </div>
        </div>
        <div class="S6__wrapper responsive">
            <?php
            // Ensure WooCommerce functions are available
            if (class_exists('WooCommerce')) {

                // Arguments for the query
                $args = array(
                    'post_type' => 'product',
                    'posts_per_page' => 2, // Initially show 2 products
                    'status' => 'publish',
                    'tax_query' => array(
                        array(
                            'taxonomy' => 'product_cat', // WooCommerce product category taxonomy
                            'field' => 'slug',           // Use the category slug
                            'terms' => 'category-sale',  // The slug of the category
                            'operator' => 'IN',
                        ),
                    ),
                );

                // The Query
                $query = new WP_Query($args);

                // The Loop
                if ($query->have_posts()) {
                    echo '<div class="S6__list">'; // Start the list container
                    $counter = 0; // Counter to alternate between left and right

                    while ($query->have_posts()) {
                        $query->the_post();

                        // Get the global product object
                        global $product;

                        // Product variables
                        $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.webp';
                        $product_title = get_the_title();
                        $product_sku   = $product->get_sku() ?: 'N/A';
                        $product_price = $product->get_price();
                        $product_desc  = wp_trim_words($product->get_description(), 20, '...');

                        // Determine left or right class based on counter
                        $class = ($counter % 2 == 0) ? 'left' : 'right';
                        $counter++; // Increment counter for next iteration
            ?>

                        <div class="S6__item <?php echo $class; ?>">
                            <div class="S6__item__img">
                                <img class="item" src="<?php echo esc_url($product_image); ?>" alt="<?php echo esc_attr($product_title); ?>">
                                <img class="sale" src="<?php echo get_template_directory_uri(); ?>/images/S6/S6_saleLeft.webp" alt="Sale">
                            </div>
                            <p><?php echo esc_html($product_title); ?></p>
                            <span><span class="size">80г</span> SKU: <?php echo esc_html($product_sku); ?></span>
                            <span class="desc"><?php echo esc_html($product_desc); ?></span>
                            <b>Ціна: <?php echo esc_html($product_price); ?> грн</b>
                            <button class="btn btn__white" id="add-to-cart-button" data-product-id="<?php echo $product->get_id(); ?>">Купити зараз!</button>
                            <img class="seed seed5 responsiveseed" src="<?php echo get_template_directory_uri(); ?>/images/S6/seed5.png">
                        </div>

            <?php
                    }
                    echo '</div>'; // End the list container
                } else {
                    echo '<p>Немає продуктів в цій категорії.</p>';
                }

                // Reset Post Data
                wp_reset_postdata();
            } else {
                echo '<p>WooCommerce не активний. Будь ласка, активуйте плагін.</p>';
            }
            ?>
        </div>

        <!-- Show More Button -->
        <button class="btn btn__green showMore">Показати більше</button>

    </div>
    <div class="S7 section__wrapper" id="about-us">
        <div class="container S7__wrapper">
            <div class="S7__left">
                <p>Як ми створюємо ваші улюблені снеки!</p>
                <div class="line"></div>
                <span>Дізнайтесь більше про процес виробництва нашої продукції та переконайтесь у її якості.</span>
            </div>
            <div class="S7__right">
                <div class="S7__rightWrapper">
                    <img class="video" src="<?php echo get_template_directory_uri(); ?>/images/video.jpg" alt="">
                    <div class="videoButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/play.webp" alt="">
                        <span>Дивитись відео</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="S8_1">
        <h2>Продукція "Епік Фудс"- смачні й корисні перекуси для активного та здорового способу життя!</h2>
        <div class="carousel section__wrapper" id="carousel1">
            <div class="carousel-track">
                <div class="carousel-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S8_1.webp" alt="">
                    <p>Насіння соняшника:</p>
                    <span>Джерело вітаміну Е, антиоксидантів і корисних жирів, підтримує здоров'я шкіри.</span>
                </div>
                <div class="carousel-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S8_2.webp" alt="">
                    <p>Арахіс:</p>
                    <span>Насичений білками та корисними жирами, підтримує серцево-судинну систему, сприяє росту м'язів та відновленню енергії.</span>
                </div>
                <div class="carousel-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S8_1.webp" alt="">
                    <p>Насіння соняшника:</p>
                    <span>Джерело вітаміну Е, антиоксидантів і корисних жирів, підтримує здоров'я шкіри.</span>
                </div>
                <div class="carousel-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S8_2.webp" alt="">
                    <p>Арахіс:</p>
                    <span>Насичений білками та корисними жирами, підтримує серцево-судинну систему, сприяє росту м'язів та відновленню енергії.</span>
                </div>
                <div class="carousel-item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S8_1.webp" alt="">
                    <p>Насіння соняшника:</p>
                    <span>Джерело вітаміну Е, антиоксидантів і корисних жирів, підтримує здоров'я шкіри.</span>
                </div>
            </div>
            <button class="carousel-btn prev-btn"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-left.webp"></button>
            <button class="carousel-btn next-btn"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-right.webp"></button>
        </div>
    </div>
    <div class="S8_2">
        <h2>Що говорять наші клієнти та чому вони обирають нас</h2>
        <p class="desc">Дізнайтесь більше про процес виробництва нашої продукції та переконайтесь у її якості.</p>
        <div class="swiper" id="swiper2">
            <!-- Additional required wrapper -->
            <div class="swiper-wrapper">
                <!-- Slides -->
                <div class="swiper-slide">
                    <div class="comment">
                        <div class="comment__item">
                            <div class="comment__top">
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/comment-mark.png" alt="">
                                <span>Житомир</span>
                            </div>
                            <div class="comment__body">
                                <p>Антоненко Антон Антонович 1</p>
                                <span>Сам відгук, який написав користувачСам відгук, який написав користувачСам відгук, який написав користувачСам відгук, який написав...</span>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/rate.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="comment">
                        <div class="comment__item">
                            <div class="comment__top">
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/comment-mark.png" alt="">
                                <span>Житомир</span>
                            </div>
                            <div class="comment__body">
                                <p>Антоненко Антон Антонович 2</p>
                                <span>Сам відгук, який написав користувачСам відгук, який написав користувачСам відгук, який написав користувачСам відгук, який написав...</span>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/rate.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="comment">
                        <div class="comment__item">
                            <div class="comment__top">
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/comment-mark.png" alt="">
                                <span>Житомир</span>
                            </div>
                            <div class="comment__body">
                                <p>Антоненко Антон Антонович 3</p>
                                <span>Сам відгук, який написав користувачСам відгук, який написав користувачСам відгук, який написав користувачСам відгук, який написав...</span>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/rate.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="swiper-slide">
                    <div class="comment">
                        <div class="comment__item">
                            <div class="comment__top">
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/comment-mark.png" alt="">
                                <span>Житомир</span>
                            </div>
                            <div class="comment__body">
                                <p>Антоненко Антон Антонович 4</p>
                                <span>Сам відгук, який написав користувачСам відгук, який написав користувачСам відгук, який написав користувачСам відгук, який написав...</span>
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/rate.png" alt="">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- If we need navigation buttons -->
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        <button class="btn btn__yellow" id="btn-add-feedback">Додати відгук</button>
    </div>
    <div class="salary" id="cooperative">
        <div class="section__wrapper">
            <p>Заробляйте до 60% з нашою продукцією у вашому магазині.</p>
        </div>
    </div>
    <div class="S8">
        <div class="container">
            <h2 class="S8__title">Дистриб'ютори можуть отримувати до 35% прибутку з оптових закупівель</h2>
            <div class="S8__wrapper">
                <div class="S8__left">
                    <p>Приєднуйтесь до команди "Епік Фудс" і отримайте вигідні умови співпраці</p>
                    <input id="input_name_form" type="text" placeholder="Ваше імʼя">
                    <input id="input_name_tel_form" type="text" placeholder="Номер телефону">
                    <button class="btn btn__green" id="btn-want-to-coop">Хочу співпрацювати</button>
                </div>
                <div class="S8__right">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S8__1.webp" alt="">
                </div>
            </div>
        </div>
    </div>
    <div class="S9 parallax" id="blog">
        <div class="S9__title">Про нас пишуть</div>
        <div class="S9__list slider-container slider2 container">
            <?php
            $args = array(
                'post_type' => 'post',
                'posts_per_page' => -1,
                'orderby' => 'date',
                'order' => 'DESC',
            );
            $blog_query = new WP_Query($args);

            if ($blog_query->have_posts()) :
                while ($blog_query->have_posts()) : $blog_query->the_post();
                    $imageOfBlog = get_the_post_thumbnail_url(get_the_ID(), 'medium');
                    $blogHeader = get_the_title();
                    $blogText = wp_trim_words(get_the_excerpt(), 20, '...');
                    $blogLink = get_permalink();
            ?>

                    <div class="slide">
                        <div class="S9__item">
                            <img src="<?php echo esc_url($imageOfBlog); ?>" alt="<?php echo esc_attr($blogHeader); ?>">
                            <p><?php echo esc_html($blogHeader); ?></p>
                            <span class="textContainer"><?php echo esc_html($blogText); ?></span>
                            <button class="btn btn__yellow toggle-btn" id="toggleButton" onclick="window.location.href='<?php echo esc_url($blogLink); ?>';">
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                            </button>
                        </div>
                    </div>

            <?php
                endwhile;
                wp_reset_postdata(); // Reset the query data
            else :
                echo '<p>No posts found.</p>';
            endif;
            ?>
            <button class="prev-button"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-left.webp"></button>
            <button class="next-button"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-right.webp"></button>
        </div>
        <img class="seed seed1" src="<?php echo get_template_directory_uri(); ?>/images/S9/S9__1.webp">
        <img class="seed seed2" src="<?php echo get_template_directory_uri(); ?>/images/S9/S9__2.webp">
        <img class="seed seed3" src="<?php echo get_template_directory_uri(); ?>/images/S9/S9__3.webp">
        <img class="seed seed4" src="<?php echo get_template_directory_uri(); ?>/images/S9/S9__4.webp">
    </div>
    <!-- <div class="S9" id="blog">
        <div class="S9__title">Про нас пишуть</div>
        <div class="S9__list slider-container slider2 container">
            <div class="slide">
                <?php
                $args = array(
                    'post_type' => 'post',
                    'posts_per_page' => 8,
                );

                $query = new WP_Query($args);

                if ($query->have_posts()) :
                    while ($query->have_posts()) : $query->the_post();
                ?>
                        <div class="S9__item">
                            <img src="<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>" alt="<?php the_title(); ?>">
                            <p><?php the_title(); ?></p>
                            <span class="textContainer"><?php the_content(); ?></span>
                            <button class="btn btn__yellow toggle-btn" id="toggleButton">
                                <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="Toggle">
                            </button>
                        </div>
                <?php
                    endwhile;
                else :
                    echo '<p>No posts found.</p>';
                endif;
                wp_reset_postdata();
                ?>

                <div class="S9__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S9_item.webp">
                    <p>1 Назва статі блогу Назва статі блогу Назва статі блогу</p>
                    <span class="textContainer">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                        irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</span>
                    <button class="btn btn__yellow toggle-btn" id="toggleButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                    </button>
                </div>
                <div class="S9__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S9_item.webp">
                    <p>2 Назва статі блогу Назва статі блогу Назва статі блогу</p>
                    <span class="textContainer">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                        irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</span>
                    <button class="btn btn__yellow toggle-btn" id="toggleButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                    </button>
                </div>
                <div class="S9__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S9_item.webp">
                    <p>3 Назва статі блогу Назва статі блогу Назва статі блогу</p>
                    <span class="textContainer">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                        irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</span>
                    <button class="btn btn__yellow toggle-btn" id="toggleButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                    </button>
                </div>
            </div>
            <div class="slide">
                <div class="S9__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S9_item.webp">
                    <p>4 Назва статі блогу Назва статі блогу Назва статі блогу</p>
                    <span class="textContainer">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                        irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</span>
                    <button class="btn btn__yellow toggle-btn" id="toggleButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                    </button>
                </div>
                <div class="S9__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S9_item.webp">
                    <p>5 Назва статі блогу Назва статі блогу Назва статі блогу</p>
                    <span class="textContainer">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                        irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</span>
                    <button class="btn btn__yellow toggle-btn" id="toggleButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                    </button>
                </div>
                <div class="S9__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S9_item.webp">
                    <p>6 Назва статі блогу Назва статі блогу Назва статі блогу</p>
                    <span class="textContainer">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                        irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</span>
                    <button class="btn btn__yellow toggle-btn" id="toggleButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                    </button>
                </div>
            </div>
            <div class="slide">
                <div class="S9__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S9_item.webp">
                    <p>7 Назва статі блогу Назва статі блогу Назва статі блогу</p>
                    <span class="textContainer">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                        irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</span>
                    <button class="btn btn__yellow toggle-btn" id="toggleButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                    </button>
                </div>
                <div class="S9__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S9_item.webp">
                    <p>8 Назва статі блогу Назва статі блогу Назва статі блогу</p>
                    <span class="textContainer">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                        irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</span>
                    <button class="btn btn__yellow toggle-btn" id="toggleButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                    </button>
                </div>
                <div class="S9__item">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/S9_item.webp">
                    <p>9 Назва статі блогу Назва статі блогу Назва статі блогу</p>
                    <span class="textContainer">Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do
                        eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis
                        nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute
                        irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.
                        Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit
                        anim id est laborum.</span>
                    <button class="btn btn__yellow toggle-btn" id="toggleButton">
                        <img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-list-down.webp" alt="">
                    </button>
                </div>
            </div>
            <button class="prev-button"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-left.webp"></button>
            <button class="next-button"><img src="<?php echo get_template_directory_uri(); ?>/images/ui/arrow-right.webp"></button>
        </div>
    </div> -->
    <div class="S10">
        <div class="section__wrapper">
            <h2>“Епік Фудс" – Смак та якість у кожному продукті</h2>
            <p>ОБИРАЙ свій смак!</p>
            <a href="<?php echo get_home_url(); ?>/#gold-niva">
                <button class="btn btn__green">Купити</button>
            </a>
        </div>
        <img class="S10__responsive" src="<?php echo get_template_directory_uri(); ?>/images/S10_responsive.webp" alt="">
    </div>
    <div class="S11 container" id="contacts">
        <h2 class="S11__title">Контакти</h2>
        <div class="map">
            <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m8!1m3!1d40816.819137903825!2d28.7326529!3d50.2536281!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x472c636f3b661b63%3A0xccb2bd09346d1253!2zRVBJQyBGb29kcyAvINCV0J_QhtCaINCk0KPQlNCh!5e0!3m2!1suk!2sua!4v1734532385662!5m2!1suk!2sua" class="map" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
        </div>
        <div class="S11__address">
            <img src="<?php echo get_template_directory_uri(); ?>/images/ui/mapMark.webp" alt="">
            <p><span>Адреса:</span> вул. Параджанова 80, Житомир, Україна</p>
        </div>
        <div class="S11__list">
            <div class="S11__list__item">
                <h2>Генеральний директор</h2>
                <b></b>
                <span>Дмитро Епік</span>
                <p>+38 (063) 88 66 777</p>
                <small></small>
            </div>
            <div class="S11__list__item">
                <h2>Торговий відділ</h2>
                <b>Керівник торгового відділу</b>
                <span>Дмитро</span>
                <p>+38 (096) 684 67 56</p>
                <small></small>
            </div>
            <div class="S11__list__item">
                <h2></h2>
                <b>Територіальний менеджер</b>
                <span>Дмитро</span>
                <p>+38 (096) 684 67 56</p>
                <small>Регіони: Житомирський, Рівненський.</small>
            </div>
            <div class="S11__list__item">
                <h2></h2>
                <b>Регіональний менеджер</b>
                <span>Ольга</span>
                <p>+38 (096) 684 67 56</p>
                <small>Регіони: Тернопільський, Івано-Франківський, Львівський, Волинський.</small>
            </div>
            <div class="S11__list__item">
                <h2>Фінансовий відділ</h2>
                <b>Бухгалтер</b>
                <span>Марина Осолінська</span>
                <p>+38 (096) 381 33 30</p>
                <small></small>
            </div>
            <div class="S11__list__item">
                <h2>Відділ маркетингу</h2>
                <b>Маркетолог</b>
                <span>Юлія Романенко</span>
                <p>+38 (068) 549 54 99</p>
                <small></small>
            </div>
        </div>
    </div>

</main>

<?php get_footer(); ?>