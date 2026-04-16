<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Authentication') ?> - Dream Blanks POS</title>
    <link rel="stylesheet" href="<?= e(asset_url('css/style.css')) ?>">
</head>
<body>
<div class="auth-shell">
    <div class="auth-card">
        <?php require $contentView; ?>
    </div>
</div>
</body>
</html>
