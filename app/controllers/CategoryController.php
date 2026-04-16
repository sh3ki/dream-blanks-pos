<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class CategoryController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorize(['Admin', 'Manager', 'Store Staff']);

        $pdo = Database::connection();
        $categories = $pdo->query(
            'SELECT c.*, p.category_name AS parent_name
             FROM categories c
             LEFT JOIN categories p ON p.id = c.parent_category_id
             ORDER BY c.display_order ASC, c.category_name ASC'
        )->fetchAll();

        $this->render('categories.list', [
            'title' => 'Categories',
            'categories' => $categories,
            'flash' => consume_flash(),
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorize(['Admin', 'Manager']);

        $name = trim((string) $request->input('category_name'));
        if ($name === '') {
            flash('error', 'Category name is required.');
            $this->redirect('/categories');
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'INSERT INTO categories (category_name, description, parent_category_id, display_order, is_active, tax_rate, created_at, updated_at)
             VALUES (:category_name, :description, :parent_category_id, :display_order, :is_active, :tax_rate, NOW(), NOW())'
        );
        $stmt->execute([
            'category_name' => $name,
            'description' => $request->input('description'),
            'parent_category_id' => $request->input('parent_category_id') ?: null,
            'display_order' => (int) $request->input('display_order', 0),
            'is_active' => $request->input('is_active') ? 1 : 0,
            'tax_rate' => (float) $request->input('tax_rate', 0),
        ]);

        flash('success', 'Category created.');
        $this->redirect('/categories');
    }
}
