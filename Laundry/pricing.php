<?php
    require_once '../layout/header.php';
    require_once '../database/Database.php';
    require_once '../Models/Models.php';
    require_once '../Models/laundry_pricing.php';

    $database = new Database();
    $conn = $database->getConnection();
    Model::setConnection($conn);

    $laundry_pricing = laundry_pricing::getLaundryPricing();

    $inventory = [];
    if ($laundry_pricing) {
        foreach ($laundry_pricing as $item) {
            $inventory[strtolower($item->item_type)] = $item;
        }
    }

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $pricing_id = $_POST['pricing_id'];
            $new_price = $_POST['new_price'];
            $price_column = $_POST['price_column'];

            if (empty($pricing_id) || !is_numeric($new_price) || $new_price < 0) {
                throw new Exception('Invalid input data');
            }

            $success = laundry_pricing::updatePrice($pricing_id, $new_price, $price_column);

            if ($success) {
                echo '<script>
                    Swal.fire({
                        title: "Success!",
                        text: "Value updated successfully!",
                        icon: "success"
                    }).then(() => window.location = "pricing.php");
                </script>';
            } else {
                throw new Exception('Failed to update pricing');
            }

        } catch (Exception $e) {
            echo '<script>
                Swal.fire({
                    title: "Error!",
                    text: "' . addslashes($e->getMessage()) . '",
                    icon: "error"
                });
            </script>';
        }
    }
?>

<!-- Main Content -->
<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
    <div class="w-full px-1">
        <div class="mb-6 flex justify-between items-center">
            <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Laundry Pricing</h1>
            <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
        </div>

        <div class="flex mb-0">
            <a href="expenses.php" class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold rounded-t-2xl z-0">Expenses</a>
            <a href="salesReport.php" class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Sales Report</a>
            <a href="pricing.php" class="px-8 py-1 bg-blue-600 text-white font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Pricing</a>
            <a href="../Rider/ManageRiders.php" class="px-10 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Riders</a>
            <a href="archived.php" class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
        </div>

        <div class="w-full">
            <div class="bg-white rounded-2xl shadow-xl p-6">
                <h2 class="border-b border-black text-xl font-bold text-gray-800 mb-6 pb-2">Set Prices</h2>

                <?php
                    $items = [
                        [
                            'type' => 'standard_clothes',
                            'title' => 'Regular Clothes',
                            'image' => '../assets/images/tops.png',
                            'price_column' => 'standard_price'
                        ],
                        [
                            'type' => 'standard_clothes',
                            'title' => 'Rushed Clothes',
                            'image' => '../assets/images/rushed.png',
                            'price_column' => 'rush_price'
                        ],
                        [
                            'type' => 'comforter_curtains',
                            'title' => 'Comforter & Curtains',
                            'image' => '../assets/images/comforter2.png',
                            'price_column' => 'standard_price'
                        ],
                        [
                            'type' => 'gown',
                            'title' => 'Gowns',
                            'image' => '../assets/images/gowns.png',
                            'price_column' => 'standard_price'
                        ],
                        [
                            'type' => 'barong',
                            'title' => 'Barong',
                            'image' => '../assets/images/barong.png',
                            'price_column' => 'standard_price'
                        ],
                        [
                            'type' => 'standard_clothes',
                            'title' => 'Minimum Weight (kg)',
                            'image' => '../assets/images/weight.png',
                            'price_column' => 'minimum_weight'
                        ]
                    ];
                ?>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <?php foreach ($items as $item): ?>
                        <?php
                        $type = strtolower($item['type']);
                        $price_column = $item['price_column'];
                        $price = 0;
                        $pricing_id = null;

                        if (isset($inventory[$type])) {
                            $record = $inventory[$type];
                            $pricing_id = $record->pricing_id;

                            if ($price_column === 'minimum_weight') {
                                $price = $record->minimum_weight ?? 0;
                            } else {
                                $price = $record->$price_column ?? 0;
                            }
                        }
                        ?>
                        <div class="item-card bg-white text-center border-2 border-transparent p-1 rounded-2xl shadow-sm">
                            <h3 class="font-bold text-gray-700 text-lg"><?= htmlspecialchars($item['title']) ?></h3>
                            <div class="w-full max-w-xs aspect-square mx-auto my-4 bg-white border-2 border-gray-200 rounded-2xl flex items-center justify-center">
                                <img src="<?= htmlspecialchars($item['image']) ?>" alt="<?= htmlspecialchars($item['title']) ?>" class="w-4/5 h-4/5 object-contain">
                            </div>
                            <h3 class="font-black text-center px-3 mb-3 text-2xl">
                                <?= ($price_column === 'minimum_weight')
                                    ? "Current Minimum Weight: {$price} kg"
                                    : "Current Price: ₱{$price}" ?>
                            </h3>

                            <?php if ($pricing_id): ?>
                                <button type="button"
                                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg font-semibold w-full"
                                    onclick="openModal(
                                        '<?= htmlspecialchars($item['title'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($item['image'], ENT_QUOTES) ?>',
                                        '<?= htmlspecialchars($price, ENT_QUOTES) ?>',
                                        '<?= $pricing_id ?>',
                                        '<?= $price_column ?>'
                                    )">
                                    Update <?= ($price_column === 'minimum_weight') ? 'Weight' : 'Price' ?>
                                </button>
                            <?php else: ?>
                                <p class="text-red-500 font-semibold">No database record found.</p>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Modal Popup -->
        <div id="priceModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
            <div class="bg-white rounded-2xl p-6 max-w-md w-full mx-4 relative">
                <button id="closeModal" class="absolute top-4 right-4 text-gray-500 hover:text-gray-700 text-2xl font-bold">&times;</button>
                <div class="text-center mb-4">
                    <img id="modalImage" src="" alt="" class="w-32 h-32 object-contain mx-auto mb-4 rounded-lg">
                    <h3 id="modalTitle" class="font-bold text-gray-700 text-lg mb-2"></h3>
                    <p id="modalCurrentPrice" class="text-gray-600 mb-4"></p>
                </div>

                <form method="POST" id="priceForm">
                    <input type="hidden" name="pricing_id" id="modalPricingId">
                    <input type="hidden" name="price_column" id="modalPriceColumn">

                    <div class="mb-4">
                        <label id="modalLabel" class="block text-xs font-bold text-gray-700 mb-2 uppercase tracking-wide">New Value</label>
                        <input type="number" name="new_price" id="newPriceInput" min="0" step="0.01" placeholder="Enter new value"
                            class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium" required>
                    </div>

                    <div class="flex justify-end gap-2 mt-6">
                        <button type="button" id="cancelBtn" class="bg-gray-300 hover:bg-gray-400 text-gray-700 px-4 py-2 rounded-lg font-semibold transition">
                            Cancel
                        </button>
                        <button type="submit" class="bg-yellow-400 hover:bg-yellow-500 text-white px-4 py-2 rounded-lg font-semibold transition">
                            Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</main>

