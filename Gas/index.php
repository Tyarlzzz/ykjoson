<?php
require_once '../database/Database.php';
require_once '../Models/GasCustomer.php';
require_once '../Models/GasOrder.php';
require_once '../Models/Models.php';

$database = new Database();
$conn = $database->getConnection();
Model::setConnection($conn);

$gas = Gas::all();

?>

<?php require '../layout/header.php' ?>

<main class="font-[Switzer] flex-1 bg-gray-50 overflow-auto p-6">
  <div class="w-full">
    <!-- Header -->
    <div class="mb-8 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Point of Sale System</h1>
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
          <div class="text-3xl font-bold text-gray-900">
            <?php
            $borrowedOrders = GasOrder::countBorrowed();
            echo $borrowedOrders;
            ?>
          </div>
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
          <div class="text-3xl font-bold text-gray-900">
            <?php
            $deliveredOrders = GasOrder::countDelivered();
            echo $deliveredOrders;
            ?>
          </div>
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
        <button onclick="window.location.href='create.php'"
          class="bg-gradient-to-br from-red-400 via-red-500 to-red-600 hover:bg-[#DC2626] text-white text-lg font-semibold flex-1 rounded-2xl shadow-lg flex items-center justify-center gap-2 transition-all border border-gray-100">
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
          <span class="text-3xl font-bold mt-1">
            <?php
            $deliveredOrders = GasOrder::countDelivered();
            echo $deliveredOrders;
            ?>
          </span>
        </div>
      </div>
    </div>

    <!-- START NG TABLE -->
    <div class="md:flex-row max-w-full mx-auto p-6 bg-white rounded-xl shadow-lg px-6">
      <div class="flex items-center justify-between mb-4">
        <h2 class="text-xl font-[Outfit] space-x-2">Today's Orders&nbsp;&nbsp;<span
            class="font-[Switzer] text-sm"><?php echo date("F j, Y"); ?></span></h2>
        <div class="flex items-center space-x-3">
          <div class="relative">
            <svg class="w-5 h-5 absolute left-3 top-1/2 -translate-y-1/2 text-gray-400"
              xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-4.35-4.35m0 0A7.5 7.5 0 1110.5 3a7.5 7.5 0 016.15 13.65z" />
            </svg>
            <input id="customSearch" type="text" placeholder="Search..."
              class="pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-400 w-64" />
          </div>
          <select id="statusFilter"
            class="border border-white rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-400">
            <option value="">All Status</option>
            <option value="Delivered">Delivered</option>
            <option value="Pending">Pending</option>
            <option value="Borrowed">Borrowed</option>
            <option value="Returned">Returned</option>
          </select>
          <div>
            <a href=""> <!-- eto ung maximize button kasi clickable toh-->
              <svg class="w-6 h-6" version="1.1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 80" fill="none"
                x="0px" y="0px">
                <path
                  d="M40.2196 2C39.115 2 38.2196 2.89543 38.2196 4C38.2196 5.10457 39.115 6 40.2196 6H55.1716L27.5858 33.5858C26.8047 34.3668 26.8047 35.6332 27.5858 36.4142C28.3668 37.1953 29.6332 37.1953 30.4142 36.4142L58 8.82843V24C58 25.1046 58.8954 26 60 26C61.1046 26 62 25.1046 62 24V4C62 2.89543 61.1046 2 60 2H40.2196Z"
                  fill="black" />
                <path
                  d="M52 37C52 35.8954 51.1046 35 50 35C48.8954 35 48 35.8954 48 37V56C48 57.1046 47.1046 58 46 58H8C6.89543 58 6 57.1046 6 56L6 18C6 16.8954 6.89543 16 8 16L27 16C28.1046 16 29 15.1046 29 14C29 12.8954 28.1046 12 27 12L8 12C4.68629 12 2 14.6863 2 18L2 56C2 59.3137 4.68629 62 8 62H46C49.3137 62 52 59.3137 52 56V37Z"
                  fill="black" />
              </svg>
            </a>
          </div>
        </div>
      </div>
      <div class="overflow-x-auto overflow-y-auto" style="height: 370px;">
        <table id="ordersTable" class="w-full">
          <thead>
            <!-- SAMPLE DATA LANG ITONG MGA NILAGAY KO DAPAT NAKA FOR EACH NA YAN -->
            <tr>
              <th>#</th>
              <th>Name</th>
              <th>Location</th>
              <th>Phone Number</th>
              <th>Qty</th>
              <th>Status</th> <!--NAKA AUTO CHANGE COLOR NARIN TOH KAYA WALA NA KAYO PROBLEMA -->
            </tr>
          </thead>
          <tbody>
            <!-- 
                //SAMPLE DATA LANG ITONG MGA NILAGAY KO DAPAT NAKA FOR EACH NA YAN
                //PAKI DELETE UNG IBANG TR KASI NDI NMN NA NEED KAPAG NAG FOR EACH NA 
                -->
            <tr>
              <td>1</td>
              <td>Erik Soliman</td>
              <td>Brgy. San Isidro, Gapan City, Nueva Ecija</td>
              <td>09123456789</td>
              <td>3</td>
              <td>Delivered</td>
            </tr>
            <tr>
              <td>2</td>
              <td>Charles Jerald Capulong Carpio</td>
              <td>Dorm 6, Room 69, CLSU Philippines</td>
              <td>09987654321</td>
              <td>7</td>
              <td>Pending</td>
            </tr>
            <tr>
              <td>3</td>
              <td>Danielle Quiambao</td>
              <td>Kapitan Pepe, Cabanatuan City, Nueva Ecija</td>
              <td>09987654321</td>
              <td>12</td>
              <td>Returned</td>
            </tr>
            <tr>
              <td>4</td>
              <td>Eurrie Elepantine</td>
              <td>Bagong Sikat, Science City of Munoz, Nueva Ecija</td>
              <td>09987654321</td>
              <td>28</td>
              <td>Returned</td>
            </tr>
            <tr>
              <td>5</td>
              <td>Aj Castro</td>
              <td>Bukang Liwayway, Bantu, Science City of Munoz, Nueva Ecija</td>
              <td>09987654321</td>
              <td>16</td>
              <td>Borrowed</td>
            </tr>
            <tr>
              <td>6</td>
              <td>Jaztin Zuriel Supsuo</td>
              <td>Sapang Cawayan, Bantug, Science City of Munoz, Nueva Ecija</td>
              <td>09987654321</td>
              <td>32</td>
              <td>Borrowed</td>
            </tr>
            <tr>
              <td>7</td>
              <td>Jose Val Eowyn Laurente</td>
              <td>Bukang Liwayway, Bantug, Science City of Munoz, Nueva Ecija</td>
              <td>09987654321</td>
              <td>32</td>
              <td>Delivered</td>
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