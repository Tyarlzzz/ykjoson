<?php 
require '../layout/header.php';
require_once '../database/Database.php';
require_once '../Models/Models.php';
require_once '../Models/LaundryArchivedOrder.php';

// Initialize database connection
$database = new Database();
$conn = $database->getConnection();
Model::setConnection($conn);

// Get all archived orders
$archivedOrders = LaundryArchivedOrder::getAllArchived();

// Get count of archived orders
$totalArchived = $archivedOrders ? count($archivedOrders) : 0;
?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-8">
    <div class="mb-6 flex justify-between items-center">
      <div>
        <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Archived Orders</h1>
        <p class="ps-3 text-sm text-gray-600 mt-1">Total archived orders: <?php echo $totalArchived; ?></p>
      </div>
      <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
    </div>

    <!-- Navigation Tabs -->
        <div class="flex mb-0">
            <a href="expenses.php"
            class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold rounded-t-2xl z-0">Expenses</a>
            <a href="salesReport.php" class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Sales
            Report</a>
            <a href="pricing.php"
            class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Pricing</a>
            <a href="../Rider/ManageRiders.php"
            class="px-10 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Riders</a>
            <a href="archived.php"
            class="px-8 py-1 bg-blue-600 text-white font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
        </div>


    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <div class="py-8 px-4">
      <div class="bg-white p-6 rounded-lg">
      <div class="flex justify-end items-center mb-4 ">
        <div class="flex items-center gap-3">
          <div class="relative flex items-center">
            <input type="text" id="searchInput" placeholder="Search by name, phone, or order ID"
              class="border border-gray-300 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-blue-300 w-80">
            <svg class="w-5 h-5 absolute left-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
              xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
            </svg>
          </div>
        </div>
      </div>

      <div class="overflow-x-auto rounded-xl border border-gray-500">
        <table id="ordersTable" class="w-full text-left table-auto ">
          <thead class="bg-gray-50">
            <tr class="text-sm text-black-500 uppercase">
              <th class="p-4 text-left">Order #</th>
              <th class="p-4 text-left">Date Created</th>
              <th class="p-4 text-left">Date Delivered</th>
              <th class="p-4 text-left">Customer Name</th>
              <th class="p-4 text-left">Phone Number</th>
              <th class="p-4 text-center">Weight</th>
              <th class="p-4 text-right">Total Price</th>
            </tr>
          </thead>
            <tbody>
              <?php if ($archivedOrders): ?>
                  <?php foreach ($archivedOrders as $index => $order): ?>
                      <tr class="border-b border-gray-200 hover:bg-gray-50 transition-colors duration-200">
                          <td class="p-4 text-gray-800 font-mono"><?php echo str_pad($order->order_id, 4, '0', STR_PAD_LEFT); ?></td>
                          <td class="p-4 text-gray-600"><?php echo date('M j, Y', strtotime($order->date_created)); ?></td>
                          <td class="p-4 text-gray-600"><?php echo date('M j, Y', strtotime($order->date_delivered)); ?></td>
                          <td class="p-4 text-gray-800 font-medium"><?php echo htmlspecialchars($order->fullname); ?></td>
                          <td class="p-4 text-gray-600"><?php echo htmlspecialchars($order->phone_number); ?></td>
                          <td class="p-4 text-gray-600 text-center"><?php echo number_format($order->total_weight, 1); ?> kg</td>
                          <td class="p-4 text-gray-800 font-semibold text-right">₱<?php echo number_format($order->total_price, 2); ?></td>
                      </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="7" class="p-8 text-center text-gray-500">
                          <div class="flex flex-col items-center">
                              <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                              </svg>
                              <p class="text-lg font-medium">No archived orders found</p>
                              <p class="text-sm">Completed orders will appear here</p>
                          </div>
                      </td>
                  </tr>
              <?php endif; ?>
          </tbody>
        </table>
      </div>
      </div>
      </div>
    </div>
  </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const table = document.getElementById('ordersTable');
    const tbody = table.getElementsByTagName('tbody')[0];
    const rows = Array.from(tbody.getElementsByTagName('tr'));

    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase().trim();

        rows.forEach(row => {
            // Skip the "no data" row
            if (row.cells.length === 1) return;

            const orderID = row.cells[0].textContent.toLowerCase();
            const name = row.cells[3].textContent.toLowerCase();
            const phone = row.cells[4].textContent.toLowerCase();

            const matches = orderID.includes(searchTerm) || 
                          name.includes(searchTerm) || 
                          phone.includes(searchTerm);

            row.style.display = matches ? '' : 'none';
        });

        // Show/hide "no results" message
        const visibleRows = rows.filter(row => row.style.display !== 'none' && row.cells.length > 1);
        if (visibleRows.length === 0 && searchTerm) {
            // Show no results message
            if (!document.getElementById('noResultsRow')) {
                const noResultsRow = document.createElement('tr');
                noResultsRow.id = 'noResultsRow';
                noResultsRow.innerHTML = `
                    <td colspan="7" class="p-8 text-center text-gray-500">
                        <div class="flex flex-col items-center">
                            <svg class="w-12 h-12 mb-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                            <p class="text-lg font-medium">No results found</p>
                            <p class="text-sm">Try adjusting your search terms</p>
                        </div>
                    </td>
                `;
                tbody.appendChild(noResultsRow);
            }
        } else {
            // Remove no results message
            const noResultsRow = document.getElementById('noResultsRow');
            if (noResultsRow) {
                noResultsRow.remove();
            }
        }
    });
});
</script>

<?php require '../layout/footer.php' ?>