<?php

// Enqueue Normalize CSS
function add_normalize_CSS()
{
    wp_enqueue_style('normalize-styles', "https://cdnjs.cloudflare.com/ajax/libs/normalize/7.0.0/normalize.min.css");
}
add_action('wp_enqueue_scripts', 'add_normalize_CSS');

// Enqueue Theme Styles
function my_theme_enqueue_styles()
{
    wp_enqueue_style('main-styles', get_template_directory_uri() . '/styles/main.css', array(), null);
}
add_action('wp_enqueue_scripts', 'my_theme_enqueue_styles');

// Enqueue Language Switcher Script
function enqueue_language_switcher_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('language-switcher', get_template_directory_uri() . '/js/language-switcher.js', array('jquery'), null, true);
}
add_action('wp_enqueue_scripts', 'enqueue_language_switcher_script');

// Register Sidebar
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
add_action('widgets_init', 'add_widget_support');

// Register Navigation Menu
function add_Main_Nav()
{
    register_nav_menu('header-menu', __('Header Menu'));
}
add_action('init', 'add_Main_Nav');

// Enqueue Custom Scripts
function enqueue_custom_script()
{
    wp_enqueue_script('custom-script', get_template_directory_uri() . '/js/cart-modal.js', array('jquery'), null, true);
    wp_localize_script('custom-script', 'themeData', array(
        'themeUri' => get_template_directory_uri(),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_custom_script');

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

// Enqueue AJAX Scripts
function enqueue_ajax_script()
{
    wp_enqueue_script('load-more-products', get_template_directory_uri() . '/js/load-more-products.js', array('jquery'), null, true);
    wp_localize_script('load-more-products', 'ajax_params', array(
        'ajax_url' => admin_url('admin-ajax.php'),
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_ajax_script');

// AJAX Handler for Loading More Products
function load_more_products()
{
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : '';

    $screen_width = isset($_COOKIE['screen_width']) ? intval($_COOKIE['screen_width']) : 0;
    $posts_per_page = 4;
    if ($screen_width > 960 && $screen_width < 1600) {
        $posts_per_page = 3;
    }
    // Query for more products
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => $posts_per_page,
        'offset'         => $offset,
        'status'         => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category,
            ),
        ),
    );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            echo get_product_html($product);
        }
    }
    wp_reset_postdata();
    die();
}
add_action('wp_ajax_load_more_products', 'load_more_products');
add_action('wp_ajax_nopriv_load_more_products', 'load_more_products');

// Mobile AJAX Handler for Loading More Products
function load_more_products_mobile()
{
    $offset = isset($_GET['offset']) ? (int) $_GET['offset'] : 0;
    $category = isset($_GET['category']) ? sanitize_text_field($_GET['category']) : 'category-sale';

    // Query for more products
    $args = array(
        'post_type'      => 'product',
        'posts_per_page' => 2,
        'offset'         => $offset,
        'status'         => 'publish',
        'tax_query'      => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category,
            ),
        ),
    );

    $query = new WP_Query($args);
    $counter = $offset;

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            global $product;
            $class = ($counter % 2 == 0) ? 'left' : 'right';
            $counter++;
            echo get_product_html($product, $class);
        }
    }
    wp_reset_postdata();
    die();
}
add_action('wp_ajax_load_more_products_mobile', 'load_more_products_mobile');
add_action('wp_ajax_nopriv_load_more_products_mobile', 'load_more_products_mobile');

// Helper function to generate product HTML
function get_product_html($product, $class = '')
{
    $product_image = wp_get_attachment_image_src(get_post_thumbnail_id(), 'full')[0] ?? get_template_directory_uri() . '/images/default.png';
    $product_title = get_the_title();
    $product_sku   = $product->get_sku() ?: 'N/A';
    $product_price = $product->get_price();
    $product_desc  = wp_trim_words($product->get_description(), 20, '...');

    // Start the product HTML structure
    $html = '<div class="section__item ' . esc_attr($class) . '">';
    $html .= '<img class="product-image" style="max-height:253px;" src="' . esc_url($product_image) . '" alt="' . esc_attr($product_title) . '">';
    $html .= '<p>' . esc_html($product_title) . '</p>';
    $html .= '<span>SKU: <span class="product-sku">' . esc_html($product_sku) . '</span></span>';
    $html .= '<span class="desc">' . esc_html($product_desc) . '</span>';
    $html .= '<b class="product-price">Ціна: ' . esc_html($product_price) . ' грн</b>';

    // Show variations for variable products
    if ($product->get_type() === 'variable') {
        $html .= get_variation_select($product);
    }

    // Add to Cart button
    $html .= '<button class="btn btn__white" id="add-to-cart-button" data-product-id="' . $product->get_id() . '">Купити зараз!</button>';
    $html .= '</div>';

    return $html;
}