<script>
    const modal = document.getElementById('priceModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalImage = document.getElementById('modalImage');
    const modalCurrentPrice = document.getElementById('modalCurrentPrice');
    const modalPricingId = document.getElementById('modalPricingId');
    const modalPriceColumn = document.getElementById('modalPriceColumn');
    const modalLabel = document.getElementById('modalLabel');
    const newPriceInput = document.getElementById('newPriceInput');

    function openModal(title, image, price, id, priceColumn) {
        modalTitle.textContent = title;
        modalImage.src = image;
        if (priceColumn === 'minimum_weight') {
            modalCurrentPrice.textContent = `Current Minimum Weight: ${price} kg`;
            modalLabel.textContent = 'New Minimum Weight (kg)';
            newPriceInput.placeholder = 'Enter new weight in kg';
            newPriceInput.step = '0.01';
        } else {
            modalCurrentPrice.textContent = `Current Price: ₱${price}`;
            modalLabel.textContent = 'New Price';
            newPriceInput.placeholder = 'Enter new price';
            newPriceInput.step = '0.01';
        }
        modalPricingId.value = id;
        modalPriceColumn.value = priceColumn;
        newPriceInput.value = ''; // clear previous
        modal.classList.remove('hidden');
    }

    document.getElementById('closeModal').addEventListener('click', () => modal.classList.add('hidden'));
    document.getElementById('cancelBtn').addEventListener('click', () => modal.classList.add('hidden'));
</script>

<?php include '../layout/footer.php'; ?>