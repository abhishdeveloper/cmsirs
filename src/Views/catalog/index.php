<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medicine Brand - Catalog</title>
    <!-- Tailwind CSS for Mobile-First UI -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Alpine.js for lightweight reactivity (dropdowns, search) -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>
<body class="bg-gray-50 text-gray-800 antialiased" x-data="{ searchOpen: false, searchQuery: '', searchResults: [] }">

    <!-- Header / Navbar -->
    <header class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16 items-center">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="/" class="text-2xl font-bold text-blue-700 tracking-tight">MediCare</a>
                </div>

                <!-- Desktop Search Bar -->
                <div class="hidden sm:block flex-1 max-w-lg mx-8 relative">
                    <input type="text" x-model="searchQuery" @input.debounce.300ms="
                            if(searchQuery.length > 2) {
                                fetch('/api/search?q=' + searchQuery)
                                .then(res => res.json())
                                .then(data => searchResults = data);
                                searchOpen = true;
                            } else {
                                searchOpen = false;
                                searchResults = [];
                            }
                        " placeholder="Search medicines by name or composition..."
                        class="w-full bg-gray-100 rounded-full pl-4 pr-10 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm">
                    <span class="absolute right-3 top-2 text-gray-400">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    </span>

                    <!-- AJAX Search Dropdown -->
                    <div x-show="searchOpen && searchResults.length > 0" @click.away="searchOpen = false" x-cloak
                         class="absolute z-50 mt-1 w-full bg-white shadow-lg rounded-xl overflow-hidden border border-gray-100 divide-y divide-gray-100">
                        <template x-for="item in searchResults" :key="item.id">
                            <a :href="'/product/' + item.slug" class="block p-4 hover:bg-gray-50 flex items-center space-x-4 transition">
                                <img :src="item.image_url ? item.image_url : '/images/placeholder.png'" class="w-12 h-12 rounded object-cover">
                                <div>
                                    <p class="text-sm font-semibold text-gray-800" x-text="item.name"></p>
                                    <p class="text-xs text-blue-600 font-bold">₹<span x-text="item.price"></span></p>
                                </div>
                            </a>
                        </template>
                    </div>
                </div>

                <!-- Mobile Menu & Cart -->
                <div class="flex items-center space-x-4">
                    <a href="/cart" class="relative text-gray-600 hover:text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span class="absolute -top-2 -right-2 bg-red-500 text-white text-xs font-bold px-1.5 py-0.5 rounded-full">0</span>
                    </a>
                    <button class="sm:hidden text-gray-600 hover:text-blue-600">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col md:flex-row gap-8">

        <!-- Sidebar: Categories & Filters -->
        <aside class="w-full md:w-1/4">
            <div class="bg-white p-5 rounded-2xl shadow-sm border border-gray-100">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Categories</h3>
                <ul class="space-y-2">
                    <li>
                        <a href="/" class="block text-sm text-gray-600 hover:text-blue-600 font-medium transition <?= !isset($_GET['category']) ? 'text-blue-600 font-bold' : '' ?>">All Products</a>
                    </li>
                    <?php foreach ($categories ?? [] as $cat): ?>
                        <li>
                            <a href="/?category=<?= htmlspecialchars($cat['slug']) ?>"
                               class="block text-sm text-gray-600 hover:text-blue-600 transition <?= (isset($_GET['category']) && $_GET['category'] === $cat['slug']) ? 'text-blue-600 font-bold' : '' ?>">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </aside>

        <!-- Product Grid -->
        <section class="w-full md:w-3/4">
            <h1 class="text-3xl font-extrabold text-gray-900 mb-6">Our Products</h1>

            <?php if (empty($products)): ?>
                <div class="bg-white p-8 text-center rounded-2xl shadow-sm border border-gray-100">
                    <p class="text-gray-500 text-lg">No products found matching your criteria.</p>
                </div>
            <?php else: ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($products as $p): ?>
                        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-lg transition duration-300 group flex flex-col">
                            <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="block relative aspect-w-1 aspect-h-1 p-4 bg-gray-50 flex items-center justify-center h-48">
                                <img src="<?= htmlspecialchars($p['primary_image'] ?? '/images/placeholder.png') ?>" alt="<?= htmlspecialchars($p['name']) ?>" class="object-contain h-full w-full group-hover:scale-105 transition duration-300">
                                <?php if ($p['requires_prescription']): ?>
                                    <span class="absolute top-2 left-2 bg-red-100 text-red-700 text-xs font-bold px-2 py-1 rounded">Rx Required</span>
                                <?php endif; ?>
                            </a>
                            <div class="p-5 flex-1 flex flex-col">
                                <p class="text-xs text-gray-400 mb-1 uppercase tracking-wide font-semibold"><?= htmlspecialchars($p['category_name'] ?? 'Uncategorized') ?></p>
                                <a href="/product/<?= htmlspecialchars($p['slug']) ?>" class="text-lg font-bold text-gray-900 hover:text-blue-600 transition line-clamp-2 mb-2"><?= htmlspecialchars($p['name']) ?></a>
                                <p class="text-sm text-gray-500 mb-4 line-clamp-1"><?= htmlspecialchars($p['composition'] ?? '') ?></p>

                                <div class="mt-auto flex items-end justify-between">
                                    <div>
                                        <?php if ($p['compare_at_price'] && $p['compare_at_price'] > $p['price']): ?>
                                            <p class="text-xs text-gray-400 line-through">₹<?= number_format($p['compare_at_price'], 2) ?></p>
                                        <?php endif; ?>
                                        <p class="text-xl font-extrabold text-blue-700">₹<?= number_format($p['price'], 2) ?></p>
                                    </div>
                                    <button class="bg-blue-600 text-white px-3 py-2 rounded-lg hover:bg-blue-700 transition flex items-center shadow-sm">
                                        <svg class="w-5 h-5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                                        Add
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </main>

</body>
</html>
