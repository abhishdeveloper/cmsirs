<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Products - Admin</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 flex h-screen">
    <!-- Main Content -->
    <main class="flex-1 p-8 overflow-y-auto w-full">
        <div class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">Manage Products</h1>
            <a href="/admin/dashboard" class="text-blue-600 font-semibold hover:underline">Back to Dashboard</a>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-500 text-xs uppercase tracking-wider border-b">
                        <th class="px-6 py-4 font-semibold">SKU / Name</th>
                        <th class="px-6 py-4 font-semibold">Category</th>
                        <th class="px-6 py-4 font-semibold text-right">Price</th>
                        <th class="px-6 py-4 font-semibold text-right">Stock</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100 text-sm">
                    <?php foreach ($products as $product): ?>
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4">
                                <p class="font-bold text-gray-900"><?= htmlspecialchars($product['name']) ?></p>
                                <p class="text-xs text-gray-500">SKU: <?= htmlspecialchars($product['sku']) ?></p>
                            </td>
                            <td class="px-6 py-4 text-gray-600"><?= htmlspecialchars($product['category_name'] ?? 'None') ?></td>
                            <td class="px-6 py-4 font-bold text-gray-900 text-right">₹<?= number_format($product['price'], 2) ?></td>
                            <td class="px-6 py-4 font-bold text-right <?= $product['stock_quantity'] < 10 ? 'text-red-600' : 'text-green-600' ?>">
                                <?= $product['stock_quantity'] ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
</body>
</html>
