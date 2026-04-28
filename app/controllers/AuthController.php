<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;
use App\Core\Database;
use App\Core\Request;
use App\Services\AuthService;

class AuthController extends Controller
{
    public function showLogin(Request $request): void
    {
        $this->render('auth.login', [
            'title' => 'Login',
            'flash' => consume_flash(),
        ], 'auth');
    }

    public function login(Request $request): void
    {
        $identity = trim((string) $request->input('identity'));
        $password = (string) $request->input('password');

        if ($identity === '' || $password === '') {
            flash('error', 'Email/username and password are required.');
            $this->redirect('/login');
        }

        $service = new AuthService();
        $result = $service->authenticate($identity, $password);

        if (!$result['ok']) {
            flash('error', $result['message']);
            $this->redirect('/login');
        }

        Auth::login($result['user']);
        flash('success', 'Welcome back, ' . Auth::user()['name'] . '.');
        $this->redirect('/dashboard');
    }

    public function logout(Request $request): void
    {
        Auth::logout();
        $this->redirect('/login');
    }

    public function profile(Request $request): void
    {
        $this->render('auth.profile', [
            'title' => 'Profile Settings',
            'user' => Auth::user(),
            'flash' => consume_flash(),
        ]);
    }

    public function updateProfile(Request $request): void
    {
        $user = Auth::user();

        $firstName = trim((string) $request->input('first_name'));
        $lastName = trim((string) $request->input('last_name'));
        $email = trim((string) $request->input('email'));

        $photoPath = null;
        try {
            $photoPath = store_uploaded_image('avatar', 'employees');
        } catch (\Throwable $e) {
            flash('error', $e->getMessage());
            $this->redirect('/profile');
        }

        if ($firstName === '' || $lastName === '' || $email === '') {
            flash('error', 'First name, last name, and email are required.');
            $this->redirect('/profile');
        }

        $pdo = Database::connection();
        $pdo->beginTransaction();

        try {
            $stmt = $pdo->prepare('UPDATE users SET first_name = :first_name, last_name = :last_name, email = :email, updated_at = NOW() WHERE id = :id');
            $stmt->execute([
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'id' => $user['id'],
            ]);

            if ($photoPath !== null) {
                $photoStmt = $pdo->prepare('UPDATE employees SET photo_path = :photo_path, updated_at = NOW() WHERE user_id = :user_id');
                $photoStmt->execute([
                    'photo_path' => $photoPath,
                    'user_id' => $user['id'],
                ]);
            }

            $pdo->commit();
        } catch (\Throwable $e) {
            $pdo->rollBack();
            flash('error', 'Unable to update profile: ' . $e->getMessage());
            $this->redirect('/profile');
        }

        $_SESSION['user']['name'] = $firstName . ' ' . $lastName;
        $_SESSION['user']['first_name'] = $firstName;
        $_SESSION['user']['last_name'] = $lastName;
        $_SESSION['user']['email'] = $email;
        flash('success', 'Profile updated.');
        $this->redirect('/profile');
    }
}
