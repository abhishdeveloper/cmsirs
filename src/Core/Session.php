<?php

namespace App\Core;

class Session {
    public static function start(): void {
        if (session_status() === PHP_SESSION_NONE) {
            // Set session options for security
            session_start([
                'cookie_lifetime' => 86400, // 1 day
                'cookie_secure' => isset($_SERVER['HTTPS']),
                'cookie_httponly' => true,
                'use_strict_mode' => true
            ]);
        }
    }

    public static function set(string $key, $value): void {
        $_SESSION[$key] = $value;
    }

    public static function get(string $key, $default = null) {
        return $_SESSION[$key] ?? $default;
    }

    public static function remove(string $key): void {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
        }
    }

    public static function destroy(): void {
        session_unset();
        session_destroy();
    }

    // Auth helpers
    public static function setUser(array $user): void {
        self::set('user_id', $user['id']);
        self::set('role', $user['role']);
        self::set('first_name', $user['first_name']);
    }

    public static function isLoggedIn(): bool {
        return self::get('user_id') !== null;
    }

    public static function isAdmin(): bool {
        return self::get('role') === 'admin';
    }

    // Flash messages
    public static function setFlash(string $type, string $message): void {
        $_SESSION['flash'][] = ['type' => $type, 'message' => $message];
    }

    public static function getFlash(): array {
        $flash = $_SESSION['flash'] ?? [];
        self::remove('flash');
        return $flash;
    }
}
