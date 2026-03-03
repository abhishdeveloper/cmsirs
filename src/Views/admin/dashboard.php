<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - MediCare</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 text-gray-800 antialiased flex">
    <!-- Sidebar -->
    <aside class="w-64 bg-gray-900 text-white min-h-screen flex flex-col hidden md:flex">
        <div class="p-6 text-2xl font-bold border-b border-gray-800">MediCare Admin</div>
        <nav class="flex-1 px-4 py-6 space-y-2 text-sm font-medium">
            <a href="/admin/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-lg bg-gray-800 text-white"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>Dashboard</a>
            <a href="/admin/orders" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>Orders</a>
            <a href="/admin/products" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>Products</a>
            <a href="#" class="flex items-center gap-3 px-4 py-3 rounded-lg hover:bg-gray-800 transition text-gray-500 cursor-not-allowed" title="Coming Soon"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z"></path></svg>Coupons</a>
        </nav>
        <div class="p-4 border-t border-gray-800">
            <a href="/logout" class="block w-full text-center bg-red-600 hover:bg-red-700 text-white font-bold py-2 rounded transition">Logout</a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="flex-1 p-8">
        <header class="flex justify-between items-center mb-8">
            <h1 class="text-3xl font-extrabold text-gray-900">Dashboard Overview</h1>
        </header>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Total Sales</p>
                    <p class="text-3xl font-extrabold text-blue-700">₹<?= number_format($stats['total_sales'] ?? 0, 2) ?></p>
                </div>
                <div class="p-3 bg-blue-50 text-blue-600 rounded-lg"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Total Orders</p>
                    <p class="text-3xl font-extrabold text-gray-900"><?= $stats['total_orders'] ?></p>
                </div>
                <div class="p-3 bg-green-50 text-green-600 rounded-lg"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Users</p>
                    <p class="text-3xl font-extrabold text-gray-900"><?= $stats['total_users'] ?></p>
                </div>
                <div class="p-3 bg-purple-50 text-purple-600 rounded-lg"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg></div>
            </div>
            <div class="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-gray-500 uppercase tracking-wider mb-1">Low Stock</p>
                    <p class="text-3xl font-extrabold <?= $stats['low_stock'] > 0 ? 'text-red-600' : 'text-gray-900' ?>"><?= $stats['low_stock'] ?></p>
                </div>
                <div class="p-3 bg-red-50 text-red-600 rounded-lg"><svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg></div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 flex justify-between items-center">
                <h2 class="text-lg font-bold text-gray-900">Recent Orders</h2>
                <a href="/admin/orders" class="text-sm font-semibold text-blue-600 hover:text-blue-800">View All</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-white text-gray-500 text-xs uppercase tracking-wider border-b">
                            <th class="px-6 py-4 font-semibold">Order ID</th>
                            <th class="px-6 py-4 font-semibold">Customer</th>
                            <th class="px-6 py-4 font-semibold">Date</th>
                            <th class="px-6 py-4 font-semibold">Status</th>
                            <th class="px-6 py-4 font-semibold text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php foreach ($recentOrders as $order): ?>
                            <tr class="hover:bg-gray-50 transition">
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900">
                                    <span class="text-gray-900"><?= htmlspecialchars($order['order_number']) ?></span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-600 font-medium"><?= htmlspecialchars($order['first_name'] . ' ' . $order['last_name']) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y', strtotime($order['created_at'])) ?></td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-2 py-1 text-xs font-bold rounded-full
                                        <?= $order['status'] === 'delivered' ? 'bg-green-100 text-green-700' :
                                            ($order['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-blue-100 text-blue-700') ?>">
                                        <?= ucfirst(htmlspecialchars($order['status'])) ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-gray-900 text-right">₹<?= number_format($order['total_amount'], 2) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</body>
</html>
