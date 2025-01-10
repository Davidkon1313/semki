//added items from all block on cart
function initializeAddToCartScript() {
    jQuery(function ($) {
        // Select all product items on the page
        const productItems = $('.section__item');

        productItems.each(function () {
            const item = $(this);
            const variationSelect = item.find('.variation-select'); // Use class for variation select
            const addToCartButton = item.find('.btn__white'); // Assuming this is the button
            const productId = addToCartButton.data('product-id'); // Get product ID
            const isVariableProduct = variationSelect.length > 0; // Check if it's a variable product

            const addToCartAjax = (productId, quantity = 1) => {
                const data = {
                    product_id: productId,
                    quantity: quantity,
                };

                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                    data: data,
                    dataType: 'json',
                    beforeSend: function (xhr) {
                        // Optionally, show loading state on the button
                        addToCartButton.prop('disabled', true).text('Додаємо у корзину...');
                    },
                    complete: function () {
                        // Reset button state
                        addToCartButton.prop('disabled', false).text('Купити зараз!');
                    },
                    success: function (res) {
                        if (res.error) {
                            alert(res.message || 'Error adding to cart.');
                        } else {
                            // Trigger WooCommerce's 'added_to_cart' event
                            $(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash]);

                            // Optionally, update the cart count (if you have a cart widget or badge)
                            const cartCount = $('.cart-count');
                            if (cartCount.length && res.fragments['.cart-count']) {
                                cartCount.text(res.fragments['.cart-count']);
                            }
                            // alert('Товар було успішно додано у корзину!');
                            toggleCashTooltipActive();
                            // openCartModal();
                        }
                    },
                    error: function () {
                        alert('Трапилася якась помилка, товар не було додано у корзину.');
                    },
                });
            };

            if (isVariableProduct) {
                // Handle variable products
                addToCartButton.on('click', function (event) {
                    event.preventDefault(); // Prevent default behavior
                    const variationId = variationSelect.val(); // Get the selected variation ID
                    if (variationId) {
                        addToCartAjax(variationId); // Use the variation ID instead of the product ID
                    } else {
                        alert('Please select a variation.');
                    }
                });
            } else {
                // Handle simple products
                addToCartButton.on('click', function (event) {
                    event.preventDefault(); // Prevent default behavior
                    addToCartAjax(productId); // Use the general product ID for simple products
                });
            }
        });
    });
}

initializeAddToCartScript();

//added items from sale block to cart
function initializeAddToCartFromSlideScript() {
    jQuery(function ($) {
        // Select all product items on the page
        const productItems = $('.S6__item'); // Ensure this matches your HTML structure

        productItems.each(function () {
            const item = $(this);
            const variationSelect = item.find('.variation-select'); // Use class for variation select
            const addToCartButton = item.find('.btn__white'); // Assuming this is the button
            const productId = addToCartButton.data('product-id'); // Get product ID
            const isVariableProduct = variationSelect.length > 0; // Check if it's a variable product

            // AJAX function to add the product to the cart
            const addToCartAjax = (productId, quantity = 1) => {
                const data = {
                    product_id: productId,
                    quantity: quantity,
                };

                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                    data: data,
                    dataType: 'json',
                    beforeSend: function (xhr) {
                        // Optionally, show loading state on the button
                        addToCartButton.prop('disabled', true).text('Додаємо у корзину...');
                    },
                    complete: function () {
                        // Reset button state
                        addToCartButton.prop('disabled', false).text('Купити зараз!');
                    },
                    success: function (res) {
                        if (res.error) {
                            alert(res.message || 'Error adding to cart.');
                        } else {
                            // Trigger WooCommerce's 'added_to_cart' event
                            $(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash]);

                            // Optionally, update the cart count (if you have a cart widget or badge)
                            const cartCount = $('.cart-count');
                            if (cartCount.length && res.fragments['.cart-count']) {
                                cartCount.text(res.fragments['.cart-count']);
                            }
                            // alert('Товар було успішно додано у корзину!');
                            toggleCashTooltipActive();
                        }
                    },
                    error: function () {
                        alert('Трапилася якась помилка, товар не було додано у корзину.');
                    },
                });
            };

            // Handle variable products
            if (isVariableProduct) {
                addToCartButton.on('click', function (event) {
                    event.preventDefault(); // Prevent default behavior
                    const variationId = variationSelect.val(); // Get the selected variation ID
                    if (variationId) {
                        addToCartAjax(variationId); // Use the variation ID instead of the product ID
                    } else {
                        alert('Please select a variation.');
                    }
                });
            } else {
                // Handle simple products
                addToCartButton.on('click', function (event) {
                    event.preventDefault(); // Prevent default behavior
                    console.log('Product ID:', productId); // Log product ID for debugging
                    addToCartAjax(productId); // Use the general product ID for simple products
                });
            }
        });
    });
}

initializeAddToCartFromSlideScript();

