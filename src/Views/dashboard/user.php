<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Dashboard - MediCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased">
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <a href="/" class="text-2xl font-bold text-blue-700 tracking-tight">MediCare</a>
                <a href="/logout" class="text-sm font-semibold text-red-600 hover:text-red-800">Logout</a>
            </div>
        </div>
    </header>

    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-8">Hello, <?= htmlspecialchars($user['first_name']) ?>!</h1>

        <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
            <h2 class="text-xl font-bold text-gray-900 mb-4">My Orders</h2>

            <?php if (empty($orders)): ?>
                <p class="text-gray-500 text-sm">You haven't placed any orders yet.</p>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-50 text-gray-600 text-sm uppercase tracking-wider">
                                <th class="p-4 border-b">Order #</th>
                                <th class="p-4 border-b">Date</th>
                                <th class="p-4 border-b">Total</th>
                                <th class="p-4 border-b">Status</th>
                                <th class="p-4 border-b">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($orders as $o): ?>
                                <tr class="border-b hover:bg-gray-50 transition">
                                    <td class="p-4 font-semibold text-gray-900"><?= htmlspecialchars($o['order_number']) ?></td>
                                    <td class="p-4 text-sm text-gray-500"><?= date('d M Y, h:i A', strtotime($o['created_at'])) ?></td>
                                    <td class="p-4 font-bold text-gray-900">₹<?= number_format($o['total_amount'], 2) ?></td>
                                    <td class="p-4">
                                        <span class="px-2 py-1 text-xs font-bold rounded-full
                                            <?= $o['status'] === 'delivered' ? 'bg-green-100 text-green-700' :
                                                ($o['status'] === 'cancelled' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700') ?>">
                                            <?= ucfirst(htmlspecialchars($o['status'])) ?>
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <a href="/dashboard/order/<?= $o['id'] ?>" class="text-blue-600 hover:text-blue-800 font-semibold text-sm">View Details & Tracking</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </main>
</body>
</html>
