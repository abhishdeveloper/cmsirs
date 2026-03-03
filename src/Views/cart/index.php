<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - MediCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-700 tracking-tight">MediCare</a>
                </div>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mb-16">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Shopping Cart</h1>

        <?php $flash = \App\Core\Session::getFlash(); foreach ($flash as $msg): ?>
            <div class="p-4 mb-4 rounded-lg <?= $msg['type'] === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' ?>">
                <?= htmlspecialchars($msg['message']) ?>
            </div>
        <?php endforeach; ?>

        <?php if (empty($items)): ?>
            <div class="bg-white p-8 text-center rounded-2xl shadow-sm border border-gray-100">
                <p class="text-gray-500 text-lg mb-4">Your cart is empty.</p>
                <a href="/" class="inline-block bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition font-semibold">Continue Shopping</a>
            </div>
        <?php else: ?>
            <div class="flex flex-col lg:flex-row gap-8">
                <!-- Cart Items -->
                <div class="w-full lg:w-2/3 space-y-4">
                    <?php foreach ($items as $item): ?>
                        <div class="bg-white p-4 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                            <div class="flex items-center space-x-4">
                                <img src="<?= htmlspecialchars($item['image_url'] ?? '/images/placeholder.png') ?>" alt="<?= htmlspecialchars($item['name']) ?>" class="w-20 h-20 object-contain bg-gray-50 rounded-lg p-2 border border-gray-100">
                                <div>
                                    <a href="/product/<?= htmlspecialchars($item['slug']) ?>" class="font-bold text-gray-900 hover:text-blue-600 line-clamp-1"><?= htmlspecialchars($item['name']) ?></a>
                                    <p class="text-sm text-gray-500 font-medium mt-1">₹<?= number_format($item['price'], 2) ?></p>
                                </div>
                            </div>
                            <div class="flex items-center space-x-6">
                                <form action="/cart/update" method="POST" class="flex items-center border border-gray-200 rounded-lg overflow-hidden bg-gray-50">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button type="button" class="px-3 py-1 text-gray-500 hover:text-blue-600 hover:bg-gray-100 transition focus:outline-none" onclick="this.nextElementSibling.stepDown(); this.form.submit()">-</button>
                                    <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1" class="w-12 text-center font-semibold bg-transparent focus:outline-none border-x border-gray-200 py-1" onchange="this.form.submit()">
                                    <button type="button" class="px-3 py-1 text-gray-500 hover:text-blue-600 hover:bg-gray-100 transition focus:outline-none" onclick="this.nextElementSibling.stepUp(); this.form.submit()">+</button>
                                </form>
                                <div class="text-right">
                                    <p class="font-bold text-gray-900">₹<?= number_format($item['total'], 2) ?></p>
                                </div>
                                <form action="/cart/remove" method="POST">
                                    <input type="hidden" name="product_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="text-red-500 hover:text-red-700 transition p-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Order Summary -->
                <div class="w-full lg:w-1/3">
                    <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-24">
                        <h2 class="text-xl font-bold text-gray-900 mb-6">Order Summary</h2>

                        <div class="space-y-3 text-sm text-gray-600 pb-6 border-b border-gray-100">
                            <div class="flex justify-between">
                                <span>Subtotal</span>
                                <span class="font-medium text-gray-900">₹<?= number_format($totals['subtotal'], 2) ?></span>
                            </div>

                            <?php if ($totals['discount'] > 0): ?>
                                <div class="flex justify-between text-green-600">
                                    <span>Discount (<?= htmlspecialchars($coupon['code'] ?? '') ?>)</span>
                                    <span class="font-medium">-₹<?= number_format($totals['discount'], 2) ?></span>
                                </div>
                            <?php endif; ?>

                            <div class="flex justify-between">
                                <span>Shipping</span>
                                <span class="font-medium text-gray-900"><?= $totals['shipping'] > 0 ? '₹' . number_format($totals['shipping'], 2) : 'Free' ?></span>
                            </div>

                            <div class="flex justify-between">
                                <span>Estimated Tax (18%)</span>
                                <span class="font-medium text-gray-900">₹<?= number_format($totals['tax'], 2) ?></span>
                            </div>
                        </div>

                        <div class="flex justify-between items-end pt-4 pb-6 border-b border-gray-100">
                            <span class="text-lg font-bold text-gray-900">Total</span>
                            <span class="text-3xl font-extrabold text-blue-700">₹<?= number_format($totals['total'], 2) ?></span>
                        </div>

                        <!-- Coupon Form -->
                        <div class="pt-6 mb-6">
                            <?php if (!$coupon): ?>
                                <form action="/cart/coupon" method="POST" class="flex gap-2">
                                    <input type="text" name="coupon_code" placeholder="Enter coupon code" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:border-blue-500 focus:ring-1 focus:ring-blue-500 text-sm uppercase" required>
                                    <button type="submit" class="bg-gray-800 text-white px-4 py-2 rounded-lg text-sm font-semibold hover:bg-gray-900 transition whitespace-nowrap">Apply</button>
                                </form>
                            <?php else: ?>
                                <div class="bg-green-50 border border-green-200 text-green-800 p-3 rounded-lg flex justify-between items-center text-sm font-medium">
                                    <span class="uppercase flex items-center gap-2">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        <?= htmlspecialchars($coupon['code']) ?> Applied
                                    </span>
                                    <form action="/cart/coupon/remove" method="POST">
                                        <button type="submit" class="text-red-500 hover:text-red-700 text-xs font-bold uppercase tracking-wider">Remove</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>

                        <a href="/checkout" class="block w-full bg-blue-600 text-white text-center font-bold py-4 rounded-xl hover:bg-blue-700 transition shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                            Proceed to Checkout
                        </a>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>
