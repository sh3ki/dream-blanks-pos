<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\DashboardService;

class DashboardController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorizePermission('dashboard.view');

        $service = new DashboardService();
        $summary = $service->summary();
        $recentSales = $service->recentSales();

        $this->render('dashboard.index', [
            'title' => 'Dashboard',
            'summary' => $summary,
            'recentSales' => $recentSales,
            'flash' => consume_flash(),
        ]);
    }
}
