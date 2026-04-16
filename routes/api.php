<?php

use App\Controllers\InventoryController;
use App\Controllers\ProductController;
use App\Core\Auth;
use App\Core\Response;
use App\Middleware\AuthMiddleware;

$router->get('/api/auth/user', function () {
    if (!Auth::check()) {
        Response::json([
            'status' => 'error',
            'message' => 'Unauthorized.',
            'data' => null,
        ], 401);
        return;
    }

    Response::json([
        'status' => 'success',
        'message' => 'User fetched.',
        'data' => Auth::user(),
    ]);
});

$router->get('/api/products/search', [ProductController::class, 'apiList'], [AuthMiddleware::class]);
$router->get('/api/inventory/low-stock', [InventoryController::class, 'lowStock'], [AuthMiddleware::class]);
