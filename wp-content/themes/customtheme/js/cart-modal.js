jQuery(document).ready(function ($) {


    const modal = $("#myModal");
    const closeBtn = $("#closeBtn");
    const modalContent = $('.cash');
    const cashTooltip = $('#cashTooltip');

    document.getElementById('cashTooltip').addEventListener('click', openCartModal);

    function openCartModal() {
        modal.css("display", "flex");
        modalContent.empty().append('<div class="cash__head"><p>Ваша корзина</p></div> <div class="cash__item">Завантаження...</div>');

        setTimeout(() => {
            modal.addClass("open");
            getCartItems(); // Populating the cart
        }, 10);
    }

    $(document).on("click", "#closeBtn", function () {
        modal.removeClass("open");
        if (localCart) {
            updateCartItems();
        } else {
            console.error("localCart is not populated yet.");
        }
        setTimeout(() => modal.css("display", "none"), 300);
    });


    $(document).on("click", "#openBtn", openCartModal);

    let localCart = null;

    function updateCartItems(cart = localCart) {
        if (!cart) {
            console.error("Cart is empty or not loaded.");
            return Promise.reject("Cart is empty or not loaded.");
        }
        console.log("Check cart before POST", JSON.stringify(cart));
        return $.ajax({
            url: wc_cart_params.ajax_url,
            type: 'POST',
            data: {
                action: 'update_cart_items',
                cart_items: JSON.stringify(cart) // Pass cart items as JSON string
            },
            success: function (response) {
                if (response.success) {
                    console.log("Cart updated successfully:", response.message);
                } else {
                    console.error("Error updating cart:", response.message);
                }
            },
            error: function (xhr, status, error) {
                console.error("AJAX error:", status, error);
            }
        });
    }


    async function getCartItems() {
        try {
            const response = await $.ajax({
                url: wc_cart_params.ajax_url,
                type: 'POST',
                data: {
                    action: 'get_cart_items'
                },
            });

            if (response.success) {
                localCart = response.data.items;
                console.log("Cart items loaded:", localCart);
                populateCartItems();
            } else {
                console.error("Failed to load cart items:", response.message);
            }
        } catch (error) {
            console.error('Error fetching cart items:', error);
        }
    }


    async function populateCartItems() {
        let totalAmount = 0;
        modalContent.empty().append('<div class="cash__head"><p>Ваша корзина</p>');

        if (localCart.length === 0) {
            modalContent.append('<div class="cash__item">Корзина пуста</div>');
            cashTooltip.removeClass("active");
            return;
        } else {
            cashTooltip.find('.count').text(localCart.length);
        }
        const cashItemsDiv = $('<div class="cash__items"></div>');

        localCart.forEach(item => {
            totalAmount += item.subtotal;
            cashItemsDiv.append(createCartItem(item));
        });

        modalContent.append(cashItemsDiv);
        modalContent.append(createTotalSection(totalAmount));
        setAutomaticData();
        const cashinputField = document.getElementById("phone_number");

        cashinputField.addEventListener("input", (e) => {
            let input = e.target.value.replace(/\D/g, ""); // Keep only digits
            const startValue = "+38";

            // Prevent accidental deletion of the prefix
            if (!input.startsWith("38")) {
                input = "38" + input;
            }

            // Format the input
            let formatted = "+38";
            if (input.length > 2) formatted += `(${input.substring(2, 5)}`;
            if (input.length > 5) formatted += `) ${input.substring(5, 8)}`;
            if (input.length > 8) formatted += ` ${input.substring(8, 10)}`;
            if (input.length > 10) formatted += ` ${input.substring(10, 12)}`;

            // Set the formatted value
            e.target.value = formatted;

            // Handle cursor positioning
            const cursorPosition = e.target.selectionStart;
            setTimeout(() => e.target.setSelectionRange(cursorPosition, cursorPosition), 0);
        });

        // Optional: Prevent invalid keys (non-numeric except for deletion)
        cashinputField.addEventListener("keydown", (e) => {
            if (
                !(
                    e.key === "Backspace" ||
                    e.key === "Delete" ||
                    e.key === "ArrowLeft" ||
                    e.key === "ArrowRight" ||
                    /^[0-9]$/.test(e.key)
                )
            ) {
                e.preventDefault();
            }
        });
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
                        <input type="number" value="${item.quantity}" class="quantity-input" data-item-key="${item.key}">
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
        const needAmount = 5000 - totalAmount;
        const orderInfo = `
            <div class="cash__info">
                <p>Особисті дані для оформлення</p>
                <input type="text" id="first_name" placeholder="Ваше імʼя">
                <input
                    id="phone_number"
                    type="text"
                    placeholder="+38(___) ___ __ __"
                    maxlength="19" />
                <button class="btn btn__yellow" id="submit_data">Замовити</button>
            </div>
        `;

        const totalText = totalAmount >= 5000 ?
            `<span>Сума замовлення: ${totalAmount} грн</span>
            <span>Сума зі знижкою: ${Math.round(totalAmount - (totalAmount * 0.37))}
 грн</span>` :
            `<span>Сума замовлення: ${totalAmount} грн</span>
               <span>До мінімальної суми замовлення зі знижкою залишилось ${needAmount} грн</span>`;

        return `
            <div class="cash__total">
                ${totalText}
            </div>
            ${orderInfo}
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
        updateCartItemQuantity(itemKey, 0, true); // Removing item
    });

    $(document).on('change', '.quantity-input', function () {
        const itemKey = $(this).data('item-key'); // Get the item key
        const newValue = parseInt($(this).val(), 10); // Get the new value from input
        if (!isNaN(newValue)) {
            let setNewValue = 0;
            localCart.forEach(item => {
                if (item.key == itemKey) {
                    setNewValue = newValue - item.quantity;
                }
            });
            updateCartItemQuantity(itemKey, setNewValue, false);
        }
    });



    async function updateCartItemQuantity(itemKey, quantityChange, isRemove = false) {
        if (isRemove) {
            localCart = localCart.filter(item => item.key !== itemKey);
            populateCartItems();
        } else {
            localCart.forEach(item => {
                if (item.key == itemKey) {
                    if (quantityChange === -1 && item.quantity === 1) return;
                    item.quantity += quantityChange;
                    item.subtotal = item.quantity * item.price;
                    populateCartItems();
                }
            });
        }
    }

    function setAutomaticData() {
        var firstName = sessionStorage.getItem('first_name');
        var phoneNumber = sessionStorage.getItem('phone_number');

        if (firstName) {
            $('#first_name').val(firstName);
        }
        if (phoneNumber) {
            $('#phone_number').val(phoneNumber);
        }

        $('#submit_data').on('click', async function (event) {
            event.preventDefault();

            const firstName = $('#first_name').val();
            let phoneNumber = $('#phone_number').val();
            let input = phoneNumber.replace(/\D/g, "");

            if (!input.startsWith("380")) {
                input = "380" + input;
            }

            if (input.length > 12) {
                input = input.substring(0, 12);
            }

            let finalPhoneNumber = "+" + input;

            const submitButton = $('#submit_data'); // Зберігаємо посилання на кнопку

            if (firstName && phoneNumber) {
                sessionStorage.setItem('first_name', firstName);
                sessionStorage.setItem('phone_number', finalPhoneNumber);

                // Змінюємо текст кнопки та вимикаємо її
                submitButton.text('Перенаправляємо...').prop('disabled', true);

                try {
                    await updateCartItems(); // Чекаємо завершення оновлення кошика
                    window.location.href = "checkout"; // Перенаправляємо на checkout
                } catch (error) {
                    console.error("Failed to update cart:", error);
                    alert("Не вдалося оновити кошик. Спробуйте ще раз.");
                } finally {
                    // Відновлюємо текст кнопки у випадку помилки
                    submitButton.text('Замовити').prop('disabled', false);
                }
            } else {
                alert('Будь ласка, введіть імʼя та номер телефону.');
            }
        });
    }

    const inputField = document.getElementById("billing_phone");

    inputField.addEventListener("input", (e) => {
        let input = e.target.value.replace(/\D/g, "");
        if (!input.startsWith("380")) {
            input = "380" + input;
        }
        if (input.length > 12) {
            input = input.substring(0, 12);
        }
        e.target.value = "+" + input;
    });

    // Optional: Prevent invalid keys (non-numeric except for deletion)
    inputField.addEventListener("keydown", (e) => {
        if (
            !(
                e.key === "Backspace" ||
                e.key === "Delete" ||
                e.key === "ArrowLeft" ||
                e.key === "ArrowRight" ||
                /^[0-9]$/.test(e.key)
            )
        ) {
            e.preventDefault();
        }
    });



});