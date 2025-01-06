<?php
// This function enqueues the Normalize.css for use. The first parameter is a name for the stylesheet, the second is the URL. Here we
// use an online version of the css file.
function add_normalize_CSS()
{
    wp_enqueue_style('normalize-styles', "https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css");
}

add_action('wp_enqueue_scripts', 'add_normalize_CSS');

function my_theme_enqueue_styles()
{
    wp_enqueue_style('main-styles', get_template_directory_uri() . '/styles/main.css', array(), null);
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

function enqueue_language_switcher_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('language-switcher', get_template_directory_uri() . '/js/language-switcher.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_language_switcher_script');


// Register a new sidebar simply named 'sidebar'
function add_widget_support()
{
    register_sidebar(array(
        'name'          => 'Sidebar',
        'id'            => 'sidebar',
        'before_widget' => '<div>',
        'after_widget'  => '</div>',
        'before_title'  => '<h2>',
        'after_title'   => '</h2>',
    ));
}
// Hook the widget initiation and run our function
add_action('widgets_init', 'add_widget_support');

// Register a new navigation menu
function add_Main_Nav()
{
    register_nav_menu('header-menu', __('Header Menu'));
}
// Hook to the init action hook, run our navigation menu function
add_action('init', 'add_Main_Nav');

// Add this to your theme's functions.php



// function enqueue_cart_modal_script()
// {
//     wp_enqueue_script('cart-modal', get_template_directory_uri() . '/js/cart-modal.js', array('jquery'), null, true);

//     // Localize script to pass AJAX URL and security nonce to the script
//     wp_localize_script('cart-modal', 'cart_modal_params', array(
//         'ajax_url' => admin_url('admin-ajax.php'),
//         'nonce' => wp_create_nonce('cart_modal_nonce')
//     ));
// }
// add_action('wp_enqueue_scripts', 'enqueue_cart_modal_script');

// Enqueue script for AJAX functionality
function enqueue_ajax_script()
{
    wp_enqueue_script('load-more-products', get_template_directory_uri() . '/js/load-more-products.js', array('jquery'), null, true);

    // Localize script to pass the AJAX URL to JS
    wp_localize_script('load-more-products', 'ajax_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}


add_action('wp_enqueue_scripts', 'enqueue_ajax_script');

// Handle the AJAX request for loading more products
// Handle the AJAX request for loading more products
function load_more_products()
{
    // Get the offset, category slug, and block ID from the AJAX request
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';
    $block_id = isset($_GET['block_id']) ? sanitize_text_field($_GET['block_id']) : '';

    // Set up the query args to fetch more products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 4,
        'offset' => $offset,
        'status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category,  // Filter by the category slug passed from JS
            ),
        ),
    );

    // Perform the query
    $query = new WP_Query($args);

    // The Loop
    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();

            // Get the global product object
            global $product;

            // Product variables
            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.png';
            $product_title = get_the_title();
            $product_sku   = $product->get_sku() ?: 'N/A';
            $product_price = $product->get_price();
            $product_desc  = wp_trim_words($product->get_description(), 20, '...');

            // Use a unique class for each product item (by using the product ID)
            echo '<div class="section__item product-' . $product->get_id() . '">';
            echo '<img class="product-image" style="max-height:253px;" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
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
    }

    // Reset Post Data
    wp_reset_postdata();

    // End the AJAX request
    die();
}
add_action('wp_ajax_load_more_products', 'load_more_products');
add_action('wp_ajax_nopriv_load_more_products', 'load_more_products');

