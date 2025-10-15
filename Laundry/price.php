<?php include '../layout/header.php' ?>

<!-- Main Content Area -->
<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
    <div class="w-full px-1">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Point of Sale System</h1>
            <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
        </div>

        <div class="w-full">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <?php
                // Hardcoded prices array (replace with DB query later)
                $prices = [
                    'regular' => 30,
                    'rushed' => 40,
                    'comforter' => 60,
                    'gowns' => 500,
                    'barong' => 250,
                    'minimum_weight' => 4  // Default minimum weight in kilos
                ];

                $items = [
                    [
                        'type' => 'regular',
                        'title' => 'Regular Clothes',
                        'image' => '../assets/images/tops.png',
                        'price' => $prices['regular']
                    ],
                    [
                        'type' => 'rushed',
                        'title' => 'Rushed Clothes',
                        'image' => '../assets/images/rushed.png',
                        'price' => $prices['rushed']
                    ],
                    [
                        'type' => 'comforter',
                        'title' => 'Comforter & Curtains',
                        'image' => '../assets/images/comforter2.png',
                        'price' => $prices['comforter']
                    ],
                    [
                        'type' => 'gowns',
                        'title' => 'Gowns',
                        'image' => '../assets/images/gowns.png',
                        'price' => $prices['gowns']
                    ],
                    [
                        'type' => 'barong',
                        'title' => 'Barong',
                        'image' => '../assets/images/barong.png',
                        'price' => $prices['barong']
                    ],
                    [
                        'type' => 'minimum_weight',
                        'title' => 'Minimum Weight',
                        'image' => '../assets/images/weight.png',  
                        'price' => $prices['minimum_weight'] 
                    ]
                ];
                ?>

                <h2 class="border-b border-black text-xl font-bold text-gray-800 mb-6 pb-2">Set Prices</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($items as $item): ?>
                        <div class="item-card bg-white-500 text-center border-2 border-transparent p-1">
                            <h3 class="font-bold text-gray-700 text-lg"><?= htmlspecialchars($item['title']) ?></h3>
                            <div class="w-full max-w-xs aspect-square mx-auto my-4 p-0 bg-white border-2 border-gray-200 shadow-sm rounded-2xl flex items-center justify-center cursor-pointer">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-4/5 h-4/5 object-contain" id="img-<?= $item['type'] ?>">
                            </div>
                            <?php if ($item['type'] == 'minimum_weight'): ?>
                                <h3 class="font-black text-center px-3 mb-3 text-2xl">Current Minimum Weight: <?= $item['price'] ?> kilos</h3>
                            <?php elseif ($item['type'] == 'gowns' || $item['type'] == 'barong'): ?>
                                <h3 class="font-black text-center px-3 mb-3 text-2xl">Current Price: ₱<?= $item['price'] ?>/piece</h3>
                            <?php else: ?>
                                <h3 class="font-black text-center px-3 mb-3 text-2xl">Current Price: ₱<?= $item['price'] ?>/kilo</h3>
                            <?php endif; ?>
                            <!-- Update button -->
                            <button type="button" class="update-btn bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold transition w-full" 
                                    data-type="<?= $item['type'] ?>" 
                                    data-title="<?= htmlspecialchars($item['title']) ?>" 
                                    data-image="<?= htmlspecialchars($item['image']) ?>" 
                                    data-price="<?= $item['price'] ?>"
                                    data-is-weight="<?= ($item['type'] == 'minimum_weight') ? 'true' : 'false' ?>">
                                Update <?= ($item['type'] == 'minimum_weight') ? 'Weight' : 'Price' ?>
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Modal Popup (Hidden by default) -->
        <div id="priceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 relative">
                <!-- Close button (X) -->
                <button id="closeModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
                
                <!-- Content -->
                <div class="text-center mb-4">
                    <img id="modalImage" src="" alt="" class="w-32 h-32 object-contain mx-auto mb-4 rounded-lg">
                    <h3 id="modalTitle" class="font-bold text-gray-700 text-lg mb-2"></h3>
                    <p id="modalCurrentPrice" class="text-gray-600 mb-4"></p>  <!-- Dynamic content here -->
                </div>
                
                <div class="mb-4">
                    <label id="modalLabel" class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">New Price</label>
                    <input type="number" id="newPriceInput" min="0" step="0.01" placeholder="Enter new price"
                           class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
                </div>
                
                <!-- Buttons (lower right) -->
                <div class="flex justify-end gap-2 mt-6">
                    <button type="button" id="cancelBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-semibold transition">
                        Cancel
                    </button>
                    <button type="button" id="updateBtn" class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded-lg font-semibold transition">
                        Update
                    </button>
                </div>
            </div>
        </div>
    </div>
</main>

</div>

<?php include '../layout/footer.php' ?>
