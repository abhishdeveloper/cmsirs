<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Details - MediCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex justify-between h-16 items-center">
            <a href="/" class="text-2xl font-bold text-blue-700 tracking-tight">MediCare</a>
            <a href="/dashboard" class="text-sm font-semibold text-gray-600 hover:text-blue-600">Back to Dashboard</a>
        </div>
    </header>

    <main class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-8 space-y-8">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Order #<?= htmlspecialchars($order['order_number']) ?></h1>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 mb-8 flex flex-col md:flex-row justify-between gap-6">
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Order Date</h3>
                <p class="text-gray-600 text-sm"><?= date('F j, Y, g:i a', strtotime($order['created_at'])) ?></p>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Payment Status</h3>
                <span class="px-3 py-1 text-sm font-bold rounded-full <?= $order['payment_status'] === 'paid' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                    <?= ucfirst(htmlspecialchars($order['payment_status'])) ?>
                </span>
            </div>
            <div>
                <h3 class="text-lg font-bold text-gray-900 mb-2">Order Total</h3>
                <p class="text-2xl font-extrabold text-blue-700">₹<?= number_format($order['total_amount'], 2) ?></p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
            <!-- Items & Summary -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                <h2 class="text-xl font-bold text-gray-900 mb-4 border-b pb-2">Items Purchased</h2>
                <ul class="space-y-4 mb-6 border-b pb-4">
                    <?php foreach ($order['items'] as $item): ?>
                        <li class="flex justify-between items-center text-sm">
                            <div>
                                <p class="font-bold text-gray-900"><?= htmlspecialchars($item['product_name']) ?></p>
                                <p class="text-gray-500 text-xs">SKU: <?= htmlspecialchars($item['sku']) ?> &times; <?= $item['quantity'] ?></p>
                            </div>
                            <span class="font-bold text-gray-900">₹<?= number_format($item['total'], 2) ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="space-y-2 text-sm text-gray-600 flex flex-col items-end">
                    <p>Subtotal: <span class="font-semibold text-gray-900 ml-4">₹<?= number_format($order['subtotal'], 2) ?></span></p>
                    <p>Discount: <span class="font-semibold text-gray-900 ml-4">-₹<?= number_format($order['discount_amount'], 2) ?></span></p>
                    <p>Shipping: <span class="font-semibold text-gray-900 ml-4">₹<?= number_format($order['shipping_amount'], 2) ?></span></p>
                    <p>Tax: <span class="font-semibold text-gray-900 ml-4">₹<?= number_format($order['tax_amount'], 2) ?></span></p>
                    <p class="text-lg font-bold text-gray-900 pt-2 border-t w-full text-right mt-2">Total: ₹<?= number_format($order['total_amount'], 2) ?></p>
                </div>
            </div>

            <!-- Tracking Timeline -->
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 h-full">
                <h2 class="text-xl font-bold text-gray-900 mb-6 border-b pb-2">Delivery Tracking</h2>
                <div class="space-y-6 relative before:absolute before:inset-0 before:ml-5 before:-translate-x-px md:before:mx-auto md:before:translate-x-0 before:h-full before:w-0.5 before:bg-gradient-to-b before:from-transparent before:via-gray-200 before:to-transparent">
                    <?php foreach ($order['tracking'] as $track): ?>
                        <div class="relative flex items-center justify-between md:justify-normal md:odd:flex-row-reverse group is-active">
                            <!-- Icon -->
                            <div class="flex items-center justify-center w-10 h-10 rounded-full border border-white bg-blue-100 text-blue-600 shadow shrink-0 md:order-1 md:group-odd:-translate-x-1/2 md:group-even:translate-x-1/2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <!-- Card -->
                            <div class="w-[calc(100%-4rem)] md:w-[calc(50%-2.5rem)] bg-white p-4 rounded border border-gray-100 shadow-sm">
                                <div class="flex items-center justify-between space-x-2 mb-1">
                                    <div class="font-bold text-gray-900 uppercase text-xs"><?= htmlspecialchars($track['status']) ?></div>
                                    <time class="text-xs font-medium text-gray-500"><?= date('M j, g:i a', strtotime($track['created_at'])) ?></time>
                                </div>
                                <div class="text-sm text-gray-600"><?= htmlspecialchars($track['description']) ?></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
