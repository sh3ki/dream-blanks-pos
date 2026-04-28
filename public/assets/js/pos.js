document.addEventListener('DOMContentLoaded', () => {
    const cart = [];
    const cartList = document.getElementById('cart-list');
    const itemsJson = document.getElementById('items-json');
    const subtotalEl = document.getElementById('cart-subtotal');
    const totalEl = document.getElementById('cart-total');
    const discountInput = document.getElementById('discount-input');
    const taxInput = document.getElementById('tax-input');

    const addButtons = document.querySelectorAll('[data-add-product]');
    addButtons.forEach((btn) => {
        btn.addEventListener('click', () => {
            const id = Number(btn.dataset.id);
            const name = btn.dataset.name;
            const unitPrice = Number(btn.dataset.price);

            const found = cart.find((item) => item.product_id === id);
            if (found) {
                found.quantity += 1;
            } else {
                cart.push({
                    product_id: id,
                    product_name: name,
                    unit_price: unitPrice,
                    quantity: 1,
                    discount: 0,
                });
            }

            renderCart();
        });
    });

    function renderCart() {
        cartList.innerHTML = '';
        let subtotal = 0;

        cart.forEach((item, index) => {
            const lineTotal = item.unit_price * item.quantity;
            subtotal += lineTotal;

            const row = document.createElement('div');
            row.className = 'cart-item';

            row.innerHTML = `
                <div class="cart-item-info">
                    <strong>${item.product_name}</strong><br>
                    <small>Qty: ${item.quantity} x ${item.unit_price.toFixed(2)}</small>
                </div>
                <div class="cart-item-actions">
                    <button type="button" class="btn btn-secondary" data-dec="${index}">-</button>
                    <button type="button" class="btn btn-secondary" data-inc="${index}">+</button>
                    <button type="button" class="btn btn-danger" data-remove="${index}">x</button>
                </div>
            `;

            cartList.appendChild(row);
        });

        cartList.querySelectorAll('[data-inc]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const index = Number(btn.dataset.inc);
                cart[index].quantity += 1;
                renderCart();
            });
        });

        cartList.querySelectorAll('[data-dec]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const index = Number(btn.dataset.dec);
                cart[index].quantity = Math.max(1, cart[index].quantity - 1);
                renderCart();
            });
        });

        cartList.querySelectorAll('[data-remove]').forEach((btn) => {
            btn.addEventListener('click', () => {
                const index = Number(btn.dataset.remove);
                cart.splice(index, 1);
                renderCart();
            });
        });

        const discount = Number(discountInput?.value || 0);
        const tax = Number(taxInput?.value || 0);
        const total = Math.max(0, subtotal - discount + tax);

        subtotalEl.textContent = subtotal.toFixed(2);
        totalEl.textContent = total.toFixed(2);

        itemsJson.value = JSON.stringify(cart);
    }

    [discountInput, taxInput].forEach((input) => {
        if (!input) {
            return;
        }

        input.addEventListener('input', () => renderCart());
    });
});
