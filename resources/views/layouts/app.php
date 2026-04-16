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

$navItems = [
    ['/dashboard', 'Dashboard'],
    ['/pos', 'POS'],
    ['/sales', 'Sales'],
    ['/products', 'Products'],
    ['/categories', 'Categories'],
    ['/inventory', 'Inventory'],
    ['/employees', 'Employees'],
    ['/expenses', 'Expenses'],
    ['/transactions', 'Transactions'],
    ['/notifications', 'Notifications'],
    ['/reports/sales', 'Reports'],
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
                <small style="color:#6b7280;">POS System</small>
            </div>
        </div>

        <ul class="nav-list">
            <?php foreach ($navItems as [$path, $label]): ?>
                <?php $isActive = $currentPath === $path || str_starts_with($currentPath, $path . '/'); ?>
                <li>
                    <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= e(url($path)) ?>"><?= e($label) ?></a>
                </li>
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
                <a href="<?= e(url('/notifications')) ?>" class="icon-btn" aria-label="Notifications">
                    🔔
                    <span class="badge-dot"></span>
                </a>
                <div class="profile">
                    <button class="profile-avatar" data-profile-toggle type="button"><?= e($initial) ?></button>
                    <div class="profile-menu" data-profile-menu>
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
            <?php foreach ($flashItems as $item): ?>
                <div class="alert <?= $item['type'] === 'success' ? 'alert-success' : 'alert-error' ?>">
                    <?= e($item['message']) ?>
                </div>
            <?php endforeach; ?>

            <?php require $contentView; ?>
        </section>
    </main>
</div>
<script src="<?= e(asset_url('js/app.js')) ?>"></script>
</body>
</html>
