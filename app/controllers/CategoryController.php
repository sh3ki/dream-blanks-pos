<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;

class CategoryController extends Controller
{
    public function index(Request $request): void
    {
        $this->authorizeAnyPermission(['categories.view', 'products.manage']);
        $editCategoryId = max(0, (int) $request->input('edit_id', 0));

        $pdo = Database::connection();
        $categories = $pdo->query(
            'SELECT c.*, p.category_name AS parent_name
             FROM categories c
             LEFT JOIN categories p ON p.id = c.parent_category_id
             ORDER BY c.display_order ASC, c.category_name ASC'
        )->fetchAll();

        $editCategory = null;
        if ($editCategoryId > 0) {
            foreach ($categories as $category) {
                if ((int) ($category['id'] ?? 0) === $editCategoryId) {
                    $editCategory = $category;
                    break;
                }
            }
        }

        $this->render('categories.list', [
            'title' => 'Categories',
            'categories' => $categories,
            'editCategory' => $editCategory,
            'flash' => consume_flash(),
        ]);
    }

    public function store(Request $request): void
    {
        $this->authorizeAnyPermission(['categories.create', 'products.manage']);

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

    public function update(Request $request, array $params): void
    {
        $this->authorizeAnyPermission(['categories.update', 'products.manage']);

        $categoryId = (int) ($params['id'] ?? 0);
        $name = trim((string) $request->input('category_name'));

        if ($categoryId < 1 || $name === '') {
            flash('error', 'Category name is required.');
            $this->redirect('/categories');
        }

        $parentCategoryId = $request->input('parent_category_id') ?: null;
        if ($parentCategoryId !== null) {
            $parentCategoryId = (int) $parentCategoryId;
            if ($parentCategoryId === $categoryId) {
                flash('error', 'Category cannot be its own parent.');
                $this->redirect('/categories?edit_id=' . $categoryId);
            }
        }

        $pdo = Database::connection();
        $stmt = $pdo->prepare(
            'UPDATE categories
             SET category_name = :category_name,
                 description = :description,
                 parent_category_id = :parent_category_id,
                 display_order = :display_order,
                 is_active = :is_active,
                 tax_rate = :tax_rate,
                 updated_at = NOW()
             WHERE id = :id'
        );
        $stmt->execute([
            'category_name' => $name,
            'description' => $request->input('description'),
            'parent_category_id' => $parentCategoryId,
            'display_order' => (int) $request->input('display_order', 0),
            'is_active' => $request->input('is_active') ? 1 : 0,
            'tax_rate' => (float) $request->input('tax_rate', 0),
            'id' => $categoryId,
        ]);

        log_activity('category.updated', ['category_id' => $categoryId]);
        flash('success', 'Category updated.');
        $this->redirect('/categories');
    }

    public function destroy(Request $request, array $params): void
    {
        $this->authorizeAnyPermission(['categories.delete', 'products.manage']);

        $categoryId = (int) ($params['id'] ?? 0);
        if ($categoryId < 1) {
            flash('error', 'Invalid category target.');
            $this->redirect('/categories');
        }

        $pdo = Database::connection();

        try {
            $stmt = $pdo->prepare('DELETE FROM categories WHERE id = :id');
            $stmt->execute(['id' => $categoryId]);

            log_activity('category.deleted', ['category_id' => $categoryId]);
            flash('success', 'Category deleted successfully.');
        } catch (\Throwable $e) {
            flash('error', 'Unable to delete category: ' . $e->getMessage());
        }

        $this->redirect('/categories');
    }
}