// Helper function to generate variation select for variable products
function get_variation_select($product)
{
    $variations = $product->get_available_variations();
    $html = '';
    if ($variations) {
        $html .= '<select class="variation-select">';
        foreach ($variations as $variation) {
            $id = $variation['variation_id'];
            $sku = $variation['sku'] ?: 'N/A';
            $image_id = $variation['image_id'];
            $image_url = wp_get_attachment_image_url($image_id, 'full');
            $price = $variation['display_price'];
            $pack_size_slug = esc_html($variation['attributes']['attribute_pa_pack-size']);
            $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
            $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';

            $html .= '<option value="' . $id . '" data-sku="' . $sku . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_html($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
        }
        $html .= '</select>';
    } else {
        $html .= '<p>Немає доступних варіацій</p>';
    }
    return $html;
}

// Handle cart items AJAX
function get_cart_items()
{
    $cart = WC()->cart->get_cart();
    $items = [];

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
            'price' => $product->get_price(),
        ];
    }

    wp_send_json_success(['items' => $items, 'total' => WC()->cart->get_total()]);
}
add_action('wp_ajax_get_cart_items', 'get_cart_items');
add_action('wp_ajax_nopriv_get_cart_items', 'get_cart_items');

// Update cart quantity AJAX
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
add_action('wp_ajax_update_cart_quantity', 'update_cart_quantity');
add_action('wp_ajax_nopriv_update_cart_quantity', 'update_cart_quantity');

// Remove cart item AJAX
function remove_cart_item()
{
    $item_key = sanitize_text_field($_POST['item_key']);
    WC()->cart->remove_cart_item($item_key);
    wp_send_json_success();
}
add_action('wp_ajax_remove_cart_item', 'remove_cart_item');
add_action('wp_ajax_nopriv_remove_cart_item', 'remove_cart_item');

function update_cart_items()
{
    // Ensure WooCommerce is loaded
    if (!class_exists('WC_Cart')) {
        wp_send_json_error(['message' => 'WooCommerce is not loaded.']);
        return;
    }

    // Validate input
    if (!isset($_POST['cart_items'])) {
        wp_send_json_error(['message' => 'No cart items provided.']);
        return;
    }

    // Decode cart items
    $cart_items = json_decode(stripslashes($_POST['cart_items']), true);

    if (!is_array($cart_items)) {
        wp_send_json_error(['message' => 'Invalid cart items format.']);
        return;
    }

    // Clear the WooCommerce cart
    WC()->cart->empty_cart();

    // Add items to the cart
    foreach ($cart_items as $item) {
        if (!isset($item['sku']) || !isset($item['quantity'])) {
            continue; // Skip invalid items
        }

        $product_id = wc_get_product_id_by_sku($item['sku']);
        if (!$product_id) {
            continue; // Skip items with invalid SKUs
        }

        $quantity = max(1, intval($item['quantity']));
        WC()->cart->add_to_cart($product_id, $quantity);
    }

    wp_send_json_success(['message' => 'Cart updated successfully.']);
}
add_action('wp_ajax_update_cart_items', 'update_cart_items');
add_action('wp_ajax_nopriv_update_cart_items', 'update_cart_items');

function get_screen_size()
{
    if (isset($_COOKIE['screen_width'])) {
        return intval($_COOKIE['screen_width']);
    }
    return 0; // Default if not set
}

// Add screen width to cookies via JavaScript
add_action('wp_head', function () {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function () {
            const screenWidth = window.innerWidth;
            if (document.cookie.indexOf("screen_width") === -1 || document.cookie.indexOf("screen_width=" + screenWidth) === -1) {
                document.cookie = "screen_width=" + screenWidth + "; path=/";
            }
        });
    </script>';
});
