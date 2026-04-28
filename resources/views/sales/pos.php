<div class="page-header">
    <div>
        <h2 class="page-title">Point of Sale</h2>
        <p class="page-subtitle">Quickly add items, adjust quantities, and close sales.</p>
    </div>
</div>

<div class="pos-shell">
    <div class="card">
        <div class="toolbar">
            <strong>Product Catalog</strong>
        </div>

        <div class="product-grid">
            <?php foreach ($products as $product): ?>
                <?php
                $qty = (int) $product['quantity_on_hand'];
                $statusClass = $qty <= 0 ? 'status-out' : ($qty <= (int) ($product['reorder_level'] ?? 0) ? 'status-low' : 'status-ok');
                $statusText = $qty <= 0 ? 'Out of Stock' : ($qty <= (int) ($product['reorder_level'] ?? 0) ? 'Low Stock' : 'In Stock');
                $initials = strtoupper(substr($product['product_name'] ?? '', 0, 2));
                ?>
                <div class="product-item">
                    <div class="product-card-header">
                        <div class="product-thumb"><?= e($initials !== '' ? $initials : 'DB') ?></div>
                        <div>
                            <div class="product-title"><?= e($product['product_name']) ?></div>
                            <div class="product-meta">SKU: <?= e($product['sku']) ?></div>
                        </div>
                    </div>
                    <div class="product-price">Price: <?= number_format((float) $product['selling_price'], 2) ?></div>
                    <div class="product-stock">
                        <span class="status <?= $statusClass ?>"><?= $statusText ?></span>
                        <span class="product-meta">Qty: <?= (int) $product['quantity_on_hand'] ?></span>
                    </div>
                    <button
                        class="btn btn-primary btn-sm"
                        type="button"
                        data-add-product
                        data-id="<?= (int) $product['id'] ?>"
                        data-name="<?= e($product['product_name']) ?>"
                        data-price="<?= (float) $product['selling_price'] ?>"
                    >
                        Add to Cart
                    </button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="card cart-panel">
        <h3 class="card-title">Cart</h3>
        <form method="post" action="<?= e(url('/pos/checkout')) ?>" id="pos-checkout-form">
            <?= csrf_field() ?>
            <input type="hidden" id="items-json" name="items_json" value="[]">

            <div id="cart-list" class="cart-list"></div>

            <div class="field field-stack">
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

            <div class="cart-summary">
                <div class="flex justify-between align-center">
                    <strong>Subtotal</strong>
                    <strong id="cart-subtotal">0.00</strong>
                </div>
                <div class="flex justify-between align-center cart-summary-total">
                    <strong>Total</strong>
                    <strong id="cart-total">0.00</strong>
                </div>
            </div>

            <button class="btn btn-success w-full" type="submit">Complete Sale</button>
        </form>
    </div>
</div>

<script src="<?= e(asset_url('js/pos.js')) ?>"></script>
