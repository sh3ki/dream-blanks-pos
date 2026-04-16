INSERT INTO roles (role_name, description) VALUES
('Admin', 'Full system access'),
('Manager', 'Store operations and employee management'),
('Cashier', 'POS and sales processing'),
('Store Staff', 'Product and inventory management'),
('Accountant', 'Financial analysis and reporting')
ON DUPLICATE KEY UPDATE description = VALUES(description);

INSERT INTO permissions (permission_name, description, module) VALUES
('users.view', 'View users and employees', 'users'),
('users.create', 'Create users and employees', 'users'),
('users.edit', 'Edit users and employees', 'users'),
('sales.process', 'Process POS sales', 'sales'),
('inventory.view', 'View inventory', 'inventory'),
('inventory.adjust', 'Adjust inventory', 'inventory'),
('products.manage', 'Manage products and categories', 'products'),
('expenses.manage', 'Create and approve expenses', 'expenses'),
('reports.view', 'View reports', 'reports'),
('settings.manage', 'Manage system settings', 'settings')
ON DUPLICATE KEY UPDATE description = VALUES(description);

-- Grant all permissions to Admin
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.role_name = 'Admin'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Manager permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_name IN ('users.view', 'users.create', 'users.edit', 'sales.process', 'inventory.view', 'inventory.adjust', 'products.manage', 'expenses.manage', 'reports.view')
WHERE r.role_name = 'Manager'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Cashier permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_name IN ('sales.process', 'inventory.view')
WHERE r.role_name = 'Cashier'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Store Staff permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_name IN ('inventory.view', 'inventory.adjust', 'products.manage')
WHERE r.role_name = 'Store Staff'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Accountant permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_name IN ('expenses.manage', 'reports.view')
WHERE r.role_name = 'Accountant'
ON DUPLICATE KEY UPDATE role_id = role_id;

INSERT INTO users (username, email, password_hash, first_name, last_name, role_id, is_active)
SELECT 'admin', 'admin@dreamblanks.local', '$2y$10$Mp.Zh9R0N0oJd5sQ31cbje6ojGqoi.x/POCWYYTKSBr/yHYnO/SFm', 'System', 'Admin', r.id, 1
FROM roles r
WHERE r.role_name = 'Admin'
ON DUPLICATE KEY UPDATE email = VALUES(email);

INSERT INTO expense_categories (category_name, description, budget_limit) VALUES
('Rent', 'Monthly rental expense', 50000),
('Utilities', 'Electricity, water, internet', 30000),
('Salaries', 'Employee payroll', 120000),
('Supplier Payments', 'Product procurement payments', 200000),
('Maintenance', 'Equipment and store maintenance', 20000),
('Marketing', 'Ads and promotions', 40000),
('Transportation', 'Delivery and fuel', 15000),
('Office Supplies', 'Administrative supplies', 10000),
('Insurance', 'Business insurance', 25000),
('Miscellaneous', 'Unplanned operational expenses', 10000)
ON DUPLICATE KEY UPDATE description = VALUES(description), budget_limit = VALUES(budget_limit);
