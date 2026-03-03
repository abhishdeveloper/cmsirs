<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function register(string $firstName, string $lastName, string $email, string $password, ?string $phone = null): bool {
        // Hash password securely
        $hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "INSERT INTO users (first_name, last_name, email, password_hash, phone)
                VALUES (:first_name, :last_name, :email, :password_hash, :phone)";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            ':first_name' => $firstName,
            ':last_name' => $lastName,
            ':email' => $email,
            ':password_hash' => $hash,
            ':phone' => $phone
        ]);
    }

    public function login(string $email, string $password): ?array {
        $sql = "SELECT * FROM users WHERE email = :email LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);

        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            return $user;
        }

        return null; // Invalid credentials
    }

    public function findById(int $id): ?array {
        $sql = "SELECT id, first_name, last_name, email, phone, role, created_at
                FROM users WHERE id = :id LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);

        $user = $stmt->fetch();
        return $user ?: null;
    }

    public function emailExists(string $email): bool {
        $sql = "SELECT COUNT(*) FROM users WHERE email = :email";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':email' => $email]);
        return (bool) $stmt->fetchColumn();
    }
}
