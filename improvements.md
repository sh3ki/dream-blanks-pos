# System Improvements Roadmap

This document summarizes the current state of the Dream Blanks POS codebase and lists the most important improvements for the whole system. It is based on a scan of the application structure, controllers, services, views, routes, schema, and seed data.

## 1. Current System Snapshot

The application already covers the core POS workflow and several operational modules:

1. Authentication with session login, lockout handling, and profile editing.
2. Dashboard with KPI cards and recent sales.
3. Employee, category, product, inventory, sales, expense, notification, transaction, and report screens.
4. Inventory stock adjustment and sales checkout transactions with database transactions.
5. CSRF protection on write requests and prepared SQL statements in the main flows.
6. Seeded roles, permissions, and role_permissions tables in the database schema.

The implementation is solid as a foundation, but it still behaves like a role-gated prototype in several places. The main opportunity is to convert the current static role checks into a real permission system and then harden each module with the missing operational features.

## 2. Key Findings From the Codebase Scan

1. Roles and permissions already exist in the schema and seed data, but the application logic still checks roles by name instead of permissions.
2. The sidebar navigation is static, so users can see menu items even when access should be restricted.
3. Most modules provide list and create flows, but edit, delete, archive, and detail views are still largely missing.
4. Controllers contain direct SQL queries, and the `app/models` layer is currently empty, so business/data access is not separated yet.
5. Large list pages have client-side filtering, but there is no server-side pagination, sorting, or export flow.
6. Reporting is basic and mostly summary-based; it needs drill-down, filters, and export support.
7. Tests are not present, which means the current sales, inventory, and permission flows are not protected against regressions.

## 3. Highest-Priority System Improvements

### 3.1 Replace Hard-Coded Role Checks With Permission Checks

The most important improvement is to move from role-name authorization to permission-based authorization.

Recommended approach:

1. Add a permission resolver that loads all permissions for the current user’s role.
2. Extend the auth/session payload with the role ID and a permission list or permission map.
3. Add controller helpers such as `can('products.create')`, `can('inventory.adjust')`, and `can('reports.view')`.
4. Add route or middleware-level permission guards for sensitive actions.
5. Use the same permission checks to drive the sidebar and action buttons.

This will let Admin assign users to roles and let roles control access to each module or action, such as read, write, update, and delete.

### 3.2 Build a Real Roles and Permissions Administration Module

The database already has `roles`, `permissions`, and `role_permissions`, but there is no UI to manage them.

Add screens and endpoints for:

1. Role list, create, edit, delete.
2. Permission list, create, edit, delete.
3. Role permission matrix with grouped module sections.
4. User-to-role assignment from employee or user administration.
5. Search, filter, and bulk update support for roles and permissions.

Suggested permission naming pattern:

1. `dashboard.view`
2. `users.view`, `users.create`, `users.update`, `users.delete`
3. `employees.view`, `employees.create`, `employees.update`, `employees.delete`
4. `products.view`, `products.create`, `products.update`, `products.delete`
5. `categories.view`, `categories.create`, `categories.update`, `categories.delete`
6. `inventory.view`, `inventory.adjust`, `inventory.count`, `inventory.export`
7. `sales.view`, `sales.process`, `sales.refund`, `sales.void`
8. `expenses.view`, `expenses.create`, `expenses.approve`, `expenses.delete`
9. `reports.view`, `reports.export`
10. `notifications.view`, `notifications.manage`
11. `settings.manage`

## 4. Module-by-Module Improvement List

### 4.1 Authentication and Account Management

Current state:

1. Login, logout, session timeout, and profile update are implemented.
2. Account lockout exists after failed attempts.
3. Remember-me and password reset are mentioned in the feature docs, but are not fully implemented in the current code paths.

Improvements:

1. Add forgot-password and secure password reset flow.
2. Add password change from profile settings.
3. Persist remember-me tokens securely instead of only exposing the checkbox in the UI.
4. Add last login, last activity, and login device/IP visibility in the profile or admin user view.
5. Add audit logging for login, logout, lockout, and profile changes.
6. Move auth-related validation into reusable request validation helpers.

### 4.2 Dashboard

Current state:

1. KPI cards show sales, transactions, average ticket, expenses, and low stock.
2. Recent sales are displayed in a table.

Improvements:

1. Add date range filters for dashboard metrics.
2. Add trend charts for sales, expenses, stock alerts, and payment methods.
3. Add role-aware dashboard widgets so each role sees only relevant metrics.
4. Add top products, low stock summary, and pending approval widgets.
5. Improve query efficiency by caching frequently used aggregates.

### 4.3 Employees and Users

Current state:

1. Employees can be created and listed.
2. A user account is created together with the employee record.
3. Role assignment is present, but it is still selected from a simple role list.

Improvements:

1. Add edit, deactivate, archive, and restore actions.
2. Add separate user management screens for admin-level access.
3. Add user-role assignment from a dedicated admin interface.
4. Add permission overview per employee or user.
5. Add profile photo upload and preview.
6. Add search filters by status, role, department, and hire date.
7. Add stronger duplicate checks for email, username, and employee ID.
8. Add password reset and account unlock controls for admins.

### 4.4 Roles and Permissions

Current state:

1. Database tables for roles, permissions, and role_permissions already exist.
2. Seed data defines the initial permission matrix.
3. Application authorization still depends on hard-coded role names.

Improvements:

1. Add a Roles module for CRUD management.
2. Add a Permissions module for CRUD management.
3. Add a Role Permissions screen with module groupings and read/write/delete checkboxes.
4. Add per-user role assignment in the user or employee management area.
5. Add sidebar visibility control based on permission access.
6. Add route protection based on permission codes, not just roles.
7. Add a permission cache to avoid repeated role lookup queries on every request.
8. Add a seed/refresh utility for rebuilding default permissions safely.
9. Add validation to prevent removing the last Admin role or leaving the system without a superuser.
10. Add an audit trail for role and permission changes.

Recommended sidebar permission mapping:

1. Dashboard: `dashboard.view`
2. POS: `sales.process`
3. Sales list: `sales.view`
4. Products: `products.view`
5. Categories: `categories.view`
6. Inventory: `inventory.view`
7. Employees: `employees.view`
8. Expenses: `expenses.view`
9. Transactions: `transactions.view`
10. Notifications: `notifications.view`
11. Reports: `reports.view`
12. Role management: `roles.manage`
13. Permission management: `permissions.manage`

### 4.5 Categories

Current state:

1. Categories can be listed and created.
2. Parent categories and tax rate are supported.

Improvements:

1. Add edit, delete, and soft-delete support.
2. Add category tree view for nested categories.
3. Add active/inactive filters and bulk status toggles.
4. Add image/icon support already hinted at in the schema.
5. Add validation to prevent circular parent relationships.

### 4.6 Products

Current state:

1. Products can be listed and created.
2. Product creation also creates inventory rows.
3. API search is available for POS usage.

Improvements:

1. Add edit, delete, duplicate, archive, and restore flows.
2. Add image upload and gallery support.
3. Add barcode generation and scanning workflows.
4. Add product variants and attribute support.
5. Add bulk import/export for product catalog maintenance.
6. Add price history tracking when selling price or cost changes.
7. Add product detail pages with movement, sales, and stock history.
8. Add stronger validation for SKU uniqueness and numeric fields.

### 4.7 Inventory

Current state:

1. Inventory lists current quantity, reserved quantity, available stock, and reorder level.
2. Manual stock adjustment writes to movement and history tables.
3. Low-stock API exists.

Improvements:

1. Add stock count and reconciliation workflow.
2. Add stock receipt / receiving from suppliers.
3. Add damaged, lost, transfer, and correction movement types.
4. Add inventory adjustment approvals for sensitive roles.
5. Add low-stock notifications and threshold alerts.
6. Add paginated inventory history and movement logs.
7. Add export to CSV/Excel for stock audits.
8. Add per-product inventory detail screens.

### 4.8 POS and Sales

Current state:

1. POS can add products, calculate totals, and complete a sale.
2. Sales transactions reduce stock and create related payment and receipt rows.
3. Sales history exists in list form.

Improvements:

1. Add hold/resume cart support.
2. Add item-level discount editing and refund/void flows.
3. Add split payment support and payment validation.
4. Add customer selection and customer history.
5. Add receipt preview, reprint, and PDF output.
6. Add cashier shift summary and cash drawer tracking.
7. Add better stock availability validation before checkout.
8. Add transaction cancellation logic with proper stock reversal.
9. Add sales detail pages with line items and payment records.
10. Add POS keyboard shortcuts and barcode-first workflows.

### 4.9 Expenses

Current state:

1. Expense listing and creation are implemented.
2. Approval flow exists.

Improvements:

1. Add rejected, paid, and archived statuses.
2. Add expense edit/delete with audit protection.
3. Add receipt/document upload for expense evidence.
4. Add budget warnings and per-category budget dashboards.
5. Add approval routing by amount or category.
6. Add expense export and printable reports.
7. Add recurring expense support for fixed monthly costs.

### 4.10 Transactions

Current state:

1. Transaction history is listed in the UI.
2. The transaction concept exists in the schema and reports.

Improvements:

