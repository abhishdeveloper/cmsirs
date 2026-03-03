<?php

// Require Composer Autoloader
require_once __DIR__ . '/../vendor/autoload.php';

use App\Core\Router;
use App\Core\Session;
use App\Controllers\CatalogController;
use App\Controllers\AuthController;
use App\Controllers\CartController;
use App\Controllers\CheckoutController;
use App\Controllers\WebhookController;
use App\Controllers\DashboardController;
use App\Controllers\AdminController;

// Load Environment Variables
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
if (file_exists(__DIR__ . '/../.env')) {
    $dotenv->load();
}

// Start Session
Session::start();

// Initialize Router
$router = new Router();

// ========================
// Public Routes (Catalog)
// ========================
$router->get('/', [CatalogController::class, 'index']);
$router->get('/product/{slug}', [CatalogController::class, 'show']);
$router->get('/api/search', [CatalogController::class, 'searchAPI']);

// ========================
// Authentication Routes
// ========================
$router->get('/register', [AuthController::class, 'showRegister']);
$router->post('/register', [AuthController::class, 'registerProcess']);
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'loginProcess']);
$router->get('/logout', [AuthController::class, 'logout']);

// ========================
// Shopping Cart Routes
// ========================
$router->get('/cart', [CartController::class, 'index']);
$router->post('/cart/add', [CartController::class, 'add']);
$router->post('/cart/update', [CartController::class, 'update']);
$router->post('/cart/remove', [CartController::class, 'remove']);
$router->post('/cart/coupon', [CartController::class, 'applyCoupon']);
$router->post('/cart/coupon/remove', [CartController::class, 'removeCoupon']);

// ========================
// Checkout & Payment Routes
// ========================
$router->get('/checkout', [CheckoutController::class, 'index']);
$router->post('/checkout', [CheckoutController::class, 'process']);
$router->post('/checkout/verify', [CheckoutController::class, 'verifyPayment']);

// ========================
// Webhook Routes
// ========================
$router->post('/webhook/razorpay', [WebhookController::class, 'handleRazorpayWebhook']);

// ========================
// User Dashboard Routes
// ========================
$router->get('/dashboard', [DashboardController::class, 'index']);
$router->get('/dashboard/order/{id}', [DashboardController::class, 'showOrder']);

// ========================
// Admin Dashboard Routes
// ========================
$router->get('/admin/dashboard', [AdminController::class, 'index']);
$router->get('/admin/orders', [AdminController::class, 'orders']);
$router->post('/admin/orders/status', [AdminController::class, 'updateOrderStatus']);
$router->get('/admin/products', [AdminController::class, 'products']);

// ========================
// Dispatch Request
// ========================
$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($requestUri, $requestMethod);
