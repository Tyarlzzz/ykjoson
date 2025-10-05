<?php include '../layout/header.php';?>

<main class="font-[Switzer] flex-1 p-8 bg-gray-50 overflow-auto">
  <div class="w-full px-8">
    <div class="mb-8 flex justify-between items-center">
      <h1 class="ps-3 text-3xl font-extrabold border-l-4 border-gray-900 text-gray-800">Manage Riders</h1>
      <p class="text-gray-500 text-base" id="currentDate"></p>
    </div>

    <div class="flex space-x-2">
      <button class="px-2 bg-gray-200 text-gray-700 font-semibold rounded-t-lg">Inventory</button>
      <button class="px-2 bg-gray-200 text-gray-700 font-semibold rounded-t-lg">Sales Report</button>
      <button class="px-2 bg-red-600 text-white font-semibold rounded-t-lg">Riders</button> 
      <button class="px-2 bg-gray-200 text-gray-700 font-semibold rounded-t-lg">Archived</button>       
    </div>

    <!-- Full-width card form -->
    <div class="w-full bg-white rounded-lg shadow-md p-8 border border-gray-200">
      <div class="w-full">
        <div class="flex gap-6 mb-6">
          <div class="flex-1">
            <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Full Name</label>
            <input type="text" id="fullName" name="fullName" placeholder="Enter full name"
              class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500 text-gray-800 font-medium">
          </div>
          <div class="flex-1">
            <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Number</label>
            <input type="text" id="phoneNumber" name="phoneNumber" placeholder="Enter phone number"
              class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500 text-gray-800 font-medium">
          </div>
        </div>

        <div class="mb-6">
          <label class="block text-xs font-bold text-gray-700 mb-3 uppercase tracking-wide">Home Address</label>
          <input type="text" id="address" name="address" placeholder="Enter home address"
            class="w-full px-4 py-3 border-2 border-gray-800 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-100 focus:border-blue-500 text-gray-800 font-medium">
        </div>
      </div>

    <div class="flex justify-end my-4">
        <button type="submit"               
            class="bg-red-500 hover:bg-red-600 text-white p-3 rounded-2xl font-semibold flex items-center justify-center gap-2 transition">
            <div class="bg-white rounded-full p-1 flex items-center justify-center">
                <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                </svg>
            </div>
        <span>Add Rider</span>
    </div>

      <div class="bg-white rounded-lg shadow-md px-8 py-4 border border-gray-200">
          <div class="flex justify-between items-center mb-4">
              <h2 class="text-xl font-bold text-gray-800">Rider's List</h2>
                <div class="relative flex items-center">
                      <input type="text" placeholder="Search"
                      class="border border-gray-300 rounded-lg py-2 pl-10 pr-4 focus:outline-none focus:ring-2 focus:ring-gray-500">
                      <svg class="w-5 h-5 absolute left-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                      xmlns="http://www.w3.org/2000/svg">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                      </svg>
                </div>
          </div>
        <div>
          <table class="w-full table-auto">
            <thead>
              <tr>
                <th class="px-4 py-2">#</th>
                <th class="px-4 py-2">Name</th>
                <th class="px-4 py-2">Phone Number</th>
                <th class="px-4 py-2">Action</th>
              </tr>
            </thead>
            <tbody id="ridersTable">
        </div>
      </div>
    </div>
  </div>
</main>

<script src="../assets/js/gas_system_js/gasManageRiders.js"></script>

<?php include '../layout/footer.php';?>
