<?php

use App\Core\Auth;
use App\Core\Csrf;

function base_path(): string
{
    $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
    $dir = rtrim(dirname($scriptName), '/');

    if ($dir === '' || $dir === '.' || $dir === '/') {
        return '';
    }

    if (str_ends_with($dir, '/public')) {
        $dir = substr($dir, 0, -7);
    }

    return rtrim($dir, '/');
}

function url(string $path = '/'): string
{
    $base = base_path();
    if ($path === '' || $path === '/') {
        return $base === '' ? '/' : $base . '/';
    }

    $normalized = '/' . ltrim($path, '/');
    return ($base === '' ? '' : $base) . $normalized;
}

function asset_url(string $path): string
{
    return url('/public/assets/' . ltrim($path, '/'));
}

function upload_root_path(): string
{
    $root = rtrim(dirname(__DIR__, 2), '/\\');
    return $root . '/public/uploads';
}

function store_uploaded_image(string $inputName, string $subdir, int $maxBytes = 2097152): ?string
{
    if (empty($_FILES[$inputName]) || !is_array($_FILES[$inputName])) {
        return null;
    }

    $file = $_FILES[$inputName];
    $error = $file['error'] ?? UPLOAD_ERR_NO_FILE;
    if ($error === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Image upload failed.');
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size > $maxBytes) {
        throw new RuntimeException('Image exceeds the 2MB limit.');
    }

    $tmpName = (string) ($file['tmp_name'] ?? '');
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Invalid upload payload.');
    }

    $mime = null;
    if (class_exists('finfo')) {
        $finfo = new finfo(FILEINFO_MIME_TYPE);
        $mime = $finfo->file($tmpName) ?: null;
    }
    if (!$mime && function_exists('mime_content_type')) {
        $mime = mime_content_type($tmpName) ?: null;
    }

    $allowed = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];
    $ext = $mime && isset($allowed[$mime]) ? $allowed[$mime] : null;

    if (!$ext) {
        $name = (string) ($file['name'] ?? '');
        $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext === 'jpeg') {
            $ext = 'jpg';
        }
        if (!in_array($ext, ['jpg', 'png', 'webp'], true)) {
            throw new RuntimeException('Unsupported image format.');
        }
    }

    $dir = rtrim(upload_root_path(), '/\\') . '/' . trim($subdir, '/');
    if (!is_dir($dir) && !mkdir($dir, 0775, true) && !is_dir($dir)) {
        throw new RuntimeException('Unable to create upload directory.');
    }

    $filename = bin2hex(random_bytes(16)) . '.' . $ext;
    $targetPath = $dir . '/' . $filename;
    if (!move_uploaded_file($tmpName, $targetPath)) {
        throw new RuntimeException('Unable to store uploaded image.');
    }

    return '/public/uploads/' . trim($subdir, '/') . '/' . $filename;
}

function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function csrf_field(): string
{
    return '<input type="hidden" name="_token" value="' . e(Csrf::token()) . '">';
}

function old(string $key, mixed $default = ''): string
{
    return e($_SESSION['_old'][$key] ?? $default);
}

function set_old(array $values): void
{
    $_SESSION['_old'] = $values;
}

function clear_old(): void
{
    unset($_SESSION['_old']);
}

function flash(string $type, string $message): void
{
    $_SESSION['_flash'][] = ['type' => $type, 'message' => $message];
}

function consume_flash(): array
{
    $flash = $_SESSION['_flash'] ?? [];
    unset($_SESSION['_flash']);
    return $flash;
}

function current_user(): ?array
{
    return Auth::user();
}

function has_permission(string $permission): bool
{
    return Auth::hasPermission($permission);
}

function has_any_permission(array $permissions): bool
{
    return Auth::hasAnyPermission($permissions);
}

function has_all_permissions(array $permissions): bool
{
    return Auth::hasAllPermissions($permissions);
}

function log_activity(string $action, string|array|null $details = null): void
{
    try {
        $pdo = \App\Core\Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO activity_logs (user_id, action, details, ip_address, created_at)
             VALUES (:user_id, :action, :details, :ip_address, NOW())'
        );
        $stmt->execute([
            'user_id' => Auth::id(),
            'action' => $action,
            'details' => is_array($details) ? json_encode($details, JSON_UNESCAPED_SLASHES) : (string) $details,
            'ip_address' => (string) ($_SERVER['REMOTE_ADDR'] ?? ''),
        ]);
    } catch (\Throwable $e) {
        // Avoid blocking primary flows due to audit log failures.
    }
}

function unread_notifications_count(): int
{
    if (!Auth::check()) {
        return 0;
    }

    try {
        $pdo = \App\Core\Database::connection();
        $stmt = $pdo->prepare('SELECT COUNT(*) AS value FROM notifications WHERE user_id = :user_id AND is_read = 0');
        $stmt->execute(['user_id' => (int) Auth::id()]);
        $row = $stmt->fetch();
        return (int) ($row['value'] ?? 0);
    } catch (\Throwable $e) {
        return 0;
    }
}
