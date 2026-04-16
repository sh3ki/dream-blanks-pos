# Dream Blanks POS System

Dream Blanks POS is a full-featured retail management system built from scratch in PHP and MySQL using a modular MVC-style architecture.

## Included in This Build

1. Authentication with session-based login, account lockout, profile management, logout.
2. Role-aware route protection and controller authorization checks.
3. Employee management module (create/list/search).
4. Product and category management modules.
5. Inventory tracking and stock adjustment with stock movement history.
6. POS screen with cart, checkout flow, sale posting, stock deduction, and receipt records.
7. Sales list, expense module with approval flow, notifications list, transaction history list.
8. Reports pages for sales, inventory, and expenses.
9. Modern responsive UI with light gray design, sidebar navigation, topbar, notification and profile dropdown.

## Tech Stack

1. PHP 8.0+
2. MySQL 5.7+ / MariaDB
3. HTML5, CSS3, Vanilla JavaScript

## Directory Structure

The project follows the planned architecture in the planning documents.

## Setup Guide

1. Copy environment file:

```bash
copy .env.example .env
```

2. Configure database credentials in .env.

3. Create database:

```sql
CREATE DATABASE dream_blanks_pos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

4. Run migration SQL:

- database/migrations/001_initial_schema.sql

5. Run seed SQL:

- database/seeds/001_seed_base_data.sql

6. Point Apache/Nginx to project root and allow rewrite to public/index.php.

7. Open app in browser:

- http://127.0.0.1/dream-blanks-pos/

## Default Admin Login

1. Username: admin
2. Password: password

Note: change default password immediately in production.

## Security Notes

1. Passwords use bcrypt.
2. CSRF middleware protects write requests.
3. PDO prepared statements are used in database operations.
4. Session timeout and lockout policy are implemented.

## Next Build Enhancements Recommended

1. Add complete edit/delete flows for all entities.
2. Add full API envelope and versioning strategy.
3. Add unit and integration tests under tests/.
4. Add PDF and email integrations for receipts/invoices.
5. Add chart package integration on dashboard.
