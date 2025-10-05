<?php
require_once '../database/Database.php';
require_once '../Models/Gas.php';
require_once '../Models/GasOrder.php';
require_once '../Models/Models.php';

$database = new Database();
$conn = $database->getConnection();
Model::setConnection($conn);

$gas = Gas::all(); 
?>

<?php require '../layout/header.php' ?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-5">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
      <h1 class="text-3xl font-extrabold text-gray-800">Point of Sale System</h1>
      <p class="text-gray-500 text-base" id="currentDate"></p>
    </div>

    <!-- Statistics Cards Section -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-[1.7fr_1.7fr_3fr_1fr] gap-5 mb-6">
      <!-- Left Column -->
      <div class="flex flex-col justify-between">
        <!-- Total Customer Card -->
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
          <div class="flex items-center gap-3 mb-1">
            <div
              class="w-12 h-12 rounded-full bg-gradient-to-br from-green-200 via-green-300 to-green-400 flex items-center justify-center">
              <img class="w-8 h-8" src="../assets/images/customers.png" alt="totalCustomerIcon">
            </div>
            <span class="text-sm font-semibold text-gray-700">Total Customer</span>
          </div>
          <div class="text-3xl font-bold text-gray-900">
            <?php
              $totalCustomers = count(Gas::all());
              echo $totalCustomers;
            ?>
          </div>
          <div class="text-xs text-gray-500 mt-1">Today</div>
        </div>

        <!-- Pending Order Card -->
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
          <div class="flex items-center gap-3 mb-1">
            <div
              class="w-12 h-12 rounded-full bg-gradient-to-br from-yellow-100 via-yellow-200 to-yellow-300 flex items-center justify-center">
              <img class="w-8 h-8" src="../assets/images/pending.png" alt="pendingOrderIcon">
            </div>
            <span class="text-sm font-semibold text-gray-700">Pending Order</span>
          </div>
          <div class="text-3xl font-bold text-gray-900">
            <?php
              $pendingOrders = GasOrder::countPending();
              echo $pendingOrders;
            ?>
          </div>
          <div class="text-xs text-gray-500 mt-1">Today</div>
        </div>
      </div>

      <!-- Middle Column -->
      <div class="flex flex-col justify-between">
        <!-- Borrowed Tanks Card -->
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
          <div class="flex items-center gap-3 mb-1">
            <div
              class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-100 via-orange-200 to-orange-300 flex items-center justify-center">
              <img class="w-8 h-8" src="../assets/images/borrowed.png" alt="borrowedTanksIcon">
            </div>
            <span class="text-sm font-semibold text-gray-700">Borrowed Tanks</span>
          </div>
          <div class="text-3xl font-bold text-gray-900">10</div>
          <div class="text-xs text-gray-500 mt-1">This Month</div>
        </div>

        <!-- Returned Tanks Card -->
        <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
          <div class="flex items-center gap-3 mb-1">
            <div
              class="w-12 h-12 rounded-full bg-gradient-to-br from-pink-200 via-pink-300 to-purple-300 flex items-center justify-center">
              <img class="w-8 h-8" src="../assets/images/returned.png" alt="returnedTanksIcon">
            </div>
            <span class="text-sm font-semibold text-gray-700">Returned Tanks</span>
          </div>
          <div class="text-3xl font-bold text-gray-900">8</div>
          <div class="text-xs text-gray-500 mt-1">This Month</div>
        </div>
      </div>

      <!-- Top Brands Sold Card -->
      <div class="bg-white rounded-2xl shadow-md p-5 border border-gray-100">
        <div class="flex items-center gap-3 mb-1">
          <div
            class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-100 via-blue-200 to-blue-300 flex items-center justify-center">
            <img class="w-6 h-6" src="../assets/images/bar-chart.png" alt="topBrandsIcon">
          </div>
          <span class="text-sm font-semibold text-gray-700">Top Brands Sold</span>
        </div>
        <div class="flex flex-col items-center gap-3 md:gap-5">
          <!-- Pie Chart -->
          <div class="relative w-full max-w-[120px] sm:max-w-[140px] md:max-w-[160px] lg:max-w-[180px]">
            <canvas id="brandsPieChart"></canvas>
          </div>
          <!-- Brands Legend -->
          <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-3 md:gap-4 text-xs">
            <div class="flex items-center gap-1.5 sm:gap-2">
              <div class="w-4 h-3 rounded-full bg-red-500"></div>
              <span class="text-gray-700 text-xs sm:text-sm md:text-base font-medium">Petron</span>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2">
              <div class="w-4 h-3 rounded-full bg-blue-500"></div>
              <span class="text-gray-700 text-xs sm:text-sm md:text-base font-medium">Econo</span>
            </div>
            <div class="flex items-center gap-1.5 sm:gap-2">
              <div class="w-4 h-3 rounded-full bg-green-500"></div>
              <span class="text-gray-700 text-xs sm:text-sm md:text-base font-medium">SeaGas</span>
            </div>
          </div>
        </div>
      </div>

      <!-- Right Column - Action Button -->
      <div class="flex flex-col gap-5">
        <!-- Add Order Button -->
        <button
          onclick="window.location.href='create.php'"class="bg-gradient-to-br from-red-400 via-red-500 to-red-600 hover:bg-[#DC2626] text-white text-lg font-semibold flex-1 rounded-2xl shadow-lg flex items-center justify-center gap-2 transition-all border border-gray-100">
          <div class="bg-white rounded-full p-1 flex items-center justify-center">
            <svg class="w-5 h-5 text-[#EF4444]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
            </svg>
          </div>
          Add Order
        </button>

        <!-- Delivered Button -->
        <div
          class="bg-gradient-to-br from-green-500 via-green-400 to-green-300 text-white px-6 py-6 rounded-2xl shadow-md flex flex-col items-start justify-center font-semibold border border-green-100 gap-3">
          <div class="flex items-center gap-1">
            <svg class="w-12 h-12" viewBox="0 0 24 24">
              <circle cx="12" cy="12" r="10" fill="white" />
              <path fill="none" stroke="black" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 12l3 3L17 9" />
            </svg>
            <span class="text-base">Delivered</span>
          </div>
          <span class="text-3xl font-bold mt-1">22</span>
        </div>
      </div>
    </div>

    <!-- Transactions Table -->
    <div class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">Today's Transactions</h2>
        <div class="flex items-center gap-3">
          <button
            class="border border-gray-300 text-gray-700 px-4 py-2 rounded-lg font-semibold hover:bg-gray-100 transition-colors">
            Select all
          </button>
          <div class="relative flex items-center">
            <input type="text" placeholder="Search"
              class="border border-gray-300 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-gray-300">
            <svg class="w-5 h-5 absolute left-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
          <div class="relative">
            <select
              class="block appearance-none w-full bg-white border border-gray-300 text-gray-700 py-2 px-4 pr-8 rounded-lg leading-tight focus:outline-none focus:ring-2 focus:ring-gray-300">
              <option>Status</option>
              <option>Returned</option>
              <option>Pending</option>
              <option>Delivered</option>
              <option>Borrowed</option>
            </select>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left table-auto">
          <thead class="bg-gray-50">
            <tr class="text-sm text-gray-500 uppercase">
              <th class="p-4 rounded-tl-lg"></th>
              <th class="p-4">#</th>
              <th class="p-4">Name</th>
              <th class="p-4">Location</th>
              <th class="p-4">Phone Number</th>
              <th class="p-4">Brand</th>
              <th class="p-4">Qty</th>
              <th class="p-4 rounded-tr-lg">Status</th>
            </tr>
          </thead>
          <tbody>
            <tr class="border-b border-gray-200">
              <td class="p-4"><input type="checkbox" class="w-4 h-4 rounded text-[#CF0000] focus:ring-[#CF0000]"></td>
              <td class="p-4 text-sm font-medium text-gray-700">001</td>
              <td class="p-4 text-sm font-medium text-gray-700">Erik S. Soliman</td>
              <td class="p-4 text-sm text-gray-500">San Nicolas, Gapan City</td>
              <td class="p-4 text-sm text-gray-500">09123456789</td>
              <td class="p-4 text-sm text-gray-500">Petron</td>
              <td class="p-4 text-sm text-gray-500">1</td>
              <td class="p-4">
                <span class="bg-status-returned text-sm px-3 py-1 rounded font-bold">Returned</span>
              </td>
            </tr>
            <tr class="border-b border-gray-200">
              <td class="p-4"><input type="checkbox" class="w-4 h-4 rounded text-[#CF0000] focus:ring-[#CF0000]"></td>
              <td class="p-4 text-sm font-medium text-gray-700">002</td>
              <td class="p-4 text-sm font-medium text-gray-700">Danielle Gonzales Quilambao</td>
              <td class="p-4 text-sm text-gray-500">San Jose City</td>
              <td class="p-4 text-sm text-gray-500">09123456789</td>
              <td class="p-4 text-sm text-gray-500">Econo</td>
              <td class="p-4 text-sm text-gray-500">2</td>
              <td class="p-4">
                <span class="bg-status-pending text-sm px-4 py-1 rounded font-bold">Pending</span>
              </td>
            </tr>
            <tr class="border-b border-gray-200">
              <td class="p-4"><input type="checkbox" class="w-4 h-4 rounded text-[#CF0000] focus:ring-[#CF0000]"></td>
              <td class="p-4 text-sm font-medium text-gray-700">003</td>
              <td class="p-4 text-sm font-medium text-gray-700">Jose Val Eowyn Laurente</td>
              <td class="p-4 text-sm text-gray-500">Bulacan, San Miguel City</td>
              <td class="p-4 text-sm text-gray-500">09837488827</td>
              <td class="p-4 text-sm text-gray-500">SeaGas</td>
              <td class="p-4 text-sm text-gray-500">1</td>
              <td class="p-4">
                <span class="bg-status-delivered text-sm px-3 py-1 rounded font-bold">Delivered</span>
              </td>
            </tr>
            <tr class="border-b border-gray-200">
              <td class="p-4"><input type="checkbox" class="w-4 h-4 rounded text-[#CF0000] focus:ring-[#CF0000]"></td>
              <td class="p-4 text-sm font-medium text-gray-700">004</td>
              <td class="p-4 text-sm font-medium text-gray-700">Evrri Elefante</td>
              <td class="p-4 text-sm text-gray-500">Nueva Ecija, Gapan City</td>
              <td class="p-4 text-sm text-gray-500">09123456789</td>
              <td class="p-4 text-sm text-gray-500">Petron</td>
              <td class="p-4 text-sm text-gray-500">3</td>
              <td class="p-4">
                <span class="bg-status-returned text-sm px-3 py-1 rounded font-bold">Returned</span>
              </td>
            </tr>
            <tr class="border-b border-gray-200">
              <td class="p-4"><input type="checkbox" class="w-4 h-4 rounded text-[#CF0000] focus:ring-[#CF0000]"></td>
              <td class="p-4 text-sm font-medium text-gray-700">005</td>
              <td class="p-4 text-sm font-medium text-gray-700">Jaztin Supsup</td>
              <td class="p-4 text-sm text-gray-500">Bantug, Bukang Liwayway City</td>
              <td class="p-4 text-sm text-gray-500">09827371738</td>
              <td class="p-4 text-sm text-gray-500">SeaGas</td>
              <td class="p-4 text-sm text-gray-500">1</td>
              <td class="p-4">
                <span class="bg-status-borrowed text-sm px-3 py-1 rounded font-bold">Borrowed</span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>

<!-- Chart.js Library -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<!-- Set current date - enUS format (ex: April 01, 2001) -->
<script>
  const options = { year: 'numeric', month: 'long', day: '2-digit' };
  document.getElementById('currentDate').textContent = new Date().toLocaleDateString('en-US', options);

  // Initialize Pie Chart
  const ctx = document.getElementById('brandsPieChart').getContext('2d');
  const brandsPieChart = new Chart(ctx, {
    type: 'doughnut',
    data: {
      labels: ['Petron', 'Econo', 'SeaGas'],
      datasets: [{
        data: [60, 25, 15],
        backgroundColor: [
          '#EF4444', // Red for Petron
          '#3B82F6', // Blue for Econo
          '#22C55E'  // Green for SeaGas
        ],
        borderWidth: 0
      }]
    },
    options: {
      responsive: true,
      maintainAspectRatio: true,
      plugins: {
        legend: {
          display: false // Hide default legend since we have custom legend
        },
        tooltip: {
          callbacks: {
            label: function (context) {
              return context.label + ': ' + context.parsed + '%';
            }
          }
        }
      }
    }
  });
</script>

<?php require '../layout/footer.php' ?>