<?php

namespace App\Controllers;

use App\Models\Product;

class CatalogController {

    private Product $productModel;

    public function __construct() {
        $this->productModel = new Product();
    }

    // Display the index/catalog page
    public function index(): void {
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $categorySlug = $_GET['category'] ?? null;
        $searchQuery = $_GET['search'] ?? null;

        $categories = $this->productModel->getActiveCategories();
        $categoryId = null;

        // Resolve category slug to ID
        if ($categorySlug) {
            foreach ($categories as $cat) {
                if ($cat['slug'] === $categorySlug) {
                    $categoryId = $cat['id'];
                    break;
                }
            }
        }

        $products = $this->productModel->getActiveProducts($page, 12, $categoryId, $searchQuery);

        require_once __DIR__ . '/../Views/catalog/index.php';
    }

    // Display high-quality dynamic product page
    public function show(string $slug): void {
        $product = $this->productModel->getBySlug($slug);

        if (!$product) {
            header("HTTP/1.0 404 Not Found");
            require_once __DIR__ . '/../Views/errors/404.php';
            exit;
        }

        require_once __DIR__ . '/../Views/catalog/show.php';
    }

    // Intelligent AJAX Search API Endpoint
    public function searchAPI(): void {
        header('Content-Type: application/json');

        $query = $_GET['q'] ?? '';

        if (strlen($query) < 2) {
            echo json_encode([]);
            exit;
        }

        $results = $this->productModel->ajaxSearch($query);
        echo json_encode($results);
        exit;
    }
}
