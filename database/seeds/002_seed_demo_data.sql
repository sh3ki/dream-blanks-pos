-- Demo users for each role
-- All demo accounts use password: password

INSERT INTO users (
    username,
    email,
    password_hash,
    first_name,
    last_name,
    role_id,
    is_active,
    failed_attempts,
    locked_until,
    created_at,
    updated_at
) VALUES
(
    'admin',
    'admin@dreamblanks.local',
    '$2y$10$Mp.Zh9R0N0oJd5sQ31cbje6ojGqoi.x/POCWYYTKSBr/yHYnO/SFm',
    'System',
    'Admin',
    (SELECT id FROM roles WHERE role_name = 'Admin' LIMIT 1),
    1,
    0,
    NULL,
    NOW(),
    NOW()
),
(
    'manager',
    'manager@dreamblanks.local',
    '$2y$10$Mp.Zh9R0N0oJd5sQ31cbje6ojGqoi.x/POCWYYTKSBr/yHYnO/SFm',
    'Mia',
    'Manager',
    (SELECT id FROM roles WHERE role_name = 'Manager' LIMIT 1),
    1,
    0,
    NULL,
    NOW(),
    NOW()
),
(
    'cashier',
    'cashier@dreamblanks.local',
    '$2y$10$Mp.Zh9R0N0oJd5sQ31cbje6ojGqoi.x/POCWYYTKSBr/yHYnO/SFm',
    'Carl',
    'Cashier',
    (SELECT id FROM roles WHERE role_name = 'Cashier' LIMIT 1),
    1,
    0,
    NULL,
    NOW(),
    NOW()
),
(
    'staff',
    'staff@dreamblanks.local',
    '$2y$10$Mp.Zh9R0N0oJd5sQ31cbje6ojGqoi.x/POCWYYTKSBr/yHYnO/SFm',
    'Sia',
    'Staff',
    (SELECT id FROM roles WHERE role_name = 'Store Staff' LIMIT 1),
    1,
    0,
    NULL,
    NOW(),
    NOW()
),
(
    'accountant',
    'accountant@dreamblanks.local',
    '$2y$10$Mp.Zh9R0N0oJd5sQ31cbje6ojGqoi.x/POCWYYTKSBr/yHYnO/SFm',
    'Ari',
    'Accountant',
    (SELECT id FROM roles WHERE role_name = 'Accountant' LIMIT 1),
    1,
    0,
    NULL,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    email = VALUES(email),
    password_hash = VALUES(password_hash),
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    role_id = VALUES(role_id),
    is_active = VALUES(is_active),
    failed_attempts = 0,
    locked_until = NULL,
    updated_at = NOW();

-- Employee rows for role users
INSERT INTO employees (
    employee_id,
    first_name,
    last_name,
    email,
    phone,
    department,
    position,
    hire_date,
    is_active,
    user_id,
    created_at,
    updated_at
) VALUES
(
    'EMP-ADM-0001',
    'System',
    'Admin',
    'admin@dreamblanks.local',
    '09000000001',
    'Management',
    'Administrator',
    '2025-01-01',
    1,
    (SELECT id FROM users WHERE username = 'admin' LIMIT 1),
    NOW(),
    NOW()
),
(
    'EMP-MGR-0001',
    'Mia',
    'Manager',
    'manager@dreamblanks.local',
    '09000000002',
    'Operations',
    'Store Manager',
    '2025-01-01',
    1,
    (SELECT id FROM users WHERE username = 'manager' LIMIT 1),
    NOW(),
    NOW()
),
(
    'EMP-CSH-0001',
    'Carl',
    'Cashier',
    'cashier@dreamblanks.local',
    '09000000003',
    'Frontline',
    'Cashier',
    '2025-01-10',
    1,
    (SELECT id FROM users WHERE username = 'cashier' LIMIT 1),
    NOW(),
    NOW()
),
(
    'EMP-STF-0001',
    'Sia',
    'Staff',
    'staff@dreamblanks.local',
    '09000000004',
    'Inventory',
    'Store Staff',
    '2025-01-12',
    1,
    (SELECT id FROM users WHERE username = 'staff' LIMIT 1),
    NOW(),
    NOW()
),
(
    'EMP-ACC-0001',
    'Ari',
    'Accountant',
    'accountant@dreamblanks.local',
    '09000000005',
    'Finance',
    'Accountant',
    '2025-01-15',
    1,
    (SELECT id FROM users WHERE username = 'accountant' LIMIT 1),
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    first_name = VALUES(first_name),
    last_name = VALUES(last_name),
    email = VALUES(email),
    phone = VALUES(phone),
    department = VALUES(department),
    position = VALUES(position),
    hire_date = VALUES(hire_date),
    is_active = VALUES(is_active),
    user_id = VALUES(user_id),
    updated_at = NOW();

-- Demo categories with stable IDs
INSERT INTO categories (
    id,
    category_name,
    description,
    parent_category_id,
    image_path,
    display_order,
    is_active,
    tax_rate,
    created_at,
    updated_at
) VALUES
(101, 'Beverages', 'Drinks and refreshments', NULL, NULL, 1, 1, 12.00, NOW(), NOW()),
(102, 'Snacks', 'Packaged and quick snacks', NULL, NULL, 2, 1, 12.00, NOW(), NOW()),
(103, 'Household', 'General household essentials', NULL, NULL, 3, 1, 12.00, NOW(), NOW()),
(104, 'Personal Care', 'Personal hygiene products', NULL, NULL, 4, 1, 12.00, NOW(), NOW()),
(105, 'Stationery', 'Office and school supplies', NULL, NULL, 5, 1, 12.00, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    category_name = VALUES(category_name),
    description = VALUES(description),
    display_order = VALUES(display_order),
    is_active = VALUES(is_active),
    tax_rate = VALUES(tax_rate),
    updated_at = NOW();

-- Demo products
INSERT INTO products (
    id,
    sku,
    product_name,
    description,
    category_id,
    subcategory_id,
    image_path,
    cost_price,
    selling_price,
    wholesale_price,
    unit_of_measurement,
    supplier_id,
    expiry_date,
    is_active,
    tax_treatment,
    commission_rate,
    created_at,
    updated_at
) VALUES
(1001, 'PRD-0001', 'Bottled Water 500ml', 'Purified drinking water', 101, NULL, NULL, 8.00, 12.00, 10.50, 'Piece', NULL, NULL, 1, 'Taxable', 0.00, NOW(), NOW()),
(1002, 'PRD-0002', 'Cola 1.5L', 'Carbonated soft drink', 101, NULL, NULL, 42.00, 55.00, 50.00, 'Piece', NULL, NULL, 1, 'Taxable', 0.00, NOW(), NOW()),
(1003, 'PRD-0003', 'Potato Chips 60g', 'Salted potato chips', 102, NULL, NULL, 20.00, 30.00, 27.00, 'Piece', NULL, NULL, 1, 'Taxable', 0.00, NOW(), NOW()),
(1004, 'PRD-0004', 'Laundry Detergent 1kg', 'Powder detergent', 103, NULL, NULL, 95.00, 125.00, 115.00, 'Piece', NULL, NULL, 1, 'Taxable', 0.00, NOW(), NOW()),
(1005, 'PRD-0005', 'Shampoo 170ml', 'Daily use shampoo', 104, NULL, NULL, 65.00, 90.00, 82.00, 'Piece', NULL, NULL, 1, 'Taxable', 0.00, NOW(), NOW()),
(1006, 'PRD-0006', 'Notebook A5', '80 leaves notebook', 105, NULL, NULL, 18.00, 25.00, 22.00, 'Piece', NULL, NULL, 1, 'Taxable', 0.00, NOW(), NOW())
ON DUPLICATE KEY UPDATE
    product_name = VALUES(product_name),
    description = VALUES(description),
    category_id = VALUES(category_id),
    cost_price = VALUES(cost_price),
    selling_price = VALUES(selling_price),
    wholesale_price = VALUES(wholesale_price),
    unit_of_measurement = VALUES(unit_of_measurement),
    is_active = VALUES(is_active),
    tax_treatment = VALUES(tax_treatment),
    commission_rate = VALUES(commission_rate),
    updated_at = NOW();

-- Demo inventory for products
INSERT INTO inventory (
    product_id,
    quantity_on_hand,
    quantity_reserved,
    reorder_level,
    reorder_quantity,
    last_counted_at,
    created_at,
    updated_at
) VALUES
(1001, 120, 5, 30, 60, NOW(), NOW(), NOW()),
(1002, 80, 2, 20, 40, NOW(), NOW(), NOW()),
(1003, 60, 4, 25, 50, NOW(), NOW(), NOW()),
(1004, 35, 0, 15, 30, NOW(), NOW(), NOW()),
(1005, 50, 3, 20, 40, NOW(), NOW(), NOW()),
(1006, 90, 6, 30, 60, NOW(), NOW(), NOW())
ON DUPLICATE KEY UPDATE
    quantity_on_hand = VALUES(quantity_on_hand),
    quantity_reserved = VALUES(quantity_reserved),
    reorder_level = VALUES(reorder_level),
    reorder_quantity = VALUES(reorder_quantity),
    last_counted_at = VALUES(last_counted_at),
    updated_at = NOW();

-- Demo sales (cashier account)
INSERT INTO sales (
    id,
    transaction_number,
    cashier_id,
    customer_id,
    payment_method,
    subtotal,
    discount,
    tax,
    total,
    amount_paid,
    `change`,
    payment_status,
    notes,
    status,
    created_at
) VALUES
(
    2001,
    'TXN-SEED-0001',
    (SELECT id FROM users WHERE username = 'cashier' LIMIT 1),
    NULL,
    'Cash',
    97.00,
    0.00,
    11.64,
    108.64,
    120.00,
    11.36,
    'Completed',
    'Demo sale #1',
    'Completed',
    DATE_SUB(NOW(), INTERVAL 2 DAY)
),
(
    2002,
    'TXN-SEED-0002',
    (SELECT id FROM users WHERE username = 'cashier' LIMIT 1),
    NULL,
    'Debit Card',
    180.00,
    10.00,
    20.40,
    190.40,
    190.40,
    0.00,
    'Completed',
    'Demo sale #2',
    'Completed',
    DATE_SUB(NOW(), INTERVAL 1 DAY)
),
(
    2003,
    'TXN-SEED-0003',
    (SELECT id FROM users WHERE username = 'cashier' LIMIT 1),
    NULL,
    'Cash',
    55.00,
    0.00,
    6.60,
    61.60,
    100.00,
    38.40,
    'Completed',
    'Demo sale #3',
    'Completed',
    NOW()
)
ON DUPLICATE KEY UPDATE
    cashier_id = VALUES(cashier_id),
    payment_method = VALUES(payment_method),
    subtotal = VALUES(subtotal),
    discount = VALUES(discount),
    tax = VALUES(tax),
    total = VALUES(total),
    amount_paid = VALUES(amount_paid),
    `change` = VALUES(`change`),
    payment_status = VALUES(payment_status),
    notes = VALUES(notes),
    status = VALUES(status),
    created_at = VALUES(created_at);

-- Demo sale items
INSERT INTO sale_items (
    id,
    sale_id,
    product_id,
    quantity,
    unit_price,
    discount,
    line_total,
    created_at
) VALUES
(3001, 2001, 1001, 3, 12.00, 0.00, 36.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3002, 2001, 1003, 2, 30.00, 0.00, 60.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3003, 2001, 1006, 1, 25.00, 24.00, 1.00, DATE_SUB(NOW(), INTERVAL 2 DAY)),
(3004, 2002, 1004, 1, 125.00, 0.00, 125.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3005, 2002, 1005, 1, 90.00, 10.00, 80.00, DATE_SUB(NOW(), INTERVAL 1 DAY)),
(3006, 2003, 1002, 1, 55.00, 0.00, 55.00, NOW())
ON DUPLICATE KEY UPDATE
    sale_id = VALUES(sale_id),
    product_id = VALUES(product_id),
    quantity = VALUES(quantity),
    unit_price = VALUES(unit_price),
    discount = VALUES(discount),
    line_total = VALUES(line_total),
    created_at = VALUES(created_at);

-- Demo payments
INSERT INTO payments (
    id,
    sale_id,
    payment_method,
    amount,
    reference_number,
    created_at
) VALUES
(4001, 2001, 'Cash', 120.00, 'SEED-CASH-0001', DATE_SUB(NOW(), INTERVAL 2 DAY)),
(4002, 2002, 'Debit Card', 190.40, 'SEED-DC-0002', DATE_SUB(NOW(), INTERVAL 1 DAY)),
(4003, 2003, 'Cash', 100.00, 'SEED-CASH-0003', NOW())
ON DUPLICATE KEY UPDATE
    sale_id = VALUES(sale_id),
    payment_method = VALUES(payment_method),
    amount = VALUES(amount),
    reference_number = VALUES(reference_number),
    created_at = VALUES(created_at);

-- Demo receipts
INSERT INTO receipts (
    id,
    sale_id,
    receipt_number,
    receipt_date,
    printed_count,
    last_printed_at,
    created_at
) VALUES
(5001, 2001, 'RCPT-SEED-0001', DATE_SUB(NOW(), INTERVAL 2 DAY), 1, DATE_SUB(NOW(), INTERVAL 2 DAY), DATE_SUB(NOW(), INTERVAL 2 DAY)),
(5002, 2002, 'RCPT-SEED-0002', DATE_SUB(NOW(), INTERVAL 1 DAY), 1, DATE_SUB(NOW(), INTERVAL 1 DAY), DATE_SUB(NOW(), INTERVAL 1 DAY)),
(5003, 2003, 'RCPT-SEED-0003', NOW(), 0, NULL, NOW())
ON DUPLICATE KEY UPDATE
    sale_id = VALUES(sale_id),
    receipt_date = VALUES(receipt_date),
    printed_count = VALUES(printed_count),
    last_printed_at = VALUES(last_printed_at),
    created_at = VALUES(created_at);

-- Demo expenses
INSERT INTO expenses (
    id,
    expense_category_id,
    description,
    amount,
    payment_method,
    reference_number,
    receipt_path,
    created_by,
    approver_id,
    status,
    approval_date,
    expense_date,
    created_at,
    updated_at
) VALUES
(
    6001,
    (SELECT id FROM expense_categories WHERE category_name = 'Utilities' LIMIT 1),
    'Electricity bill - March',
    8200.00,
    'Bank Transfer',
    'UTIL-2026-03',
    NULL,
    (SELECT id FROM users WHERE username = 'accountant' LIMIT 1),
    (SELECT id FROM users WHERE username = 'manager' LIMIT 1),
    'Approved',
    DATE_SUB(NOW(), INTERVAL 6 DAY),
    DATE_SUB(CURDATE(), INTERVAL 6 DAY),
    DATE_SUB(NOW(), INTERVAL 6 DAY),
    NOW()
),
(
    6002,
    (SELECT id FROM expense_categories WHERE category_name = 'Office Supplies' LIMIT 1),
    'Receipt paper and printer ink',
    2400.00,
    'Cash',
    'OFF-2026-04',
    NULL,
    (SELECT id FROM users WHERE username = 'accountant' LIMIT 1),
    NULL,
    'Pending',
    NULL,
    DATE_SUB(CURDATE(), INTERVAL 2 DAY),
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    NOW()
),
(
    6003,
    (SELECT id FROM expense_categories WHERE category_name = 'Maintenance' LIMIT 1),
    'POS terminal preventive maintenance',
    1500.00,
    'Cash',
    'MAIN-2026-04',
    NULL,
    (SELECT id FROM users WHERE username = 'manager' LIMIT 1),
    (SELECT id FROM users WHERE username = 'admin' LIMIT 1),
    'Approved',
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    DATE_SUB(CURDATE(), INTERVAL 1 DAY),
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    NOW()
)
ON DUPLICATE KEY UPDATE
    expense_category_id = VALUES(expense_category_id),
    description = VALUES(description),
    amount = VALUES(amount),
    payment_method = VALUES(payment_method),
    reference_number = VALUES(reference_number),
    created_by = VALUES(created_by),
    approver_id = VALUES(approver_id),
    status = VALUES(status),
    approval_date = VALUES(approval_date),
    expense_date = VALUES(expense_date),
    updated_at = NOW();

-- Demo transactions
INSERT INTO transactions (
    id,
    transaction_number,
    transaction_type,
    amount,
    transaction_date,
    created_by,
    status,
    reference_id,
    notes,
    created_at
) VALUES
(
    7001,
    'TRX-SEED-SALE-0001',
    'SALE',
    108.64,
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    (SELECT id FROM users WHERE username = 'cashier' LIMIT 1),
    'Completed',
    'TXN-SEED-0001',
    'Mapped from demo sale #1',
    DATE_SUB(NOW(), INTERVAL 2 DAY)
),
(
    7002,
    'TRX-SEED-SALE-0002',
    'SALE',
    190.40,
    DATE_SUB(NOW(), INTERVAL 1 DAY),
    (SELECT id FROM users WHERE username = 'cashier' LIMIT 1),
    'Completed',
    'TXN-SEED-0002',
    'Mapped from demo sale #2',
    DATE_SUB(NOW(), INTERVAL 1 DAY)
),
(
    7003,
    'TRX-SEED-EXP-0001',
    'EXPENSE',
    8200.00,
    DATE_SUB(NOW(), INTERVAL 6 DAY),
    (SELECT id FROM users WHERE username = 'accountant' LIMIT 1),
    'Completed',
    'UTIL-2026-03',
    'Utilities expense entry',
    DATE_SUB(NOW(), INTERVAL 6 DAY)
),
(
    7004,
    'TRX-SEED-EXP-0002',
    'EXPENSE',
    2400.00,
    DATE_SUB(NOW(), INTERVAL 2 DAY),
    (SELECT id FROM users WHERE username = 'accountant' LIMIT 1),
    'Pending',
    'OFF-2026-04',
    'Office supplies expense pending approval',
    DATE_SUB(NOW(), INTERVAL 2 DAY)
)
ON DUPLICATE KEY UPDATE
    transaction_type = VALUES(transaction_type),
    amount = VALUES(amount),
    transaction_date = VALUES(transaction_date),
    created_by = VALUES(created_by),
    status = VALUES(status),
    reference_id = VALUES(reference_id),
    notes = VALUES(notes),
    created_at = VALUES(created_at);
