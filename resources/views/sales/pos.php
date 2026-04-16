<div class="pos-shell">
    <div class="card">
        <div class="toolbar">
            <strong>Product Catalog</strong>
        </div>

        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <div class="product-item">
                    <div style="font-weight:700;"><?= e($product['product_name']) ?></div>
                    <div style="font-size:12px;color:#6b7280;">SKU: <?= e($product['sku']) ?></div>
                    <div style="margin:8px 0;">Price: <?= number_format((float) $product['selling_price'], 2) ?></div>
                    <div style="font-size:12px;">Stock: <?= (int) $product['quantity_on_hand'] ?></div>
                    <button
                        class="btn btn-primary"
                        type="button"
                        data-add-product
                        data-id="<?= (int) $product['id'] ?>"
                        data-name="<?= e($product['product_name']) ?>"
                        data-price="<?= (float) $product['selling_price'] ?>"
                        style="margin-top:8px;"
                    >
                        Add to Cart
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card">
        <h3 style="margin-top:0;">Cart</h3>
        <form method="post" action="<?= e(url('/pos/checkout')) ?>" id="pos-checkout-form">
            <?= csrf_field() ?>
            <input type="hidden" id="items-json" name="items_json" value="[]">

            <div id="cart-list" class="cart-list"></div>

            <div class="field" style="margin-top:10px;">
                <label for="discount-input">Discount</label>
                <input class="input" id="discount-input" type="number" step="0.01" name="discount" value="0">
            </div>
            <div class="field">
                <label for="tax-input">Tax</label>
                <input class="input" id="tax-input" type="number" step="0.01" name="tax" value="0">
            </div>
            <div class="field">
                <label>Payment Method</label>
                <select class="select" name="payment_method">
                    <option>Cash</option>
                    <option>Credit Card</option>
                    <option>Debit Card</option>
                    <option>Digital Wallet</option>
                    <option>Check</option>
                </select>
            </div>
            <div class="field">
                <label>Amount Paid</label>
                <input class="input" type="number" step="0.01" name="amount_paid" required>
            </div>

            <div class="field"><label>Notes</label><textarea class="textarea" name="notes" rows="2"></textarea></div>

            <div class="flex justify-between align-center" style="margin:8px 0;">
                <strong>Subtotal</strong>
                <strong id="cart-subtotal">0.00</strong>
            </div>
            <div class="flex justify-between align-center" style="margin-bottom:10px;">
                <strong>Total</strong>
                <strong id="cart-total">0.00</strong>
            </div>

            <button class="btn btn-success w-full" type="submit">Complete Sale</button>
        </form>
    </div>
</div>

<script src="<?= e(asset_url('js/pos.js')) ?>"></script>
