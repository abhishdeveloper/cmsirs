<?php

namespace App\Models;

use App\Core\Database;
use PDO;
use Exception;

class Order {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
    }

    public function createOrder(array $data, array $items): int {
        try {
            $this->db->beginTransaction();

            // 1. Generate Order Number
            $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            // 2. Insert Shipping Address (simplified for demo, assumes data array has address fields)
            $stmt = $this->db->prepare("
                INSERT INTO addresses (user_id, type, street_address, city, state, postal_code, country)
                VALUES (:user_id, 'shipping', :street, :city, :state, :zip, 'India')
            ");
            $stmt->execute([
                ':user_id' => $data['user_id'],
                ':street'  => $data['shipping_street'],
                ':city'    => $data['shipping_city'],
                ':state'   => $data['shipping_state'],
                ':zip'     => $data['shipping_zip']
            ]);
            $shippingId = $this->db->lastInsertId();

            // Use same address for billing in this simplified flow
            $billingId = $shippingId;

            // 3. Insert Order
            $stmt = $this->db->prepare("
                INSERT INTO orders (
                    user_id, order_number, subtotal, discount_amount, coupon_id,
                    tax_amount, shipping_amount, total_amount, status, payment_status,
                    shipping_address_id, billing_address_id, razorpay_order_id
                ) VALUES (
                    :user_id, :order_number, :subtotal, :discount_amount, :coupon_id,
                    :tax_amount, :shipping_amount, :total_amount, 'pending', 'pending',
                    :shipping_id, :billing_id, :rzp_order_id
                )
            ");

            $stmt->execute([
                ':user_id'         => $data['user_id'],
                ':order_number'    => $orderNumber,
                ':subtotal'        => $data['subtotal'],
                ':discount_amount' => $data['discount'],
                ':coupon_id'       => $data['coupon_id'],
                ':tax_amount'      => $data['tax'],
                ':shipping_amount' => $data['shipping'],
                ':total_amount'    => $data['total'],
                ':shipping_id'     => $shippingId,
                ':billing_id'      => $billingId,
                ':rzp_order_id'    => $data['razorpay_order_id']
            ]);

            $orderId = $this->db->lastInsertId();

            // 4. Insert Order Items & Update Stock
            $stmtItem = $this->db->prepare("
                INSERT INTO order_items (order_id, product_id, product_name, sku, price, quantity, total)
                VALUES (:order_id, :product_id, :name, :sku, :price, :quantity, :total)
            ");

            $stmtStock = $this->db->prepare("
                UPDATE products SET stock_quantity = stock_quantity - :qty
                WHERE id = :id AND stock_quantity >= :qty
            ");

            foreach ($items as $item) {
                // Fetch current product data to get accurate SKU and name for historical record
                $stmtProd = $this->db->prepare("SELECT name, sku FROM products WHERE id = :id");
                $stmtProd->execute([':id' => $item['id']]);
                $prod = $stmtProd->fetch();

                if (!$prod) {
                    throw new Exception("Product {$item['id']} not found.");
                }

                $stmtItem->execute([
                    ':order_id'   => $orderId,
                    ':product_id' => $item['id'],
                    ':name'       => $prod['name'],
                    ':sku'        => $prod['sku'],
                    ':price'      => $item['price'],
                    ':quantity'   => $item['quantity'],
                    ':total'      => $item['total']
                ]);

                // Update stock
                $stmtStock->execute([
                    ':qty' => $item['quantity'],
                    ':id'  => $item['id']
                ]);

                if ($stmtStock->rowCount() === 0) {
                     throw new Exception("Insufficient stock for product: {$prod['name']}");
                }
            }

            // 5. Update Coupon Usage
            if ($data['coupon_id']) {
                $stmtCoupon = $this->db->prepare("UPDATE coupons SET times_used = times_used + 1 WHERE id = :id");
                $stmtCoupon->execute([':id' => $data['coupon_id']]);
            }

            // 6. Add Initial Tracking Status
            $stmtTrack = $this->db->prepare("
                INSERT INTO order_tracking (order_id, status, description)
                VALUES (:order_id, 'pending', 'Order created and pending payment.')
            ");
            $stmtTrack->execute([':order_id' => $orderId]);

            $this->db->commit();
            return $orderId;

        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function updatePaymentStatus(string $rzpOrderId, string $rzpPaymentId, string $signature, string $status = 'paid'): bool {
        $stmt = $this->db->prepare("
            UPDATE orders
            SET payment_status = :status, status = 'processing', razorpay_payment_id = :payment_id, razorpay_signature = :signature
            WHERE razorpay_order_id = :order_id
        ");

        $result = $stmt->execute([
            ':status'     => $status,
            ':payment_id' => $rzpPaymentId,
            ':signature'  => $signature,
            ':order_id'   => $rzpOrderId
        ]);

        if ($result && $stmt->rowCount() > 0) {
             // Add tracking update
             $stmtId = $this->db->prepare("SELECT id FROM orders WHERE razorpay_order_id = :order_id");
             $stmtId->execute([':order_id' => $rzpOrderId]);
             $orderId = $stmtId->fetchColumn();

             if ($orderId) {
                 $stmtTrack = $this->db->prepare("
                    INSERT INTO order_tracking (order_id, status, description)
                    VALUES (:order_id, 'processing', 'Payment received successfully.')
                 ");
                 $stmtTrack->execute([':order_id' => $orderId]);
             }
             return true;
        }
        return false;
    }

    public function getOrderDetails(int $orderId, int $userId): ?array {
        $stmt = $this->db->prepare("
            SELECT o.*, c.code as coupon_code
            FROM orders o
            LEFT JOIN coupons c ON o.coupon_id = c.id
            WHERE o.id = :id AND o.user_id = :user_id
        ");
        $stmt->execute([':id' => $orderId, ':user_id' => $userId]);
        $order = $stmt->fetch();

        if (!$order) {
            return null;
        }

        // Get items
        $stmtItems = $this->db->prepare("SELECT * FROM order_items WHERE order_id = :order_id");
        $stmtItems->execute([':order_id' => $orderId]);
        $order['items'] = $stmtItems->fetchAll();

        // Get shipping address
        $stmtAddr = $this->db->prepare("SELECT * FROM addresses WHERE id = :id");
        $stmtAddr->execute([':id' => $order['shipping_address_id']]);
        $order['shipping_address'] = $stmtAddr->fetch();

        // Get tracking
        $stmtTrack = $this->db->prepare("SELECT * FROM order_tracking WHERE order_id = :order_id ORDER BY created_at DESC");
        $stmtTrack->execute([':order_id' => $orderId]);
        $order['tracking'] = $stmtTrack->fetchAll();

        return $order;
    }
}
