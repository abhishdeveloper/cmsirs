<?php

namespace App\Controllers;

use App\Core\Session;
use App\Models\Order;
use App\Models\User;

class DashboardController {

    private Order $orderModel;
    private User $userModel;

    public function __construct() {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }

        $this->orderModel = new Order();
        $this->userModel = new User();
    }

    public function index(): void {
        $user = $this->userModel->findById(Session::get('user_id'));

        // Fetch User's Orders
        $db = \App\Core\Database::getInstance()->getConnection();
        $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = :user_id ORDER BY created_at DESC");
        $stmt->execute([':user_id' => $user['id']]);
        $orders = $stmt->fetchAll();

        require_once __DIR__ . '/../Views/dashboard/user.php';
    }

    public function showOrder(int $orderId): void {
        $userId = Session::get('user_id');
        $order = $this->orderModel->getOrderDetails($orderId, $userId);

        if (!$order) {
            Session::setFlash('error', 'Order not found.');
            $this->redirect('/dashboard');
        }

        require_once __DIR__ . '/../Views/dashboard/order_detail.php';
    }

    private function redirect(string $url): void {
        header("Location: $url");
        exit;
    }
}
