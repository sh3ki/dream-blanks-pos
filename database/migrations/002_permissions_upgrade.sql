-- Permission matrix upgrade for permission-driven authorization and access control module

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

INSERT INTO role_permissions (role_id, permission_id)
SELECT r.id, p.id
FROM roles r
CROSS JOIN permissions p
WHERE r.role_name = 'Admin'
ON DUPLICATE KEY UPDATE role_id = role_id;

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
