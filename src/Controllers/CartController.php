<?php

namespace App\Controllers;

use App\Core\Cart;
use App\Core\Session;

class CartController {

    private Cart $cart;

    public function __construct() {
        $this->cart = new Cart();
    }

    public function index(): void {
        $items = $this->cart->getItems();
        $totals = $this->cart->getTotals();
        $coupon = $this->cart->getAppliedCoupon();

        require_once __DIR__ . '/../Views/cart/index.php';
    }

    public function add(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 1);

        if ($productId > 0 && $quantity > 0) {
            $this->cart->add($productId, $quantity);
            Session::setFlash('success', 'Item added to cart.');
        }

        $this->redirect('/cart');
    }

    public function update(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        $productId = (int)($_POST['product_id'] ?? 0);
        $quantity = (int)($_POST['quantity'] ?? 0);

        if ($productId > 0) {
            $this->cart->update($productId, $quantity);
            Session::setFlash('success', 'Cart updated.');
        }

        $this->redirect('/cart');
    }

    public function remove(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        $productId = (int)($_POST['product_id'] ?? 0);

        if ($productId > 0) {
            $this->cart->remove($productId);
            Session::setFlash('success', 'Item removed from cart.');
        }

        $this->redirect('/cart');
    }

    public function applyCoupon(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        $code = trim($_POST['coupon_code'] ?? '');

        if (!empty($code)) {
            $result = $this->cart->applyCoupon($code);
            if (isset($result['error'])) {
                Session::setFlash('error', $result['error']);
            } else {
                Session::setFlash('success', $result['success']);
            }
        }

        $this->redirect('/cart');
    }

    public function removeCoupon(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/cart');
            return;
        }

        $this->cart->removeCoupon();
        Session::setFlash('success', 'Coupon removed.');
        $this->redirect('/cart');
    }

    private function redirect(string $url): void {
        header("Location: $url");
        exit;
    }
}
