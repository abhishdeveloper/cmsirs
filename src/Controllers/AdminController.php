<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Order;
use App\Models\Product;

class AdminController {

    private Order $orderModel;
    private Product $productModel;

    public function __construct() {
        if (!Session::isAdmin()) {
            Session::setFlash('error', 'Unauthorized access.');
            $this->redirect('/');
        }

        $this->orderModel = new Order();
        $this->productModel = new Product();
    }

    public function index(): void {
        $db = \App\Core\Database::getInstance()->getConnection();

        // Basic Stats
        $stats = [
            'total_sales' => $db->query("SELECT SUM(total_amount) FROM orders WHERE payment_status = 'paid'")->fetchColumn(),
            'total_orders' => $db->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
            'total_users' => $db->query("SELECT COUNT(*) FROM users")->fetchColumn(),
            'low_stock' => $db->query("SELECT COUNT(*) FROM products WHERE stock_quantity < 10 AND is_active = 1")->fetchColumn(),
        ];

        // Recent Orders
        $recentOrders = $db->query("
            SELECT o.*, u.first_name, u.last_name
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC LIMIT 5
        ")->fetchAll();

        require_once __DIR__ . '/../Views/admin/dashboard.php';
    }

    public function orders(): void {
        $db = \App\Core\Database::getInstance()->getConnection();

        // Paginated orders view
        $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
        $perPage = 10;
        $offset = ($page - 1) * $perPage;

        $total = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();

        $orders = $db->query("
            SELECT o.*, u.first_name, u.last_name
            FROM orders o
            LEFT JOIN users u ON o.user_id = u.id
            ORDER BY o.created_at DESC LIMIT $perPage OFFSET $offset
        ")->fetchAll();

        require_once __DIR__ . '/../Views/admin/orders.php';
    }

    public function updateOrderStatus(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/orders');
        }

        $orderId = (int)($_POST['order_id'] ?? 0);
        $status = $_POST['status'] ?? '';
        $description = $_POST['description'] ?? "Status updated to $status.";

        if ($orderId && $status) {
            $db = \App\Core\Database::getInstance()->getConnection();

            // Start Transaction
            $db->beginTransaction();

            // 1. Update Order Status
            $stmt = $db->prepare("UPDATE orders SET status = :status WHERE id = :id");
            $stmt->execute([':status' => $status, ':id' => $orderId]);

            // 2. Add Tracking Entry
            $stmtTrack = $db->prepare("
                INSERT INTO order_tracking (order_id, status, description)
                VALUES (:order_id, :status, :description)
            ");
            $stmtTrack->execute([
                ':order_id' => $orderId,
                ':status' => $status,
                ':description' => $description
            ]);

            $db->commit();
            Session::setFlash('success', 'Order status updated successfully.');
        }

        $this->redirect('/admin/orders');
    }

    public function products(): void {
        $db = \App\Core\Database::getInstance()->getConnection();

        $products = $db->query("
            SELECT p.*, c.name as category_name
            FROM products p
            LEFT JOIN categories c ON p.category_id = c.id
            ORDER BY p.created_at DESC LIMIT 50
        ")->fetchAll();

        require_once __DIR__ . '/../Views/admin/products.php';
    }

    private function redirect(string $url): void {
        header("Location: $url");
        exit;
    }
}
