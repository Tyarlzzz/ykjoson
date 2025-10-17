<?php include '../layout/header.php'; ?>

<?php
// Get rider details 
$riderId = isset($_GET['id']) ? trim($_GET['id']) : '';
$riderName = isset($_GET['name']) ? trim($_GET['name']) : '';
$riderPhone = isset($_GET['phone']) ? trim($_GET['phone']) : '';
$riderAddress = isset($_GET['address']) ? trim($_GET['address']) : '';
?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-8">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Inventory & Sales Report</h1>
      <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex mb-0">
      <a href="updateInventory.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold rounded-t-2xl z-0">Inventory</a>
      <a href="salesReport.php"
        class="px-5 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Sales
        Report</a>
      <a href="manageRiders.php"
        class="px-10 py-1 bg-red-600 text-white font-semibold rounded-t-2xl -ml-3 z-0">Riders</a>
      <a href="archived.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
      <a href="expenses.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Expenses</a>
    </div>

    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <form action="updateRider.php" method="POST" id="orderForm">
        <div class="bg-white rounded-2xl shadow-xl p-6 sticky top-8">
          <div class="mb-6">
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Full
                  Name</label>
                <input type="text" id="fullName" name="fullName" placeholder="Enter full name"
                  value="<?= htmlspecialchars($riderName) ?>"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
              <div>
                <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Number</label>
                <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Enter phone number"
                  value="<?= htmlspecialchars($riderPhone) ?>"
                  class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
              </div>
            </div>
            <div class="mb-4">
              <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">
                Address</label>
              <input type="text" id="address" name="address" placeholder="Enter address"
                value="<?= htmlspecialchars($riderAddress) ?>"
                class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-50 focus:border-blue-500 text-gray-800 font-medium">
            </div>

            <div class="flex items-end mt-6 justify-end gap-3">
              <?php if ($riderId !== ''): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($riderId) ?>">
              <?php endif; ?>

              <button type="button" onclick="window.location.href='manageRiders.php'"
                class="bg-gray-500 text-white p-3 rounded-xl transition">
                <span>Cancel</span>
              </button>

              <button type="submit"
                class="bg-blue-500 text-white p-3 rounded-xl flex items-center gap-2">
                <div class="bg-white rounded-full p-1 flex items-center justify-center">
                  <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                  </svg>
                </div>
                <span>Update Rider</span>
              </button>
            </div>
          </div>

          <div class="flex items-center">
            <h2 class="text-lg font-bold text-gray-700">Rider Lists</h2>
            <div class="flex-1 h-px bg-black ml-4"></div>
          </div>

          <div class="mt-4 max-h-64 overflow-y-auto overflow-x-auto">
            <table class="w-full table-auto border-collapse">
              <thead>
                <tr class="bg-gray-200">
                  <th class="px-4 py-2 border-b border-gray-300 text-left">#</th>
                  <th class="px-4 py-2 border-b border-gray-300 text-left">Name</th>
                  <th class="px-4 py-2 border-b border-gray-300 text-left">Phone Number</th>
                  <th class="px-4 py-2 border-b border-gray-300 text-left">Address</th>
                  <th class="px-4 py-2 border-b border-gray-300 text-center">Actions</th>
                </tr>
              </thead>
              <tbody>
                <?php
                // Example riders (replace with DB query) - added more data for testing
                $riders = [
                  ['id' => 1, 'name' => 'Erik Soliman', 'phone' => '09171234567', 'address' => '123 Bagong Sikat, Science City of Munoz'],
                  ['id' => 2, 'name' => 'Jane Cruz', 'phone' => '09181234567', 'address' => '243 Caanawan, San Jose City'],
                  ['id' => 3, 'name' => 'Rommel Cruz', 'phone' => '09191234567', 'address' => '243 Caanawan, San Jose City'],
                  ['id' => 4, 'name' => 'Rouelyn Joson', 'phone' => '09201234567', 'address' => '123 Bagong Sikat, Science City of Munoz'],
                  ['id' => 5, 'name' => 'Aj Castro ', 'phone' => '09211234567', 'address' => '123 Bagong Sikat, Science City of Munoz'],
                  ['id' => 6, 'name' => 'Danielle Quiambao', 'phone' => '09221234567', 'address' => '123 Bagong Sikat, Science City of Munoz'],
                  ['id' => 7, 'name' => 'Charles Carpio', 'phone' => '09231234567', 'address' => '142 CLSU Village'],
                  ['id' => 8, 'name' => 'Jose Eowyn', 'phone' => '09241234567', 'address' => '123 Bagong Sikat, Science City of Munoz'],
                  ['id' => 9, 'name' => 'Eurri Martinez', 'phone' => '09251234567', 'address' => '123 Bagong Sikat, Science City of Munoz'],
                ];
                foreach ($riders as $rider): ?>
                  <tr class="hover:bg-gray-100">
                    <td class="px-4 py-2 border-b border-gray-300">
                      <?= htmlspecialchars($rider['id']) ?>
                    </td>
                    <td class="px-4 py-2 border-b border-gray-300">
                      <?= htmlspecialchars($rider['name']) ?>
                    </td>
                    <td class="px-4 py-2 border-b border-gray-300">
                      <?= htmlspecialchars($rider['phone']) ?>
                    </td>
                    <td
                      class="px-4 py-2 border-b border-gray-300 truncate overflow-hidden text-ellipsis whitespace-nowrap max-w-[200px]">
                      <?= htmlspecialchars($rider['address']) ?>
                    </td>
                    <td class="px-4 py-2 border-b border-gray-300 text-center">
                      <a
                        href="editRider.php?id=<?= $rider['id'] ?>&name=<?= urlencode($rider['name']) ?>&phone=<?= urlencode($rider['phone']) ?>&address=<?= urlencode($rider['address']) ?>">
                        <button type="button"
                          class="bg-yellow-400 text-white px-3 py-1 rounded-lg transition">
                          Edit
                        </button>
                      </a>
                      <a href="deleteRider.php?id=<?= $rider['id'] ?>"
                        onclick="return confirm('Are you sure you want to delete this rider?');">
                        <button type="button" class="bg-red-500 text-white px-3 py-1 rounded-lg transition">
                          Delete
                        </button>
                      </a>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>

          </div>
      </form>
    </div>
  </div>
</main>

<script src="../assets/js/gas_system_js/gasManageRiders.js"></script>

<?php include '../layout/footer.php'; ?>