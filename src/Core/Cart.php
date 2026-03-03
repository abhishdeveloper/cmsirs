<?php

namespace App\Core;

use App\Models\Product;

class Cart {
    private Product $productModel;

    public function __construct() {
        $this->productModel = new Product();
        if (Session::get('cart') === null) {
            Session::set('cart', []);
        }
    }

    public function add(int $productId, int $quantity): void {
        $cart = Session::get('cart');

        if (isset($cart[$productId])) {
            $cart[$productId] += $quantity;
        } else {
            $cart[$productId] = $quantity;
        }

        Session::set('cart', $cart);
    }

    public function update(int $productId, int $quantity): void {
        $cart = Session::get('cart');

        if ($quantity <= 0) {
            unset($cart[$productId]);
        } else {
            $cart[$productId] = $quantity;
        }

        Session::set('cart', $cart);
    }

    public function remove(int $productId): void {
        $cart = Session::get('cart');
        unset($cart[$productId]);
        Session::set('cart', $cart);
    }

    public function clear(): void {
        Session::set('cart', []);
        Session::remove('coupon');
    }

    public function getItems(): array {
        $cart = Session::get('cart', []);
        $items = [];

        foreach ($cart as $productId => $quantity) {
            // Re-fetch product to ensure price and stock are up to date
            // In a real app, might want a specialized method just for cart items
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, name, slug, price, stock_quantity,
                                  (SELECT image_url FROM product_images WHERE product_id = p.id AND is_primary = 1 LIMIT 1) as image_url
                                  FROM products p WHERE id = :id");
            $stmt->execute([':id' => $productId]);
            $product = $stmt->fetch();

            if ($product) {
                // Ensure we don't exceed stock
                $actualQty = min($quantity, $product['stock_quantity']);

                // Update session if requested quantity exceeded stock
                if ($actualQty !== $quantity) {
                     $this->update($productId, $actualQty);
                }

                $items[] = [
                    'id' => $product['id'],
                    'name' => $product['name'],
                    'slug' => $product['slug'],
                    'price' => $product['price'],
                    'image_url' => $product['image_url'],
                    'quantity' => $actualQty,
                    'total' => $product['price'] * $actualQty
                ];
            } else {
                // Product no longer exists or is inactive, remove from cart
                $this->remove($productId);
            }
        }

        return $items;
    }

    public function getSubtotal(): float {
        $items = $this->getItems();
        $subtotal = 0;
        foreach ($items as $item) {
            $subtotal += $item['total'];
        }
        return $subtotal;
    }

    // Applies a coupon code and calculates the discount
    public function applyCoupon(string $code): ?array {
        $db = Database::getInstance()->getConnection();

        $sql = "SELECT * FROM coupons
                WHERE code = :code
                AND is_active = 1
                AND (start_date IS NULL OR start_date <= NOW())
                AND (end_date IS NULL OR end_date >= NOW())";

        $stmt = $db->prepare($sql);
        $stmt->execute([':code' => $code]);
        $coupon = $stmt->fetch();

        if (!$coupon) {
            return ['error' => 'Invalid or expired coupon code.'];
        }

        if ($coupon['usage_limit'] !== null && $coupon['times_used'] >= $coupon['usage_limit']) {
            return ['error' => 'This coupon has reached its usage limit.'];
        }

        $subtotal = $this->getSubtotal();

        if ($coupon['min_cart_amount'] !== null && $subtotal < $coupon['min_cart_amount']) {
            return ['error' => "Minimum cart amount of ₹{$coupon['min_cart_amount']} required."];
        }

        // Optional: Check per-user limit here if user is logged in
        if (Session::isLoggedIn()) {
             $userId = Session::get('user_id');
             $stmt = $db->prepare("SELECT COUNT(*) FROM orders WHERE user_id = :user_id AND coupon_id = :coupon_id");
             $stmt->execute([':user_id' => $userId, ':coupon_id' => $coupon['id']]);
             $userUses = $stmt->fetchColumn();

             if ($coupon['usage_per_user'] !== null && $userUses >= $coupon['usage_per_user']) {
                 return ['error' => 'You have already used this coupon the maximum number of times.'];
             }
        }

        // Calculate discount
        $discountAmount = 0;
        if ($coupon['type'] === 'fixed') {
            $discountAmount = min($coupon['value'], $subtotal); // Can't discount more than subtotal
        } elseif ($coupon['type'] === 'percentage') {
            $discountAmount = $subtotal * ($coupon['value'] / 100);
            if ($coupon['max_discount_amount'] !== null && $discountAmount > $coupon['max_discount_amount']) {
                $discountAmount = $coupon['max_discount_amount'];
            }
        }

        // Save valid coupon to session
        Session::set('coupon', [
            'id' => $coupon['id'],
            'code' => $coupon['code'],
            'type' => $coupon['type'],
            'value' => $coupon['value'],
            'discount_amount' => $discountAmount
        ]);

        return ['success' => 'Coupon applied successfully!', 'discount' => $discountAmount];
    }

    public function removeCoupon(): void {
        Session::remove('coupon');
    }

    public function getAppliedCoupon(): ?array {
        // Re-calculate to ensure it's still valid against current cart subtotal
        $coupon = Session::get('coupon');
        if ($coupon) {
             // Re-apply logic to recalculate discount amount based on new subtotal
             $res = $this->applyCoupon($coupon['code']);
             if (isset($res['error'])) {
                 $this->removeCoupon();
                 return null;
             }
             return Session::get('coupon');
        }
        return null;
    }

    public function getTotals(): array {
        $subtotal = $this->getSubtotal();
        $coupon = $this->getAppliedCoupon();
        $discount = $coupon ? $coupon['discount_amount'] : 0;

        $shipping = 0;
        if ($subtotal > 0 && $subtotal < 500) { // Free shipping over 500
            $shipping = 50;
        }

        $tax = ($subtotal - $discount) * 0.18; // Assuming 18% GST

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'tax' => $tax,
            'total' => max(0, $subtotal - $discount + $shipping + $tax)
        ];
    }
}
