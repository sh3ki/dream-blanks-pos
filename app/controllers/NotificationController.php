<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class NotificationController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Cashier', 'Store Staff', 'Accountant']);

        $pdo = Database::connection();
        $stmt = $pdo->prepare('SELECT * FROM notifications WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 100');
        $stmt->execute(['user_id' => (int) Auth::id()]);

        $this->render('notifications.list', [
            'title' => 'Notifications',
            'notifications' => $stmt->fetchAll(),
            'flash' => consume_flash(),
        ]);
    }

    public function markRead(Request $request, array $params): void
    {
        $this->authorize(['Admin', 'Manager', 'Cashier', 'Store Staff', 'Accountant']);
        $id = (int) ($params['id'] ?? 0);

        $pdo = Database::connection();
        $stmt = $pdo->prepare('UPDATE notifications SET is_read = 1, read_at = NOW() WHERE id = :id AND user_id = :user_id');
        $stmt->execute([
            'id' => $id,
            'user_id' => (int) Auth::id(),
        ]);

        $this->redirect('/notifications');
    }
}
