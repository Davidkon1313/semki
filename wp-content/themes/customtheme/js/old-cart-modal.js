jQuery(document).ready(function ($) {
    const modal = $("#myModal");
    const closeBtn = $("#closeBtn");
    const modalContent = $('.cash');
    const cashTooltip = $('#cashTooltip');

    document.getElementById('cashTooltip').addEventListener('click', openCartModal);

    // Функція для відкриття модального вікна
    function openCartModal() {
        modal.css("display", "flex");
        modalContent.empty().append('<div class="cash__head"><p>Ваша корзина</p></div> <div class="cash__item">Завантаження...</div>');

        setTimeout(() => {
            modal.addClass("open");
            populateCartItems(); // Populating the cart
        }, 10);
    }

    // Close modal functionality
    $(document).on("click", "#closeBtn", function () {
        modal.removeClass("open");
        setTimeout(() => modal.css("display", "none"), 300);
    });

    // Відкриваємо модальне вікно при натисканні на кнопку
    // Замість openBtn.on() перевіримо в контейнері бургер-меню
    $(document).on("click", "#openBtn", openCartModal); // Використовуємо делегування подій

    // Функція для закриття модального вікна
    $(document).on("click", "#closeBtn", () => {
        modal.removeClass("open");
        setTimeout(() => {
            modal.css("display", "none");
        }, 300); // Тривалість анімації
    });

    async function populateCartItems() {
        try {
            const response = await $.ajax({
                url: wc_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_cart_items'
                },
            });

            if (response.success) {
                const cartItems = response.data.items;
                let totalAmount = 0;
                modalContent.empty().append('<div class="cash__head"><p>Ваша корзина</p>');

                // If cart is empty
                if (cartItems.length === 0) {
                    modalContent.append('<div class="cash__item">Корзина пуста</div>');
                    return;
                }

                cartItems.forEach(item => {
                    totalAmount += item.subtotal;
                    modalContent.append(createCartItem(item));
                });

                // Add total and personal info
                modalContent.append(createTotalSection(totalAmount));

                setAutomaticData();
                cashTooltip.removeClass("active");
            }
        } catch (error) {
            console.error('Error fetching cart items:', error);
        }
    }

    function createCartItem(item) {
        return `
            <div class="cash__item" data-item-key="${item.key}">
                <img class="close" src="${themeData.themeUri}/images/ui/close.png" alt="" data-remove-item="${item.key}">
                <div class="cash__itemWrapp">
                    <div class="cash__img">
                        <img style="max-width:64px;" src="${item.image}" alt="">
                    </div>
                    <div class="cash__text">
                        <p>${item.name}</p>
                        <span>${item.description}</span>
                        <span>SKU: ${item.sku}</span>
                    </div>
                    <div class="cash__number">
                        <button class="decrease">-</button>
                        <span>${item.quantity}</span>
                        <button class="increase">+</button>
                    </div>
                    <div class="cash__sum">
                        <span>${item.subtotal}</span> <span>грн</span>
                    </div>
                </div>
            </div>
        `;
    }

    function createTotalSection(totalAmount) {
        return `
            <div class="cash__total">
                <span>Сума замовлення: ${totalAmount} грн</span>
            </div>
            <div class="cash__info">
                <p>Особисті дані для оформлення</p>
                <input type="text" id="first_name" placeholder="Ваше імʼя">
                <input type="text" id="phone_number" placeholder="Номер телефону">
                <button class="btn btn__yellow" id="submit_data">Замовити</button>
            </div>
        `;
    }

    $(document).on('click', '.increase, .decrease', function () {
        const parentItem = $(this).closest('.cash__item');
        const itemKey = parentItem.data('item-key');
        const isIncrease = $(this).hasClass('increase');
        updateCartItemQuantity(itemKey, isIncrease ? 1 : -1);
    });

    $(document).on('click', '[data-remove-item]', function () {
        const itemKey = $(this).data('remove-item');
        updateCartItemQuantity(itemKey, 0, true);
    });

    // Update item quantity or remove
    async function updateCartItemQuantity(itemKey, quantityChange, isRemove = false) {
        $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: isRemove ? 'remove_cart_item' : 'update_cart_quantity',
                item_key: itemKey,
                quantity_change: quantityChange,
            },
            success: function (response) {
                if (response.success) {
                    populateCartItems(); // Refresh cart items
                }
            }
        });
    }

    // Remove item from cart
    $(document).on('click', '[data-remove-item]', function () {
        const itemKey = $(this).data('remove-item');
        updateCartItemQuantity(itemKey, 0, true);
    });

    function setAutomaticData() {
        $('#submit_data').on('click', function (event) {
            event.preventDefault();

            const firstName = $('#first_name').val();
            const phoneNumber = $('#phone_number').val();

            if (firstName && phoneNumber) {
                sessionStorage.setItem('first_name', firstName);
                sessionStorage.setItem('phone_number', phoneNumber);
                window.location.href = 'checkout'; // Redirect to WooCommerce checkout
            } else {
                alert('Будь ласка, введіть імʼя та номер телефону.');
            }
        });
    }
});