function load_more_products_mobile()
{
    // Get the offset and category from the AJAX request
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'category-sale';

    // Set up the query args to fetch more products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => 2, // Load 2 products at a time
        'offset' => $offset,
        'status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field' => 'slug',
                'terms' => $category,  // Filter by the category slug
            ),
        ),
    );

    // Perform the query
    $query = new WP_Query($args);

    // The Loop
    if ($query->have_posts()) {
        $counter = $offset; // Initialize counter with the offset

        while ($query->have_posts()) {
            $query->the_post();

            // Get the global product object
            global $product;

            // Product variables
            $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.png';
            $product_title = get_the_title();
            $product_sku   = $product->get_sku() ?: 'N/A';
            $product_price = $product->get_price();
            $product_desc  = wp_trim_words($product->get_description(), 20, '...');

            // Determine left or right class based on counter
            $class = ($counter % 2 == 0) ? 'left' : 'right';
            $counter++; // Increment counter for next iteration

            // Use echo to output HTML
            echo '<div class="S6__item ' . esc_attr($class) . '">';
            echo '    <div class="S6__item__img">';
            echo '        <img class="item" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
            echo '        <img class="sale" src="' . esc_url(get_template_directory_uri() . '/images/S6/S6_saleLeft.png') . '" alt="Sale">';
            echo '    </div>';
            echo '    <p>' . esc_html($product_title) . '</p>';
            echo '    <span><span class="size">80г</span> SKU: ' . esc_html($product_sku) . '</span>';
            echo '    <span class="desc">' . esc_html($product_desc) . '</span>';
            echo '    <b>Ціна: ' . esc_html($product_price) . ' грн</b>';
            echo '    <button class="btn btn__white">Купити зараз!</button>';
            echo '</div>';
        }
    }

    // Reset Post Data
    wp_reset_postdata();

    // End the AJAX request
    die();
}

add_action('wp_ajax_load_more_products_mobile', 'load_more_products_mobile');
add_action('wp_ajax_nopriv_load_more_products_mobile', 'load_more_products_mobile');



function enqueue_custom_script()
{
    wp_enqueue_script('custom-script', get_template_directory_uri() . '/js/cart-modal.js', array('jquery'), null, true);
    wp_localize_script('custom-script', 'themeData', array(
        'themeUri' => get_template_directory_uri()
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_script');


// Add the following to your theme's functions.php to handle AJAX requests
add_action('wp_ajax_get_cart_items', 'get_cart_items');
add_action('wp_ajax_nopriv_get_cart_items', 'get_cart_items');
function get_cart_items()
{
    $cart = WC()->cart->get_cart();
    $items = [];
    $total = WC()->cart->get_total();

    foreach ($cart as $cart_item_key => $cart_item) {
        $product = $cart_item['data'];
        $items[] = [
            'key' => $cart_item_key,
            'name' => $product->get_name(),
            'description' => $product->get_short_description(),
            'sku' => $product->get_sku(),
            'image' => wp_get_attachment_image_url($product->get_image_id(), 'thumbnail'),
            'quantity' => $cart_item['quantity'],
            'subtotal' => $cart_item['line_subtotal'],
        ];
    }

    wp_send_json_success(['items' => $items, 'total' => $total]);
}

add_action('wp_ajax_update_cart_quantity', 'update_cart_quantity');
add_action('wp_ajax_nopriv_update_cart_quantity', 'update_cart_quantity');
function update_cart_quantity()
{
    $item_key = sanitize_text_field($_POST['item_key']);
    $quantity_change = intval($_POST['quantity_change']);

    if (isset(WC()->cart->cart_contents[$item_key])) {
        $current_quantity = WC()->cart->cart_contents[$item_key]['quantity'];
        WC()->cart->set_quantity($item_key, max(1, $current_quantity + $quantity_change));
    }

    wp_send_json_success();
}

add_action('wp_ajax_remove_cart_item', 'remove_cart_item');
add_action('wp_ajax_nopriv_remove_cart_item', 'remove_cart_item');
function remove_cart_item()
{
    $item_key = sanitize_text_field($_POST['item_key']);
    WC()->cart->remove_cart_item($item_key);
    wp_send_json_success();
}

function custom_checkout_scripts()
{
    if (is_checkout()) {
        wp_enqueue_script(
            'custom-checkout-script',
            get_template_directory_uri() . '/js/custom-checkout.js',
            array('jquery'),
            '1.0',
            true
        );
    }
}
add_action('wp_enqueue_scripts', 'custom_checkout_scripts');
