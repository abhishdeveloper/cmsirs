<?php

namespace App\Controllers;

use App\Models\User;
use App\Core\Session;
use Exception;

class AuthController {

    private User $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function showRegister(): void {
        // Assume simple inclusion or basic templating
        require_once __DIR__ . '/../Views/auth/register.php';
    }

    public function registerProcess(array $data): void {
        try {
            $firstName = trim($data['first_name'] ?? '');
            $lastName = trim($data['last_name'] ?? '');
            $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = $data['password'] ?? '';
            $confirmPassword = $data['password_confirm'] ?? '';
            $phone = trim($data['phone'] ?? '');

            if (!$firstName || !$lastName || !$email || !$password) {
                Session::setFlash('error', 'Please fill in all required fields.');
                $this->redirect('/register');
                return;
            }

            if ($password !== $confirmPassword) {
                Session::setFlash('error', 'Passwords do not match.');
                $this->redirect('/register');
                return;
            }

            if ($this->userModel->emailExists($email)) {
                Session::setFlash('error', 'Email is already registered.');
                $this->redirect('/register');
                return;
            }

            // Register user
            if ($this->userModel->register($firstName, $lastName, $email, $password, $phone)) {
                Session::setFlash('success', 'Registration successful! Please login.');
                $this->redirect('/login');
            } else {
                Session::setFlash('error', 'An error occurred during registration. Please try again.');
                $this->redirect('/register');
            }
        } catch (Exception $e) {
            Session::setFlash('error', 'System error: ' . $e->getMessage());
            $this->redirect('/register');
        }
    }

    public function showLogin(): void {
        require_once __DIR__ . '/../Views/auth/login.php';
    }

    public function loginProcess(array $data): void {
        try {
            $email = filter_var($data['email'] ?? '', FILTER_VALIDATE_EMAIL);
            $password = $data['password'] ?? '';

            if (!$email || !$password) {
                Session::setFlash('error', 'Please provide a valid email and password.');
                $this->redirect('/login');
                return;
            }

            $user = $this->userModel->login($email, $password);

            if ($user) {
                // Prevent session fixation
                session_regenerate_id(true);

                Session::setUser($user);

                if (Session::isAdmin()) {
                    $this->redirect('/admin/dashboard');
                } else {
                    $this->redirect('/dashboard');
                }
            } else {
                Session::setFlash('error', 'Invalid email or password.');
                $this->redirect('/login');
            }
        } catch (Exception $e) {
            Session::setFlash('error', 'System error: ' . $e->getMessage());
            $this->redirect('/login');
        }
    }

    public function logout(): void {
        Session::destroy();
        $this->redirect('/login');
    }

    private function redirect(string $url): void {
        header("Location: $url");
        exit;
    }
}
