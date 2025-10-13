<?php
    require '../layout/header.php';
    require_once '../database/Database.php';
    require_once '../Models/Models.php';
    require_once '../Models/Order.php';
    require_once '../Models/GasCustomer.php';
    require_once '../Models/GasOrder.php';

    $database = new Database();
    $conn = $database->getConnection();
    Model::setConnection($conn);

    $gasOrders = GasOrder::getAllOrdersWithDetails();
?>

<main class=" font-[Switzer] flex-1 p-8">
  <div class="w-full px-6">
    <div class="mb-8 flex justify-between items-center">
      <h1 class="text-3xl font-extrabold text-gray-800">Orders List</h1>
      <p class="text-gray-500 text-base" id="currentDate"></p>
    </div>

    <div class="flex items-center justify-between mb-4">
      <button
        class="bg-[#19B900] text-white px-6 py-2 rounded-xl shadow-md font-semibold flex items-center gap-2 hover:bg-green-700 transition-colors">
        Change Status
      </button>

      <button
        class="bg-[#CF0000] text-white px-6 py-2 rounded-xl shadow-md font-semibold flex items-center gap-2 hover:bg-red-700 transition-colors">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        Add Order
      </button>
    </div>

    <div class="bg-white p-6 rounded-lg shadow-md">
      <div class="flex justify-between items-center mb-4">
        <h2 class="text-xl font-bold text-gray-800">Today's Transactions</h2>
        <div class="flex items-center gap-3">
          <button
            class="bg-red-500 text-white px-4 py-2 rounded-lg font-semibold flex items-center gap-2 hover:bg-red-600 transition-colors">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"
            xmlns="http://www.w3.org/2000/svg">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
              </path>
            </svg>
            Delete
          </button>
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
              <?php if ($gasOrders && count($gasOrders) > 0): ?>
                  <?php $counter = 1; ?>
                  <?php foreach ($gasOrders as $order): ?>
                      <tr>
                          <td class="p-4">
                              <input type="checkbox">
                          </td>
                          <td><?php echo $counter++; ?></td>
                          <td><?php echo $order['fullname']; ?></td>
                          <td><?php echo $order['address']; ?></td>
                          <td><?php echo $order['phone_number']; ?></td>
                          <td><?php echo $order['brands'] ?? 'N/A'; ?></td>
                          <td><?php echo $order['total_quantity']; ?></td>
                          <td class="p-4">
                              <span class="text-sm px-3 py-1 rounded font-medium <?php echo GasOrder::getStatusColorClass($order['status']); ?>">
                                  <?php echo $order['status']; ?>
                              </span>
                          </td>
                      </tr>
                  <?php endforeach; ?>
              <?php else: ?>
                  <tr>
                      <td colspan="8" class="text-center py-8 text-gray-500 italic">
                          No gas orders found
                      </td>
                  </tr>
              <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</main>


<?php include '../layout/footer.php' ?>