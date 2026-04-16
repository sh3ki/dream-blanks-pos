# Project Architecture

## 1. Architecture Goals

1. Keep the codebase modular and maintainable.
2. Separate business logic from controllers and views.
3. Enforce secure, role-aware request handling.
4. Support growth from single-store operations to larger deployments.
5. Keep all UI pages mobile responsive.

## 2. High-Level System Design

The application uses a layered monolith architecture with RESTful endpoints and server-rendered pages.

1. Presentation Layer: HTML, CSS, JavaScript, reusable page layouts and shared UI components.
2. Routing and Controller Layer: maps HTTP routes to controller actions.
3. Middleware Layer: authentication, authorization, validation, CSRF checks, request guards.
4. Service Layer: core business logic (sales, inventory, reporting, notification workflows).
5. Data Access Layer (Models): database interaction and query orchestration.
6. Persistence Layer: MySQL/MariaDB relational schema with keys and indexes.

## 3. Directory and Module Architecture

### Configuration

1. config/database.php: database connection and options.
2. config/app.php: global app settings.
3. config/constants.php: fixed values and module constants.

### Application Modules

1. app/controllers: HTTP entry points and response orchestration.
2. app/models: database entities and data queries.
3. app/services: reusable business rules and transactions.
4. app/middleware: auth/role/validation request gates.
5. app/helpers: utility functions for auth, validation, files, dates, mail.

### Public Layer

1. public/index.php: front controller.
2. public/assets/css: global and responsive styles.
3. public/assets/js: app logic, POS behavior, charts.
4. public/uploads: controlled user-upload storage.

### Views

1. resources/views/layouts: shared app and auth layouts.
2. resources/views/<module>: module-specific templates.

### Persistence and Operations

1. database/migrations: schema evolution scripts.
2. database/seeds: default/seeded records.
3. storage/logs: runtime logs.
4. storage/backups: backup artifacts.

## 4. Request Lifecycle

1. Client sends request to front controller.
2. Router resolves route and controller action.
3. Middleware validates authentication, authorization, and request integrity.
4. Controller validates request data and delegates to service layer.
5. Service executes business logic and database transactions.
6. Model/data layer reads and writes records.
7. Controller returns JSON (API) or rendered view (web page).
8. Critical operations create activity and audit logs.

## 5. Domain Modules and Responsibilities

1. Auth Module: login/logout, session controls, lockouts, reset flows.
2. User/Employee Module: user lifecycle, role assignments, employee metadata.
3. Product/Category Module: product catalog, hierarchy, pricing, barcodes.
4. Inventory Module: stock movement, adjustments, reorder thresholds, stock counts.
5. POS/Sales Module: transaction creation, payments, receipt trigger, returns.
6. Expense Module: expense capture, approvals, budget tracking.
7. Dashboard/Report Module: KPI aggregates, chart data, period analytics.
8. Notification Module: event generation, user preferences, delivery logs.
9. Transaction History Module: unified financial ledger and change tracking.

## 6. Data Architecture Principles

1. Normalized schema (3NF) for transactional entities.
2. Foreign key constraints for data integrity.
3. created_at and updated_at in key tables.
4. Soft-delete/status flags for recoverable records.
5. Indexing for high-frequency search and filter columns.
6. Explicit transaction handling for sale posting and inventory updates.

## 7. Security Architecture

1. Password hashing with bcrypt.
2. Session hardening and inactivity timeout.
3. Login attempt throttling and temporary lockout.
4. RBAC and permission checks in middleware and service layer.
5. Prepared statements for all SQL operations.
6. CSRF token checks for state-changing requests.
7. Output encoding for UI rendering.
8. Audit trails for sensitive operations.

## 8. API Architecture

1. RESTful route structure under /api.
2. JSON request/response contracts with clear validation messages.
3. Consistent status codes and error envelope.
4. Pagination and filtering for list endpoints.
5. Auth checks for every protected endpoint.
6. Versioning strategy recommended as /api/v1 for future compatibility.

## 9. Frontend Architecture

1. Shared layout templates for auth and app pages.
2. Reusable components (forms, tables, cards, modal, alerts, pagination, filters).
3. Module-specific JavaScript for POS, charts, and page behaviors.
4. CSS split into base style and responsive overrides.
5. Progressive enhancement for keyboard navigation and accessibility.

## 10. Reporting and Analytics Architecture

1. Aggregation queries and summary tables for dashboard metrics.
2. Time-based grouping for hourly/daily/weekly/monthly reporting.
3. Cached summary generation where needed for heavy dashboards.
4. Export pipeline for CSV/Excel/PDF generation.

## 11. File and Document Handling

1. Controlled upload paths by content type.
2. File type and size validation.
3. Unique filename strategy to prevent collisions.
4. Optional image optimization for product photos and receipts.
5. Secure access checks for uploaded financial documents.

## 12. Deployment Architecture

1. PHP 8 runtime on Apache/Nginx.
2. MySQL or MariaDB backend.
3. Environment-based configuration via .env.
4. HTTPS required in production.
5. Scheduled backup jobs and log rotation.
6. Opcache and compression enabled for production performance.

## 13. Scalability and Maintainability Strategy

1. Keep services cohesive and controllers thin.
2. Use shared utilities for repeated logic.
3. Add module boundaries to reduce coupling.
4. Enforce coding standards and review checklist.
5. Add automated tests for critical business flows.
6. Prepare for horizontal scaling by keeping app stateless outside session storage.

## 14. Observability and Operations

1. Structured application logs.
2. Error logging without exposing sensitive data.
3. Activity logs for compliance and troubleshooting.
4. Daily operational reports for business stakeholders.
5. Health checks and uptime monitoring integration readiness.

## 15. Architectural Decision Records (Recommended)

Document and version key technical decisions:

1. Auth strategy and session policy.
2. Inventory update consistency model.
3. Receipt/invoice rendering stack.
4. Reporting aggregation and caching choices.
5. Notification channel strategy.
