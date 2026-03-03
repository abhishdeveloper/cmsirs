<?php

namespace App\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Helpers\PdfGenerator;
use App\Helpers\Mailer;
use Razorpay\Api\Api;
use Exception;

class WebhookController {

    private Order $orderModel;
    private User $userModel;

    public function __construct() {
        $this->orderModel = new Order();
        $this->userModel = new User();
    }

    public function handleRazorpayWebhook(): void {
        // Only accept POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit;
        }

        $postBody = file_get_contents('php://input');
        $webhookSignature = $_SERVER['HTTP_X_RAZORPAY_SIGNATURE'] ?? '';

        $webhookSecret = $_ENV['RAZORPAY_WEBHOOK_SECRET'] ?? 'dummy_webhook_secret';
        $api = new Api($_ENV['RAZORPAY_KEY_ID'] ?? 'rzp_test_dummy_key', $_ENV['RAZORPAY_KEY_SECRET'] ?? 'dummy_secret');

        try {
            // Verify signature using Razorpay SDK
            $api->utility->verifyWebhookSignature($postBody, $webhookSignature, $webhookSecret);
            $payload = json_decode($postBody, true);

            // Handle event types
            switch ($payload['event']) {
                case 'payment.captured':
                case 'order.paid':
                    $paymentData = $payload['payload']['payment']['entity'];
                    $orderData = $payload['payload']['order']['entity'] ?? null;

                    if (!$orderData) {
                         // Fallback to fetch order ID from payment entity
                         $razorpayOrderId = $paymentData['order_id'];
                    } else {
                         $razorpayOrderId = $orderData['id'];
                    }

                    $razorpayPaymentId = $paymentData['id'];

                    // Assuming signature is null for webhooks since it's verified securely via hash
                    $this->orderModel->updatePaymentStatus($razorpayOrderId, $razorpayPaymentId, 'webhook_verified', 'paid');

                    // Generate Invoice & Send Email asynchronously (or synchronously here)
                    $this->generateAndSendInvoice($razorpayOrderId);

                    http_response_code(200);
                    echo json_encode(['status' => 'success']);
                    break;

                case 'payment.failed':
                    $paymentData = $payload['payload']['payment']['entity'];
                    $razorpayOrderId = $paymentData['order_id'];

                    $this->orderModel->updatePaymentStatus($razorpayOrderId, $paymentData['id'], 'webhook_failed', 'failed');

                    http_response_code(200);
                    echo json_encode(['status' => 'recorded failure']);
                    break;

                default:
                    // Unhandled event
                    http_response_code(200);
                    break;
            }
        } catch (\Razorpay\Api\Errors\SignatureVerificationError $e) {
            error_log("Webhook Signature Verification Failed: " . $e->getMessage());
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'Invalid signature']);
        } catch (Exception $e) {
            error_log("Webhook Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    private function generateAndSendInvoice(string $razorpayOrderId): void {
        try {
            // Since we need order specifics, we must fetch the internal order
            // Note: In real app, might want a function `getOrderByRazorpayId`
            $db = \App\Core\Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT id, user_id FROM orders WHERE razorpay_order_id = :rzp_id");
            $stmt->execute([':rzp_id' => $razorpayOrderId]);
            $res = $stmt->fetch();

            if (!$res) return;

            $orderId = $res['id'];
            $userId  = $res['user_id'];

            // Fetch comprehensive details
            $order = $this->orderModel->getOrderDetails($orderId, $userId);
            $user = $this->userModel->findById($userId);

            // Attach user array to order for PDF details
            $order['user'] = $user;

            // Generate PDF
            $pdfPath = PdfGenerator::generateInvoice($order);

            // Send Email
            $mailer = new Mailer();
            $mailer->sendInvoice($user, $order, $pdfPath);

            // Clean up: Delete PDF to save space (optional, based on requirement)
            // if (file_exists($pdfPath)) { unlink($pdfPath); }

        } catch (Exception $e) {
             error_log("Invoice Generation/Sending Error: " . $e->getMessage());
        }
    }
}
