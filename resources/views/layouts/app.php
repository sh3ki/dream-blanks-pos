<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$basePath = base_path();
if ($basePath !== '' && str_starts_with($currentPath, $basePath)) {
    $currentPath = substr($currentPath, strlen($basePath)) ?: '/';
}
$currentPath = '/' . ltrim($currentPath, '/');
$flashItems = $flash ?? consume_flash();
$user = current_user();
$initial = strtoupper(substr($user['name'] ?? 'U', 0, 1));
$unreadNotifications = has_permission('notifications.view') ? unread_notifications_count() : 0;

$navGroups = [
    [
        'label' => 'Operations',
        'items' => [
            ['/dashboard', 'Dashboard', ['dashboard.view']],
            ['/pos', 'POS', ['sales.process']],
            ['/sales', 'Sales', ['sales.view']],
        ],
    ],
    [
        'label' => 'Catalog',
        'items' => [
            ['/products', 'Products', ['products.view', 'products.manage']],
            ['/categories', 'Categories', ['categories.view', 'products.manage']],
            ['/inventory', 'Inventory', ['inventory.view']],
        ],
    ],
    [
        'label' => 'People',
        'items' => [
            ['/employees', 'Employees', ['employees.view', 'users.view']],
        ],
    ],
    [
        'label' => 'Finance',
        'items' => [
            ['/expenses', 'Expenses', ['expenses.view', 'expenses.manage']],
            ['/transactions', 'Transactions', ['transactions.view']],
            ['/reports/sales', 'Reports', ['reports.view']],
        ],
    ],
    [
        'label' => 'Admin',
        'items' => [
            ['/notifications', 'Notifications', ['notifications.view']],
            ['/access-control', 'Roles & Permissions', ['roles.manage', 'permissions.manage']],
        ],
    ],
];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Dream Blanks POS') ?> - Dream Blanks POS</title>
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset_url('css/responsive.css')) ?>">
</head>
<body>
<div class="layout">
    <aside class="sidebar" data-sidebar>
        <div class="logo">
            <div class="logo-mark">DB</div>
            <div>
                <div>Dream Blanks</div>
                <small class="logo-subtitle">POS System</small>
            </div>
        </div>

        <ul class="nav-list">
            <?php foreach ($navGroups as $group): ?>
                <li class="nav-section-title"><?= e($group['label']) ?></li>
                <?php foreach ($group['items'] as [$path, $label, $permissions]): ?>
                    <?php if (!has_any_permission($permissions)): ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    <?php $isActive = $currentPath === $path || str_starts_with($currentPath, $path . '/'); ?>
                    <li>
                        <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= e(url($path)) ?>"><?= e($label) ?></a>
                    </li>
                <?php endforeach; ?>
            <?php endforeach; ?>
        </ul>
    </aside>

    <main class="main">
        <header class="topbar">
            <div class="topbar-left">
                <button class="icon-btn mobile-menu-btn" data-mobile-menu type="button">☰</button>
                <h1 class="topbar-title"><?= e($title ?? 'Dashboard') ?></h1>
            </div>

            <div class="topbar-right">
                <?php if (has_permission('notifications.view')): ?>
                <a href="<?= e(url('/notifications')) ?>" class="icon-btn" aria-label="Notifications">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M12 22c1.1 0 2-.9 2-2h-4c0 1.1.9 2 2 2zm6-6v-5c0-3.07-1.64-5.64-4.5-6.32V4c0-.83-.67-1.5-1.5-1.5s-1.5.67-1.5 1.5v.68C7.63 5.36 6 7.92 6 11v5l-2 2v1h16v-1l-2-2z"/></svg>
                    <?php if ($unreadNotifications > 0): ?>
                        <span class="badge-dot"></span>
                    <?php endif; ?>
                </a>
                <?php endif; ?>
                <div class="profile action-menu" data-menu>
                    <button class="profile-avatar" data-menu-toggle type="button"><?= e($initial) ?></button>
                    <div class="profile-menu menu" data-menu-list>
                        <a href="<?= e(url('/profile')) ?>">Profile Settings</a>
                        <form method="post" action="<?= e(url('/logout')) ?>">
                            <?= csrf_field() ?>
                            <button type="submit">Logout</button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <section class="content">
            <?php require $contentView; ?>
        </section>

        <footer class="app-footer">Dream Blanks POS &middot; Build 1.0</footer>
    </main>
</div>

<div class="toast-container" data-toast-container>
    <?php foreach ($flashItems as $item): ?>
        <div class="toast <?= $item['type'] === 'success' ? 'success' : 'error' ?>" data-toast>
            <?= e($item['message']) ?>
        </div>
    <?php endforeach; ?>
</div>

<div class="modal" id="confirm-modal" data-confirm-modal>
    <div class="modal-card modal-sm">
        <div class="modal-header">
            <strong data-confirm-title>Confirm Action</strong>
            <button class="modal-close" type="button" data-modal-close aria-label="Close">&times;</button>
        </div>
        <div class="modal-body">
            <p data-confirm-message>Are you sure you want to proceed?</p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" type="button" data-modal-close>Cancel</button>
            <button class="btn btn-danger" type="button" data-confirm-accept>Confirm</button>
        </div>
    </div>
</div>
<script src="<?= e(asset_url('js/app.js')) ?>"></script>
</body>
</html>
