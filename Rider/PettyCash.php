<?php
require '../layout/header.php';
require_once __DIR__ . '/../database/Database.php';

$db = new Database();
$conn = $db->getConnection();

// ✅ Fetch all riders
try {
    $stmt = $conn->prepare("SELECT rider_id AS id, fullname, petty_cash FROM riders ORDER BY rider_id ASC");
    $stmt->execute();
    $riders = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("<p style='color:red;'>Database Error: " . htmlspecialchars($e->getMessage()) . "</p>");
}

// ✅ Determine active rider
$active_rider_id = isset($_GET['rider_id']) ? (int)$_GET['rider_id'] : ($riders[0]['id'] ?? 1);
$selected_rider = null;
foreach ($riders as $rider) {
    if ($rider['id'] == $active_rider_id) {
        $selected_rider = $rider;
        break;
    }
}
if (!$selected_rider && count($riders) > 0) {
    $selected_rider = $riders[0];
    $active_rider_id = $riders[0]['id'];
}

// ✅ Get today's total sales dynamically
$today = date('Y-m-d');
try {
    // ✅ GAS SALES — count each order_id only once
    $stmt_gas = $conn->prepare("
        SELECT IFNULL(SUM(gas_per_order.total_per_order), 0) AS gas_sales
        FROM (
            SELECT o.order_id, SUM(goi.total) AS total_per_order
            FROM gas_ordered_items goi
            INNER JOIN orders o ON o.order_id = goi.order_id
            WHERE DATE(o.order_date) = :today
              AND LOWER(o.status) = 'paid'
              AND o.business_type = 'Gas System'
            GROUP BY o.order_id
        ) AS gas_per_order
    ");
    $stmt_gas->execute([':today' => $today]);
    $gas_sales = (float)$stmt_gas->fetchColumn();

    // ✅ LAUNDRY SALES — count each order_id only once
    $stmt_laundry = $conn->prepare("
        SELECT IFNULL(SUM(laundry_per_order.total_per_order), 0) AS laundry_sales
        FROM (
            SELECT o.order_id, SUM(loi.total) AS total_per_order
            FROM laundry_ordered_items loi
            INNER JOIN orders o ON o.order_id = loi.order_id
            WHERE DATE(o.order_date) = :today
              AND LOWER(o.status) = 'paid'
              AND o.business_type = 'Laundry System'
            GROUP BY o.order_id
        ) AS laundry_per_order
    ");
    $stmt_laundry->execute([':today' => $today]);
    $laundry_sales = (float)$stmt_laundry->fetchColumn();

    // ✅ Combine both systems
    $total_sales = $gas_sales + $laundry_sales;

} catch (PDOException $e) {
    $total_sales = 0;
}


$current_petty = (float)($selected_rider['petty_cash'] ?? 0);
$total_amount = $total_sales + $current_petty;
?>

<main class="font-[Switzer] flex-1 p-6 bg-gray-50 overflow-auto">
    <div class="w-full">
        <!-- Header -->
        <div class="mb-8 flex justify-between items-center">
            <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Petty Cash</h1>
            <p class="text-gray-500 text-base" id="currentDate"></p>
        </div>

        <!-- ✅ Rider Tabs -->
        <div class="mb-1 flex gap-2">
            <?php foreach ($riders as $rider): ?>
                <?php $is_active = ($rider['id'] == $active_rider_id); ?>
                <a href="?rider_id=<?= $rider['id'] ?>"
                   class="rider-tab px-4 py-2 rounded-xl font-semibold text-white <?= $is_active ? 'bg-blue-600' : 'bg-gray-400' ?>">
                    <?= htmlspecialchars($rider['fullname']) ?>
                </a>
            <?php endforeach; ?>
        </div>

        <!-- Two-column layout -->
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-20">
            <!-- Left: Calculator -->
            <div class="lg:col-span-3 bg-white shadow-md rounded-xl p-6 self-start">
                <div class="bg-gray-100 rounded-xl flex justify-between items-center p-8 mb-6 shadow-[inset_0_2px_4px_rgba(0,0,0,0.2)]">
                    <span class="text-6xl font-bold">₱</span>
                    <input type="text" id="amountInput" class="flex-1 ml-2 bg-transparent outline-none text-4xl font-bold text-gray-700 pt-4 text-end" readonly>
                </div>

                <div class="flex justify-end mb-4">
                    <button onclick="clearInput()" class="clear-btn">X</button>
                </div>

                <!-- Number Pad -->
                <div class="grid grid-cols-3 gap-4" id="numpad">
                    <?php
                        $numbers = [1,2,3,4,5,6,7,8,9,".",0];
                        foreach ($numbers as $num) {
                            $jsNum = is_numeric($num) ? $num : "'$num'";
                            echo "<button onclick='appendNumber($jsNum)' class='bg-gray-200 text-5xl shadow-md font-extrabold py-2 rounded-xl'>$num</button>";
                        }
                    ?>
                    <button type="button" onclick="submitAmount()" class="bg-blue-600 text-2xl font-semibold text-white py-4 rounded-xl">Submit</button>
                </div>
            </div>

            <!-- Right: Summary -->
            <div class="bg-white shadow-md rounded-xl p-6 lg:col-span-2 self-start">
                <h2 class="font-bold text-lg text-center">PETTY CASH</h2>
                <p class="text-black-500 text-base border-b-2 border-gray-500 text-center" id="receiptDate"></p>
                <p class="text-md text-black-800 font-semibold mb-4 text-left">
                    Name: <?= htmlspecialchars($selected_rider['fullname']) ?><br>
                    <div class="flex items-center justify-between mt-1">
                        <span id="currentPettyCash" class="text-sm text-gray-500">
                            Current Petty Cash: ₱<?= number_format($current_petty, 2) ?>
                        </span>
                        <button onclick="clearPettyCash()"
                                class="ml-2 bg-yellow-500 hover:bg-yellow-600 text-white text-xs font-semibold px-3 py-1 rounded-md">
                            Clear
                        </button>
                    </div>
                </p>


                <div class="space-y-4 mt-4">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Petty cash given</span>
                    </div>

                    <div class="flex justify-end items-center mb-4">
                        <span id="pettyCashGiven" class="text-5xl font-bold">₱0</span>
                    </div>

                    <div class="flex justify-between items-center">
                        <span class="text-gray-600">Today’s Total Sales for Laundry and Gasul</span>
                    </div>

                    <div class="flex justify-end items-center">
                        <span id="sales" class="text-5xl font-bold">
                            ₱<?= number_format($total_sales, 2) ?>
                        </span>
                    </div>

                    <div class="flex justify-between items-center border-t-2 border-black pt-4">
                        <span class="text-gray-600 font-semibold">Total Amount</span>
                    </div>
                    <div class="flex justify-end items-center">
                        <p id="totalAmount" class="text-5xl font-extrabold text-black-700">
                            ₱<?= number_format($total_amount, 2) ?>
                        </p>
                    </div>
                </div>

                <!-- Buttons -->
                <div class="mt-6 flex gap-4">
                    <button onclick="deletePettyCash()" class="flex-1 bg-red-500 text-white py-2 rounded-md font-semibold">Delete</button>
                    <button onclick="confirmPettyCash()" class="flex-1 bg-green-500 text-white py-2 rounded-md font-semibold">Confirm</button>
                </div>
            </div>
        </div>
    </div>
</main>

<!-- ✅ Pass PHP data to JS -->
<script>
    const salesAmount = <?= json_encode($total_sales) ?>;
    const riderId = <?= json_encode($active_rider_id) ?>;
</script>

<?php
require '../layout/footer.php';
?>
