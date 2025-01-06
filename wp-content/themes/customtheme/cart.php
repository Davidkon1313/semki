<?php get_header(); ?>

<main>
    <div class="cash">
        <div class="cash__head">
            <p>Ваша корзина</p>
        </div>
        <?php
        $cart_items = WC()->cart->get_cart();

        if (! empty($cart_items)) {
            foreach ($cart_items as $cart_item_key => $cart_item) {
                $product = $cart_item['data'];
                $product_name = $product->get_name();
                $product_quantity = $cart_item['quantity'];
                $product_price = $product->get_price();
                $product_sku = $product->get_sku();
                $product_img = wp_get_attachment_image_url($product->get_image_id(), 'full');
                $product_attribute = $product->get_attribute('pa_pack-size');

                echo '<div class="cash__item" data-cart-item-key="' . esc_attr($cart_item_key) . '">';
                echo '<div class="cash__itemWrapp">';
                echo '<div class="cash__img">';
                echo '<img src="' . esc_url($product_img) . '" alt="' . esc_attr($product_name) . '">';
                echo '</div>';
                echo '<div class="cash__text">';
                echo '<p>' . esc_html($product_name) . '</p>';
                echo '<span>' . esc_html($product_attribute) . '</span>';
                echo '<span>' . esc_html($product_sku) . '</span>';
                echo '</div>';
                echo '<div class="cash__number">';
                echo '<button class="minus">-</button>';
                echo '<span class="quantity">' . esc_html($product_quantity) . '</span>';
                echo '<button class="plus">+</button>';
                echo '</div>';
                echo '<div class="cash__sum">';
                echo '<span>' . $product_price . '</span> <span>грн</span>';
                echo '</div>';
                echo '</div>';
                echo '</div>';
            }
        } else {
            echo '<p>Your cart is empty.</p>';
        }
        ?>

        <script>
            jQuery(document).ready(function($) {
                $('.cash__item .plus, .cash__item .minus').on('click', function() {
                    var $this = $(this);
                    var cart_item_key = $this.closest('.cash__item').data('cart-item-key');
                    var current_quantity = parseInt($this.siblings('.quantity').text());

                    // Determine if it's a plus or minus button
                    if ($this.hasClass('plus')) {
                        current_quantity++;
                    } else if ($this.hasClass('minus') && current_quantity > 1) {
                        current_quantity--;
                    }

                    // Send the updated quantity to the server via AJAX
                    $.ajax({
                        url: wc_cart_params.ajax_url,
                        type: 'POST',
                        data: {
                            action: 'update_cart_item_quantity',
                            cart_item_key: cart_item_key,
                            quantity: current_quantity,
                        },
                        success: function(response) {
                            if (response.success) {
                                // Update the quantity displayed on the page
                                $this.siblings('.quantity').text(response.data.new_quantity);

                                // Optionally update cart totals dynamically
                                if (response.data.cart_totals) {
                                    // Example: $('#cart-totals').html(response.data.cart_totals.total_price);
                                }
                            } else {
                                alert(response.data.message || 'Error updating quantity');
                            }
                        },
                        error: function() {
                            alert('Error occurred while updating quantity');
                        }
                    });
                });
            });
        </script>
        <div class="cash__item">
            <div class="cash__itemWrapp">
                <div class="cash__img">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/cashImg.png" alt="">
                </div>
                <div class="cash__text">
                    <p>Насіння соняшника смажене</p>
                    <span>80 г</span>
                    <span>SKU: U194729901</span>
                </div>
                <div class="cash__number">
                    <button>-</button>
                    <span>1</span>
                    <button>+</button>
                </div>
                <div class="cash__sum">
                    <span>35</span> <span>грн</span>
                </div>
            </div>
        </div>
        <div class="cash__item">
            <div class="cash__itemWrapp">
                <div class="cash__img">
                    <img src="<?php echo get_template_directory_uri(); ?>/images/cashImg.png" alt="">
                </div>
                <div class="cash__text">
                    <p>Насіння соняшника смажене</p>
                    <span>80 г</span>
                    <span>SKU: U194729901</span>
                </div>
                <div class="cash__number">
                    <button>-</button>
                    <span>1</span>
                    <button>+</button>
                </div>
                <div class="cash__sum">
                    <span>35</span> <span>грн</span>
                </div>
            </div>
        </div>
        <input type="text" placeholder="Ваше імʼя">
        <input type="text" placeholder="Номер телефону">
        <button>Замовити</button>
    </div>
</main>

<?php get_footer(); ?>