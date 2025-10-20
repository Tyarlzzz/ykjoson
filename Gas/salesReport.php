<?php
session_start();

if (!isset($_SESSION['owner_logged_in']) || $_SESSION['owner_logged_in'] !== true) {
  header('Location: ownerAccess.php');
  exit;
}

require '../layout/header.php';
require_once 'reportFunctions.php';
require_once '../database/Database.php';

$pdo = (new Database())->getConnection();

// Get current year and month
$currentYear = date('Y');
$currentMonth = date('n'); // 1-12

// Fetch current week's data for summary cards
$currentWeekData = getCurrentWeekData($pdo);

// Fetch yearly sales data for JavaScript
$yearlySalesData = getYearlySalesData($pdo, $currentYear);

// Get available months for dropdown
$availableMonths = getAvailableMonths($pdo, $currentYear);

$fullMonthNames = [
  1 => 'January',
  2 => 'February',
  3 => 'March',
  4 => 'April',
  5 => 'May',
  6 => 'June',
  7 => 'July',
  8 => 'August',
  9 => 'September',
  10 => 'October',
  11 => 'November',
  12 => 'December'
];
?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="font-[Outfit] ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">
        Inventory & Sales Report
      </h1>
      <div class="flex items-center gap-2">
        <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
        <a href="logoutInventorySales.php" class="bg-red-600 text-white py-1 px-4 rounded-full">Logout</a>
      </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex mb-0">
      <a href="expenses.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl">Expenses</a>
      <a href="salesReport.php" class="px-5 py-1 bg-red-600 text-white font-base rounded-t-2xl -ml-3 z-0">Sales
        Report</a>
      <a href="updateInventory.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Inventory</a>
      <a href="../Rider/manageRiders.php"
        class="px-10 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Riders</a>
      <a href="archived.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
    </div>

    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <div class="p-8">

        <!-- Header -->
        <div class="mb-5 flex">
          <h2 class="font-[Outfit] text-3xl font-bold text-gray-800 mb-2">This Week's Summary</h2>
          <div class="flex items-center justify-between">
            <span id="weekDisplay" class="ms-3 ps-1 border-l border-gray-900 text-md">
              <?php
              echo getMonthName($currentMonth) . ' - Week ' . $currentWeekData['week'];
              ?>
            </span>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-4 gap-6 mb-8 justify-left">
          <div class="bg-red-600 text-white rounded-xl px-4 py-3 flex flex-col items-start">
            <span class="text-lg font-semibold">Sales</span>
            <span id="summaryCardSales" class="text-5xl font-bold mt-2">
              ₱ <?php echo number_format($currentWeekData['sales']); ?>
            </span>
          </div>
          <div class="bg-red-600 text-white rounded-xl px-4 py-3 flex flex-col items-start">
            <span class="text-lg font-semibold">Customers</span>
            <span id="summaryCardCustomers" class="text-5xl font-bold mt-2">
              <?php echo $currentWeekData['customers']; ?>
            </span>
          </div>
          <div class="bg-red-600 text-white rounded-xl px-4 py-3 flex flex-col items-start">
            <span class="text-lg font-semibold">Delivered</span>
            <span id="summaryCardDelivered" class="text-5xl font-bold mt-2">
              <?php echo $currentWeekData['paid']; ?>
            </span>
          </div>
          <div class="bg-red-600 text-white rounded-xl px-4 py-3 flex flex-col items-start">
            <span class="text-lg font-semibold">Net Worth</span>
            <span id="summaryCardNetWorth" class="text-5xl font-bold mt-2">
              ₱ <?php echo number_format($currentWeekData['netWorth']); ?>
            </span>
          </div>
        </div>

        <!-- Sales Chart with Filters -->
        <div class="bg-gray-100 border rounded-lg p-6 mb-6">
          <div class="flex justify-between items-center mb-4">
            <h2 id="salesChartTitle" class="text-xl font-bold">
              <?php echo $fullMonthNames[$currentMonth]; ?> Sales Summary
            </h2>
            <div class="flex">
              <div class="flex items-center justify-center w-full">
                <p class="text-lg text-center w-20">Filter by:</p>
              </div>
              <select id="salesFilterType" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="Week">Weekly</option>
                <option value="Month">Monthly</option>
              </select>
              <div class="flex items-center justify-center w-full">
                <p class="text-lg text-center w-20">Month:</p>
              </div>
              <select id="salesMonth" class="px-4 py-2 border border-gray-300 rounded-lg">
                <?php
                foreach ($availableMonths as $month) {
                  $selected = ($month['month_num'] == $currentMonth) ? 'selected' : '';
                  echo "<option value='{$fullMonthNames[$month['month_num']]}' {$selected}>{$fullMonthNames[$month['month_num']]}</option>";
                }
                ?>
              </select>
            </div>
          </div>
          <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Customer Chart with Filters -->
        <div class="bg-gray-100 border rounded-lg p-6 mb-6">
          <div class="flex justify-between items-center mb-4">
            <h2 id="customerChartTitle" class="text-xl font-bold">
              <?php echo $fullMonthNames[$currentMonth]; ?> Number of Customers
            </h2>
            <div class="flex">
              <div class="flex items-center justify-center w-full">
                <p class="text-lg text-center w-20">Filter by: </p>
              </div>
              <select id="customerFilterType" class="px-4 py-2 border border-gray-300 rounded-lg">
                <option value="Week">Weekly</option>
                <option value="Month">Monthly</option>
              </select>
              <div class="flex items-center justify-center w-full">
                <p class="text-lg text-center w-20">Month:</p>
              </div>
              <select id="customerMonth" class="px-4 py-2 border border-gray-300 rounded-lg">
                <?php
                foreach ($availableMonths as $month) {
                  $selected = ($month['month_num'] == $currentMonth) ? 'selected' : '';
                  echo "<option value='{$fullMonthNames[$month['month_num']]}' {$selected}>{$fullMonthNames[$month['month_num']]}</option>";
                }
                ?>
              </select>
            </div>
          </div>
          <canvas id="numCustomer" height="100"></canvas>
        </div>


      </div>
    </div>
  </div>
</main>

<!-- Pass PHP data to JavaScript -->
<script>
  window.salesData = <?php echo json_encode([
    $currentYear => $yearlySalesData
  ]); ?>;

  window.currentYear = <?php echo $currentYear; ?>;
  window.currentMonth = "<?php echo $fullMonthNames[$currentMonth]; ?>";
</script>

<?php require '../layout/footer.php'; ?>
<script src="../assets/js/gas_system_js/gasCharts.js"></script>
<script src="../assets/js/gas_system_js/salesReport.js"></script>