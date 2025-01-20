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

document.addEventListener("DOMContentLoaded", () => {
    // sorting items by type
    const sortItemsByProductType = (containerId) => {
        const container = document.querySelector(`#${containerId}`);
        const items = Array.from(container.querySelectorAll(".section__item"));

        // Define the custom order for sorting
        const sortOrder = ["Соняшник", "Гарбуз", "Арахіс"];

        // Sort the items based on the custom order
        items.sort((a, b) => {
            const typeA = a.getAttribute("product-type");
            const typeB = b.getAttribute("product-type");
            return sortOrder.indexOf(typeA) - sortOrder.indexOf(typeB);
        });

        // Append the sorted items back to the container
        items.forEach((item) => container.appendChild(item));
    };

    // Sort all product blocks on page load
    document.querySelectorAll(".section__list").forEach((list) => {
        sortItemsByProductType(list.id);
    });

    // Function to show a limited number of items
    const showInitialItems = (containerId, limit) => {
        const container = document.querySelector(`#${containerId}`);
        const items = container.querySelectorAll(".section__item");

        items.forEach((item, index) => {
            item.style.display = index < limit ? "flex" : "none";
        });
    };

    // Function to handle the 'Show More' button click
    const handleShowMore = (button) => {
        const containerId = button.id.replace("load-more-products", "product-block");
        const container = document.querySelector(`#${containerId}`);
        const items = container.querySelectorAll(".section__item");

        const offset = parseInt(button.dataset.offset, 10);
        const currentlyVisible = Array.from(items).filter(
            (item) => item.style.display !== "none"
        ).length;

        const nextVisibleCount = currentlyVisible + offset;

        // Show more items
        items.forEach((item, index) => {
            if (index < nextVisibleCount) {
                item.style.display = "flex";
            }
        });

        // If all items are visible, hide the button
        if (nextVisibleCount >= items.length) {
            button.style.display = "none";
        }
    };

    // Initialize all blocks
    document.querySelectorAll(".btn__showMore").forEach((button) => {
        const initialOffset = parseInt(button.dataset.offset, 10);
        const containerId = button.id.replace("load-more-products", "product-block");

        // Show initial items
        showInitialItems(containerId, initialOffset);

        // Add event listener to the button
        button.addEventListener("click", () => handleShowMore(button));
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