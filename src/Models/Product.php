<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Product {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    // Get all active products with pagination and optional search/category filters
    public function getActiveProducts(int $page = 1, int $perPage = 12, ?int $categoryId = null, ?string $search = null): array {
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug,
                (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as primary_image
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.is_active = 1 AND p.deleted_at IS NULL";

        $params = [];

        if ($categoryId) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        if ($search) {
            $sql .= " AND (p.name LIKE :search OR p.composition LIKE :search OR p.sku LIKE :search)";
            $params[':search'] = "%$search%";
        }

        $sql .= " ORDER BY p.created_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->db->prepare($sql);

        // Bind parameters safely
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        $stmt->execute();

        return $stmt->fetchAll();
    }

    // Get product details by slug
    public function getBySlug(string $slug): ?array {
        $sql = "SELECT p.*, c.name as category_name, c.slug as category_slug
                FROM products p
                LEFT JOIN categories c ON p.category_id = c.id
                WHERE p.slug = :slug AND p.is_active = 1 AND p.deleted_at IS NULL LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([':slug' => $slug]);

        $product = $stmt->fetch();

        if ($product) {
            $product['images'] = $this->getImages($product['id']);
        }

        return $product ?: null;
    }

    // Get product images
    public function getImages(int $productId): array {
        $sql = "SELECT image_url, is_primary FROM product_images WHERE product_id = :product_id ORDER BY display_order ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        return $stmt->fetchAll();
    }

    // Fetch all active categories
    public function getActiveCategories(): array {
        $sql = "SELECT id, name, slug FROM categories ORDER BY name ASC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    // Intelligent AJAX Search (Lightweight Query)
    public function ajaxSearch(string $query, int $limit = 5): array {
        $sql = "SELECT p.id, p.name, p.slug, p.price,
                (SELECT image_url FROM product_images pi WHERE pi.product_id = p.id AND pi.is_primary = 1 LIMIT 1) as image_url
                FROM products p
                WHERE p.is_active = 1 AND p.deleted_at IS NULL AND (p.name LIKE :query OR p.composition LIKE :query)
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':query', "%$query%", PDO::PARAM_STR);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}
