<?php require '../layout/header.php' ?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Inventory & Sales Report</h1>
      <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex mb-0">
      <a href="updateInventory.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold rounded-t-2xl z-0">Inventory</a>
      <a href="salesReport.php" class="px-5 py-1 bg-red-600 text-white font-semibold rounded-t-2xl -ml-3 z-0">Sales
        Report</a>
      <a href="../Rider/manageRiders.php"
        class="px-10 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Riders</a>
      <a href="archived.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
      <a href="expenses.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Expenses</a>
    </div>

    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <div class="p-8">

        <!-- Header -->
        <div class="mb-5 flex">
          <h2 class="font-[Outfit] text-3xl font-bold text-gray-800 mb-2">This Week's Summary</h2>
          <div class="flex items-center justify-between">
            <span id="weekDisplay" class="ms-3 ps-1 border-l border-gray-900 text-md">October - Week 1</span>
          </div>
        </div>

        <!-- Summary Cards -->
        <div class="grid grid-cols-4 gap-6 mb-8 justify-left">
          <div class="bg-red-600 text-white rounded-xl px-4 py-3 flex flex-col items-start">
            <span class="text-lg font-semibold">Sales</span>
            <span id="summaryCardSales" class="text-5xl font-bold mt-2">₱ 3,450</span>
          </div>
          <div class="bg-red-600 text-white rounded-xl px-4 py-3 flex flex-col items-start">
            <span class="text-lg font-semibold">Customers</span>
            <span id="summaryCardCustomers" class="text-5xl font-bold mt-2">27</span>
          </div>
          <div class="bg-red-600 text-white rounded-xl px-4 py-3 flex flex-col items-start">
            <span class="text-lg font-semibold">Delivered</span>
            <span id="summaryCardDelivered" class="text-5xl font-bold mt-2">26</span>
          </div>
          <div class="bg-red-600 text-white rounded-xl px-4 py-3 flex flex-col items-start">
            <span class="text-lg font-semibold">Net Worth</span>
            <span class="text-5xl font-bold mt-2">₱ 9,109</span>
          </div>
        </div>

        <!-- Sales Chart Section -->
        <div class="flex items-center gap-4 mb-6">
          <label class="font-medium">Filter By</label>
          <select id="salesFilterType" class="border-2 border-black rounded-md px-2 py-1">
            <option value="Week">Week</option>
            <option value="Month">Month</option>
          </select>
          <label class="font-medium">Month</label>
          <select id="salesMonth" class="border-2 border-black rounded-md px-2 py-1">
            <option value="September" selected>September</option>
            <option value="October">October</option>
            <option value="November">November</option>
            <option value="December">December</option>
          </select>
        </div>

        <!-- Sales Chart Area -->
        <div class="bg-gray-100 border rounded-lg p-6 mb-6">
          <h2 id="salesChartTitle" class="text-xl font-bold mb-4">October Sales Summary</h2>
          <canvas id="salesChart" height="100"></canvas>
        </div>

        <!-- Customer Chart Section -->
        <div class="flex items-center gap-4 mb-6">
          <label class="font-medium">Filter By</label>
          <select id="customerFilterType" class="border-2 border-black rounded-md px-2 py-1">
            <option value="Week">Week</option>
            <option value="Month">Month</option>
          </select>
          <label class="font-medium">Month</label>
          <select id="customerMonth" class="border-2 border-black rounded-md px-2 py-1">
            <option value="September" selected>September</option>
            <option value="October">October</option>
            <option value="November">November</option>
            <option value="December">December</option>
          </select>
        </div>

        <!-- Customer Chart Area -->
        <div class="bg-gray-100 border rounded-lg p-6">
          <h2 id="customerChartTitle" class="text-xl font-bold mb-4">October Number of Customers</h2>
          <canvas id="numCustomer" height="100"></canvas>
        </div>

      </div>
    </div>
  </div>
</main>

<?php require '../layout/footer.php' ?>
<script src="../assets/js/gas_system_js/salesReport.js"></script>