<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - MediCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <a href="/" class="text-2xl font-bold text-blue-700 tracking-tight">MediCare</a>
            <a href="/cart" class="text-sm font-semibold text-gray-600 hover:text-blue-600">Back to Cart</a>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mb-16">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Checkout</h1>
        <?php $flash = \App\Core\Session::getFlash(); foreach ($flash as $msg): ?>
            <div class="p-4 mb-4 rounded-lg <?= $msg['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($msg['message']) ?>
            </div>
        <?php endforeach; ?>

        <div class="flex flex-col lg:flex-row gap-8">
            <div class="w-full lg:w-2/3">
                <form id="checkout-form" action="/checkout" method="POST" class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 space-y-6">
                    <h2 class="text-xl font-bold text-gray-900 border-b pb-2">Shipping Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="col-span-full">
                            <label class="block text-sm font-medium text-gray-700">Street Address</label>
                            <input type="text" name="address" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">City</label>
                            <input type="text" name="city" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">State</label>
                            <input type="text" name="state" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Postal Code</label>
                            <input type="text" name="zip" required class="mt-1 w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-blue-500 focus:border-blue-500">
                        </div>
                    </div>
                </form>
            </div>

            <div class="w-full lg:w-1/3">
                <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-24">
                    <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>
                    <ul class="space-y-4 mb-6 border-b pb-4">
                        <?php foreach ($items as $item): ?>
                            <li class="flex justify-between items-center text-sm">
                                <div>
                                    <p class="font-bold text-gray-900"><?= htmlspecialchars($item['name']) ?></p>
                                    <p class="text-gray-500">Qty: <?= $item['quantity'] ?></p>
                                </div>
                                <span class="font-bold text-gray-900">₹<?= number_format($item['total'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <div class="space-y-3 text-sm text-gray-600 pb-6 border-b border-gray-100">
                        <div class="flex justify-between"><span>Subtotal</span><span class="font-medium text-gray-900">₹<?= number_format($totals['subtotal'], 2) ?></span></div>
                        <?php if ($totals['discount'] > 0): ?><div class="flex justify-between text-green-600"><span>Discount</span><span class="font-medium">-₹<?= number_format($totals['discount'], 2) ?></span></div><?php endif; ?>
                        <div class="flex justify-between"><span>Shipping</span><span class="font-medium text-gray-900"><?= $totals['shipping'] > 0 ? '₹' . number_format($totals['shipping'], 2) : 'Free' ?></span></div>
                        <div class="flex justify-between"><span>Tax (18%)</span><span class="font-medium text-gray-900">₹<?= number_format($totals['tax'], 2) ?></span></div>
                    </div>
                    <div class="flex justify-between items-end pt-4 pb-6">
                        <span class="text-lg font-bold text-gray-900">Total to Pay</span>
                        <span class="text-3xl font-extrabold text-blue-700">₹<?= number_format($totals['total'], 2) ?></span>
                    </div>
                    <button onclick="document.getElementById('checkout-form').submit()" class="block w-full bg-blue-600 text-white text-center font-bold py-4 rounded-xl hover:bg-blue-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        Pay with Razorpay
                    </button>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
