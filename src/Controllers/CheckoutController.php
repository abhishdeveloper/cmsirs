<?php

namespace App\Controllers;

use App\Core\Session;
use App\Core\Cart;
use App\Models\Order;
use Razorpay\Api\Api;
use Exception;

class CheckoutController {

    private Cart $cart;
    private Order $orderModel;
    private Api $razorpay;

    public function __construct() {
        $this->cart = new Cart();
        $this->orderModel = new Order();

        // Initialize Razorpay API with Dummy credentials if not set
        $keyId = $_ENV['RAZORPAY_KEY_ID'] ?? 'rzp_test_dummy_key';
        $keySecret = $_ENV['RAZORPAY_KEY_SECRET'] ?? 'dummy_secret';
        $this->razorpay = new Api($keyId, $keySecret);
    }

    public function index(): void {
        if (!Session::isLoggedIn()) {
            Session::setFlash('error', 'Please log in to proceed to checkout.');
            $this->redirect('/login?redirect=/checkout');
        }

        $items = $this->cart->getItems();
        if (empty($items)) {
            Session::setFlash('error', 'Your cart is empty.');
            $this->redirect('/cart');
        }

        $totals = $this->cart->getTotals();
        $coupon = $this->cart->getAppliedCoupon();
        $user = Session::get('user');

        require_once __DIR__ . '/../Views/checkout/index.php';
    }

    public function process(): void {
        if (!Session::isLoggedIn()) {
            $this->redirect('/login');
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/checkout');
        }

        $items = $this->cart->getItems();
        if (empty($items)) {
            $this->redirect('/cart');
        }

        $totals = $this->cart->getTotals();
        $coupon = $this->cart->getAppliedCoupon();

        // Basic validation of address data
        $shippingStreet = trim($_POST['address'] ?? '');
        $shippingCity = trim($_POST['city'] ?? '');
        $shippingState = trim($_POST['state'] ?? '');
        $shippingZip = trim($_POST['zip'] ?? '');

        if (!$shippingStreet || !$shippingCity || !$shippingState || !$shippingZip) {
            Session::setFlash('error', 'Please fill in all shipping details.');
            $this->redirect('/checkout');
        }

        try {
            // 1. Create Razorpay Order
            $amountInPaise = intval(round($totals['total'] * 100)); // Razorpay expects amount in paise
            $receiptId = 'rcpt_' . time();

            $orderData = [
                'receipt'         => $receiptId,
                'amount'          => $amountInPaise,
                'currency'        => 'INR',
                'payment_capture' => 1 // Auto capture
            ];

            $razorpayOrder = $this->razorpay->order->create($orderData);

            // 2. Prepare order data for database
            $data = [
                'user_id'           => Session::get('user_id'),
                'subtotal'          => $totals['subtotal'],
                'discount'          => $totals['discount'],
                'coupon_id'         => $coupon ? $coupon['id'] : null,
                'shipping'          => $totals['shipping'],
                'tax'               => $totals['tax'],
                'total'             => $totals['total'],
                'shipping_street'   => $shippingStreet,
                'shipping_city'     => $shippingCity,
                'shipping_state'    => $shippingState,
                'shipping_zip'      => $shippingZip,
                'razorpay_order_id' => $razorpayOrder['id']
            ];

            // 3. Save Order and Items to Database (Status: Pending)
            $orderId = $this->orderModel->createOrder($data, $items);

            // 4. Pass data to Razorpay Checkout View (Zero-redirect flow)
            $razorpayKey = $_ENV['RAZORPAY_KEY_ID'] ?? 'rzp_test_dummy_key';
            $userEmail = Session::get('user')['email'] ?? 'test@example.com';
            $userPhone = Session::get('user')['phone'] ?? '9999999999';
            $userName = Session::get('user')['first_name'] . ' ' . Session::get('user')['last_name'];

            require_once __DIR__ . '/../Views/checkout/payment.php';

        } catch (Exception $e) {
            Session::setFlash('error', 'Failed to initialize payment: ' . $e->getMessage());
            $this->redirect('/checkout');
        }
    }

    public function verifyPayment(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/');
        }

        $razorpayPaymentId = $_POST['razorpay_payment_id'] ?? '';
        $razorpayOrderId = $_POST['razorpay_order_id'] ?? '';
        $razorpaySignature = $_POST['razorpay_signature'] ?? '';

        $success = true;
        $error = "Payment Failed";

        if (empty($razorpayPaymentId)) {
            $success = false;
        } else {
            // Verify Signature
            $api = new Api($_ENV['RAZORPAY_KEY_ID'] ?? 'rzp_test_dummy_key', $_ENV['RAZORPAY_KEY_SECRET'] ?? 'dummy_secret');

            try {
                $attributes = [
                    'razorpay_order_id' => $razorpayOrderId,
                    'razorpay_payment_id' => $razorpayPaymentId,
                    'razorpay_signature' => $razorpaySignature
                ];

                $api->utility->verifyPaymentSignature($attributes);
                $success = true;
            } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
                $success = false;
                $error = 'Razorpay Error : ' . $e->getMessage();
            }
        }

        if ($success) {
            // Update Order Status to Paid
            $this->orderModel->updatePaymentStatus($razorpayOrderId, $razorpayPaymentId, $razorpaySignature, 'paid');

            // Clear Cart
            $this->cart->clear();

            Session::setFlash('success', 'Payment successful! Your order has been placed.');
            $this->redirect('/dashboard'); // Or order confirmation page
        } else {
            // Update Order Status to Failed
            $this->orderModel->updatePaymentStatus($razorpayOrderId, $razorpayPaymentId, $razorpaySignature, 'failed');
            Session::setFlash('error', 'Payment Verification Failed: ' . $error);
            $this->redirect('/checkout');
        }
    }

    private function redirect(string $url): void {
        header("Location: $url");
        exit;
    }
}