1. Normalize the transaction model so sales, payments, refunds, adjustments, and expenses can be viewed in a single ledger consistently.
2. Add drill-down detail pages for each transaction type.
3. Add stronger filters by date, type, user, and status.
4. Add export and reconciliation views.
5. Add linked source records for traceability.

### 4.11 Notifications

Current state:

1. Notification list and mark-as-read are implemented.

Improvements:

1. Add unread badge counts in the topbar.
2. Add notification preferences by module and urgency.
3. Add bulk mark-as-read and delete actions.
4. Add system notifications for low stock, approvals, and failed logins.
5. Add notification delivery history.

### 4.12 Reports

Current state:

1. Sales, inventory, and expense reports exist.
2. Reporting is page-based and summary-driven.

Improvements:

1. Add date range pickers to every report.
2. Add export to CSV, Excel, and PDF.
3. Add report drill-down by product, cashier, category, and payment method.
4. Add scheduled report delivery.
5. Add report permission checks per module.
6. Add cached aggregates for heavy reports.

### 4.13 UI and Layout

Current state:

1. The layout is clean and functional.
2. Sidebar navigation is fixed and static.
3. Reusable filter component already exists.

Improvements:

1. Make the sidebar dynamic based on permissions and active module access.
2. Add module badges for low stock, unread notifications, and pending approvals.
3. Add standardized empty states, loading states, and error states.
4. Add consistent action menus for edit, delete, approve, and view.
5. Improve accessibility with proper labels, focus states, and keyboard navigation.
6. Add reusable modal and confirmation components.
7. Improve visual hierarchy on tables and cards for dense data screens.

## 5. Platform and Codebase Improvements

### 5.1 Data Access Layer

1. Introduce models or repositories under `app/models` so SQL is not spread across controllers.
2. Move repeated query logic into service or repository classes.
3. Centralize pagination, filtering, and sorting helpers.
4. Standardize database transaction handling.

### 5.2 Validation and Error Handling

1. Add request validation helpers for all create and update forms.
2. Return consistent validation messages in both web and API paths.
3. Improve exception handling and logging context.
4. Add user-friendly error pages for 403, 404, and 500 states.

### 5.3 Testing

1. Add unit tests for auth, permissions, and service layer rules.
2. Add integration tests for POS checkout, inventory deduction, and expense approval.
3. Add regression tests for role/permission matrix behavior.
4. Add basic smoke tests for routes and view rendering.

### 5.4 Security Hardening

1. Add permission-aware authorization at route and controller levels.
2. Ensure all write actions require CSRF protection.
3. Add stronger session handling and token rotation where needed.
4. Add rate limiting or request throttling for login and sensitive endpoints.
5. Add audit logging for role changes, user changes, and financial actions.

### 5.5 Performance and Scalability

1. Add pagination to every large dataset page.
2. Add indexes for frequent search and filter columns if any are missing.
3. Cache dashboard summaries and reusable permission lookups.
4. Reduce duplicate queries in list pages.
5. Add server-side search for large tables when data grows.

## 6. Suggested Delivery Order

### Phase 1: Core Access Control

1. Build roles and permissions management UI.
2. Replace role-name checks with permission checks.
3. Make sidebar navigation permission-aware.
4. Add audit logging for permission changes.

### Phase 2: Core Module Completion

1. Add edit/delete/restore flows for products, categories, employees, and expenses.
2. Add pagination and detail pages.
3. Add better validation and consistent error handling.

### Phase 3: POS and Inventory Hardening

1. Add POS hold/resume, refunds, and receipt improvements.
2. Add stock count and reconciliation.
3. Add alerts and inventory history screens.

### Phase 4: Reporting and Operations

1. Add exportable reports and date filters.
2. Add dashboard charts and drill-down views.
3. Add notification preferences and unread counts.

### Phase 5: Quality and Maintainability

1. Add tests.
2. Add models or repositories.
3. Add performance caching and query cleanup.

## 7. Acceptance Criteria For The Roles And Permissions Feature

The roles and permissions feature should be considered complete when:

1. Admin can create, edit, and delete roles.
2. Admin can create, edit, and delete permissions.
3. Admin can assign permissions to roles from a matrix UI.
4. Admin can assign users to roles from the user or employee screen.
5. Each sidebar item and protected action is hidden or blocked according to permission.
6. The application no longer depends on hard-coded role names for authorization.
7. The permission model covers read, create, update, delete, approve, export, and other module-specific actions.
8. Role and permission changes are logged.

## 8. Notes

1. The current system is already a functional foundation for a POS workflow.
2. The biggest structural gap is the difference between having permission tables in the database and actually enforcing them in the app.
3. Once permission-based access is in place, the rest of the feature backlog becomes safer to expand.