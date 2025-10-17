<?php require '../layout/header.php' ?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-8">
    <div class="mb-6 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Archived Transactions</h1>
      <p class="text-gray-500 text-base"><?php echo date('F j, Y'); ?></p>
    </div>

    <!-- Navigation Tabs -->
    <div class="flex mb-0">
      <a href="updateInventory.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold rounded-t-2xl z-0">Inventory</a>
      <a href="salesReport.php"
        class="px-5 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Sales
        Report</a>
      <a href="../Rider/manageRiders.php"
        class="px-10 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-0">Riders</a>
      <a href="archived.php"
        class="px-8 py-1 bg-red-600 text-white font-semibold border-gray-400 rounded-t-2xl -ml-3 z-10">Archived</a>
      <a href="expenses.php"
        class="px-8 py-1 bg-gray-300 text-gray-700 font-semibold border-l-2 border-gray-400 rounded-t-2xl -ml-3 z-10">Expenses</a>
    </div>

    <!-- Container -->
    <div class="w-full bg-white rounded-lg rounded-tl-none shadow-md border border-gray-200 overflow-hidden">
      <div class="py-8 px-4">
      <div class="bg-white p-6 rounded-lg">
      <div class="flex justify-end items-center mb-4 ">
        <div class="flex items-center gap-3">
          <div class="relative flex items-center">
            <input type="text" placeholder="Search"
              class="border border-gray-300 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-gray-300">
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
              <th class="p-4">#</th>
              <th class="p-4">Date</th>
              <th class="p-4">Name</th>
              <th class="p-4">Phone Number</th>
              <th class="p-4">Quantity</th>
              <th class="p-4">Price</th>
            </tr>
          </thead>
            <tbody>
              <!-- AS OF NOW ONLY USER INTERFACE PALANG -->
              <tr>
                  <td>0001</td>
                  <td>August 1, 2025</td> 
                  <td>Jaztin Zuriel</td>
                  <td>09359232950</td>
                  <td>4</td>
                  <td>P100</td>
              </tr>
          </tbody>
        </table>
      </div>
      </div>
      </div>
    </div>
  </div>
</main>

<?php require '../layout/footer.php' ?>