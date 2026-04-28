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

-- Expanded permission matrix for module-level access control
INSERT INTO permissions (permission_name, description, module) VALUES
('dashboard.view', 'View dashboard', 'dashboard'),
('users.update', 'Update user account and role assignments', 'users'),
('employees.view', 'View employee records', 'employees'),
('employees.create', 'Create employee records', 'employees'),
('employees.update', 'Update employee records', 'employees'),
('employees.delete', 'Delete employee records', 'employees'),
('products.view', 'View product records', 'products'),
('products.create', 'Create product records', 'products'),
('products.update', 'Update product records', 'products'),
('products.delete', 'Delete product records', 'products'),
('categories.view', 'View category records', 'categories'),
('categories.create', 'Create category records', 'categories'),
('categories.update', 'Update category records', 'categories'),
('categories.delete', 'Delete category records', 'categories'),
('sales.view', 'View sales records', 'sales'),
('sales.refund', 'Process refunds', 'sales'),
('sales.void', 'Void sale transactions', 'sales'),
('expenses.view', 'View expense records', 'expenses'),
('expenses.create', 'Create expense records', 'expenses'),
('expenses.approve', 'Approve expense records', 'expenses'),
('expenses.delete', 'Delete expense records', 'expenses'),
('reports.export', 'Export reports to CSV', 'reports'),
('transactions.view', 'View transaction history', 'transactions'),
('notifications.view', 'View notifications', 'notifications'),
('roles.manage', 'Manage roles and role assignments', 'access_control'),
('permissions.manage', 'Manage permissions and role matrix', 'access_control')
ON DUPLICATE KEY UPDATE description = VALUES(description), module = VALUES(module);

-- Ensure Admin receives all permissions, including newly seeded rows
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.role_name = 'Admin'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Expanded Manager permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_name IN (
	'dashboard.view',
	'users.view', 'users.create', 'users.edit', 'users.update',
	'employees.view', 'employees.create', 'employees.update',
	'sales.process', 'sales.view',
	'inventory.view', 'inventory.adjust',
	'products.manage', 'products.view', 'products.create', 'products.update',
	'categories.view', 'categories.create', 'categories.update',
	'expenses.manage', 'expenses.view', 'expenses.create', 'expenses.approve',
	'reports.view', 'reports.export', 'transactions.view', 'notifications.view'
)
WHERE r.role_name = 'Manager'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Expanded Cashier permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_name IN (
	'dashboard.view',
	'sales.process', 'sales.view',
	'inventory.view',
	'notifications.view'
)
WHERE r.role_name = 'Cashier'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Expanded Store Staff permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_name IN (
	'dashboard.view',
	'inventory.view', 'inventory.adjust',
	'products.manage', 'products.view', 'products.create', 'products.update',
	'categories.view', 'categories.create', 'categories.update',
	'notifications.view'
)
WHERE r.role_name = 'Store Staff'
ON DUPLICATE KEY UPDATE role_id = role_id;

-- Expanded Accountant permissions
INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
JOIN permissions p ON p.permission_name IN (
	'dashboard.view',
	'sales.view',
	'expenses.manage', 'expenses.view', 'expenses.create', 'expenses.approve',
	'reports.view', 'reports.export', 'transactions.view', 'notifications.view'
)
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
