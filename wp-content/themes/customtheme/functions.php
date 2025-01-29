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
            $weight = get_post_meta($id, '_weight', true);

            if ($weight) {
                $weight_in_grams = $weight * 1000;
            } else {
                $weight_in_grams = 'N/A';
            }

            $html .= '<option value="' . $id . '" data-sku="' . $sku . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_html($price) . '" data-weight="' . esc_html($weight_in_grams) . '">Розмір упаковки: ' . esc_html($pack_size_name) . ' | Вага: ' . esc_html($weight_in_grams) . ' г</option>';

            // $html .= '<option value="' . $id . '" data-sku="' . $sku . '" data-image="' . esc_url($image_url) . '" data-price="' . esc_html($price) . '">Розмір упаковки: ' . esc_html($pack_size_name) . '</option>';
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

        // Get variation attributes
        $attributes = $product->get_attributes();

        // Prepare product name with attributes
        $product_name = $product->get_name();

        // Check for 'pack-size' attribute
        if (isset($cart_item['variation']['attribute_pa_pack-size'])) {
            $pack_size_slug = esc_html($cart_item['variation']['attribute_pa_pack-size']);
            $pack_size_term = get_term_by('slug', $pack_size_slug, 'pa_pack-size');
            $pack_size_name = $pack_size_term ? $pack_size_term->name : 'Unknown Size';
            $product_name .= ' - ' . $pack_size_name;
        }

        // Add cart item data
        $items[] = [
            'key' => $cart_item_key,
            'name' => $product_name, // Updated name with attribute term names
            'description' => $product->get_short_description(),
            'sku' => $product->get_sku(),
            'image' => wp_get_attachment_image_url($product->get_image_id(), 'large'),
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

add_action('wp_ajax_send_email', 'handle_send_service');
add_action('wp_ajax_nopriv_send_email', 'handle_send_service');

function handle_send_service()
{
    $first_name = sanitize_text_field($_POST['first_name']);
    $phone_number = sanitize_text_field($_POST['phone_number']);
    $service = sanitize_text_field($_POST['service']);

    $to = 'epicfoods2oo4@gmail.com';
    $subject = "Послуга - $service";
    $message = "First Name: $first_name\nPhone Number: $phone_number";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    if (wp_mail($to, $subject, $message, $headers)) {
        wp_send_json_success('Email sent successfully.');
    } else {
        wp_send_json_error('Failed to send email.');
    }

    wp_die();
}


function enqueue_my_script()
{
    wp_enqueue_script('handle_send_service', get_template_directory_uri() . '/js/modal-form.js', ['jquery'], null, true);
    wp_localize_script('handle_send_service', 'my_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_my_script');

function custom_wp_mail_from($email)
{
    return 'admin@dev.epic-foods.com.ua'; // Replace with your desired "From" email address
}

function custom_wp_mail_from_name($name)
{
    return 'Epic Foods'; // Replace with your desired "From" name
}

add_filter('wp_mail_from', 'custom_wp_mail_from');
add_filter('wp_mail_from_name', 'custom_wp_mail_from_name');

add_action('wp_ajax_send_feedback', 'handle_send_feedback');
add_action('wp_ajax_nopriv_send_feedback', 'handle_send_feedback');

function handle_send_feedback()
{
    $fname = sanitize_text_field($_POST['fname']);
    $ftext = sanitize_text_field($_POST['ftext']);

    $to = 'epicfoods2oo4@gmail.com';
    $subject = "Відгук";
    $message = "First Name: $fname\nFeedback Text: $ftext";
    $headers = ['Content-Type: text/plain; charset=UTF-8'];

    if (wp_mail($to, $subject, $message, $headers)) {
        wp_send_json_success('Email sent successfully.');
    } else {
        wp_send_json_error('Failed to send email.');
    }

    wp_die();
}

function enqueue_my_handle_send_feedback_script()
{
    wp_enqueue_script('handle_send_feedback', get_template_directory_uri() . '/js/feedback-form.js', ['jquery'], null, true);
    wp_localize_script('handle_send_feedback', 'my_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php')
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_my_handle_send_feedback_script');

// Save checkout fields to session
function save_checkout_fields_to_session($post_data)
{
    // Parse the posted data into an array
    parse_str($post_data, $checkout_data);

    // Save the fields you want into WooCommerce session
    foreach ($checkout_data as $key => $value) {
        if (strpos($key, 'billing_') === 0 || strpos($key, 'shipping_') === 0) {
            WC()->session->set($key, $value);
        }
    }
}
add_action('woocommerce_checkout_update_order_review', 'save_checkout_fields_to_session');

// AJAX handler to save checkout fields
function save_checkout_data_via_ajax()
{
    // Check if data is posted
    if (isset($_POST['checkout_data'])) {
        $checkout_data = wc_clean($_POST['checkout_data']);

        // Save the fields to WooCommerce session
        foreach ($checkout_data as $key => $value) {
            WC()->session->set($key, $value);
        }

        wp_send_json_success(['message' => 'Data saved successfully']);
    } else {
        wp_send_json_error(['message' => 'No data to save']);
    }

    wp_die();
}
add_action('wp_ajax_save_checkout_data', 'save_checkout_data_via_ajax');
add_action('wp_ajax_nopriv_save_checkout_data', 'save_checkout_data_via_ajax');

// Repopulate checkout fields from session
function repopulate_checkout_fields_from_session($input, $key)
{
    $saved_value = WC()->session->get($key);
    if (!empty($saved_value)) {
        $input = $saved_value;
    }
    return $input;
}
add_filter('woocommerce_checkout_get_value', 'repopulate_checkout_fields_from_session', 10, 2);

function enqueue_checkout_save_script()
{
    if (is_checkout()) {
        wp_enqueue_script('custom-checkout-save', get_template_directory_uri() . '/js/custom-checkout-save.js', ['jquery'], null, true);
        wp_localize_script('custom-checkout-save', 'ajax_object', [
            'ajax_url' => admin_url('admin-ajax.php'),
        ]);
    }
}
add_action('wp_enqueue_scripts', 'enqueue_checkout_save_script');

function send_order_details_email($order_id)
{
    if (!$order_id) {
        return;
    }

    // Get the order object
    $order = wc_get_order($order_id);

    // Define the email content
    $to = 'epicfoods2oo4@gmail.com'; // Replace with your email address
    $subject = 'New Order Placed: #' . $order->get_order_number();
    $headers = ['Content-Type: text/html; charset=UTF-8'];

    // Prepare email body
    $body = '<h1>New Order Details</h1>';
    $body .= '<p><strong>Order ID:</strong> ' . $order->get_order_number() . '</p>';
    $body .= '<p><strong>Date:</strong> ' . wc_format_datetime($order->get_date_created()) . '</p>';
    $body .= '<p><strong>Customer Name:</strong> ' . $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() . '</p>';
    $body .= '<p><strong>Email:</strong> ' . $order->get_billing_email() . '</p>';
    $body .= '<p><strong>Phone:</strong> ' . $order->get_billing_phone() . '</p>';
    $body .= '<h2>Items:</h2>';
    $body .= '<ul>';

    foreach ($order->get_items() as $item_id => $item) {
        $product = $item->get_product();
        $body .= '<li>' . $item->get_name() . ' x ' . $item->get_quantity() . ' - ' . wc_price($item->get_total()) . '</li>';
    }

    $body .= '</ul>';
    $body .= '<p><strong>Total:</strong> ' . $order->get_formatted_order_total() . '</p>';

    // Send the email
    wp_mail($to, $subject, $body, $headers);
}
add_action('woocommerce_thankyou', 'send_order_details_email');

add_action('woocommerce_cart_updated', 'conditionally_apply_coupon_g2zt8ks6');
add_action('woocommerce_checkout_update_order_review', 'conditionally_apply_coupon_g2zt8ks6');

function conditionally_apply_coupon_g2zt8ks6()
{
    // Ensure WooCommerce is active and cart object exists
    if (!WC()->cart) {
        return;
    }

    // Define the coupon code
    $coupon_code = 'G2ZT8KS6';

    // Get the cart total before discounts
    $cart_total = WC()->cart->get_subtotal();
    $minimum_total = 5000;

    // Apply or remove the coupon based on the cart total
    if ($cart_total >= $minimum_total) {
        // Apply the coupon if not already applied
        if (!WC()->cart->has_discount($coupon_code)) {
            WC()->cart->apply_coupon($coupon_code);
        }
    } else {
        // Remove the coupon if the total is below the threshold
        if (WC()->cart->has_discount($coupon_code)) {
            WC()->cart->remove_coupon($coupon_code);
        }
    }
}

function write_to_json_file()
{
    // Get data from the AJAX request
    $first_name = sanitize_text_field($_POST['firstName']);
    $phone_number = sanitize_text_field($_POST['phoneNumber']);
    $service = sanitize_text_field($_POST['service']);

    // Define the JSON file path
    $file_path = get_stylesheet_directory() . '/service-orders.json';

    // Read the existing data from the JSON file
    $existing_data = [];
    if (file_exists($file_path)) {
        $existing_data = json_decode(file_get_contents($file_path), true) ?: [];
    }

    // Append the new data
    $existing_data[] = [
        'firstName' => $first_name,
        'phoneNumber' => $phone_number,
        'service' => $service,
    ];

    // Write the updated data back to the JSON file
    file_put_contents($file_path, json_encode($existing_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

    wp_send_json_success('Data saved successfully!');
}
add_action('wp_ajax_write_to_json_file', 'write_to_json_file');
add_action('wp_ajax_nopriv_write_to_json_file', 'write_to_json_file');

function enqueue_custom_scripts()
{
    wp_enqueue_script('custom-script', get_stylesheet_directory_uri() . '/js/modal-form.js', ['jquery'], null, true);

    // Localize script to pass AJAX URL
    wp_localize_script('custom-script', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

function delete_service_order()
{
    $index = isset($_POST['index']) ? intval($_POST['index']) : null;

    if ($index === null) {
        wp_send_json_error('Invalid index.');
        return;
    }

    $file_path = get_stylesheet_directory() . '/service-orders.json';

    if (file_exists($file_path)) {
        $orders = json_decode(file_get_contents($file_path), true) ?: [];

        if (isset($orders[$index])) {
            // Remove the specified row
            unset($orders[$index]);
            $orders = array_values($orders); // Reindex the array

            // Write the updated data back to the JSON file
            file_put_contents($file_path, json_encode($orders, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

            wp_send_json_success('Order deleted successfully.');
        } else {
            wp_send_json_error('Order not found.');
        }
    } else {
        wp_send_json_error('File does not exist.');
    }
}
add_action('wp_ajax_delete_service_order', 'delete_service_order');
add_action('wp_ajax_nopriv_delete_service_order', 'delete_service_order');
