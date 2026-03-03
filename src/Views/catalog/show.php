<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($product['name']) ?> - MediCare</title>
    <!-- Tailwind CSS for Mobile-First UI -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased" x-data="{ currentImage: '<?= htmlspecialchars($product['images'][0]['image_url'] ?? '/images/placeholder.png') ?>' }">

    <!-- Simple Header for Product Page -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-700 tracking-tight">MediCare</a>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="/cart" class="relative text-gray-600 hover:text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </a>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <nav class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 text-sm text-gray-500 flex space-x-2">
        <a href="/" class="hover:text-blue-600 transition">Home</a>
        <span>/</span>
        <a href="/?category=<?= htmlspecialchars($product['category_slug'] ?? '') ?>" class="hover:text-blue-600 transition"><?= htmlspecialchars($product['category_name'] ?? 'Uncategorized') ?></a>
        <span>/</span>
        <span class="text-gray-900 font-medium line-clamp-1"><?= htmlspecialchars($product['name']) ?></span>
    </nav>

    <!-- Product Section -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 mb-16">
        <div class="bg-white rounded-3xl shadow-sm border border-gray-100 overflow-hidden flex flex-col md:flex-row p-6 md:p-10 gap-10">

            <!-- Product Images (Left) -->
            <div class="w-full md:w-1/2 flex flex-col gap-4">
                <div class="bg-gray-50 rounded-2xl flex items-center justify-center p-6 border border-gray-100 aspect-w-4 aspect-h-3">
                    <img :src="currentImage" alt="<?= htmlspecialchars($product['name']) ?>" class="object-contain max-h-96 w-full rounded-lg transition duration-500 transform hover:scale-105">
                </div>

                <!-- Thumbnails Gallery -->
                <?php if (!empty($product['images']) && count($product['images']) > 1): ?>
                <div class="flex space-x-4 overflow-x-auto py-2 scrollbar-hide">
                    <?php foreach ($product['images'] as $img): ?>
                        <button @click="currentImage = '<?= htmlspecialchars($img['image_url']) ?>'"
                                :class="{'ring-2 ring-blue-600 border-transparent': currentImage === '<?= htmlspecialchars($img['image_url']) ?>'}"
                                class="flex-shrink-0 w-20 h-20 bg-gray-50 border border-gray-200 rounded-xl p-2 hover:border-blue-400 focus:outline-none transition">
                            <img src="<?= htmlspecialchars($img['image_url']) ?>" class="object-contain h-full w-full rounded">
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>

            <!-- Product Details (Right) -->
            <div class="w-full md:w-1/2 flex flex-col">
                <div class="mb-4 flex items-start justify-between">
                    <div>
                        <h1 class="text-3xl md:text-4xl font-extrabold text-gray-900 leading-tight mb-2"><?= htmlspecialchars($product['name']) ?></h1>
                        <p class="text-lg text-gray-600 font-medium"><?= htmlspecialchars($product['composition'] ?? '') ?></p>
                    </div>
                    <?php if ($product['requires_prescription']): ?>
                        <span class="bg-red-50 text-red-700 text-xs font-bold px-3 py-1 rounded-full border border-red-200 whitespace-nowrap ml-4">Rx Required</span>
                    <?php endif; ?>
                </div>

                <div class="mb-6 flex items-end gap-3">
                    <span class="text-4xl font-extrabold text-blue-700">₹<?= number_format($product['price'], 2) ?></span>
                    <?php if ($product['compare_at_price'] && $product['compare_at_price'] > $product['price']): ?>
                        <span class="text-xl text-gray-400 line-through mb-1">MRP ₹<?= number_format($product['compare_at_price'], 2) ?></span>
                        <span class="text-sm text-green-600 font-bold mb-1.5 ml-2">
                            <?= round((($product['compare_at_price'] - $product['price']) / $product['compare_at_price']) * 100) ?>% OFF
                        </span>
                    <?php endif; ?>
                </div>

                <div class="bg-gray-50 border border-gray-100 rounded-xl p-4 mb-6 grid grid-cols-2 gap-4 text-sm text-gray-700">
                    <div>
                        <span class="font-semibold text-gray-900 block">Dosage Form</span>
                        <?= htmlspecialchars($product['dosage_form'] ?? 'N/A') ?>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900 block">Pack Size</span>
                        <?= htmlspecialchars($product['pack_size'] ?? 'N/A') ?>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900 block">Manufacturer</span>
                        <?= htmlspecialchars($product['manufacturer'] ?? 'N/A') ?>
                    </div>
                    <div>
                        <span class="font-semibold text-gray-900 block">SKU</span>
                        <?= htmlspecialchars($product['sku']) ?>
                    </div>
                </div>

                <div class="mb-8 flex-1">
                    <h3 class="text-lg font-bold text-gray-900 mb-2">Description</h3>
                    <p class="text-gray-600 leading-relaxed text-sm">
                        <?= nl2br(htmlspecialchars($product['description'] ?? 'No description available for this product.')) ?>
                    </p>
                </div>

                <!-- Add to Cart Action -->
                <div class="mt-auto border-t border-gray-100 pt-6">
                    <form action="/cart/add" method="POST" class="flex items-center space-x-4">
                        <input type="hidden" name="product_id" value="<?= $product['id'] ?>">
                        <div class="flex items-center border border-gray-300 rounded-lg bg-white">
                            <button type="button" class="px-4 py-3 text-gray-500 hover:text-blue-600 hover:bg-gray-50 transition rounded-l-lg focus:outline-none" onclick="document.getElementById('qty').stepDown()">-</button>
                            <input type="number" id="qty" name="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>" class="w-16 text-center text-lg font-semibold bg-transparent focus:outline-none border-x border-gray-200 py-3" readonly>
                            <button type="button" class="px-4 py-3 text-gray-500 hover:text-blue-600 hover:bg-gray-50 transition rounded-r-lg focus:outline-none" onclick="document.getElementById('qty').stepUp()">+</button>
                        </div>

                        <?php if ($product['stock_quantity'] > 0): ?>
                            <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white font-bold py-4 px-8 rounded-xl shadow-lg hover:shadow-xl transition transform hover:-translate-y-0.5 flex items-center justify-center space-x-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                <span>Add to Cart</span>
                            </button>
                        <?php else: ?>
                            <button disabled class="flex-1 bg-gray-400 text-white font-bold py-4 px-8 rounded-xl cursor-not-allowed">Out of Stock</button>
                        <?php endif; ?>
                    </form>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