// show more items for all block
jQuery(function ($) {
    // On click of any "Show More" button
    $('[id^=load-more-products-]').on('click', function () {
        var button = $(this);
        var offset = button.data('offset'); // Get current offset
        var category = button.data('category'); // Get category slug
        var blockId = button.attr('id').replace('load-more-products-', ''); // Extract block ID (e.g. 1, 2, 3, etc.)

        // Send AJAX request to WordPress
        $.ajax({
            url: ajax_params.ajax_url,
            method: 'GET',
            data: {
                action: 'load_more_products', // The action hook that handles AJAX in PHP
                offset: offset, // Current offset (for pagination)
                category: category, // Category slug (for the category filter)
                block_id: blockId // Block identifier (to target the correct product block)
            },
            beforeSend: function () {
                button.text('Завантаження...'); // Change button text while loading
            },
            success: function (response) {
                if (response) {
                    // Append new products to the correct product block
                    $('#product-block-' + blockId).append(response);
                    button.data('offset', offset + 4); // Update the offset for the next request

                    // If there are no more products, hide the "Show More" button
                    if (response.trim() === '') {
                        button.hide();
                    } else {
                        button.text('Показати ще'); // Reset the button text
                    }
                    initializeAddToCartScript();
                    autoSelectFirstVariation();
                } else {
                    button.hide();
                }
            }
        });
    });
});


//load more items for mobile sale block
jQuery(function ($) {
    // On click of the "Show More" button
    $('.showMore').on('click', function () {
        var button = $(this);
        var offset = $('.S6__list .S6__item').length; // Current number of displayed items
        var category = 'category-sale'; // Static category slug
        var blockId = '1'; // Static block ID, or modify as needed

        // Send AJAX request to WordPress
        $.ajax({
            url: ajax_params.ajax_url, // Ensure this is defined in your theme's localized JS
            method: 'GET',
            data: {
                action: 'load_more_products_mobile', // The action hook that handles AJAX in PHP
                offset: offset, // Current offset (for pagination)
                category: category, // Category slug (for the category filter)
                block_id: blockId // Block identifier (to target the correct product block)
            },
            beforeSend: function () {
                button.text('Завантаження...'); // Change button text while loading
            },
            success: function (response) {
                if (response) {
                    // Append new products to the correct product block
                    $('.S6__list').append(response);
                    button.text('Показати більше'); // Reset button text
                }

                // If there are no more products, hide the "Show More" button
                if (response.trim() === '') {
                    button.hide();
                }
            },
            error: function () {
                alert('Error loading products');
            }
        });
    });
});

// activating cash button
async function toggleCashTooltipActive() {
    var cashTooltip = $('#cashTooltip');
    try {
        const response = await $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'get_cart_items'
            },
        });

        if (response.success) {
            const localCart = response.data.items;
            console.log("Cart items loaded:", localCart);
            cashTooltip.find('.count').text(localCart.length);
            if (!cashTooltip.hasClass("active") && localCart.length != 0) {
                cashTooltip.toggleClass('active');
            }
        } else {
            console.error("Failed to load cart items:", response.message);
        }
    } catch (error) {
        console.error('Error fetching cart items:', error);
    }
}

document.addEventListener('DOMContentLoaded', function () {
    toggleCashTooltipActive();
});


//add to cart from all mobile blocks
function initializeAddToCartScriptMobile() {
    jQuery(function ($) {
        // Select all product items on the page
        const productItems = $('.section__itemResponsive');

        productItems.each(function () {
            const item = $(this);
            const variationSelect = item.find('.variation-select'); // Use class for variation select
            const addToCartButton = item.find('.btn__yellow'); // Assuming this is the button
            const productId = addToCartButton.data('product-id'); // Get product ID
            const isVariableProduct = variationSelect.length > 0; // Check if it's a variable product

            const addToCartAjax = (productId, quantity = 1) => {
                const data = {
                    product_id: productId,
                    quantity: quantity,
                };

                $.ajax({
                    type: 'POST',
                    url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'add_to_cart'),
                    data: data,
                    dataType: 'json',
                    beforeSend: function (xhr) {
                        // Optionally, show loading state on the button
                        addToCartButton.prop('disabled', true).text('Додаємо у корзину...');
                    },
                    complete: function () {
                        // Reset button state
                        addToCartButton.prop('disabled', false).text('Купити зараз!');
                    },
                    success: function (res) {
                        if (res.error) {
                            alert(res.message || 'Error adding to cart.');
                        } else {
                            // Trigger WooCommerce's 'added_to_cart' event
                            $(document.body).trigger('added_to_cart', [res.fragments, res.cart_hash]);

                            // Optionally, update the cart count (if you have a cart widget or badge)
                            const cartCount = $('.cart-count');
                            if (cartCount.length && res.fragments['.cart-count']) {
                                cartCount.text(res.fragments['.cart-count']);
                            }
                            // alert('Товар було успішно додано у корзину!');
                            toggleCashTooltipActive();
                            // openCartModal();
                        }
                    },
                    error: function () {
                        alert('Трапилася якась помилка, товар не було додано у корзину.');
                    },
                });
            };

            if (isVariableProduct) {
                // Handle variable products
                addToCartButton.on('click', function (event) {
                    event.preventDefault(); // Prevent default behavior
                    const variationId = variationSelect.val(); // Get the selected variation ID
                    if (variationId) {
                        addToCartAjax(variationId); // Use the variation ID instead of the product ID
                    } else {
                        alert('Будь ласка, виберіть варіацію.');
                    }
                });
            } else {
                // Handle simple products
                addToCartButton.on('click', function (event) {
                    event.preventDefault(); // Prevent default behavior
                    addToCartAjax(productId); // Use the general product ID for simple products
                });
            }
        });
    });
}

initializeAddToCartScriptMobile();